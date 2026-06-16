<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,' . $productId],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'sale_start' => ['nullable', 'date'],
            'sale_end' => ['nullable', 'date', 'after_or_equal:sale_start'],
            'weight' => ['required', 'integer', 'min:1'], // in grams
            'packaging_type' => ['required', 'string'], // Pail, Sack, Set
            'moq' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_bulky' => ['required', 'boolean'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
