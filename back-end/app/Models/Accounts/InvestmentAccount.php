<?php

namespace App\Models\Accounts;

use App\Models\Accounts\Traits\HasEqualCorrection;
use App\Models\Accounts\Traits\HasDepositBonus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestmentAccount extends Account
{
    use HasFactory;
    use HasEqualCorrection;
    use HasDepositBonus;

    // make a deposit and apply a bonus
    public function deposit(float $amount): float
    {
        $minDeposit = $this->getMinDepositAmount();

        if ($amount < $minDeposit) {
            throw new \InvalidArgumentException("O valor mínimo para depósito é R$ {$minDeposit}");
        }

        $bonus = $this->getDepositBonus();
        $this->balance += ($amount + $bonus);
        $this->save();

        return $this->balance;
    }

    // applies a monthly correction of 0.1% to the balance
    public function applyMonthlyCorrection(): void
    {
        $correction = $this->balance * $this->getCorrectionRate();
        $this->balance += $correction;
        $this->save();
    }

    // return the current balance of the account
    public function getBalance(): float
    {
        return $this->balance;
    }

    // return the minimum accepted deposit amount
    public function getMinDepositAmount(): float
    {
        return 50.00;
    }
}
