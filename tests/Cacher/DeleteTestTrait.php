<?php
declare(strict_types=1);

namespace Tests\Cacher;

use Fyre\Cache\Exceptions\CacheException;

trait DeleteTestTrait
{
    public function testDelete(): void
    {
        $this->cache->set('test', 'value');

        $this->assertTrue(
            $this->cache->delete('test')
        );

        $this->assertFalse(
            $this->cache->has('test')
        );
    }

    public function testDeleteInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->delete('test/');
    }

    public function testDeleteMissing(): void
    {
        $this->assertFalse(
            $this->cache->delete('missing')
        );
    }

    public function testDeleteMultiple(): void
    {
        $this->cache->set('test1', 'value1');
        $this->cache->set('test2', 'value2');

        $this->assertTrue(
            $this->cache->deleteMultiple(['test1', 'test2'])
        );

        $this->assertFalse(
            $this->cache->has('test1')
        );

        $this->assertFalse(
            $this->cache->has('test2')
        );
    }
}
