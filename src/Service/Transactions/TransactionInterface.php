<?php

declare(strict_types=1);

namespace App\Service\Transactions;

interface TransactionInterface
{
    public function calculateCommission(string $date, int $userId, float $amount, string $currency): float;
}
