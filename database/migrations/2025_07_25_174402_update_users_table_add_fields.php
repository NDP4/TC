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
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_hash')->after('email')->nullable();
            $table->string('phone_number')->after('password_hash')->nullable();
            $table->foreignId('skill_level_id')->nullable()->after('phone_number')->constrained('skill_levels');
            $table->decimal('reputation_score', 5, 2)->default(0.00)->after('skill_level_id');
            $table->boolean('is_active')->default(true)->after('reputation_score');
        });

        // Rename password to password_hash
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('password_hash', 'password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'skill_level_id',
                'reputation_score',
                'is_active'
            ]);
        });
    }
};
