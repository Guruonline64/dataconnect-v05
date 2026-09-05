<?php
declare(strict_types=1);

interface DataProvider {
    public function purchaseData(string $network, string $plan, string $phone, float $amount, string $reference): array;
}
