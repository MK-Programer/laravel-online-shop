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
        Schema::create('access_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'Super Admin', 'Admin', 'User'
            $table->string('slug')->unique(); // e.g., 'super-admin', 'admin', 'user'
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('access_roles')
            ->insert([
                [
                    'name' => 'Admin',
                    'slug' => 'admin',
                    'description' => 'User with administrative privileges to manage content and users.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'User',
                    'slug' => 'user',
                    'description' => 'Regular user with limited access to system features.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_roles');
    }
};
