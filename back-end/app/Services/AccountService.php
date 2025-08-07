<?php

namespace App\Services;

use App\Models\Accounts\Account;
use App\Models\Accounts\CheckingAccount;
use App\Models\Accounts\SavingsAccount;
use App\Models\Accounts\InvestmentAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AccountService
{
    // list of all the accounts of the authenticated user
    public function getUserAccounts(int $userId): Collection
    {
        $accounts = new Collection();
        
        // check if the user exists
        $userExists = User::find($userId);
        if (!$userExists) {
            throw new \Exception("Usuário com ID {$userId} não encontrado");
        }
        
        // find all the accounts by type
        $checkingAccounts = CheckingAccount::where('user_id', $userId)->where('account_type', 'checking')->get();
        $savingsAccounts = SavingsAccount::where('user_id', $userId)->where('account_type', 'savings')->get();
        $investmentAccounts = InvestmentAccount::where('user_id', $userId)->where('account_type', 'investment')->get();
        
        // merge all accounts into a single collection
        $accounts = $accounts->merge($checkingAccounts)
                           ->merge($savingsAccounts)
                           ->merge($investmentAccounts);
        
        // sort by creation date (most recent first)
        return $accounts->sortByDesc('created_at');
    }

    // create a new bank account
    public function createAccount(int $userId, array $data): Account
    {
        // generates a unique account and agency number
        $accountNumber = $this->generateAccountNumber();
        $agencyNumber = $this->generateAgencyNumber();
        
        // calls the method to check for already existing accounts of the specified type
        $existingAccount = $this->checkExistingAccountType($userId, $data['account_type']);

        if ($existingAccount) {
            throw new \Exception('Você já possui uma conta ' . $this->getAccountTypeName($data['account_type']) . ' ativa');
        }

        // create the specific instance based on the type
        $accountClass = $this->getAccountClass($data['account_type']);
        
        return $accountClass::create([
            'user_id' => $userId,
            'account_number' => $accountNumber,
            'agency_number' => $agencyNumber,
            'account_type' => $data['account_type'],
            'balance' => 0.00
        ]);
    }

    // search for a specific account by ID and user ID
    public function getAccountById(string $accountId, int $userId): Account
    {
        $account = CheckingAccount::where('id', $accountId)->where('user_id', $userId)->where('account_type', 'checking')->first()
                ?? SavingsAccount::where('id', $accountId)->where('user_id', $userId)->where('account_type', 'savings')->first()
                ?? InvestmentAccount::where('id', $accountId)->where('user_id', $userId)->where('account_type', 'investment')->first();

        if (!$account) {
            throw new \Exception('Conta não encontrada');
        }

        return $account;
    }

    // make a deposit into a specific account
    public function makeDeposit(string $accountId, int $userId, float $amount): float
    {
        $account = $this->getAccountById($accountId, $userId);
        
        // the method getAccountById already returns the specific instance
        return $account->deposit($amount);
    }

    // get the balance of a specific account
    public function getAccountBalance(string $accountId, int $userId): float
    {
        $account = $this->getAccountById($accountId, $userId);
        
        // same thing
        return $account->getBalance();
    }

    // check if the user already has an account of the specified type
    private function checkExistingAccountType(int $userId, string $accountType): ?Account
    {
        return match($accountType) {
            'checking' => CheckingAccount::where('user_id', $userId)->where('account_type', 'checking')->first(),
            'savings' => SavingsAccount::where('user_id', $userId)->where('account_type', 'savings')->first(),
            'investment' => InvestmentAccount::where('user_id', $userId)->where('account_type', 'investment')->first(),
            default => null
        };
    }

    // get the class name of the account based on the type
    private function getAccountClass(string $accountType): string
    {
        return match($accountType) {
            'checking' => CheckingAccount::class,
            'savings' => SavingsAccount::class,
            'investment' => InvestmentAccount::class,
            default => throw new \Exception('Tipo de conta inválido: ' . $accountType)
        };
    }

    // get the pt-br name of the account type
    private function getAccountTypeName(string $accountType): string
    {
        return match($accountType) {
            'checking' => 'corrente',
            'savings' => 'poupança',
            'investment' => 'investimento',
            default => 'desconhecida'
        };
    }

    // generate a unique agency number
    private function generateAgencyNumber(): string
    {
        // change this
        return '0001';
    }

    // generate a unique account number
    private function generateAccountNumber(): string
    {
        do {
            $accountNumber = rand(100000, 999999) . '-' . rand(10, 99);
        } while ($this->accountNumberExists($accountNumber));

        return $accountNumber;
    }

    // check if an account number already exists
    private function accountNumberExists(string $accountNumber): bool
    {
        return CheckingAccount::where('account_number', $accountNumber)->where('account_type', 'checking')->exists()
            || SavingsAccount::where('account_number', $accountNumber)->where('account_type', 'savings')->exists()
            || InvestmentAccount::where('account_number', $accountNumber)->where('account_type', 'investment')->exists();
    }
}