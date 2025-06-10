<?php
declare(strict_types=1);

namespace Beauty\Cache;

use Psr\SimpleCache\CacheInterface;

class CacheFactory
{
    public function __construct(
        private iterable $drivers,
    )
    {
    }

    public function create(array $config): CacheInterface
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($config['driver'] ?? '')) {
                return $driver->make($config);
            }
        }

        throw new \InvalidArgumentException("Unsupported cache driver: {$config['driver']}");
    }
}