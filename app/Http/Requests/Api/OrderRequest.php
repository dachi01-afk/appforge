<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $order = $this->route('order');
            // Allow edit only if the order belongs to the user and status is 'pending'
            return $order && $order->user_id === auth()->id() && $order->status === 'pending';
        }
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('feature_list')) {
            $featureList = $this->input('feature_list');

            if (is_string($featureList)) {
                // Try to decode as JSON array
                $decoded = json_decode($featureList, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->merge([
                        'feature_list' => $decoded,
                    ]);
                } else {
                    // Try to split comma-separated list
                    $features = array_map('trim', explode(',', $featureList));
                    $this->merge([
                        'feature_list' => array_filter($features),
                    ]);
                }
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isPost = $this->isMethod('post');

        return [
            // Order main fields
            'title' => [$isPost ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => [$isPost ? 'required' : 'sometimes', 'string'],
            'app_type' => [$isPost ? 'required' : 'sometimes', 'string', 'max:100'],
            'platform' => [$isPost ? 'required' : 'sometimes', 'string', 'max:100'],
            'budget' => [$isPost ? 'required' : 'sometimes', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'priority' => [$isPost ? 'required' : 'sometimes', Rule::in(['low', 'medium', 'high'])],

            // Order detail fields
            'feature_list' => [$isPost ? 'required' : 'sometimes', 'array'],
            'feature_list.*' => ['string'],
            'design_preference' => [$isPost ? 'required' : 'sometimes', 'string'],
            'reference_app' => ['nullable', 'string', 'url'],
            'target_user' => ['nullable', 'string'],
            'business_flow' => ['nullable', 'string'],
            'additional_notes' => ['nullable', 'string'],

            // Order files
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,docx,doc,png,jpg,jpeg,zip'],
        ];
    }
}
