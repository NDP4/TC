<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider_id',
        'category_id',
        'location_id',
        'title',
        'description',
        'main_image_url',
        'thumbnail_url',
        'gallery_images',
        'base_duration_hr',
        'base_coin_cost',
        'avg_rating',
        'review_count',
        'is_featured',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'base_duration_hr' => 'decimal:2',
            'base_coin_cost' => 'decimal:2',
            'avg_rating' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the provider of the service.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Get the category of the service.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the location of the service.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the media for the service.
     */
    public function serviceMedia(): HasMany
    {
        return $this->hasMany(ServiceMedia::class);
    }

    /**
     * Get the transactions for the service.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the tags associated with the service.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'service_tags');
    }
}
