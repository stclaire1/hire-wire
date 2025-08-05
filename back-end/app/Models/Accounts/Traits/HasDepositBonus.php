<?php

namespace App\Models\Accounts\Traits;

trait HasDepositBonus
{
    public function getDepositBonus(): float
    {
        return 0.50;
    }
}
