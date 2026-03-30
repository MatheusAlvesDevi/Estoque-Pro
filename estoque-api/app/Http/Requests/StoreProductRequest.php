<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:155|unique:products,code',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'minimumstock' => 'required|integer|min:0',
            'supplier_id' => 'required|integer|exists:supplier,id'
        ];
    }

    protected function prepareForValidation(): void
    {
        $supplierInput = $this->input('supplier');

        if (is_array($supplierInput)) {
            $supplierInput = $supplierInput['id'] ?? null;
        }

        $this->merge([
            'minimumstock' => $this->input(
                'minimumstock',
                $this->input('minimumStock', $this->input('minimum_stock'))
            ),
            'supplier_id' => $this->input(
                'supplier_id',
                $this->input('supplierId', $supplierInput)
            ),
        ]);
    }
}