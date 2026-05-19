<?php

namespace App\Observers;

use App\Events\MeleeCreated;
use App\Events\MeleeDeleted;
use App\Events\MeleeStatusChanged;
use App\Models\MeleeDiamond;

class MeleeObserver
{
    /**
     * Fire MeleeCreated after a diamond is first persisted.
     */
    public function created(MeleeDiamond $diamond): void
    {
        MeleeCreated::dispatch($diamond);
    }

    /**
     * Fire MeleeStatusChanged only when the status field actually changed.
     *
     * Status is computed by the saving() boot hook from available_pieces,
     * so wasChanged('status') is the safe guard — it reads Eloquent's dirty
     * map after the INSERT/UPDATE has been committed.
     */
    public function updated(MeleeDiamond $diamond): void
    {
        if (! $diamond->wasChanged('status')) {
            return;
        }

        MeleeStatusChanged::dispatch(
            $diamond,
            (string) ($diamond->getOriginal('status') ?? ''),
            (string) $diamond->status,
        );
    }

    /**
     * Fire MeleeDeleted after a diamond is removed (soft or hard delete).
     */
    public function deleted(MeleeDiamond $diamond): void
    {
        MeleeDeleted::dispatch($diamond);
    }
}
