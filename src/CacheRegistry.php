<?php
declare(strict_types=1);

namespace Beauty\Cache;

use Psr\SimpleCache\CacheInterface;

class CacheRegistry
{
    private array $resolved = [];

    public function __construct(
        private array $caches,
        private CacheFactory $factory,
        private string $default = 'default'
    )
    {
    }

    public function get(?string $name = null): CacheInterface
    {
        $name ??= $this->default;

        if (!isset($this->caches[$name])) {
            throw new \InvalidArgumentException("Cache [$name] is not configured.");
        }

        if (!isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->factory->create($this->caches[$name]);
        }

        return $this->resolved[$name];
    }

    public function set(string $name, CacheInterface $cache): void
    {
        $this->resolved[$name] = $cache;
    }

    public function has(string $name): bool
    {
        return isset($this->caches[$name]);
    }

    public function names(): array
    {
        return array_keys($this->caches);
    }

    public function getDefaultCacheName(): string
    {
        return $this->default;
    }
}