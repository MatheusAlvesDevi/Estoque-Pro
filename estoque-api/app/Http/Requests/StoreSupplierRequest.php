<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'CNPJ' => 'required|string|max:255|unique:supplier,CNPJ',
            'email' => 'required|email|max:155|unique:supplier,email',
            'tel' => 'required|string|max:20'
        ];
    }
}
