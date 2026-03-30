<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $fillable = ['name','CNPJ','tel','email'];

    public function supllyProduct(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
