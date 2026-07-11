<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletEmployee extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'outlet_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'outlet_admin';
    }

    public function isSalesperson(): bool
    {
        return $this->role === 'salesperson';
    }
}
