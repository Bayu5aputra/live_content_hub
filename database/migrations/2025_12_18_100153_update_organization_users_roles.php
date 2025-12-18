<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert 'editor' dan 'viewer' menjadi 'user'
        DB::table('organization_users')
            ->whereIn('role', ['editor', 'viewer'])
            ->update(['role' => 'user']);
    }

    public function down(): void
    {
        // Tidak bisa rollback karena data sudah berubah
    }
};
