<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Transaction;

abstract class Account extends Model
{
    use HasFactory;

    // specify the table name (all accounts will be stored in the same table with a column to differentiate the type)
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'account_number', 
        'agency_number', 
        'account_type', 
        'balance'
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    // one account belongs to only one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // one account can have many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // methods to be implemented by subclasses
    abstract public function deposit(float $amount): float;
    abstract public function applyMonthlyCorrection(): void;
    abstract public function getBalance(): float;
    abstract public function getCorrectionRate(): float; // each account type has its own correction rate
    abstract public function getMinDepositAmount(): float; // each account type has its own minimum deposit amount that is accepted
}