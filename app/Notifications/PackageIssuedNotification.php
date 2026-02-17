<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Package;

class PackageIssuedNotification extends Notification
{
    use Queueable;

    protected $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->package->id,
            'slip_id' => $this->package->slip_id,
            'person_name' => $this->package->person_name,
            'message' => 'New package issued to ' . $this->package->person_name,
            'link' => route('packages.show', $this->package->id),
        ];
    }
}
