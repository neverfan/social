<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('CREATE INDEX IF NOT EXISTS posts_user_id_idx ON "posts" (user_id);');
        DB::unprepared('CREATE INDEX IF NOT EXISTS posts_user_id_and_primary_id_idx ON "posts" (user_id, id);');
        DB::unprepared('CREATE INDEX IF NOT EXISTS posts_created_at_user_id_idx ON "posts" (created_at, id);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP INDEX posts_user_id_idx");
        DB::unprepared("DROP INDEX posts_user_id_and_primary_id_idx");
        DB::unprepared("DROP INDEX posts_created_at_user_id_idx");
    }
};
