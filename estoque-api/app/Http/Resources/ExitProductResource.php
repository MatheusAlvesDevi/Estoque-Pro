<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExitProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'name' => $this->product->name,
            'reason' => $this->reason,
            'data_de_saida' => $this->data_de_saida,
            'created_at' => $this->created_at
        ];
    }
}