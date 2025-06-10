<?php
declare(strict_types=1);

namespace Beauty\Cache\Tests\Repositories;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Vasqo\LRU\LRUCache;

class LruCacheTest extends TestCase
{
    private CacheInterface $cache;

    protected function setUp(): void
    {
        $lru = new LRUCache(capacity: 3);
        $this->cache = new \Beauty\Cache\Repositories\LruCache($lru);
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('foo', 'bar');
        $this->assertSame('bar', $this->cache->get('foo'));
    }

    public function testGetReturnsDefaultIfMissing(): void
    {
        $this->assertSame('default', $this->cache->get('missing', 'default'));
    }

    public function testHas(): void
    {
        $this->cache->set('exists', 1);
        $this->assertTrue($this->cache->has('exists'));

        $this->assertFalse($this->cache->has('not_exists'));
    }

    public function testDelete(): void
    {
        $this->cache->set('key', 'value');
        $this->assertTrue($this->cache->has('key'));

        $this->cache->delete('key');
        $this->assertFalse($this->cache->has('key'));
    }

    public function testEvictionOnOverflow(): void
    {
        $this->cache->set('a', 1);
        $this->cache->set('b', 2);
        $this->cache->set('c', 3);

        // Access 'a' to mark it as recently used
        $this->cache->get('a');

        // Insert fourth, should evict 'b'
        $this->cache->set('d', 4);

        $this->assertTrue($this->cache->has('a'));
        $this->assertFalse($this->cache->has('b'));
        $this->assertTrue($this->cache->has('c'));
        $this->assertTrue($this->cache->has('d'));
    }
}
