<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Cache\Cache,
    Fyre\Cache\Cacher,
    Fyre\Cache\Handlers\FileCacher,
    PHPUnit\Framework\TestCase,
    Tests\Cacher\DecrementTest,
    Tests\Cacher\DeleteTest,
    Tests\Cacher\EmptyTest,
    Tests\Cacher\HasTest,
    Tests\Cacher\IncrementTest,
    Tests\Cacher\Remembertest,
    Tests\Cacher\SaveGetTest;

use function
    rmdir;

final class FileTest extends TestCase
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
            'className' =>  FileCacher::class,
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
