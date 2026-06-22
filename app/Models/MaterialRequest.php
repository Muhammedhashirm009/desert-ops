<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'requested_by',
        'requested_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'requested_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequestItem::class);
    }
}
