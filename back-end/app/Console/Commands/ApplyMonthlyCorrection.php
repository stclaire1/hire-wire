<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyCorrectionService;

class ApplyMonthlyCorrection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'correction:apply-monthly {--force : Força a execução mesmo se já foi aplicada hoje}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica correção monetária mensal a todas as contas';

    /**
     * The monthly correction service instance.
     *
     * @var MonthlyCorrectionService
     */
    protected $correctionService;

    /**
     * Create a new command instance.
     *
     * @param MonthlyCorrectionService $correctionService
     */
    public function __construct(MonthlyCorrectionService $correctionService)
    {
        parent::__construct();
        $this->correctionService = $correctionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando aplicação da correção monetária mensal...');
        
        // check if correction was already applied today
        if (!$this->option('force') && $this->correctionService->wasCorrectionAppliedToday()) {
            $this->warn('⚠️  Correção monetária já foi aplicada hoje. Use --force para aplicar novamente.');
            return 1;
        }
        
        try {
            $results = $this->correctionService->applyMonthlyCorrectionToAllAccounts();
            
            if (!empty($results['errors'])) {
                $this->error('Alguns erros ocorreram durante o processamento:');
                foreach ($results['errors'] as $error) {
                    $this->line("   • {$error}");
                }
            }
            
            $this->displayResults($results);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Erro crítico ao aplicar correção monetária: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * display results
     *
     * @param array $results
     */
    private function displayResults(array $results): void
    {
        $this->info('✅ Correção monetária aplicada com sucesso!');
        $this->line('');
        
        // general
        $this->info("RESUMO GERAL:");
        $this->line("   • Total de contas processadas: {$results['total_accounts']}");
        $this->line("   • Total de correção aplicada: R$ " . number_format($results['total_correction_amount'], 2, ',', '.'));
        $this->line("   • Data/Hora: " . now()->format('d/m/Y H:i:s'));
        
        // by account (if exists)
        if (!empty($results['accounts_processed']) && $this->output->isVerbose()) {
            $this->line('');
            $this->info("📋 DETALHES POR CONTA:");
            
            foreach ($results['accounts_processed'] as $account) {
                if ($account['correction_amount'] > 0) {
                    $this->line(sprintf(
                        "   • %s %s: R$ %s (saldo: R$ %s → R$ %s)",
                        $account['account_type'],
                        $account['account_number'],
                        number_format($account['correction_amount'], 2, ',', '.'),
                        number_format($account['old_balance'], 2, ',', '.'),
                        number_format($account['new_balance'], 2, ',', '.')
                    ));
                }
            }
        }
        
        if ($results['total_accounts'] > 0 && $results['total_correction_amount'] == 0) {
            $this->comment('Nenhuma correção foi aplicada (todas as contas tinham saldo zero ou não elegíveis).');
        }
    }
}
