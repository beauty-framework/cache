<?php
declare(strict_types=1);

namespace Beauty\Cache\Repositories;

use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    private string $cacheDir;
    private CacheInterface $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/beauty-cache-test-' . bin2hex(random_bytes(4));
        mkdir($this->cacheDir, recursive: true);
        $this->cache = new FileCache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir . '/*.cache') as $file) {
            unlink($file);
        }
        rmdir($this->cacheDir);
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('foo', 'bar');
        $this->assertSame('bar', $this->cache->get('foo'));
    }

    public function testReturnsDefaultWhenKeyMissing(): void
    {
        $this->assertSame('default', $this->cache->get('not-found', 'default'));
    }

    public function testHas(): void
    {
        $this->cache->set('check', 123);
        $this->assertTrue($this->cache->has('check'));

        $this->assertFalse($this->cache->has('missing'));
    }

    public function testDelete(): void
    {
        $this->cache->set('to-delete', 'value');
        $this->assertTrue($this->cache->has('to-delete'));

        $this->cache->delete('to-delete');
        $this->assertFalse($this->cache->has('to-delete'));
    }

    public function testClear(): void
    {
        $this->cache->set('key1', 'val1');
        $this->cache->set('key2', 'val2');

        $this->cache->clear();

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function testTtlExpires(): void
    {
        $this->cache->set('short_lived', 'temp', 1);
        sleep(2);

        $this->assertNull($this->cache->get('short_lived'));
    }
}
