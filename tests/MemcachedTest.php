<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Cache\Cache,
    Fyre\Cache\Cacher,
    Fyre\Cache\Exceptions\CacheException,
    Fyre\Cache\Handlers\MemcachedCacher,
    PHPUnit\Framework\TestCase,
    Tests\Cacher\DecrementTest,
    Tests\Cacher\DeleteTest,
    Tests\Cacher\EmptyTest,
    Tests\Cacher\HasTest,
    Tests\Cacher\IncrementTest,
    Tests\Cacher\Remembertest,
    Tests\Cacher\SaveGetTest;

final class MemcachedTest extends TestCase
{

    protected Cacher $cache;

    use
        DecrementTest,
        DeleteTest,
        EmptyTest,
        HasTest,
        IncrementTest,
        Remembertest,
        SaveGetTest;

    public function testSize(): void
    {
        $this->cache->save('test', 'value');

        $this->assertEquals(
            75,
            $this->cache->size()
        );
    }

    public function testSizeEmpty(): void
    {
        $this->assertEquals(
            75,
            $this->cache->size()
        );
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);

        Cache::load([
            'className' =>  MemcachedCacher::class,
            'host' => '1.1.1.1'
        ]);
    }
    
    protected function setUp(): void
    {
        Cache::clear();
        Cache::setConfig('default', [
            'className' =>  MemcachedCacher::class,
            'host' => getenv('MEMCACHED_HOST'),
            'port' => getenv('MEMCACHED_PORT'),
            'prefix' => 'prefix.'
        ]);

        $this->cache = Cache::use();
    }

    protected function tearDown(): void
    {
        $this->cache->empty();
    }

}
