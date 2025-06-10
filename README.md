# Beauty Cache

A simple, PSR-16-compatible cache component for the Beauty Framework. Supports multiple cache backends (drivers), including in-memory array cache, Redis (via ext-redis), file cache, and RoadRunner key-value plugin.

## Features

* ✅ PSR-16 `CacheInterface` compliant
* ✅ Simple driver system
* ✅ Lazy connection resolution via `CacheRegistry`
* ✅ Easy to extend with custom drivers
* ✅ Clean, typed API

## Supported Drivers

| Driver ID       | Description                              | TTL Support | Flush Support | Requirements          |
|-----------------|------------------------------------------|-------------|---------------|-----------------------|
| `array`         | In-memory array cache                    | ❌           | ✅             | none                  |
| `file`          | File-based cache storage                 | ✅           | ✅             | none                  |
| `redis`         | Redis-based cache via ext-redis          | ✅           | ✅             | PHP extension `redis` |
| `roadrunner-kv` | RoadRunner KeyValue plugin (gRPC bridge) | ⚠️ Partial  | ✅             | RoadRunner + plugin   |
| `memory`        | LRU cache in memory                      | ❌           | ❌             | LRU cache             |
> ℹ️ `memcached` is not supported out of the box but can be added as a custom driver (see example below).

## Installation

```bash
composer require beauty-framework/cache
```

## Configuration Example

```php
use Beauty\Cache\CacheRegistry;
use Beauty\Cache\CacheFactory;
use Beauty\Cache\Driver\ArrayCacheDriver;
use Beauty\Cache\Driver\FileCacheDriver;
use Beauty\Cache\Driver\RedisCacheDriver;
use Beauty\Cache\Driver\KVCacheDriver;

$registry = new CacheRegistry(
    caches: [
        'redis' => ['driver' => 'redis', 'host' => '127.0.0.1', 'port' => 6379],
        'file' => ['driver' => 'file', 'directory' => __DIR__.'/storage/cache'],
        'kv' => ['driver' => 'roadrunner-kv', 'store' => 'memory']
    ],
    factory: new CacheFactory([
        new ArrayCacheDriver(),
        new FileCacheDriver(),
        new RedisCacheDriver(),
        new KVCacheDriver(),
    ]),
    default: 'redis'
);

$cache = $registry->get();
$cache->set('foo', 'bar');
echo $cache->get('foo'); // bar
```

## Custom Driver Example: Memcached
```php
use Memcached;
use Psr\SimpleCache\CacheInterface;

class Memcache implements CacheInterface 
{
    public function __construct(private Memcached $memcached) {}
    
    public function get(string $key, mixed $default = null): mixed {
        $value = $this->memcached->get($key);
        return $value === false ? $default : $value;
    }
    
    public function set(string $key, mixed $value, $ttl = null): bool {
        return $this->memcached->set($key, $value, $ttl ?? 0);
    }
    
    public function delete(string $key): bool {
        return $this->memcached->delete($key);
    }
    
    public function clear(): bool {
        return $this->memcached->flush();
    }
    
    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }
    public function setMultiple(iterable $values, $ttl = null): bool {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) return false;
        }
        return true;
    }
    
    public function deleteMultiple(iterable $keys): bool {
        foreach ($keys as $key) {
            if (!$this->delete($key)) return false;
        }
        return true;
    }
    
    public function has(string $key): bool {
        return $this->memcached->get($key) !== false;
    }
}    
```

```php
use Beauty\Cache\Driver\CacheDriverInterface;
use Psr\SimpleCache\CacheInterface;
use Memcached;

class MemcachedCacheDriver implements CacheDriverInterface
{
    public function supports(string $driver): bool
    {
        return $driver === 'memcached';
    }

    public function make(array $config): CacheInterface
    {
        $memcached = new Memcached();
        $memcached->addServer($config['host'] ?? '127.0.0.1', $config['port'] ?? 11211);

        return new 
}
```

## License

MIT
