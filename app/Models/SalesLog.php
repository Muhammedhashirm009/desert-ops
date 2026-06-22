<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesLog extends Model
{
    use HasFactory;

    protected $table = 'sales_logs';

    protected $fillable = [
        'outlet_id',
        'log_date',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesLogItem::class);
    }
}
