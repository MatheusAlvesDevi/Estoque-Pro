<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'tel' => 'nullable|string|max:20',
            'CNPJ' => 'nullable|string|max:155'

        ];
    }
}
