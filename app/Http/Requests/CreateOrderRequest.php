<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
    return [
        'hold_id' => 'required|exists:holds,id',
        'payment_reference' => 'required|string',
    ];
    }
}
