<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'supplier_id',
        'name',
        'sku',
        'model',
        'description',
        'purchase_price',
        'selling_price',
        'quantity',
        'minimum_stock',
        'status',
        'image',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'quantity'       => 'integer',
        'minimum_stock'  => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Is this product low on stock?
     */
    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->minimum_stock;
    }

    /**
     * Is this product out of stock?
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Profit per unit.
     */
    public function profitPerUnit(): float
    {
        return (float) $this->selling_price - (float) $this->purchase_price;
    }
}