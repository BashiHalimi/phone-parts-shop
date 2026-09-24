<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'amount',
        'method',
        'reference',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('admin.dashboard'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('admin.dashboard'));
    }

    /**
     * The parent record (Sale or Purchase).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}