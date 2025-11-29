<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id'        => ['required', 'exists:orders,id'],
            'status'          => ['required', 'in:success,failure'],
            'idempotency_key' => ['required', 'string'],
            'data'            => ['nullable', 'array'],
        ];
    }
}
