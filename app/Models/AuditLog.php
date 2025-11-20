<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
	public $timestamps = false; // table has created_at only

	protected $fillable = [
		'auditable_type',
		'auditable_id',
		'user_id',
		'event',
		'old_values',
		'new_values',
		'created_at',
	];

	protected $casts = [
		'old_values' => 'array',
		'new_values' => 'array',
	];
}
