<?php
namespace App\Services;

use App\Contracts\DataProvider;
use Illuminate\Support\Facades\Http;

class VtuNgProvider implements DataProvider
{
    public function purchaseData(string $network, string $plan, string $phone, float $amount, string $reference): array
    {
        $base = trim((string) env('VTU_BASE_URL'));
        $token = trim((string) env('VTU_TOKEN'));
        $path = trim((string) env('VTU_DATA_PATH'));

        if ($base === '' || $token === '' || $path === '') {
            return ['success' => false, 'status' => 'not_configured'];
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withToken($token)
                ->post(rtrim($base, '/') . '/' . ltrim($path, '/'), [
                    'network' => $network,
                    'plan' => $plan,
                    'phone' => $phone,
                    'amount' => $amount,
                    'reference' => $reference,
                ]);

            if ($response->successful()) {
                $json = $response->json() ?: [];
                return [
                    'success' => (bool)($json['success'] ?? true),
                    'status' => $json['status'] ?? 'successful',
                    'provider_reference' => $json['provider_reference'] ?? $json['reference'] ?? null,
                    'message' => $json['message'] ?? null,
                ];
            }

            return ['success' => false, 'status' => 'failed', 'message' => 'Provider request failed'];
        } catch (\Throwable $e) {
            report($e);
            return ['success' => false, 'status' => 'failed', 'message' => 'Provider unavailable'];
        }
    }
}
