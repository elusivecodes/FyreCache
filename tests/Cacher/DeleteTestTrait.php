<?php
declare(strict_types=1);

namespace Tests\Cacher;

use Fyre\Cache\Exceptions\CacheException;

trait DeleteTestTrait
{

    public function testDelete(): void
    {
        $this->cache->save('test', 'value');

        $this->assertTrue(
            $this->cache->delete('test')
        );

        $this->assertFalse(
            $this->cache->has('test')
        );
    }

    public function testDeleteMissing(): void
    {
        $this->assertFalse(
            $this->cache->delete('missing')
        );
    }

    public function testDeleteInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->delete('test/');
    }

}
