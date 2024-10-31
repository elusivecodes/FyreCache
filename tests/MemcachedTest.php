<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\MemcachedCacher;
use PHPUnit\Framework\TestCase;
use Tests\Cacher\DecrementTestTrait;
use Tests\Cacher\DeleteTestTrait;
use Tests\Cacher\EmptyTestTrait;
use Tests\Cacher\HasTestTrait;
use Tests\Cacher\IncrementTestTrait;
use Tests\Cacher\RemembertestTrait;
use Tests\Cacher\SaveGetTestTrait;

use function getenv;

final class MemcachedTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use RemembertestTrait;
    use SaveGetTestTrait;

    protected Cacher $cache;

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);

        (new CacheManager())->build([
            'className' => MemcachedCacher::class,
            'port' => 1234,
        ]);
    }

    public function testSize(): void
    {
        $this->cache->save('test', 'value');

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
        $this->cache = (new CacheManager())->build([
            'className' => MemcachedCacher::class,
            'host' => getenv('MEMCACHED_HOST'),
            'port' => getenv('MEMCACHED_PORT'),
            'prefix' => 'prefix.',
        ]);
    }

    protected function tearDown(): void
    {
        $this->cache->empty();
    }
}
