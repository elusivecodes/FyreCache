<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\ArrayCacher;
use Fyre\Container\Container;
use PHPUnit\Framework\TestCase;
use Tests\Cacher\DecrementTestTrait;
use Tests\Cacher\DeleteTestTrait;
use Tests\Cacher\EmptyTestTrait;
use Tests\Cacher\HasTestTrait;
use Tests\Cacher\IncrementTestTrait;
use Tests\Cacher\RemembertestTrait;
use Tests\Cacher\SaveGetTestTrait;

final class ArrayTest extends TestCase
{
    use DecrementTestTrait;
    use DeleteTestTrait;
    use EmptyTestTrait;
    use HasTestTrait;
    use IncrementTestTrait;
    use RemembertestTrait;
    use SaveGetTestTrait;

    protected Cacher $cache;

    public function testSize(): void
    {
        $this->cache->save('test', 'value');

        $this->assertSame(
            44,
            $this->cache->size()
        );
    }

    public function testSizeEmpty(): void
    {
        $this->assertSame(
            0,
            $this->cache->size()
        );
    }

    protected function setUp(): void
    {
        $this->cache = Container::getInstance()
            ->use(CacheManager::class)
            ->build([
                'className' => ArrayCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.',
            ]);
    }

    protected function tearDown(): void
    {
        $this->cache->delete('test');
    }
}
