<?php

namespace Tests\Feature;

use App\Models\QrisProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisProfileCrudTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_normalizes_payload_and_merchant_name_before_store(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => '  Toko Normalized  ',
                'static_payload' => " 00020101 abcd  ",
                'is_active' => true,
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('qris_profiles', [
            'merchant_name' => 'Toko Normalized',
            'static_payload' => '00020101ABCD',
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
                'static_payload' => '000201010211',
                'is_active' => true,
            ])
            ->assertRedirect(route('dashboard'));

        $profileA = QrisProfile::query()->where('merchant_name', 'Toko A')->firstOrFail();

        $this->assertTrue($profileA->is_active);

        $this->actingAs($user)
            ->post('/dashboard/qris-profiles', [
                'merchant_name' => 'Toko B',
                'static_payload' => '000201010212',
            ])
            ->assertRedirect(route('dashboard'));

        $profileB = QrisProfile::query()->where('merchant_name', 'Toko B')->firstOrFail();

        $this->actingAs($user)
            ->patch('/dashboard/qris-profiles/' . $profileB->id, [
                'merchant_name' => 'Toko B Baru',
                'static_payload' => '0002010102129999',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('qris_profiles', [
            'id' => $profileB->id,
            'merchant_name' => 'Toko B Baru',
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
