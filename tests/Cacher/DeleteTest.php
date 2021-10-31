<?php
declare(strict_types=1);

namespace Tests\Cacher;

use
    Fyre\Cache\Exceptions\CacheException;

trait DeleteTest
{

    public function testDelete(): void
    {
        $this->cache->save('test', 'value');

        $this->assertEquals(
            true,
            $this->cache->delete('test')
        );

        $this->assertEquals(
            false,
            $this->cache->has('test')
        );
    }

    public function testDeleteMissing(): void
    {
        $this->assertEquals(
            false,
            $this->cache->delete('missing')
        );
    }

    public function testDeleteInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->delete('test/');
    }

}
