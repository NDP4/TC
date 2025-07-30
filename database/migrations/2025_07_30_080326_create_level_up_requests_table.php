<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('level_up_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('target_skill_level_id')->constrained('skill_levels');
            $table->json('documents'); // Store document URLs and types
            $table->text('notes')->nullable(); // User notes for the request
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('verification_reason')->nullable(); // Reason for approval/rejection
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Verifier user ID
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Ensure only one pending request per user
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_up_requests');
    }
};
