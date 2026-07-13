<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type',
        'item_id',
        'old_cost_price',
        'new_cost_price',
        'quantity_received',
        'unit_cost',
        'grn_id',
        'supplier_name',
        'changed_by',
        'notes',
    ];

    protected $casts = [
        'old_cost_price' => 'decimal:2',
        'new_cost_price' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get the owning item (material or product).
     */
    public function item()
    {
        if ($this->item_type === 'material') {
            return $this->belongsTo(Material::class, 'item_id');
        }
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function getItemNameAttribute(): string
    {
        if ($this->item_type === 'material') {
            return Material::find($this->item_id)?->name ?? 'Unknown Material';
        }
        return Product::find($this->item_id)?->name ?? 'Unknown Product';
    }
}
