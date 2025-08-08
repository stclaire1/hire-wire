<?php

namespace App\Services;

use App\Models\Accounts\CheckingAccount;
use App\Models\Accounts\SavingsAccount;
use App\Models\Accounts\InvestmentAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonthlyCorrectionService
{
    /**
     * apply monthly correction to all accounts with positive balance
     *
     * @return array
     */
    public function applyMonthlyCorrectionToAllAccounts(): array
    {
        $results = [
            'total_accounts' => 0,
            'total_correction_amount' => 0,
            'accounts_processed' => [],
            'errors' => []
        ];

        DB::transaction(function () use (&$results) {
            try {
                // checking accounts
                $this->processAccountType(CheckingAccount::class, 'Corrente', $results);
                
                // savings accounts
                $this->processAccountType(SavingsAccount::class, 'Poupança', $results);

                // investment accounts
                $this->processAccountType(InvestmentAccount::class, 'Investimento', $results);
                
                Log::info('Correção monetária mensal aplicada com sucesso', $results);
                
            } catch (\Exception $e) {
                Log::error('Erro ao aplicar correção monetária mensal: ' . $e->getMessage());
                $results['errors'][] = $e->getMessage();
                throw $e;
            }
        });

        return $results;
    }

    /**
     * apply correction to a specific account type
     *
     * @param string $accountClass
     * @param string $accountTypeName
     * @param array $results
     */
    private function processAccountType(string $accountClass, string $accountTypeName, array &$results): void
    {
        $accounts = $accountClass::where('balance', '>', 0)->get();
        
        foreach ($accounts as $account) {
            try {
                // check if correction was already applied this month
                if ($this->wasCorrectionAppliedThisMonth($account->id)) {
                    continue; // skip this account as it was already corrected
                }
                
                $oldBalance = $account->balance;
                
                // correction based on the current balance
                $correctionAmount = $oldBalance * $account->getCorrectionRate();
                
                // apply correction only if balance > 0
                if ($correctionAmount > 0) {
                    // update account balance
                    $account->balance += $correctionAmount;
                    $account->save();
                    
                    // register new transaction (type=monthly_correction)
                    Transaction::create([
                        'account_id' => $account->id,
                        'transaction_type' => 'monthly_correction',
                        'amount' => $correctionAmount
                    ]);
                    
                    $results['total_accounts']++;
                    $results['total_correction_amount'] += $correctionAmount;
                    $results['accounts_processed'][] = [
                        'account_id' => $account->id,
                        'account_number' => $account->account_number,
                        'account_type' => $accountTypeName,
                        'old_balance' => $oldBalance,
                        'new_balance' => $account->balance,
                        'correction_amount' => $correctionAmount
                    ];
                }
                
            } catch (\Exception $e) {
                $error = "Erro na conta {$account->account_number}: " . $e->getMessage();
                $results['errors'][] = $error;
                Log::error($error);
            }
        }
    }

    /**
     * check if correction was already applied this month for the account
     *
     * @param int $accountId
     * @return bool
     */
    private function wasCorrectionAppliedThisMonth(int $accountId): bool
    {
        $currentMonth = now()->format('Y-m');
        
        return Transaction::where('account_id', $accountId)
            ->where('transaction_type', 'monthly_correction')
            ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$currentMonth])
            ->exists();
    }

     /**
     * check if correction was applied today (daily control)
     *
     * @return bool
     */
    public function wasCorrectionAppliedToday(): bool
    {
        return Transaction::where('transaction_type', 'monthly_correction')
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * last correction stats
     *
     * @return array|null
     */
    public function getLastCorrectionStats(): ?array
    {
        // search the most recent correction date
        $latestDate = Transaction::where('transaction_type', 'monthly_correction')
            ->selectRaw('DATE(created_at) as correction_date')
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->value('correction_date');

        if (!$latestDate) {
            return null;
        }

        // search all corrections for this date
        $lastCorrections = Transaction::where('transaction_type', 'monthly_correction')
            ->whereDate('created_at', $latestDate)
            ->get();

        if ($lastCorrections->isEmpty()) {
            return null;
        }

        return [
            'date' => $lastCorrections->first()->created_at->format('d/m/Y'),
            'total_accounts' => $lastCorrections->count(),
            'total_amount' => $lastCorrections->sum('amount'),
            'average_correction' => $lastCorrections->avg('amount')
        ];
    }
}
