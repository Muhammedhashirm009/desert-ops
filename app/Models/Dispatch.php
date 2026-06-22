<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispatch_number',
        'outlet_id',
        'dispatch_date',
        'status', // pending, dispatched, received
        'notes',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DispatchItem::class);
    }
}
