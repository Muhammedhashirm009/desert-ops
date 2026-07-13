<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutletCatalogItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'retail_price', 'cost_price', 'description', 'is_active', 'created_by',
    ];

    protected $casts = [
        'retail_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(OutletCatalogIngredient::class);
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'outlet_catalog_assignments')->withTimestamps();
    }

    public function productionRuns(): HasMany
    {
        return $this->hasMany(OutletProductionRun::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
