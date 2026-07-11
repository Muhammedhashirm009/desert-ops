<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesLogItem extends Model
{
    use HasFactory;

    protected $table = 'sales_log_items';

    protected $fillable = [
        'sales_log_id',
        'product_id',
        'outlet_catalog_item_id',
        'quantity_sold',
        'unit_price',
        'total_revenue',
        'commission_amount',
        'net_revenue',
    ];

    protected $casts = [
        'quantity_sold' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_revenue' => 'decimal:2',
    ];

    public function salesLog(): BelongsTo
    {
        return $this->belongsTo(SalesLog::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(OutletCatalogItem::class, 'outlet_catalog_item_id');
    }
}
