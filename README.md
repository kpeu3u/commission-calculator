# Commission Fee Calculator

## Overview
This PHP application calculates commission fees for deposit and withdrawal transactions based on predefined rules. The commission fees vary depending on the user type (private/business) and transaction details. It fetches real-time exchange rates from `https://api.exchangeratesapi.io/latest` using an API key for accurate currency conversion.

## Features
- **Deposits** are charged a **0.03%** commission.
- **Withdrawals**:
 - **Private clients**:
  - **First 1000 EUR per week (up to 3 transactions) is free**.
  - **0.3% commission** is applied to any amount exceeding the free limit.
 - **Business clients**:
  - **0.5% commission** applies to all withdrawals.
- **Fetches real-time exchange rates via API with authentication.**
- **Rounding** rules applied based on currency precision.
- **PSR-4 compliant** with Composer autoloading.
- **Automated tests** included using PHPUnit.

## Installation
1. Clone the repository or download the files.
2. Navigate to the project directory:
   ```sh
   cd commission-calculator
   ```
3. Install dependencies using Composer:
   ```sh
   composer install
   ```

## Configuration
To use a different API authentication key, update the `CommissionCalculator.php` file:
```php
private const API_KEY = 'your_api_key_here';
```

## Usage
To process a CSV file and calculate commission fees:
```sh
php script.php input.csv
```

## Running Tests
To run automated tests:
```sh
composer test
```


## Folder Structure
```
commission-calculator/
│── src/
│   ├── Transactions/
│   │   ├── TransactionInterface.php
│   │   ├── Deposit.php
│   │   ├── WithdrawBusiness.php
│   │   ├── WithdrawPrivate.php
│   ├── CommissionCalculator.php
│── tests/
│   ├── CommissionCalculatorTest.php
│── vendor/  # (Created after running `composer install`)
│── .gitignore # (Excludes vendor directory)
│── .php-cs-fixer.dist.php  # (PHP Coding Standards Fixer configuration)
│── composer.json
│── composer.lock  # (Generated after running `composer install`)
│── input.csv  # (Sample input file for testing)
│── phpstan.neon.dist  # (PHPStan configuration)
│── phpunit.xml.dist  # (PHPUnit configuration)
│── script.php  # (Bootstrap to run the script)
│── README.md  # (Instructions and documentation)
```

## License
This project is provided for educational purposes.
