<?php

namespace App\Models\Accounts\Traits;

trait HasEqualCorrection
{
    public function getCorrectionRate(): float
    {
        return 0.001; // 0.1%
    }
}