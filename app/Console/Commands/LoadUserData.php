<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LoadUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-user-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load test user data from csv file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Prepare upload...');

        $storage = Storage::disk('private');
        $filename = 'people.v2.csv';

        if (!$storage->exists($filename)) {
            $this->error("File not found, check path: {$storage->path($filename)}");
            return false;
        }

        DB::table('users')->truncate();

        $totalRows = $this->getTotalRows($storage->readStream($filename));
        $progressBar = $this->output->createProgressBar($totalRows);

        $resource = $storage->readStream($filename);
        $this->info("Start upload...");

        $chunkSize = 1250;
        $insertData = [];
        $startTime = microtime(true);

        while ($csvRow = fgetcsv($resource, 255, PHP_EOL)) {
            [$fullname, $date, $city] = explode(',', Arr::first($csvRow));
            [$lastName, $firstName] = explode(' ', $fullname);

            $insertData[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => mb_substr($lastName, -1) === 'а' ? 'female' : 'male',
                'city' => $city,
                'birth_date' => Carbon::parse($date)->format('Y-m-d'),
                'biography' => fake()->sentences(1, 3),
                'password' => '$2y$12$3I0W3kTOQubwCthd.WZz5OCew6aEddyvpcgLqcFCQ59nyniQAmXSu',
                //'password' => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $insertCount = count($insertData);
            if ($insertCount >= $chunkSize) {
                $this->insetDataChunk($insertData);
                $progressBar->advance($insertCount);
            }
        }

        if (!empty($insertData)) {
            $this->insetDataChunk($insertData);
        }

        $executionTime = substr((microtime(true) - $startTime), 0, 6);
        $progressBar->finish();

        $this->info("");
        $this->info("End upload, total rows: {$totalRows}, total time: {$executionTime}");
    }

    private function insetDataChunk(array &$insertData): void
    {
        DB::table('users')->insert($insertData);
        $insertData = [];
    }

    private function getTotalRows($resource): int
    {
        $totalRows = 0;
        while (fgetcsv($resource, 255, PHP_EOL)) {
            $totalRows++;
        }

        return $totalRows;
    }
}
