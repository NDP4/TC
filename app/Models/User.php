<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'avatar',
        'skill_level_id',
        'reputation_score',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'reputation_score'  => 'decimal:2',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Get the skill level for the user.
     */
    public function skillLevel(): BelongsTo
    {
        return $this->belongsTo(SkillLevel::class);
    }

    /**
     * Get the services offered by the user.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'provider_id');
    }

    /**
     * Get the transactions initiated by the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    /**
     * Get the verifications submitted by the user.
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get the chat messages sent by the user.
     */
    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /**
     * Get the coin ledger entries for the user.
     */
    public function coinLedgers(): HasMany
    {
        return $this->hasMany(CoinLedger::class);
    }

    /**
     * Get the disputes reported by the user.
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'reporter_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
