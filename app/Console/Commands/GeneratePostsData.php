<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneratePostsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-posts-data {--startId=1} {--users=10000} {--posts=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate posts data for users with adding friends';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalUsers = DB::table('users')->count();

        $startUserId = (int) $this->option('startId');// Стартовый user_id
        $usersCount = min((int)$this->option('users'), $totalUsers);// Общее количество пользователей для генерации постов
        $postPerUser = (int) $this->option('posts');// Сколько постов на одного пользователя
        $totalRows = $usersCount * $postPerUser;// Общее количество постов, которое будет сгенерировано (3 млн. - по умолчанию)

        $this->info('Clear posts...');
        DB::table('posts')->truncate();

        // Выбираем случайных пользователей для генерации постов
        $users = range($startUserId, $totalUsers);
        shuffle($users);
        $users = array_slice($users, 0, $usersCount);

        // Распределяем посты равномерно во времени за последний год
        $date = now()->subYear()->subDay();
        $postDiffSeconds = round (((365 * 24 * 60 * 60) / $totalRows), 1, PHP_ROUND_HALF_EVEN);
        $chunkSize = 1000;

        $this->info('Start generate posts...');

        $startTime = microtime(true);
        $progressBar = $this->output->createProgressBar($totalRows);

        collect($users)
            ->shuffle()
            ->chunk($chunkSize)
            ->each(function ($users) use ($progressBar, $postPerUser, &$date, $postDiffSeconds) {
                $chunkData = [];
                foreach ($users as $userId) {
                    for($postIndex = 0; $postIndex < $postPerUser; $postIndex++) {
                        $date = $date->addSeconds($postDiffSeconds);
                        $chunkData[] = [
                            'user_id' => $userId,
                            'text' => fake()->realText(),
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                    }
                }

                $progressBar->advance(count($chunkData));
                DB::table('posts')->insert($chunkData);
            });

        $progressBar->finish();
        $executionTime = substr((microtime(true) - $startTime), 0, 6);

        $this->info("");
        $this->info("End generating, total posts: {$totalRows}, total time: {$executionTime}");
    }
}
