<?php
declare(strict_types=1);

namespace App\Tests\Service;
session_start(); // Ensure session is started for testing
use App\Service\Transactions\Deposit;
use App\Service\Transactions\WithdrawBusiness;
use App\Service\Transactions\WithdrawPrivate;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private array $exchangeRates;

    protected function setUp(): void
    {

        $_SESSION['weeklyWithdrawals'] = [];

        $this->exchangeRates = [
            'EUR' => 1.0,
            'USD' => 1.1497,
            'JPY' => 129.53
        ];
    }

    public function testDepositCommission(): void
    {
        $deposit = new Deposit();
        $this->assertEquals(0.06, $deposit->calculateCommission('2024-03-11', 1, 200.00, 'EUR'));
    }

    public function testWithdrawBusinessCommission(): void
    {
        $withdrawBusiness = new WithdrawBusiness();
        $this->assertEquals(1.50, $withdrawBusiness->calculateCommission('2024-03-11', 2, 300.00, 'EUR'));
    }

    public function testWithdrawPrivateFreeLimit(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $this->assertEquals(0.00, $withdrawPrivate->calculateCommission('2024-03-11', 1, 1000.00, 'EUR'));
    }

    public function testWithdrawPrivateExceedingLimit(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $this->assertEquals(0.90, $withdrawPrivate->calculateCommission('2024-03-11', 1, 1300.00, 'EUR'));
    }

    public function testWithdrawPrivateMultipleTransactionsWithinLimit(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $withdrawPrivate->calculateCommission('2024-03-11', 1, 500.00, 'EUR');
        $withdrawPrivate->calculateCommission('2024-03-12', 1, 300.00, 'EUR');
        $fee = $withdrawPrivate->calculateCommission('2024-03-13', 1, 200.00, 'EUR');
        $this->assertEquals(0.00, $fee);
    }

    public function testWithdrawPrivateExceedingLimitWithMultipleTransactions(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $withdrawPrivate->calculateCommission('2024-03-11', 1, 500.00, 'EUR');
        $withdrawPrivate->calculateCommission('2024-03-12', 1, 300.00, 'EUR');
        $withdrawPrivate->calculateCommission('2024-03-13', 1, 200.00, 'EUR');
        $fee = $withdrawPrivate->calculateCommission('2024-03-14', 1, 300.00, 'EUR');
        $this->assertEquals(0.90, $fee); // 300 * 0.003
    }

    public function testWithdrawPrivateExceedingTransactionCount(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $this->assertEquals(0.00, $withdrawPrivate->calculateCommission('2024-03-11', 1, 100.00, 'EUR'));
        $this->assertEquals(0.00, $withdrawPrivate->calculateCommission('2024-03-12', 1, 100.00, 'EUR'));
        $this->assertEquals(0.00, $withdrawPrivate->calculateCommission('2024-03-13', 1, 100.00, 'EUR'));
        $this->assertEquals(0.30, $withdrawPrivate->calculateCommission('2024-03-14', 1, 100.00, 'EUR'));
    }

    public function testWithdrawPrivateCurrencyConversion(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $fee = $withdrawPrivate->calculateCommission('2024-03-11', 1, 30000, 'JPY');
        $this->assertEqualsWithDelta(0.00, $fee, 0.01);
    }

    public function testSessionPersistence(): void
    {
        $withdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $withdrawPrivate->calculateCommission('2024-03-11', 1, 1000.00, 'EUR');
        session_write_close();

        $newWithdrawPrivate = new WithdrawPrivate($this->exchangeRates);
        $fee = $newWithdrawPrivate->calculateCommission('2024-03-12', 1, 500.00, 'EUR');
        $this->assertEquals(0.00, $fee);
    }
}