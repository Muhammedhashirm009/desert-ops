<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type', // asset, liability, equity, revenue, expense
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Calculate current net balance of the account
     */
    public function getBalanceAttribute(): float
    {
        $debits = $this->journalEntries()->sum('debit');
        $credits = $this->journalEntries()->sum('credit');

        if ($this->type === 'asset' || $this->type === 'expense') {
            return $debits - $credits;
        }

        return $credits - $debits;
    }
}
