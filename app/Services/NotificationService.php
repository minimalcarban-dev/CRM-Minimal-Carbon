<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Centralized notification dispatch service.
 *
 * Replaces inline notification dispatching spread across OrderController,
 * DiamondController, MeleeDiamondController, PackageController, and AdminController.
 *
 * All notifications are dispatched via ->afterResponse() to avoid blocking
 * the current request thread.
 */
class NotificationService
{
    /**
     * Send a notification to all admins except the sender.
     *
     * @param  string  $notificationClass  Fully-qualified notification class name
     * @param  array   $args               Constructor arguments for the notification
     * @param  int     $excludeAdminId     Admin ID to exclude (usually the actor)
     * @param  array   $permissionSlugs    Optional permission slugs to filter recipients
     */
    public function notifyAdmins(
        string $notificationClass,
        array $args,
        int $excludeAdminId,
        array $permissionSlugs = []
    ): void {
        dispatch(function () use ($notificationClass, $args, $excludeAdminId, $permissionSlugs) {
            try {
                $query = Admin::where('id', '!=', $excludeAdminId);

                // If permission slugs provided, filter by super admin OR having those permissions
                if (!empty($permissionSlugs)) {
                    $query->where(function ($q) use ($permissionSlugs) {
                        $q->where('is_super', true)
                            ->orWhereHas('permissions', function ($pq) use ($permissionSlugs) {
                                $pq->whereIn('slug', $permissionSlugs);
                            });
                    });
                }

                $admins = $query->get();

                if ($admins->isNotEmpty()) {
                    $notification = new $notificationClass(...$args);
                    Notification::send($admins, $notification);
                }
            } catch (\Exception $e) {
                Log::error('NotificationService dispatch failed', [
                    'notification' => $notificationClass,
                    'error'        => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }

    /**
     * Send a notification to a specific set of admins.
     *
     * @param  \Illuminate\Support\Collection|array  $admins
     * @param  string  $notificationClass
     * @param  array   $args
     */
    public function notifySpecificAdmins($admins, string $notificationClass, array $args): void
    {
        dispatch(function () use ($admins, $notificationClass, $args) {
            try {
                $notification = new $notificationClass(...$args);
                Notification::send($admins, $notification);
            } catch (\Exception $e) {
                Log::error('NotificationService dispatch failed', [
                    'notification' => $notificationClass,
                    'error'        => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
