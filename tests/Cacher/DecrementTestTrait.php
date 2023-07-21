<?php
declare(strict_types=1);

namespace Tests\Cacher;

use Fyre\Cache\Exceptions\CacheException;

trait DecrementTestTrait
{

    public function testDecrement(): void
    {
        $this->cache->save('test', 5);

        $this->assertSame(
            4,
            $this->cache->decrement('test')
        );
    }

    public function testDecrementAmount(): void
    {
        $this->cache->save('test', 10);

        $this->assertSame(
            5,
            $this->cache->decrement('test', 5)
        );
    }

    public function testDecrementPersists(): void
    {
        $this->cache->save('test', 5);
        $this->cache->decrement('test');

        $this->assertSame(
            4,
            $this->cache->get('test')
        );
    }

    public function testDecrementInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->decrement('test/');
    }

}
