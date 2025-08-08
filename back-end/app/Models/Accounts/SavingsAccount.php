<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavingsAccount extends Account
{
    use HasFactory;

    // uses the "accounts" table but the column "account_type" value is "savings"
    protected $table = 'accounts';

    // make a deposit without applying a bonus
    public function deposit(float $amount): float
    {
        $minDeposit = $this->getMinDepositAmount();

        if ($amount < $minDeposit) {
            throw new \InvalidArgumentException("O valor mínimo para depósito é R$ {$minDeposit}");
        }

        $this->balance += $amount;
        $this->save();

        return $this->balance;
    }

    // applies a monthly correction of 0.001% to the balance
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

    // return the correction rate for this type of account (only different)
    public function getCorrectionRate(): float
    {
        return 0.00001; // 0.001%
    }

    // return the minimum accepted deposit amount
    public function getMinDepositAmount(): float
    {
        return 5.00;
    }
}
