<?php

namespace App\Console\Commands;

use App\Services\AccountService;
use Illuminate\Console\Command;

class FixAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:fix-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix account balances based on existing transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing account balances...');
        
        $accountService = new AccountService();
        $results = $accountService->fixAccountBalances();
        
        if (empty($results)) {
            $this->info('No accounts found to fix.');
            return;
        }
        
        $this->info('Account balances fixed successfully!');
        $this->newLine();
        
        // create a table to show the results
        $headers = ['Account ID', 'Account Number', 'Type', 'Old Balance', 'New Balance', 'Difference'];
        $rows = [];
        
        foreach ($results as $result) {
            $rows[] = [
                $result['account_id'],
                $result['account_number'],
                ucfirst($result['account_type']),
                'R$ ' . number_format($result['old_balance'], 2, ',', '.'),
                'R$ ' . number_format($result['new_balance'], 2, ',', '.'),
                'R$ ' . number_format($result['difference'], 2, ',', '.')
            ];
        }
        
        $this->table($headers, $rows);
        $this->info(count($results) . ' accounts were processed.');
    }
}
