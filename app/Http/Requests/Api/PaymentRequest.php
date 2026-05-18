<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only allow the client who owns the order associated with the payment to submit payment proof
        $payment = $this->route('payment');
        return $payment && $payment->order->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|string|max:255',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:4096', // Max 4MB
        ];
    }
}
