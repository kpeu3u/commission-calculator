<?php
declare(strict_types=1);
namespace App;

require __DIR__ . '/vendor/autoload.php';

session_start();
use App\Service\CommissionCalculator;
use Exception;

if ($argc < 2) {
    echo "Usage: php script.php input.csv" . PHP_EOL;
    exit(1);
}

$calculator = new CommissionCalculator();
$inputFile = $argv[1] ?? 'input.csv';

try {
    $calculator->processCSV($inputFile);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}