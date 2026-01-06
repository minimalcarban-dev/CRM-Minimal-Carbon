<?php

namespace App\Notifications;

use App\Models\JobTrack;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ImportCompleted extends Notification
{
    use Queueable;

    protected $jobTrack;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobTrack $jobTrack)
    {
        $this->jobTrack = $jobTrack;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'job_track_id' => $this->jobTrack->id,
            'type' => 'import',
            'status' => $this->jobTrack->status,
            'file_name' => $this->jobTrack->file_name,
            'total_rows' => $this->jobTrack->total_rows,
            'successful_rows' => $this->jobTrack->successful_rows,
            'failed_rows' => $this->jobTrack->failed_rows,
        ];

        if ($this->jobTrack->status === 'completed') {
            $title = '✅ Import Completed Successfully';
            
            if ($this->jobTrack->failed_rows > 0) {
                $message = "{$this->jobTrack->successful_rows} diamonds imported. {$this->jobTrack->failed_rows} rows failed.";
                $data['error_file_path'] = $this->jobTrack->error_file_path;
                $data['action_text'] = 'Download Error Report';
                $data['action_url'] = route('diamond.job.status', $this->jobTrack->id, false);
            } else {
                $message = "All {$this->jobTrack->successful_rows} diamonds imported successfully!";
                $data['action_text'] = 'View Diamonds';
                $data['action_url'] = route('diamond.index', [], false);
            }
        } else {
            $title = '❌ Import Failed';
            $message = 'Import job failed: ' . ($this->jobTrack->error_message ?? 'Unknown error');
            $data['action_text'] = 'View Details';
            $data['action_url'] = route('diamond.job.status', $this->jobTrack->id, false);
        }

        $data['title'] = $title;
        $data['message'] = $message;
        $data['icon'] = $this->jobTrack->status === 'completed' ? 'check-circle' : 'x-circle';
        $data['icon_color'] = $this->jobTrack->status === 'completed' ? 'success' : 'danger';

        return $data;
    }
}