<?php
declare(strict_types=1);

namespace Beauty\Cache\Drivers;

use Psr\SimpleCache\CacheInterface;

interface CacheDriverInterface
{
    /**
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool;

    /**
     * @param array $config
     * @return CacheInterface
     */
    public function make(array $config): CacheInterface;
}