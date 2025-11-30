<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'idempotency_key' => ['required', 'string'],
            'payment_reference' => ['required', 'string'],
            'status' => ['required', 'in:success,failed'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
