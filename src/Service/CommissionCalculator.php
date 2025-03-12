<?php

declare(strict_types=1);

namespace App\Service;

require 'vendor/autoload.php';

use App\Service\Transactions\Deposit;
use App\Service\Transactions\WithdrawBusiness;
use App\Service\Transactions\WithdrawPrivate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CommissionCalculator
{
    private array $exchangeRates;
    private Client $httpClient;
    private const API_KEY = 'your_api_key_here';

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->exchangeRates = $this->fetchExchangeRates();
    }

    private function fetchExchangeRates(): array
    {
        try {
            $response = $this->httpClient->get('https://api.exchangeratesapi.io/v1/latest', [
                'query' => [
                    'access_key' => self::API_KEY,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            return $data['rates'] ?? [];
        } catch (GuzzleException $e) {
            echo 'Failed to fetch exchange rates. Using default values. Error: ('.$e->getMessage().') '.PHP_EOL;

            return [
                'EUR' => 1.0,
                'USD' => 1.1497,
                'JPY' => 129.53,
            ];
        }
    }

    /**
     * @throws \Exception
     */
    public function processCSV(string $filename): void
    {
        if (!file_exists($filename)) {
            throw new \Exception("File not found: $filename");
        }

        $file = fopen($filename, 'r');
        while (($line = fgetcsv($file)) !== false) {
            $this->processOperation($line);
        }
        fclose($file);
    }

    /**
     * @throws \Exception
     */
    private function processOperation(array $data): void
    {
        [$date, $userId, $userType, $operationType, $amount, $currency] = $data;
        $amount = (float) $amount;

        $transaction = match ($operationType) {
            'deposit' => new Deposit(),
            'withdraw' => $userType === 'private'
                ? new WithdrawPrivate($this->exchangeRates)
                : new WithdrawBusiness(),
            default => throw new \Exception("Unsupported operation type: $operationType"),
        };

        $fee = $transaction->calculateCommission($date, (int) $userId, $amount, $currency);
        echo $this->roundUp($fee, $currency).PHP_EOL;
    }

    private function roundUp(float $amount, string $currency): string
    {
        $precision = ($currency === 'JPY') ? 0 : 2;

        return number_format(ceil($amount * pow(10, $precision)) / pow(10, $precision), $precision, '.', '');
    }
}
