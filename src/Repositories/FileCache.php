<?php
declare(strict_types=1);

namespace Beauty\Cache\Repositories;

use DateInterval;
use DateTime;

class FileCache implements CacheInterface
{
    /**
     * @param string $directory
     */
    public function __construct(
        protected string $directory
    )
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->path($key);
        if (!file_exists($file)) return $default;

        $data = unserialize(file_get_contents($file));
        if ($data['expires'] !== null && $data['expires'] < time()) {
            unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param DateInterval|int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        $expires = $this->normalizeTtl($ttl);
        $data = [
            'expires' => $expires ? time() + $expires : null,
            'value' => $value,
        ];

        return file_put_contents($this->path($key), serialize($data)) !== false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $file = $this->path($key);
        return !file_exists($file) || unlink($file);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        foreach (glob($this->directory . '/*.cache') as $file) {
            unlink($file);
        }
        return true;
    }

    /**
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @param iterable $values
     * @param DateInterval|int|null $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
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
        foreach ($keys as $key) {
            if (!$this->delete($key)) return false;
        }
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return file_exists($this->path($key));
    }

    /**
     * @param int|DateInterval|null $ttl
     * @return int|null
     */
    protected function normalizeTtl(null|int|DateInterval $ttl): ?int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTime())->add($ttl)->getTimestamp() - time();
        }
        return $ttl;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function path(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . sha1($key) . '.cache';
    }
}