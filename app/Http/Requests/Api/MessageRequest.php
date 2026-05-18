<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        
        // Ensure user can only send messages to their own orders, or they are an admin
        return $order && ($order->user_id === auth()->id() || auth()->user()->role === 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required_without:attachment', 'nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:png,jpg,jpeg,gif,pdf,docx,doc,zip,rar'],
        ];
    }
}
