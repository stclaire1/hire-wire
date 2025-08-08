<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyCorrectionService;
use Illuminate\Support\Facades\Artisan;

class CheckScheduleStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica o status dos agendamentos e da correção monetária';

    /**
     * monthly correction service instance
     *
     * @var MonthlyCorrectionService
     */
    protected $correctionService;

    /**
     * create a new command instance.
     *
     * @param MonthlyCorrectionService $correctionService
     */
    public function __construct(MonthlyCorrectionService $correctionService)
    {
        parent::__construct();
        $this->correctionService = $correctionService;
    }

    /**
     * execute the console command.
     */
    public function handle()
    {
        $this->info('📅 STATUS DOS AGENDAMENTOS');
        $this->line('');

        // display scheduled commands
        $this->info('🔄 Comandos Agendados:');
        $this->line('   • correction:apply-monthly - Executa todo último dia do mês às 23:59');
        $this->line('   • Para executar manualmente: php artisan correction:apply-monthly');
        $this->line('');

        // last correction stats
        $this->info('ÚLTIMA CORREÇÃO MONETÁRIA:');
        $lastStats = $this->correctionService->getLastCorrectionStats();
        
        if ($lastStats) {
            $this->line("   • Data: {$lastStats['date']}");
            $this->line("   • Contas processadas: {$lastStats['total_accounts']}");
            $this->line("   • Valor total aplicado: R$ " . number_format($lastStats['total_amount'], 2, ',', '.'));
            $this->line("   • Média por conta: R$ " . number_format($lastStats['average_correction'], 2, ',', '.'));
        } else {
            $this->comment('   • Nenhuma correção monetária foi aplicada ainda.');
        }
        
        $this->line('');

        // run a test
        if ($this->confirm('Deseja testar a correção monetária agora?', false)) {
            $this->line('');
            $this->info('🧪 Executando teste da correção monetária...');
            Artisan::call('correction:apply-monthly', ['--force' => true]);
            $this->line(Artisan::output());
        }

        return 0;
    }
}
