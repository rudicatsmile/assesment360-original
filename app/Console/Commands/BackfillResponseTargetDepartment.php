<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillResponseTargetDepartment extends Command
{
    protected $signature = 'responses:backfill-target-department
                            {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Backfill target_department_id on responses from user\'s home department for backward compatibility';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Mode dry-run: tidak ada perubahan yang akan disimpan.');
        }

        // Find responses with NULL target_department_id
        $query = DB::table('responses')
            ->join('users', 'users.id', '=', 'responses.user_id')
            ->whereNull('responses.target_department_id')
            ->whereNotNull('users.department_id')
            ->select([
                'responses.id as response_id',
                'responses.user_id',
                'users.name as user_name',
                'users.department_id',
            ]);

        $total = $query->count();

        if ($total === 0) {
            $this->info('✅ Tidak ada response yang perlu di-backfill. Semua sudah memiliki target_department_id.');
            return self::SUCCESS;
        }

        $this->info("📋 Ditemukan {$total} response dengan target_department_id = NULL.");

        if (!$dryRun && !$this->confirm("Lanjutkan backfill {$total} response?")) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        $query->orderBy('responses.id')->chunk(100, function ($responses) use ($dryRun, &$updated, &$skipped, $bar) {
            foreach ($responses as $response) {
                if ($response->department_id === null) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    DB::table('responses')
                        ->where('id', $response->response_id)
                        ->update(['target_department_id' => $response->department_id]);
                }

                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("🔍 Dry-run selesai: {$updated} response akan diupdate, {$skipped} dilewati (user tanpa department).");
        } else {
            $this->info("✅ Backfill selesai: {$updated} response diupdate, {$skipped} dilewati.");
        }

        return self::SUCCESS;
    }
}
