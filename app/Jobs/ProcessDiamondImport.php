<?php

namespace App\Jobs;

use App\Models\Diamond;
use App\Models\JobTrack;
use App\Exports\FailedDiamondsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ImportCompleted;
use App\Services\CurrencyService;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProcessDiamondImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1; // Don't retry imports

    protected $jobTrackId;
    protected $filePath;
    protected $adminId;
    protected $debug;
    protected $thresholdMs;
    protected $existingLotNos = [];
    protected $existingSKUs = [];
    protected $skipBarcodeFile = false;
    protected $currencyService;

    /**
     * Create a new job instance.
     */
    public function __construct($jobTrackId, $filePath, $adminId)
    {
        $this->jobTrackId = $jobTrackId;
        $this->filePath = $filePath;
        $this->adminId = $adminId;
        $this->debug = (bool) env('IMPORT_DEBUG', true);
        $this->thresholdMs = (int) env('IMPORT_DEBUG_THRESHOLD_MS', 150); // log steps slower than 150ms
        $this->skipBarcodeFile = (bool) env('IMPORT_SKIP_BARCODE_FILE', false); // Skip file write for speed
    }

    protected function dbg($message, array $context = [])
    {
        if ($this->debug) {
            $context['job_track_id'] = $this->jobTrackId;
            $context['mem_mb'] = round(memory_get_usage(true) / 1048576, 2);
            Log::debug('[IMPORT]', array_merge(['msg' => $message], $context));
        }
    }

    protected function msSince($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobTrack = JobTrack::find($this->jobTrackId);

        if (!$jobTrack) {
            Log::error('JobTrack not found', ['id' => $this->jobTrackId]);
            return;
        }

        try {
            $tAll = microtime(true);
            // Update status to processing
            $jobTrack->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            Log::info('Starting import job', ['job_track_id' => $this->jobTrackId]);
            $this->dbg('Job started');

            // Initialize currency service for INR to USD conversion
            $this->currencyService = app(CurrencyService::class);

            // Load existing lot_nos and SKUs for faster duplicate checking (memory-based)
            $tPreload = microtime(true);
            $this->existingLotNos = Diamond::pluck('lot_no')->toArray();
            $this->existingSKUs = Diamond::pluck('sku')->toArray();
            $preloadMs = $this->msSince($tPreload);
            $this->dbg('Preloaded existing data', ['lot_nos' => count($this->existingLotNos), 'skus' => count($this->existingSKUs), 'ms' => $preloadMs]);

            // Resolve and validate file path (works across OS & queue context)
            $resolvedPath = $this->filePath;
            $originalPath = $this->filePath;

            if (!file_exists($resolvedPath)) {
                $relative = str_replace([storage_path('app') . DIRECTORY_SEPARATOR, storage_path('app/')], '', $resolvedPath);
                $relative = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relative);

                if (\Illuminate\Support\Facades\Storage::disk('local')->exists(str_replace(DIRECTORY_SEPARATOR, '/', $relative))) {
                    $resolvedPath = \Illuminate\Support\Facades\Storage::disk('local')->path(str_replace(DIRECTORY_SEPARATOR, '/', $relative));
                } else {
                    throw new \Exception("Source file not found. Expected at: {$originalPath}. The file may have been moved or deleted.");
                }
            }
            $this->dbg('Resolved import path', ['path' => $resolvedPath]);

            // Read Excel/CSV file
            $tRead = microtime(true);
            $rows = Excel::toArray([], $resolvedPath)[0] ?? [];
            $this->dbg('File read complete', ['ms' => $this->msSince($tRead), 'rows_including_header' => count($rows)]);

            if (empty($rows)) {
                throw new \Exception('Excel file is empty or invalid');
            }

            // Remove header row
            $header = array_shift($rows);

            // Normalize headers: convert to snake_case (e.g., "Diamond Type" -> "diamond_type")
            $header = array_map(function ($h) {
                if ($h === null)
                    return '';
                $h = trim((string) $h);
                // Replace spaces and dashes with underscores, lowercase everything
                $h = strtolower(preg_replace('/[\s\-]+/', '_', $h));
                // Remove any non-alphanumeric characters except underscores
                $h = preg_replace('/[^a-z0-9_]/', '', $h);
                return $h;
            }, $header);

            $totalRows = count($rows);

            // Update total rows
            $jobTrack->update(['total_rows' => $totalRows]);
            $this->dbg('Header parsed & total rows set', ['total_rows' => $totalRows, 'header' => $header]);

            $successCount = 0;
            $failedRows = [];

            // Process each row
            $tLoop = microtime(true);
            foreach ($rows as $index => $row) {
                try {
                    $tRow = microtime(true);
                    // Convert header to keys
                    $data = array_combine($header, $row);

                    // Skip empty rows
                    if (empty(array_filter($data))) {
                        $this->dbg('Skipping empty row', ['row_number' => $index + 2]);
                        continue;
                    }

                    // Validate and import
                    $this->importRow($data, $index + 2); // +2 because of header and 0-index
                    $successCount++;
                    $tRowMs = $this->msSince($tRow);
                    if ($tRowMs > $this->thresholdMs) {
                        $this->dbg('Row processing slow', ['row_number' => $index + 2, 'ms' => $tRowMs]);
                    }

                } catch (\Exception $e) {
                    $failedRows[] = [
                        'row' => $index + 2,
                        'data' => $data ?? $row,
                        'error' => $e->getMessage(),
                    ];
                    $this->dbg('Row failed', ['row_number' => $index + 2, 'error' => $e->getMessage()]);
                }

                // Update progress every 10 rows
                if (($index + 1) % 10 == 0) {
                    $processed = $index + 1;
                    $jobTrack->update([
                        'processed_rows' => $processed,
                        'successful_rows' => $successCount,
                        'failed_rows' => count($failedRows),
                        'progress_percentage' => round(($processed / $totalRows) * 100),
                    ]);
                    $this->dbg('Progress update', [
                        'processed' => $processed,
                        'success' => $successCount,
                        'failed' => count($failedRows),
                        'loop_ms' => $this->msSince($tLoop)
                    ]);
                    $tLoop = microtime(true);
                }
            }

            // Final update
            $jobTrack->update([
                'processed_rows' => $totalRows,
                'successful_rows' => $successCount,
                'failed_rows' => count($failedRows),
                'progress_percentage' => 100,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Generate error report if there are failures
            if (count($failedRows) > 0) {
                $this->generateErrorReport($jobTrack, $failedRows, $header);
            }

            // Send notification to admin
            $admin = \App\Models\Admin::find($this->adminId);
            if ($admin) {
                $admin->notify(new ImportCompleted($jobTrack));
            }

            Log::info('Import job completed', [
                'job_track_id' => $this->jobTrackId,
                'success' => $successCount,
                'failed' => count($failedRows),
            ]);
            $this->dbg('Job completed', [
                'total_ms' => $this->msSince($tAll),
                'success' => $successCount,
                'failed' => count($failedRows),
                'peak_mem_mb' => round(memory_get_peak_usage(true) / 1048576, 2)
            ]);

        } catch (\Exception $e) {
            Log::error('Import job failed', [
                'job_track_id' => $this->jobTrackId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dbg('Job failed', ['error' => $e->getMessage()]);

            $jobTrack->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Send failure notification
            $admin = \App\Models\Admin::find($this->adminId);
            if ($admin) {
                $admin->notify(new ImportCompleted($jobTrack));
            }
        }
    }

    /**
     * Import a single row
     */
    protected function importRow(array $data, int $rowNumber)
    {
        $tRow = microtime(true);
        // Prepare data
        $lotNo = (string) ($data['lot_no'] ?? '');
        $sku = (string) ($data['sku'] ?? '');

        // Validate required fields
        if (empty($lotNo)) {
            throw new \Exception('lot_no is required');
        }
        if (empty($sku)) {
            throw new \Exception('sku is required');
        }

        // Check for duplicates (memory-based - much faster than DB query)
        if (in_array($lotNo, $this->existingLotNos, true)) {
            throw new \Exception("Duplicate lot_no: {$lotNo} already exists");
        }
        if (in_array($sku, $this->existingSKUs, true)) {
            throw new \Exception("Duplicate sku: {$sku} already exists");
        }

        // Build barcode
        $tBarcode = microtime(true);
        $barcodeNumber = $this->buildBarcodeNumber($lotNo);
        $barcodeImageUrl = $this->generateBarcodeDataUri($sku, $barcodeNumber);
        $barcodeMs = $this->msSince($tBarcode);
        if ($barcodeMs > $this->thresholdMs) {
            $this->dbg('Barcode generation slow', ['row' => $rowNumber, 'ms' => $barcodeMs]);
        }

        // Get price values and convert from INR to USD
        $perCt = $this->currencyService->inrToUsd($this->toNumericOrNull($data['per_ct'] ?? null));
        $purchasePrice = $this->currencyService->inrToUsd($this->toNumericOrNull($data['purchase_price'] ?? $data['price'] ?? null));
        $listingPrice = $this->currencyService->inrToUsd($this->toNumericOrNull($data['listing_price'] ?? null));
        $shippingPrice = $this->currencyService->inrToUsd($this->toNumericOrDefault($data['shipping_price'] ?? null, 0)) ?? 0;
        $durationPrice = $this->currencyService->inrToUsd($this->toNumericOrDefault($data['duration_price'] ?? null, 0)) ?? 0;
        $soldOutPrice = $this->currencyService->inrToUsd($this->toNumericOrNull($data['sold_out_price'] ?? null));
        $profit = $this->currencyService->inrToUsd($this->toNumericOrNull($data['profit'] ?? null));

        // Create diamond with converted prices
        $tCreate = microtime(true);
        Diamond::create([
            'lot_no' => $lotNo,
            'sku' => $sku,
            'material' => $data['material'] ?? null,
            'cut' => $data['cut'] ?? null,
            'clarity' => $data['clarity'] ?? null,
            'color' => $data['color'] ?? null,
            'shape' => $data['shape'] ?? null,
            'measurement' => $data['measurement'] ?? null,
            'weight' => $this->toNumericOrNull($data['weight'] ?? null),
            'per_ct' => $perCt,
            'purchase_price' => $purchasePrice,
            'margin' => $this->toNumericOrNull($data['margin'] ?? null),
            'listing_price' => $listingPrice,
            'shipping_price' => $shippingPrice,
            'purchase_date' => $this->parseExcelDate($data['purchase_date'] ?? null),
            'sold_out_date' => $this->parseExcelDate($data['sold_out_date'] ?? null),
            'is_sold_out' => $data['is_sold_out'] ?? 'IN Stock',
            'duration_days' => $this->toNumericOrDefault($data['duration_days'] ?? null, 0),
            'duration_price' => $durationPrice,
            'sold_out_price' => $soldOutPrice,
            'profit' => $profit,
            'sold_out_month' => $data['sold_out_month'] ?? null,
            'barcode_number' => $barcodeNumber,
            'barcode_image_url' => $barcodeImageUrl,
            'description' => $data['description'] ?? null,
            'note' => $data['note'] ?? null,
            'diamond_type' => $data['diamond_type'] ?? null,
            'admin_id' => $data['admin_id'] ?? null,
        ]);
        $createMs = $this->msSince($tCreate);
        if ($createMs > $this->thresholdMs) {
            $this->dbg('DB create slow', ['row' => $rowNumber, 'ms' => $createMs]);
        }

        // Update memory arrays to prevent false duplicates in same batch
        $this->existingLotNos[] = $lotNo;
        $this->existingSKUs[] = $sku;

        $totalRowMs = $this->msSince($tRow);
        if ($totalRowMs > ($this->thresholdMs * 2)) {
            $this->dbg('Total row time', ['row' => $rowNumber, 'ms' => $totalRowMs]);
        }
    }

    /**
     * Build barcode number using brand code and lot number
     */
    protected function buildBarcodeNumber(string $lotNo): string
    {
        $year = date('y');
        $brandCode = env('DIAMOND_BRAND_CODE', '100');

        // Extract numeric part from lot_no (e.g., "L0010078" -> "10078")
        $numericLot = preg_replace('/[^0-9]/', '', $lotNo);
        $numericLot = $numericLot ?: '0';
        $paddedLot = str_pad($numericLot, 6, '0', STR_PAD_LEFT);

        $baseBarcode = $year . $brandCode . $paddedLot;

        // Check for duplicates and append counter if needed
        $barcode = $baseBarcode;
        $counter = 1;
        while (Diamond::where('barcode_number', $barcode)->exists()) {
            $barcode = $baseBarcode . $counter;
            $counter++;
        }

        return $barcode;
    }

    /**
     * Parse Excel date (handles serial numbers and string formats)
     */
    protected function parseExcelDate($value)
    {
        if (empty($value) || $value === '' || $value === null) {
            return null;
        }

        // If numeric (Excel serial date)
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // If already a valid date string, try to parse it
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate barcode data URI (SVG as base64)
     * Optional: Skip file writing for faster import (file can be generated on-demand later)
     */
    protected function generateBarcodeDataUri($sku, $barcodeNumber)
    {
        try {
            $generator = new BarcodeGeneratorSVG();
            $barcodeSVG = $generator->getBarcode($barcodeNumber, $generator::TYPE_CODE_128, 2, 50);
            $dataUri = 'data:image/svg+xml;base64,' . base64_encode($barcodeSVG);

            // Only write file if not skipped (for performance optimization)
            if (!$this->skipBarcodeFile) {
                $publicDir = public_path('barcodes');
                if (!file_exists($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }
                $filePath = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $barcodeNumber . '.svg';
                file_put_contents($filePath, $barcodeSVG);
            }

            return $dataUri;
        } catch (\Exception $e) {
            Log::error('Barcode generation failed', [
                'sku' => $sku,
                'barcode_number' => $barcodeNumber,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate error report Excel file
     */
    protected function generateErrorReport(JobTrack $jobTrack, array $failedRows, array $header)
    {
        try {
            $fileName = 'failed_diamonds_' . $jobTrack->id . '_' . now()->format('Ymd_His') . '.xlsx';
            $filePath = 'imports/errors/' . $fileName;

            // Prepare data for export
            $exportData = [];
            foreach ($failedRows as $failure) {
                $row = [
                    'Original Row' => $failure['row'],
                    'Error Description' => $failure['error'],
                ];

                // Add original data columns
                foreach ($header as $column) {
                    $row[$column] = $failure['data'][$column] ?? '';
                }

                $exportData[] = $row;
            }

            // Export to Excel
            Excel::store(new FailedDiamondsExport($exportData), $filePath, 'public');

            // Update job track with error file path
            $jobTrack->update([
                'error_file_path' => $filePath,
            ]);

            Log::info('Error report generated', [
                'job_track_id' => $jobTrack->id,
                'file_path' => $filePath,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate error report', [
                'job_track_id' => $jobTrack->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Import job failed completely', [
            'job_track_id' => $this->jobTrackId,
            'error' => $exception->getMessage(),
        ]);

        $jobTrack = JobTrack::find($this->jobTrackId);
        if ($jobTrack) {
            $jobTrack->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Convert value to numeric or null (handles empty strings from Excel)
     */
    protected function toNumericOrNull($value)
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Convert value to numeric or default (handles empty strings from Excel)
     */
    protected function toNumericOrDefault($value, $default = 0)
    {
        if ($value === null || $value === '' || $value === false) {
            return $default;
        }
        return is_numeric($value) ? (float) $value : $default;
    }
}
