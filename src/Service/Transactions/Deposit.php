<?php

declare(strict_types=1);

namespace App\Service\Transactions;

class Deposit implements TransactionInterface
{
    private const DEPOSIT_FEE = 0.0003;

    public function calculateCommission(string $date, int $userId, float $amount, string $currency): float
    {
        return $amount * self::DEPOSIT_FEE;
    }
}
