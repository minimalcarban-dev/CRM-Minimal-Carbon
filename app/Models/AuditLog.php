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
		'created_at' => 'datetime',
	];

	/**
	 * The entity that this audit log belongs to.
	 */
	public function auditable()
	{
		return $this->morphTo();
	}

	/**
	 * The admin who performed this action.
	 */
	public function admin()
	{
		return $this->belongsTo(Admin::class, 'user_id');
	}
}
