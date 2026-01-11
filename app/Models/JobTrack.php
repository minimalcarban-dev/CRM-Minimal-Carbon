<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobTrack extends Model
{
    protected $fillable = [
        'admin_id',
        'type',
        'status',
        'file_name',
        'file_path',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'error_file_path',
        'result_file_path',
        'progress_percentage',
        'started_at',
        'completed_at',
        'error_message',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the admin that owns the job
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Check if job is complete
     */
    public function isComplete()
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    /**
     * Check if job is still running
     */
    public function isRunning()
    {
        return in_array($this->status, ['queued', 'processing']);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        return round(($this->processed_rows / $this->total_rows) * 100);
    }

    /**
     * Get success rate
     */
    public function getSuccessRate()
    {
        if ($this->processed_rows == 0) {
            return 0;
        }
        return round(($this->successful_rows / $this->processed_rows) * 100);
    }

    /**
     * Get estimated time remaining (in seconds)
     */
    public function getEstimatedTimeRemaining()
    {
        if (!$this->started_at || $this->processed_rows == 0) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $speed = $this->processed_rows / $elapsed; // rows per second
        $remaining = $this->total_rows - $this->processed_rows;

        return $speed > 0 ? round($remaining / $speed) : null;
    }

    /**
     * Format estimated time remaining
     */
    public function getFormattedTimeRemaining()
    {
        $seconds = $this->getEstimatedTimeRemaining();
        
        if ($seconds === null) {
            return 'Calculating...';
        }

        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' minutes';
        } else {
            return round($seconds / 3600, 1) . ' hours';
        }
    }

    /**
     * Get processing speed (rows per second)
     */
    public function getProcessingSpeed()
    {
        if (!$this->started_at || $this->processed_rows == 0) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        return round($this->processed_rows / $elapsed, 2);
    }
}
