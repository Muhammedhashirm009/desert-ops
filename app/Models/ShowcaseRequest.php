<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowcaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'outlet_id',
        'requested_by',
        'requested_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'requested_date' => 'date',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShowcaseRequestItem::class);
    }
}
