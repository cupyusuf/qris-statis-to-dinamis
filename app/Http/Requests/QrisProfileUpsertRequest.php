<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class QrisProfileUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'merchant_name' => ['required', 'string', 'max:120'],
            'static_payload' => ['required', 'string', 'max:10000', 'regex:/^[0-9A-Z]+$/', 'starts_with:000201'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'static_payload.regex' => 'Static payload hanya boleh berisi huruf kapital A-Z dan angka 0-9.',
            'static_payload.starts_with' => 'Static payload QRIS harus diawali dengan 000201.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merchantName = trim((string) $this->input('merchant_name', ''));
        $staticPayload = strtoupper(trim((string) $this->input('static_payload', '')));

        $this->merge([
            'merchant_name' => $merchantName,
            'static_payload' => preg_replace('/\s+/', '', $staticPayload) ?? $staticPayload,
        ]);
    }
}
