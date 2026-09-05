<?php
namespace App\Contracts;

interface DataProvider
{
    public function purchaseData(
        string $network,
        string $plan,
        string $phone,
        float $amount,
        string $reference
    ): array;
}
