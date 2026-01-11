<?php

namespace App\Jobs;

use App\Models\Diamond;
use App\Models\JobTrack;
use App\Exports\DiamondsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Notifications\ExportCompleted;

class ProcessDiamondExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected $jobTrackId;
    protected $filters;
    protected $adminId;

    public function __construct($jobTrackId, $filters, $adminId)
    {
        $this->jobTrackId = $jobTrackId;
        $this->filters = $filters;
        $this->adminId = $adminId;
    }

    public function handle(): void
    {
        $jobTrack = JobTrack::find($this->jobTrackId);
        
        if (!$jobTrack) {
            Log::error('JobTrack not found', ['id' => $this->jobTrackId]);
            return;
        }

        try {
            $jobTrack->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            Log::info('Starting export job', ['job_track_id' => $this->jobTrackId]);

            // Build query with filters and eager load relationships
            $query = Diamond::with(['assignedAdmin:id,name', 'assignedByAdmin:id,name']);
            $this->applyFilters($query, $this->filters);
            
            $totalRows = $query->count();
            $jobTrack->update(['total_rows' => $totalRows]);

            // Generate file name
            $fileName = 'diamonds_export_' . now()->format('Ymd_His') . '.xlsx';
            $filePath = 'exports/' . $fileName;

            // Get diamonds with relationships loaded
            $diamonds = $query->get();
            
            // Export to Excel with chunking support
            Excel::store(new DiamondsExport($diamonds), $filePath, 'public');

            // Update final status
            $jobTrack->update([
                'processed_rows' => $totalRows,
                'successful_rows' => $totalRows,
                'progress_percentage' => 100,
                'status' => 'completed',
                'completed_at' => now(),
                'result_file_path' => $filePath,
            ]);

            // Send notification
            $admin = \App\Models\Admin::find($this->adminId);
            if ($admin) {
                $admin->notify(new ExportCompleted($jobTrack));
            }

            Log::info('Export job completed', [
                'job_track_id' => $this->jobTrackId,
                'rows' => $totalRows,
            ]);

        } catch (\Exception $e) {
            Log::error('Export job failed', [
                'job_track_id' => $this->jobTrackId,
                'error' => $e->getMessage(),
            ]);

            $jobTrack->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $admin = \App\Models\Admin::find($this->adminId);
            if ($admin) {
                $admin->notify(new ExportCompleted($jobTrack));
            }
        }
    }

    protected function applyFilters($query, $filters)
    {
        if (!empty($filters['lot_no'])) {
            $query->where('lot_no', 'like', '%' . $filters['lot_no'] . '%');
        }
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }
        if (!empty($filters['material'])) {
            $query->where('material', $filters['material']);
        }
        if (!empty($filters['cut'])) {
            $query->where('cut', $filters['cut']);
        }
        if (!empty($filters['clarity'])) {
            $query->where('clarity', $filters['clarity']);
        }
        if (!empty($filters['color'])) {
            $query->where('color', $filters['color']);
        }
        if (!empty($filters['shape'])) {
            $query->where('shape', $filters['shape']);
        }
        if (!empty($filters['is_sold_out'])) {
            $query->where('is_sold_out', $filters['is_sold_out']);
        }
        if (!empty($filters['carat_from'])) {
            $query->where('weight', '>=', $filters['carat_from']);
        }
        if (!empty($filters['carat_to'])) {
            $query->where('weight', '<=', $filters['carat_to']);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Export job failed completely', [
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
}
