<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $admin = DB::table('users')->where('email', 'admin@pha.gov.pk')->first();

        if ($admin) {
            DB::table('users')->where('email', 'admin@pha.gov.pk')->update([
                'password' => Hash::make('pha@2026'),
                'role' => 'super_admin',
                'name' => 'Admin User',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->insert([
                'name' => 'Admin User',
                'email' => 'admin@pha.gov.pk',
                'password' => Hash::make('pha@2026'),
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We do not delete the admin user on rollback to prevent accidental account deletion
    }
};
