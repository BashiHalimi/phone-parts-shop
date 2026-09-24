<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'amount',
        'description',
        'date',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];
    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('admin.dashboard'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('admin.dashboard'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The standard list of expense categories.
     */
    public static function categories(): array
    {
        return [
            'rent'        => 'Rent',
            'electricity' => 'Electricity',
            'internet'    => 'Internet',
            'transport'   => 'Transport',
            'salary'      => 'Salary',
            'supplies'    => 'Shop Supplies',
            'marketing'   => 'Marketing',
            'maintenance' => 'Maintenance',
            'tax'         => 'Tax',
            'other'       => 'Other',
        ];
    }

    /**
     * Human-readable category label.
     */
    public function categoryLabel(): string
    {
        return self::categories()[$this->category] ?? ucfirst($this->category);
    }
}