<?php

namespace App\Http\Controllers;

use App\Models\QrisProfile;
use App\Support\QrisCsv;
use Inertia\Inertia;
use Inertia\Response;

final class QrisController extends Controller
{
    public function index(): Response
    {
        $activeProfile = QrisProfile::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first() ?? QrisProfile::query()->latest('updated_at')->first();

        $fallbackSummary = QrisCsv::summarize(public_path('qris.csv'));
        $merchantName = $activeProfile?->merchant_name ?? $fallbackSummary['merchant_name'];
        $staticPayload = $activeProfile?->static_payload ?? config('qris.static_payload');

        return Inertia::render('Welcome', [
            'qris' => [
                'merchantName' => $merchantName,
                'staticPayload' => $staticPayload,
            ],
        ]);
    }
}
