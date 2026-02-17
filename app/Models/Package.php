<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slip_id',
        'person_name',
        'mobile_number',
        'package_description',
        'package_image',
        'issue_date',
        'issue_time',
        'return_date',
        'actual_return_date',
        'actual_return_time',
        'status',
        'notes',
        'admin_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'return_date' => 'date',
        'actual_return_date' => 'date',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Issued');
    }

    public function scopeOverdue($query)
    {
        // Simple definition: 'Overdue' status OR (Issued AND return_date < today)
        return $query->where('status', 'Overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'Issued')
                    ->whereDate('return_date', '<', Carbon::today());
            });
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'Returned');
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        if ($this->status === 'Issued' && $this->return_date < Carbon::today()) {
            return '<span class="badge bg-danger">Overdue</span>';
        }

        return match ($this->status) {
            'Issued' => '<span class="badge bg-warning text-dark">Issued</span>',
            'Returned' => '<span class="badge bg-success">Returned</span>',
            'Overdue' => '<span class="badge bg-danger">Overdue</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
