<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('location_id')->constrained('locations');
            $table->string('title');
            $table->text('description');
            $table->string('main_image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->json('gallery_images')->nullable();
            $table->decimal('base_duration_hr', 8, 2);
            $table->decimal('base_coin_cost', 10, 2);
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->integer('review_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
