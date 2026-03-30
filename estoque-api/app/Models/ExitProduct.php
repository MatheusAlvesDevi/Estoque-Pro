<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExitProduct extends Model
{
    protected $table = 'exitproduct';

    protected $fillable = ['name','data_de_saida','quantity','reason','product_id','user_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

        public function userEstoque(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
