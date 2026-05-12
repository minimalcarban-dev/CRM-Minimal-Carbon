<?php

namespace App\Services;

class StatusTransitionService
{
    /**
     * Status progression flows for each order type
     */
    private const STATUS_FLOWS = [
        'ready_to_ship' => [
            'r_order_in_process',
            'r_order_shipped',
            'r_order_cancelled',
        ],
        'custom_diamond' => [
            'd_diamond_in_discuss',
            'd_diamond_in_making',
            'd_diamond_completed',
            'd_diamond_in_certificate',
            'd_order_shipped',
            'd_order_cancelled',
        ],
        'custom_jewellery' => [
            'j_diamond_in_progress',
            'j_diamond_completed',
            'j_diamond_in_discuss',
            'j_cad_in_progress',
            'j_cad_done',
            'j_order_completed',
            'j_order_in_qc',
            'j_qc_done',
            'j_order_shipped',
            'j_order_hold',
            'j_order_cancelled',
        ],
    ];

    /**
     * Get the status flow for a given order type
     */
    public function getStatusFlow(string $orderType): array
    {
        return self::STATUS_FLOWS[$orderType] ?? [];
    }

    /**
     * Get the next status in the flow
     */
    public function getNextStatus(string $orderType, string $currentStatus): ?string
    {
        $flow = $this->getStatusFlow($orderType);
        $currentIndex = array_search($currentStatus, $flow, true);

        if ($currentIndex === false || !isset($flow[$currentIndex + 1])) {
            return null;
        }

        return $flow[$currentIndex + 1];
    }

    /**
     * Get the previous status in the flow
     */
    public function getPreviousStatus(string $orderType, string $currentStatus): ?string
    {
        $flow = $this->getStatusFlow($orderType);
        $currentIndex = array_search($currentStatus, $flow, true);

        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        return $flow[$currentIndex - 1];
    }

    /**
     * Determine order type from status
     */
    public function getOrderTypeFromStatus(string $status): ?string
    {
        $prefix = substr($status, 0, 1);

        return match ($prefix) {
            'r' => 'ready_to_ship',
            'd' => 'custom_diamond',
            'j' => 'custom_jewellery',
            default => null,
        };
    }

    /**
     * Check if status transition is valid
     */
    public function isValidTransition(string $orderType, string $fromStatus, string $toStatus): bool
    {
        $flow = $this->getStatusFlow($orderType);
        $fromIndex = array_search($fromStatus, $flow, true);
        $toIndex = array_search($toStatus, $flow, true);

        if ($fromIndex === false || $toIndex === false) {
            return false;
        }

        // Allow moving forward or backward by one step, or staying in the same status
        return abs($fromIndex - $toIndex) <= 1;
    }

    /**
     * Get all valid statuses for an order type
     */
    public function getValidStatuses(string $orderType): array
    {
        return $this->getStatusFlow($orderType);
    }
}
