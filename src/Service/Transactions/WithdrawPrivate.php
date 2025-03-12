<?php

declare(strict_types=1);

namespace App\Service\Transactions;

class WithdrawPrivate implements TransactionInterface
{
    private array $exchangeRates;
    protected array $weeklyWithdrawals = [];
    private const FREE_WEEKLY_WITHDRAW_LIMIT = 1000.00;
    private const PRIVATE_WITHDRAW_FEE = 0.003;
    private const FREE_WITHDRAWALS_COUNT = 3;

    public function __construct(array $exchangeRates)
    {
        $this->exchangeRates = $exchangeRates;
        $this->loadWeeklyWithdrawals();
    }

    public function __destruct()
    {
        $this->saveWeeklyWithdrawals();
    }

    public function calculateCommission(string $date, int $userId, float $amount, string $currency): float
    {
        $convertedAmount = $this->convertToEUR($amount, $currency);
        $week = $this->getWeekNumber($date);

        $this->initializeWeeklyWithdrawals($userId, $week);
        $this->updateWeeklyWithdrawals($userId, $week, $convertedAmount);

        if ($this->isExceedingFreeLimit($userId, $week)) {
            return $this->calculateExcessCommission($userId, $week, $currency);
        }

        if ($this->isExceedingFreeWithdrawals($userId, $week)) {
            return $this->calculateFullCommission($convertedAmount, $currency);
        }

        return 0.0;
    }

    private function convertToEUR(float $amount, string $currency): float
    {
        return $amount / $this->exchangeRates[$currency];
    }

    private function convertToOriginalCurrency(float $amount, string $currency): float
    {
        return $amount * $this->exchangeRates[$currency];
    }

    private function getWeekNumber(string $date): string
    {
        return date('oW', strtotime($date));
    }

    private function initializeWeeklyWithdrawals(int $userId, string $week): void
    {
        if (!isset($this->weeklyWithdrawals[$userId][$week])) {
            $this->weeklyWithdrawals[$userId][$week] = ['count' => 0, 'amount' => 0.0, 'excess' => 0.0];
        }
    }

    private function updateWeeklyWithdrawals(int $userId, string $week, float $amount): void
    {
        ++$this->weeklyWithdrawals[$userId][$week]['count'];
        $this->weeklyWithdrawals[$userId][$week]['amount'] += $amount;
    }

    private function isExceedingFreeLimit(int $userId, string $week): bool
    {
        return $this->weeklyWithdrawals[$userId][$week]['amount'] > self::FREE_WEEKLY_WITHDRAW_LIMIT;
    }

    private function isExceedingFreeWithdrawals(int $userId, string $week): bool
    {
        return $this->weeklyWithdrawals[$userId][$week]['count'] > self::FREE_WITHDRAWALS_COUNT;
    }

    private function calculateExcessCommission(int $userId, string $week, string $currency): float
    {
        $excessAmount = max(0, $this->weeklyWithdrawals[$userId][$week]['amount'] - self::FREE_WEEKLY_WITHDRAW_LIMIT - $this->weeklyWithdrawals[$userId][$week]['excess']);
        $this->weeklyWithdrawals[$userId][$week]['excess'] += $excessAmount;

        return $this->convertToOriginalCurrency($excessAmount * self::PRIVATE_WITHDRAW_FEE, $currency);
    }

    private function calculateFullCommission(float $amount, string $currency): float
    {
        return $this->convertToOriginalCurrency($amount * self::PRIVATE_WITHDRAW_FEE, $currency);
    }

    private function loadWeeklyWithdrawals(): void
    {
        if (isset($_SESSION['weeklyWithdrawals'])) {
            $this->weeklyWithdrawals = $_SESSION['weeklyWithdrawals'];
        }
    }

    private function saveWeeklyWithdrawals(): void
    {
        $_SESSION['weeklyWithdrawals'] = $this->weeklyWithdrawals;
    }
}
