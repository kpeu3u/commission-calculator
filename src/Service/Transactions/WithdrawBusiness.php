<?php

declare(strict_types=1);

namespace App\Service\Transactions;

class WithdrawBusiness implements TransactionInterface
{
    private const BUSINESS_WITHDRAW_FEE = 0.005;

    public function calculateCommission(string $date, int $userId, float $amount, string $currency): float
    {
        return $amount * self::BUSINESS_WITHDRAW_FEE;
    }
}
