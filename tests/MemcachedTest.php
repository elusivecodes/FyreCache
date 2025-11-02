<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\MemcachedCacher;
use Fyre\Container\Container;
use PHPUnit\Framework\TestCase;
use Tests\Cacher\DecrementTestTrait;
use Tests\Cacher\DeleteTestTrait;
use Tests\Cacher\EmptyTestTrait;
use Tests\Cacher\GetSetTestTrait;
use Tests\Cacher\HasTestTrait;
use Tests\Cacher\IncrementTestTrait;
use Tests\Cacher\RemembertestTrait;

use function getenv;

final class MemcachedTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use GetSetTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use RemembertestTrait;

    protected Cacher $cache;

    public function testDebug(): void
    {
        $data = $this->cache->__debugInfo();

        $this->assertSame(
            [
                'config' => [
                    'expire' => null,
                    'prefix' => 'prefix.',
                    'host' => '*****',
                    'port' => '*****',
                    'weight' => 1,
                    'className' => MemcachedCacher::class,
                ],
            ],
            $data
        );
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);

        Container::getInstance()
            ->use(CacheManager::class)
            ->build([
                'className' => MemcachedCacher::class,
                'port' => 1234,
            ]);
    }

    public function testSize(): void
    {
        $this->cache->set('test', 'value');

        $this->assertSame(
            75,
            $this->cache->size()
        );
    }

    public function testSizeEmpty(): void
    {
        $this->assertSame(
            75,
            $this->cache->size()
        );
    }

    protected function setUp(): void
    {
        $this->cache = Container::getInstance()
            ->use(CacheManager::class)
            ->build([
                'className' => MemcachedCacher::class,
                'host' => getenv('MEMCACHED_HOST'),
                'port' => getenv('MEMCACHED_PORT'),
                'prefix' => 'prefix.',
            ]);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }
}
