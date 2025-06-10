<?php
declare(strict_types=1);

namespace Beauty\Cache\Repositories;

class LruCache implements CacheInterface
{
    /**
     * @param \Vasqo\LRU\LRUCache $lru
     */
    public function __construct(
        protected \Vasqo\LRU\LRUCache $lru,
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
        $val = $this->lru->get($key);
        return $val !== null ? unserialize($val) : $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param \DateInterval|int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->lru->put($key, serialize($value));
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $this->lru->remove($key);
        return true;
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return false;
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
     * @param \DateInterval|int|null $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
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
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->lru->get($key) !== null;
    }
}