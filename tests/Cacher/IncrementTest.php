<?php
declare(strict_types=1);

namespace Tests\Cacher;

use
    Fyre\Cache\Exceptions\CacheException;

trait IncrementTest
{

    public function testIncrement(): void
    {
        $this->assertEquals(
            1,
            $this->cache->increment('test')
        );
    }

    public function testIncrementAmount(): void
    {
        $this->assertEquals(
            5,
            $this->cache->increment('test', 5)
        );
    }

    public function testIncrementPersists(): void
    {
        $this->cache->increment('test');

        $this->assertEquals(
            1,
            $this->cache->get('test')
        );
    }

    public function testIncrementExisting(): void
    {
        $this->cache->save('test', 5);
        $this->cache->increment('test');

        $this->assertEquals(
            6,
            $this->cache->get('test')
        );
    }

    public function testIncrementInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->increment('test/');
    }

}
