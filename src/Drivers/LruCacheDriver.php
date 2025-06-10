<?php
declare(strict_types=1);

namespace Beauty\Cache\Drivers;

use Beauty\Cache\Repositories\LruCache;
use Psr\SimpleCache\CacheInterface;

class LruCacheDriver implements CacheDriverInterface
{

    /**
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool
    {
        return $driver === 'lru';
    }

    /**
     * @param array $config
     * @return CacheInterface
     */
    public function make(array $config): CacheInterface
    {
        return new LruCache(new \Vasqo\LRU\LRUCache($config['size']));
    }
}