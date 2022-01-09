<?php
declare(strict_types=1);

namespace Tests\Cacher;

use
    Fyre\Cache\Exceptions\CacheException;

trait RememberTest
{

    public function testRemember(): void
    {
        $this->cache->save('test', 1);

        $this->assertSame(
            1,
            $this->cache->remember('test', fn() => 2)
        );
    }

    public function testRememberMissing(): void
    {
        $this->assertSame(
            2,
            $this->cache->remember('test', fn() => 2)
        );
    }

    public function testRememberPersists(): void
    {
        $this->cache->remember('test', fn() => 2);

        $this->assertSame(
            2,
            $this->cache->get('test')
        );
    }

    public function testRememberExpiry(): void
    {
        $this->cache->remember('test', fn() => 2, 1);

        sleep(2);

        $this->assertNull(
            $this->cache->get('test')
        );
    }

    public function testRememberInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->remember('test/', fn() => 2);
    }

}
