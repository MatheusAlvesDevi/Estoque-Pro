<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'code' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products', 'code')->ignore($this->route('id')),
            ],
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'minimumstock' => 'sometimes|integer|min:0',
            'supplier_id' => 'sometimes|integer|exists:supplier,id',
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

