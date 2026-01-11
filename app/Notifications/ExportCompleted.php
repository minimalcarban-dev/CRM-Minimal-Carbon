<?php

namespace App\Notifications;

use App\Models\JobTrack;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExportCompleted extends Notification
{
    use Queueable;

    protected $jobTrack;

    public function __construct(JobTrack $jobTrack)
    {
        $this->jobTrack = $jobTrack;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $data = [
            'job_track_id' => $this->jobTrack->id,
            'type' => 'export',
            'status' => $this->jobTrack->status,
            'total_rows' => $this->jobTrack->total_rows,
        ];

        if ($this->jobTrack->status === 'completed') {
            $title = '✅ Export Completed';
            $message = "{$this->jobTrack->total_rows} diamonds exported successfully. File ready for download.";
            $data['result_file_path'] = $this->jobTrack->result_file_path;
            $data['action_text'] = 'Download File';
            // Use relative URL so it works regardless of APP_URL/port when queued
            $data['action_url'] = route('diamond.job.download', $this->jobTrack->id, false);
            $data['icon'] = 'download';
            $data['icon_color'] = 'success';
        } else {
            $title = '❌ Export Failed';
            $message = 'Export job failed: ' . ($this->jobTrack->error_message ?? 'Unknown error');
            $data['action_text'] = 'View Details';
            $data['action_url'] = route('diamond.job.status', $this->jobTrack->id, false);
            $data['icon'] = 'x-circle';
            $data['icon_color'] = 'danger';
        }

        $data['title'] = $title;
        $data['message'] = $message;

        return $data;
    }
}