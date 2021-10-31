<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Cache\Cache,
    Fyre\Cache\Cacher,
    Fyre\Cache\Exceptions\CacheException,
    Fyre\Cache\Handlers\RedisCacher,
    PHPUnit\Framework\TestCase,
    Tests\Cacher\DecrementTest,
    Tests\Cacher\DeleteTest,
    Tests\Cacher\EmptyTest,
    Tests\Cacher\HasTest,
    Tests\Cacher\IncrementTest,
    Tests\Cacher\Remembertest,
    Tests\Cacher\SaveGetTest;

final class RedisTest extends TestCase
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
            859296,
            $this->cache->size()
        );
    }

    public function testSizeEmpty(): void
    {
        $this->assertEquals(
            921024,
            $this->cache->size()
        );
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);

        Cache::load([
            'className' =>  RedisCacher::class,
            'host' => '1.1.1.1'
        ]);
    }
    
    public function testInvalidAuth(): void
    {
        $this->expectException(CacheException::class);

        Cache::load([
            'className' =>  RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => 'invalid'
        ]);
    }
    
    protected function setUp(): void
    {
        Cache::clear();
        Cache::setConfig('default', [
            'className' =>  RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => getenv('REDIS_PASSWORD'),
            'database' => getenv('REDIS_DATABASE'),
            'port' => getenv('REDIS_PORT'),
            'prefix' => 'prefix.'
        ]);

        $this->cache = Cache::use();
    }

    protected function tearDown(): void
    {
        $this->cache->empty();
    }

}
