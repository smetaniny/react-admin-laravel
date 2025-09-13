<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(
            'users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'roles')) {
                    $table->json('roles')->nullable()->after('email_verified_at');
                }
            
                if (!Schema::hasColumn('users', 'permissions')) {
                    $table->json('permissions')->nullable()->after('roles');
                }
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'roles')) {
                    $table->dropColumn('roles');
                }
            
                if (Schema::hasColumn('users', 'permissions')) {
                    $table->dropColumn('permissions');
                }
            }
        );
    }
};
