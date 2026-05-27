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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $payload = (string) $this->input('static_payload', '');

            if ($payload === '') {
                return;
            }

            if (! preg_match('/6304[0-9A-F]{4}$/', $payload)) {
                $validator->errors()->add(
                    'static_payload',
                    'Akhir payload harus berformat 6304XXXX (tag CRC). Contoh suffix valid: 6304A13F.',
                );

                return;
            }

            $actualCrc = substr($payload, -4);
            $payloadWithoutCrc = substr($payload, 0, -4);
            $computedCrc = $this->computeCrc16($payloadWithoutCrc);

            if ($actualCrc !== $computedCrc) {
                $validator->errors()->add(
                    'static_payload',
                    sprintf(
                        'CRC tidak cocok. Nilai saat ini %s, seharusnya %s. Periksa 4 karakter terakhir payload.',
                        $actualCrc,
                        $computedCrc,
                    ),
                );
            }
        });
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

    private function computeCrc16(string $input): string
    {
        $crc = 0xFFFF;
        $length = strlen($input);

        for ($index = 0; $index < $length; $index++) {
            $crc ^= ord($input[$index]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}