<?php

namespace App\Http\Controllers;

use App\Models\QrisProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $activeProfileId = QrisProfile::query()->where('is_active', true)->value('id');

        return Inertia::render('Dashboard', [
            'profiles' => QrisProfile::query()
                ->latest('updated_at')
                ->get()
                ->map(static fn(QrisProfile $profile): array => [
                    'id' => $profile->id,
                    'merchant_name' => $profile->merchant_name,
                    'static_payload' => $profile->static_payload,
                    'is_active' => $profile->is_active,
                    'created_at' => $profile->created_at?->diffForHumans(),
                    'updated_at' => $profile->updated_at?->diffForHumans(),
                ])
                ->values()
                ->all(),
            'activeProfileId' => $activeProfileId,
            'status' => $request->session()->get('status'),
        ]);
    }
}
