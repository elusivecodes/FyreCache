<?php
declare(strict_types=1);

namespace Tests\Cacher;

use
    Fyre\Cache\Exceptions\CacheException;

trait HasTest
{

    public function testHas(): void
    {
        $this->cache->save('test', 1);

        $this->assertTrue(
            $this->cache->has('test')
        );
    }

    public function testHasMissing(): void
    {
        $this->assertFalse(
            $this->cache->has('test')
        );
    }

    public function testHasInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->has('test/');
    }

}
