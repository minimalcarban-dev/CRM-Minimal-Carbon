<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Normalize payment fields into a consistent summary.
     */
    public function normalizePaymentSummary(array $validated, float $grossSell): array
    {
        $status = $validated['payment_status'] ?? null;
        $received = array_key_exists('amount_received', $validated) && $validated['amount_received'] !== ''
            ? round((float) $validated['amount_received'], 2)
            : null;
        $due = array_key_exists('amount_due', $validated) && $validated['amount_due'] !== ''
            ? round((float) $validated['amount_due'], 2)
            : null;

        if ($grossSell <= 0) {
            return [
                'payment_status' => 'full',
                'amount_received' => 0.0,
                'amount_due' => 0.0,
            ];
        }

        if ($status === null) {
            if ($received === null && $due === null) {
                $status = 'full';
            } elseif (($received ?? 0) <= 0 && ($due ?? 0) > 0) {
                $status = 'due';
            } elseif (($received ?? 0) > 0 && ($received ?? 0) < $grossSell) {
                $status = 'partial';
            } else {
                $status = 'full';
            }
        }

        if ($status === 'full') {
            $received = $grossSell;
            $due = 0.0;
        } elseif ($status === 'due') {
            $received = 0.0;
            $due = $due !== null ? max(0, $due) : $grossSell;
        } elseif ($status === 'custom') {
            if ($received === null) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received is required when payment status is custom.',
                ]);
            }

            $received = max(0, min($received, $grossSell));
            $due = round(max($grossSell - $received, 0), 2);
            $status = $received <= 0
                ? 'due'
                : ($due > 0 ? 'partial' : 'full');
        } else {
            if ($received === null) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received is required when payment status is partial.',
                ]);
            }

            if ($received <= 0 || $received >= $grossSell) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Partial payment must be greater than 0 and less than the gross sell amount.',
                ]);
            }

            $received = max(0, min($received, $grossSell));
            $due = round(max($grossSell - $received, 0), 2);
        }

        return [
            'payment_status' => $status,
            'amount_received' => $received,
            'amount_due' => $due,
        ];
    }
}
