<?php

namespace App\Imports;

use App\Models\Diamond;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Throwable;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Services\CurrencyService;

class DiamondsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    SkipsEmptyRows
{
    private $rowCount = 0;
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];
    private $failures = [];
    private $currencyService;

    public function __construct()
    {
        $this->currencyService = app(CurrencyService::class);
    }

    /**
     * Prepare data before validation
     */
    public function prepareForValidation($data, $index)
    {
        // Convert lot_no and sku to string if they're numeric
        if (isset($data['lot_no'])) {
            $data['lot_no'] = (string) $data['lot_no'];
        }
        if (isset($data['sku'])) {
            $data['sku'] = (string) $data['sku'];
        }

        return $data;
    }

    public function model(array $row)
    {
        $this->rowCount++;

        // Skip truly empty rows
        if (empty(array_filter($row))) {
            return null;
        }

        try {
            $lotNo = (string) $row['lot_no'];
            $sku = (string) $row['sku'];

            // Build proper barcode number using lot_no
            $barcodeNumber = $this->buildBarcodeNumber($lotNo);

            // Generate barcode image
            $barcodeImageUrl = $this->generateBarcodeDataUri($sku, $barcodeNumber);

            // Get price values and convert from INR to USD
            $perCt = $this->toNumericOrNull($row['per_ct'] ?? null);
            $purchasePrice = $this->toNumericOrNull($row['purchase_price'] ?? $row['price'] ?? null);
            $listingPrice = $this->toNumericOrNull($row['listing_price'] ?? null);
            $shippingPrice = $this->toNumericOrDefault($row['shipping_price'] ?? null, 0);
            $soldOutPrice = $this->toNumericOrNull($row['sold_out_price'] ?? null);

            // Convert all price fields from INR to USD
            $perCt = $this->currencyService->inrToUsd($perCt);
            $purchasePrice = $this->currencyService->inrToUsd($purchasePrice);
            $listingPrice = $this->currencyService->inrToUsd($listingPrice);
            $shippingPrice = $this->currencyService->inrToUsd($shippingPrice) ?? 0;
            $soldOutPrice = $this->currencyService->inrToUsd($soldOutPrice);

            $diamond = new Diamond([
                'lot_no' => $lotNo,
                'sku' => $sku,
                'material' => $row['material'] ?? null,
                'cut' => $row['cut'] ?? null,
                'clarity' => $row['clarity'] ?? null,
                'color' => $row['color'] ?? null,
                'shape' => $row['shape'] ?? null,
                'measurement' => $row['measurement'] ?? null,
                'weight' => $this->toNumericOrNull($row['weight'] ?? null),
                'per_ct' => $perCt,
                'purchase_price' => $purchasePrice,
                'margin' => $this->toNumericOrNull($row['margin'] ?? null),
                'listing_price' => $listingPrice,
                'shipping_price' => $shippingPrice,
                'purchase_date' => $this->parseExcelDate($row['purchase_date'] ?? null),
                'sold_out_date' => $this->parseExcelDate($row['sold_out_date'] ?? null),
                // NOTE: is_sold_out, duration_days, duration_price, sold_out_month, profit
                // are calculated automatically by model's boot event - no need to set here
                'sold_out_price' => $soldOutPrice,
                'barcode_number' => $barcodeNumber,
                'barcode_image_url' => $barcodeImageUrl,
                'description' => $row['description'] ?? null,
                'note' => $row['note'] ?? null,
                'diamond_type' => $row['diamond_type'] ?? null,
                'admin_id' => $row['admin_id'] ?? null,
            ]);

            $this->successCount++;
            Log::info('Diamond imported successfully', [
                'row' => $this->rowCount + 1,
                'sku' => $sku,
                'barcode_number' => $barcodeNumber
            ]);

            return $diamond;

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[$this->rowCount + 1] = $e->getMessage();
            Log::error('Diamond import row error', [
                'row' => $this->rowCount + 1,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Laravel validation rules for each row
     */
    public function rules(): array
    {
        return [
            'lot_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diamonds', 'lot_no')
            ],
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diamonds', 'sku')
            ],
            'material' => 'nullable|string|max:255',
            'cut' => 'nullable|string|max:255',
            'clarity' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'shape' => 'nullable|string|max:255',
            'measurement' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'per_ct' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0', // Backward compatibility
            'margin' => 'nullable|numeric|min:0',
            'listing_price' => 'nullable|numeric|min:0',
            'shipping_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'sold_out_date' => 'nullable|date',
            'is_sold_out' => 'nullable|string|in:IN Stock,Sold',
            'duration_days' => 'nullable|integer|min:0',
            'duration_price' => 'nullable|numeric|min:0',
            'sold_out_price' => 'nullable|numeric|min:0',
            'profit' => 'nullable|numeric',
            'sold_out_month' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'diamond_type' => 'nullable|string|max:255',
            'barcode_number' => 'nullable|string|max:255',
            'admin_id' => 'nullable|integer|exists:admins,id',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'lot_no.required' => 'Lot number is required',
            'lot_no.integer' => 'Lot number must be a valid integer',
            'lot_no.unique' => 'Lot number :input already exists',
            'sku.required' => 'SKU is required',
            'sku.unique' => 'SKU :input already exists',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'admin_id.exists' => 'Selected admin does not exist',
        ];
    }

    /**
     * Handle validation failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errorCount++;
            $this->failures[] = $failure;

            $errors = implode(', ', $failure->errors());
            $this->errors[$failure->row()] = $errors;

            Log::warning('Diamond import validation failed', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values()
            ]);
        }
    }

    /**
     * Handle general errors
     */
    public function onError(Throwable $e)
    {
        $this->errorCount++;
        $this->errors[$this->rowCount + 1] = $e->getMessage();

        Log::error('Diamond import error', [
            'row' => $this->rowCount + 1,
            'error' => $e->getMessage(),
            'type' => get_class($e)
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * Build barcode number from lot_no
     */
    protected function buildBarcodeNumber(string $lotNo): string
    {
        $year = date('y');
        $brandCode = env('DIAMOND_BRAND_CODE', '100');

        // Extract numeric part from lot_no
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
     * Generate barcode image and return data URI
     */
    protected function generateBarcodeDataUri(string $sku, string $barcodeNumber): string
    {
        try {
            $generator = new BarcodeGeneratorSVG();
            $svgContent = $generator->getBarcode($sku, $generator::TYPE_CODE_128);
            $dataUri = 'data:image/svg+xml;base64,' . base64_encode($svgContent);

            $publicDir = public_path('barcodes');
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            $filePath = $publicDir . DIRECTORY_SEPARATOR . 'barcode_' . $barcodeNumber . '.svg';
            file_put_contents($filePath, $svgContent);

            return $dataUri;
        } catch (\Exception $e) {
            Log::error('Barcode generation failed during import', [
                'sku' => $sku,
                'barcode_number' => $barcodeNumber,
                'error' => $e->getMessage()
            ]);
            // Return empty string if barcode generation fails
            return '';
        }
    }

    /**
     * Convert value to numeric or null (handles empty strings)
     */
    private function toNumericOrNull($value)
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Convert value to numeric or default (handles empty strings)
     */
    private function toNumericOrDefault($value, $default = 0)
    {
        if ($value === null || $value === '' || $value === false) {
            return $default;
        }
        return is_numeric($value) ? (float) $value : $default;
    }
}
