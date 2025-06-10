<?php
declare(strict_types=1);

namespace Beauty\Cache\Repositories;

use DateInterval;
use DateTime;
use Redis;

class RedisCache implements CacheInterface
{

    /**
     * @param Redis $redis
     * @param string $prefix
     */
    public function __construct(
        protected Redis $redis,
        protected string $prefix = '',
    )
    {
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $val = $this->redis->get($this->key($key));
        return $val !== false ? unserialize($val) : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param DateInterval|int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $ttl = $this->normalizeTtl($ttl);
        $key = $this->key($key);
        $value = serialize($value);

        return $ttl > 0 ? $this->redis->setex($key, $ttl, $value) : $this->redis->set($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return (bool) $this->redis->del($this->key($key));
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    /**
     * @param iterable $values
     * @param DateInterval|int|null $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) return false;
        }
        return true;
    }

    /**
     * @param iterable $keys
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $deleted = true;
        foreach ($keys as $key) {
            $deleted &= $this->delete($key);
        }
        return $deleted;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->redis->exists($this->key($key)) > 0;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function key(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * @param int|DateInterval|null $ttl
     * @return int
     */
    protected function normalizeTtl(null|int|DateInterval $ttl): int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTime())->add($ttl)->getTimestamp() - time();
        }
        return $ttl ?? 0;
    }
}