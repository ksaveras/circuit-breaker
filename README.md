# Circuit Breaker
![Travis (.org) branch](https://img.shields.io/travis/ksaveras/circuit-breaker/master)
[![Maintainability](https://api.codeclimate.com/v1/badges/ff7c6a6aafed0d8e49f1/maintainability)](https://codeclimate.com/github/ksaveras/circuit-breaker/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/ff7c6a6aafed0d8e49f1/test_coverage)](https://codeclimate.com/github/ksaveras/circuit-breaker/test_coverage)
![PHPStan Level](https://img.shields.io/badge/PHPStan%20Level-7-brightgreen)
![GitHub](https://img.shields.io/github/license/ksaveras/circuit-breaker)

More information: https://martinfowler.com/bliki/CircuitBreaker.html

## Installation
```
composer require ksaveras/circuit-breaker
```

## Use

Simple circuit check
```php
use \Ksaveras\CircuitBreaker\Storage\Apcu;
use \Ksaveras\CircuitBreaker\CircuitBreaker;
use \Ksaveras\CircuitBreaker\Factory\CircuitFactory;

$storage = new Apcu();
$period = 300;

$circuitBreaker = new CircuitBreaker('service-api', $storage, new CircuitFactory($period));
$circuitBreaker->setFailureThreshold(3);

if ($circuitBreaker->isAvailable()) {
    try {
        // call 3rd party service api
        $circuitBreaker->success();
    } catch (\Exception $exception) {
        $circuitBreaker->failure();
    }   
}
```

Use callback
```php
use \Ksaveras\CircuitBreaker\Storage\Apcu;
use \Ksaveras\CircuitBreaker\CircuitBreaker;
use \Ksaveras\CircuitBreaker\Exception\OpenCircuitException;
use \Ksaveras\CircuitBreaker\Factory\CircuitFactory;

$storage = new Apcu();
$period = 300;

$circuitBreaker = new CircuitBreaker('service-api', $storage, new CircuitFactory($period));
$circuitBreaker->setFailureThreshold(3);

try {
    $circuitBreaker->call(
        function () {
            $this->callApi();
        }
    );
} catch (OpenCircuitException $exception) {
    // Open circuit
} catch (\Exception $exception) {
    // 3rd party exception
}
```

## Tests
```
composer test
```

## Code quality
```
composer phpstan
composer phpcsfix
```
