<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // own, franchise
        'commission_rate',
        'contact_person',
        'phone',
        'address',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
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
}
