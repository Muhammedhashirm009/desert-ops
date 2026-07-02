<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Outlet extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'type', // own, franchise
        'commission_rate',
        'contact_person',
        'phone',
        'address',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'password' => 'hashed',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(OutletStock::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }

    public function salesLogs(): HasMany
    {
        return $this->hasMany(SalesLog::class);
    }

    public function assignedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'outlet_product')->withTimestamps();
    }

    public function assignedMaterials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'outlet_product')->withTimestamps();
    }

    public function showcaseRequests(): HasMany
    {
        return $this->hasMany(ShowcaseRequest::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(OutletStockMovement::class);
    }
}
