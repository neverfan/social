<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        DB::unprepared('CREATE INDEX IF NOT EXISTS users_first_name_and_last_name_gin_idx ON "users" USING GIN (lower(first_name) gin_trgm_ops, lower(last_name) gin_trgm_ops);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP INDEX users_first_name_and_last_name_gin_idx");
    }
};
