<?php
declare(strict_types=1);

namespace Beauty\Cache\Drivers;

use Psr\SimpleCache\CacheInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\KeyValue\Factory;

class KVCacheDriver implements CacheDriverInterface
{
    /**
     * @param string $driver
     * @return bool
     */
    public function supports(string $driver): bool
    {
        return $driver === 'roadrunner-kv';
    }

    /**
     * @param array $config
     * @return CacheInterface
     */
    public function make(array $config): CacheInterface
    {
        $factory = new Factory(RPC::create($config['rpc']));

        return $factory->select($config['store']);
    }
}