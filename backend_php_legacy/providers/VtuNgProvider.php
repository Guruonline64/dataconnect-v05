<?php
declare(strict_types=1);
require_once __DIR__.'/ProviderInterface.php';

/*
 * VTU.ng adapter placeholder.
 * Keep credentials and the exact production endpoint in server environment variables.
 * Implement this adapter after the company supplies the production hosting/API configuration.
 */
final class VtuNgProvider implements DataProvider {
    public function purchaseData(string $network, string $plan, string $phone, float $amount, string $reference): array {
        $base = getenv('VTU_BASE_URL') ?: '';
        $token = getenv('VTU_TOKEN') ?: '';
        if ($base === '' || $token === '') {
            return ['success'=>false, 'status'=>'not_configured', 'message'=>'Provider is not configured on the server'];
        }

        // Deliberately do not guess VTU.ng's production request path or payload.
        // Configure the exact endpoint/payload from the company's VTU.ng API documentation.
        return ['success'=>false, 'status'=>'adapter_pending', 'message'=>'VTU.ng adapter requires the confirmed production API contract'];
    }
}
