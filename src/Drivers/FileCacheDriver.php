<?php
declare(strict_types=1);

namespace Beauty\Cache\Drivers;

use Beauty\Cache\Repositories\FileCache;
use Psr\SimpleCache\CacheInterface;

class FileCacheDriver implements CacheDriverInterface
{

    /**
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool
    {
        return $driver === 'file';
    }

    /**
     * @param array $config
     * @return CacheInterface
     */
    public function make(array $config): CacheInterface
    {
        return new FileCache($config['path']);
    }
}