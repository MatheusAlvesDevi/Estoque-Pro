<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'code',
        'price',
        'quantity',
        'minimumstock',
        'supplier_id',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function entryProduct(): HasMany
    {
        return $this->hasMany(EntryProduct::class);
    }

    public function exitProduct(): HasMany
    {
        return $this->hasMany(ExitProduct::class);
    }
}