<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExitProductRequest extends FormRequest
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
            'data_de_saida' => 'required|date|date_format:Y-m-d',
            'reason' => 'required|string|max:155',
            'user_id' => 'nullable|integer|exists:users,id'
        ];
    }
}