<?php
declare(strict_types=1);

namespace Beauty\Cache\Drivers;

use Beauty\Cache\Repositories\ArrayCache;
use Psr\SimpleCache\CacheInterface;

class ArrayCacheDriver implements CacheDriverInterface
{

    /**
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool
    {
        return $driver === 'array';
    }

    /**
     * @param array $config
     * @return CacheInterface
     */
    public function make(array $config): CacheInterface
    {
        return new ArrayCache();
    }
}