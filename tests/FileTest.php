<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\Cache;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\FileCacher;
use PHPUnit\Framework\TestCase;
use Tests\Cacher\DecrementTestTrait;
use Tests\Cacher\DeleteTestTrait;
use Tests\Cacher\EmptyTestTrait;
use Tests\Cacher\HasTestTrait;
use Tests\Cacher\IncrementTestTrait;
use Tests\Cacher\RemembertestTrait;
use Tests\Cacher\SaveGetTestTrait;

use function rmdir;

final class FileTest extends TestCase
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
        Cache::clear();
        Cache::setConfig('default', [
            'className' => FileCacher::class,
            'path' => 'cache',
            'prefix' => 'prefix.'
        ]);

        $this->cache = Cache::use();
    }

    protected function tearDown(): void
    {
        $this->cache->delete('test');
        rmdir('cache');
    }

}
