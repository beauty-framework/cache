<?php
declare(strict_types=1);

namespace Beauty\Cache\Drivers;

use Beauty\Cache\Repositories\RedisCache;
use Psr\SimpleCache\CacheInterface;

class RedisCacheDriver implements CacheDriverInterface
{

    /**
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool
    {
        return $driver === 'redis';
    }

    /**
     * @param array $config
     * @return CacheInterface
     */
    public function make(array $config): CacheInterface
    {
        $redis = new \Redis();
        $redis->connect($config['host'], $config['port']);
        $redis->auth([
            'username' => $config['auth']['username'] ?? null,
            'password' => $config['auth']['password'] ?? null,
        ]);

        $redis->select($config['database']);

        return new RedisCache($redis, $config['prefix'] ?? '');
    }
}