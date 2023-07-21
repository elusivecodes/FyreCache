<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\Cache;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\RedisCacher;
use PHPUnit\Framework\TestCase;
use Tests\Cacher\DecrementTestTrait;
use Tests\Cacher\DeleteTestTrait;
use Tests\Cacher\EmptyTestTrait;
use Tests\Cacher\HasTestTrait;
use Tests\Cacher\IncrementTestTrait;
use Tests\Cacher\RemembertestTrait;
use Tests\Cacher\SaveGetTestTrait;

use function getenv;

final class RedisTest extends TestCase
{

    protected Cacher $cache;

    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use RemembertestTrait;
    use SaveGetTestTrait;

    public function testSize(): void
    {
        $this->cache->save('test', 'value');

        $this->assertSame(
            859296,
            $this->cache->size()
        );
    }

    public function testSizeEmpty(): void
    {
        $this->assertSame(
            921024,
            $this->cache->size()
        );
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);

        Cache::load([
            'className' => RedisCacher::class,
            'port' => 1234
        ]);
    }
    
    public function testInvalidAuth(): void
    {
        $this->expectException(CacheException::class);

        Cache::load([
            'className' => RedisCacher::class,
            'host' => getenv('REDIS_HOST'),
            'password' => 'invalid'
        ]);
    }
    
    protected function setUp(): void
    {
        Cache::clear();
        Cache::setConfig('default', [
            'className' => RedisCacher::class,
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
