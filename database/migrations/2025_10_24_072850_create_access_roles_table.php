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
                    'name' => 'Customer',
                    'slug' => 'customer',
                    'description' => 'A customer is a user who visits the online shop to browse products, add items to the cart, and make purchases. They can also create an account to manage orders, track deliveries, and receive updates or offers.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Admin',
                    'slug' => 'admin',
                    'description' => 'User with administrative privileges to manage content and users.',
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
