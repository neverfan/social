<?php

namespace App\Console\Commands;

use App\Jobs\UpdateUserFeedCacheJob;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use stdClass;

class CacheWarming extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm {--only_active=1} {--active_sub_days=7} {--batch_size=5000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache warming for users feeds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch_size');
        $activeSubDays = (int) $this->option('active_sub_days');
        $onlyActive = (bool) $this->option('only_active');
        $totalUsers = !$onlyActive ? DB::table('users')->count() :
            DB::table('users')->whereDate('last_login_at', '>=', now()->subDays($activeSubDays))->count();

        $progressBar = $this->output->createProgressBar($totalUsers);

        if ($onlyActive) {
            $this->info("Start cache warming for active users");
        } else {
            $this->info("Start cache warming for all users");
        }

        $this->info("Users for cache feeds: {$totalUsers}");
        $this->info("Generating batches of {$batchSize} users each...");

        $chunkItem = 1;
        DB::table('users')
            ->select('users.id')
            // Берем пользователей у которых `last_login_at` меньше N-дней
            ->when($onlyActive, fn($query) => $query->whereDate('last_login_at', '>=', now()->subDays($activeSubDays)))
            ->orderByDesc('last_login_at')// Сортируем по активности, самые активные первые получают кэш
            ->chunk($batchSize, function (Collection $users) use (&$chunkItem, $progressBar, $batchSize) {
                $jobs = $users->transform(fn(stdClass $user) => new UpdateUserFeedCacheJob($user->id));
                Bus::batch($jobs)
                    ->name("feeds::cache::warm::batch_{$chunkItem}")
                    ->allowFailures()
                    ->dispatch();
                $progressBar->advance($jobs->count());
                $chunkItem++;
            });

        $progressBar->finish();

        $this->info("");
        $this->info("Batch generating completed. Go to /horizon for watch processing...");
    }
}
