<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('get')) {
            return [
                'id' => ['required', 'integer', 'exists:products,id'],
            ];
        }

        if ($this->isMethod('post')) {
            return [
                'name'  => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:1'],
            ];
        }

        return [];
    }

    public function validationData()
    {
        return array_merge($this->all(), $this->route()->parameters());
    }
}
