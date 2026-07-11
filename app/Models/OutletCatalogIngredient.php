<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletCatalogIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_catalog_item_id', 'product_id', 'material_id', 'default_quantity',
    ];

    protected $casts = [
        'default_quantity' => 'decimal:2',
    ];

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(OutletCatalogItem::class, 'outlet_catalog_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function getItemNameAttribute(): string
    {
        if ($this->material_id && $this->material) {
            return $this->material->name;
        }
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }
        return 'Unknown Item';
    }

    public function getItemSkuAttribute(): string
    {
        if ($this->material_id && $this->material) {
            return $this->material->sku;
        }
        if ($this->product_id && $this->product) {
            return $this->product->sku;
        }
        return '—';
    }
}
