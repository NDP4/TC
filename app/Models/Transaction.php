<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'buyer_id',
        'hours_booked',
        'coins_charged',
        'status',
        'booked_at',
        'completed_at',
        'cancelled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hours_booked'  => 'decimal:2',
            'coins_charged' => 'decimal:2',
            'booked_at'     => 'datetime',
            'completed_at'  => 'datetime',
            'cancelled_at'  => 'datetime',
        ];
    }

    /**
     * Get the service involved in the transaction.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the buyer who initiated the transaction.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the coin ledger entries for the transaction.
     */
    public function coinLedgers(): HasMany
    {
        return $this->hasMany(CoinLedger::class);
    }

    /**
     * Get the transaction media (evidence).
     */
    public function transactionMedia(): HasMany
    {
        return $this->hasMany(TransactionMedia::class);
    }

    /**
     * Get the dispute for the transaction.
     */
    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * Get the notifications that refer to the transaction.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the review for the transaction.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get the chat messages for the transaction.
     */
    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get the escrow that holds the transaction.
     */
    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }
}
