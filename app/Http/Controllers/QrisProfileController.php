<?php

namespace App\Http\Controllers;

use App\Http\Requests\QrisProfileUpsertRequest;
use App\Models\QrisProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

final class QrisProfileController extends Controller
{
    public function store(QrisProfileUpsertRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $profile = QrisProfile::query()->create([
                'merchant_name' => $validated['merchant_name'],
                'static_payload' => $validated['static_payload'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            if ($profile->is_active || QrisProfile::query()->count() === 1) {
                QrisProfile::query()->whereKeyNot($profile->id)->update(['is_active' => false]);
                $profile->update(['is_active' => true]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QRIS profile created.')]);

        return to_route('dashboard');
    }

    public function update(QrisProfileUpsertRequest $request, QrisProfile $qrisProfile): RedirectResponse
    {
        $validated = $request->validated();

        unset($validated['is_active']);

        $qrisProfile->update($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QRIS profile updated.')]);

        return to_route('dashboard');
    }

    public function destroy(QrisProfile $qrisProfile): RedirectResponse
    {
        $wasActive = $qrisProfile->is_active;
        $qrisProfile->delete();

        if ($wasActive) {
            $replacement = QrisProfile::query()->latest('updated_at')->first();

            if ($replacement !== null) {
                $replacement->update(['is_active' => true]);
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QRIS profile deleted.')]);

        return to_route('dashboard');
    }

    public function activate(QrisProfile $qrisProfile): RedirectResponse
    {
        DB::transaction(function () use ($qrisProfile): void {
            QrisProfile::query()->update(['is_active' => false]);
            $qrisProfile->update(['is_active' => true]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('QRIS profile activated.')]);

        return to_route('dashboard');
    }
}
