<?php

namespace Tests\Feature;

use App\Models\QrisProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisProfileCrudTest extends TestCase
{
    use RefreshDatabase;

    private function withCrc(string $payloadWithoutCrc): string
    {
        $normalized = strtoupper(preg_replace('/\s+/', '', $payloadWithoutCrc) ?? $payloadWithoutCrc);
        $payload = str_ends_with($normalized, '6304') ? $normalized : $normalized . '6304';

        return $payload . $this->computeCrc16($payload);
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

    public function test_rejects_invalid_static_payload_format(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => 'Toko Invalid',
                'static_payload' => 'abc-123-invalid',
            ])
            ->assertSessionHasErrors('static_payload');

        $this->assertDatabaseMissing('qris_profiles', [
            'merchant_name' => 'Toko Invalid',
        ]);
    }

    public function test_rejects_payload_with_invalid_crc(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $validPayload = $this->withCrc('000201010211');
        $invalidCrcPayload = substr($validPayload, 0, -4) . 'FFFF';

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => 'Toko CRC Invalid',
                'static_payload' => $invalidCrcPayload,
            ])
            ->assertSessionHasErrors('static_payload');

        $this->assertDatabaseMissing('qris_profiles', [
            'merchant_name' => 'Toko CRC Invalid',
        ]);
    }

    public function test_normalizes_payload_and_merchant_name_before_store(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $validPayload = $this->withCrc('000201010211ABCD');

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => '  Toko Normalized  ',
                'static_payload' => '  ' . strtolower($validPayload) . '  ',
                'is_active' => true,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('qris_profiles', [
            'merchant_name' => 'Toko Normalized',
            'static_payload' => $validPayload,
        ]);
    }

    public function test_dashboard_can_manage_qris_profiles(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => 'Toko A',
                'static_payload' => $this->withCrc('000201010211'),
                'is_active' => true,
            ])
            ->assertRedirect(route('dashboard'));

        $profileA = QrisProfile::query()->where('merchant_name', 'Toko A')->firstOrFail();

        $this->assertTrue($profileA->is_active);

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => 'Toko B',
                'static_payload' => $this->withCrc('000201010212'),
            ])
            ->assertRedirect(route('dashboard'));

        $profileB = QrisProfile::query()->where('merchant_name', 'Toko B')->firstOrFail();

        $updatedPayload = $this->withCrc('0002010102129999');

        $this->actingAs($user)
            ->patch('/dashboard/qris-profiles/' . $profileB->id, [
                'merchant_name' => 'Toko B Baru',
                'static_payload' => $updatedPayload,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('qris_profiles', [
            'id' => $profileB->id,
            'merchant_name' => 'Toko B Baru',
            'static_payload' => $updatedPayload,
        ]);

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles/' . $profileB->id . '/activate')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('qris_profiles', [
            'id' => $profileB->id,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('qris_profiles', [
            'id' => $profileA->id,
            'is_active' => 0,
        ]);

        $this->actingAs($user)
            ->delete('/dashboard/qris-profiles/' . $profileB->id)
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('qris_profiles', [
            'id' => $profileB->id,
        ]);

        $this->assertDatabaseHas('qris_profiles', [
            'id' => $profileA->id,
            'is_active' => 1,
        ]);
    }
}
