<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Cache\Cache,
    Fyre\Cache\Exceptions\CacheException,
    Fyre\Cache\Handlers\FileCacher,
    PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{

    public function testLoad(): void
    {
        $this->assertInstanceOf(
            FileCacher::class,
            Cache::load([
                'className' => FileCacher::class
            ])
        );
    }

    public function testLoadInvalidHandler(): void
    {
        $this->expectException(CacheException::class);

        Cache::load([
            'className' => 'Invalid'
        ]);
    }

    public function testUse(): void
    {
        $handler1 = Cache::use();
        $handler2 = Cache::use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            FileCacher::class,
            $handler1
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
    }

}
