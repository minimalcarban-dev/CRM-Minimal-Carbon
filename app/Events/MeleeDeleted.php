<?php

namespace App\Events;

use App\Models\MeleeDiamond;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeleeDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MeleeDiamond $diamond)
    {
    }
}
