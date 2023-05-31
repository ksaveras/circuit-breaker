# Circuit Breaker

More information: https://martinfowler.com/bliki/CircuitBreaker.html

## Installation
```
composer require ksaveras/circuit-breaker
```

## Use

Simple circuit check using Symfony Redis cache

```php
use Ksaveras\CircuitBreaker\CircuitBreakerFactory;
use Ksaveras\CircuitBreaker\Storage\CacheStorage;
use Ksaveras\CircuitBreaker\Policy\ExponentialRetryPolicy;
use Symfony\Component\Cache\Adapter\RedisAdapter;

$redisAdapter = new RedisAdapter(
    RedisAdapter::createConnection('redis://localhost'),
    // the namespace for circuit breaker storage
    'circuit-breaker',
    // max TTL is set to 24 hours
    86400
);

$factory = new CircuitBreakerFactory(
    // max 3 failures before setting circuit breaker as open
    3,
    new CacheStorage($redisAdapter),
    // exponential retry starting with 30 seconds
    new ExponentialRetryPolicy(30),
);

$circuitBreaker = $factory->create('service-api');

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
use Ksaveras\CircuitBreaker\CircuitBreakerFactory;
use Ksaveras\CircuitBreaker\Storage\CacheStorage;
use Ksaveras\CircuitBreaker\Policy\ExponentialRetryPolicy;
use Symfony\Component\Cache\Adapter\RedisAdapter;

$redisAdapter = new RedisAdapter(
    RedisAdapter::createConnection('redis://localhost'),
    // the namespace for circuit breaker storage
    'circuit-breaker',
    // max TTL is set to 24 hours
    86400
);

$factory = new CircuitBreakerFactory(
    // max 3 failures before setting circuit breaker as open
    3,
    new CacheStorage($redisAdapter),
    // exponential retry starting with 30 seconds
    new ExponentialRetryPolicy(30),
);

$circuitBreaker = $factory->create('service-api');

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

## Code Quality
```
composer static-analysis
```
