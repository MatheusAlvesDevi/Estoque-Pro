<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'name' => 'required|string|max:155',
            'data_de_entrada' => 'required|date',
            'reason' => 'required|string|max:155',
            'user_id' => 'nullable|integer|exists:users,id'
        ];
    }
}