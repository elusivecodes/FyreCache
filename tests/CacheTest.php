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

    public function getConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => FileCacher::class,
                    'path' => 'cache',
                    'prefix' => 'prefix.'
                ]
            ],
            Cache::getConfig()
        );
    }

    public function getConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.'
            ],
            Cache::getConfig('default')
        );
    }

    public function getKey(): void
    {
        $handler = Cache::use();

        $this->assertSame(
            'default',
            Cache::getKey($handler)
        );
    }

    public function getKeyInvalid(): void
    {
        $handler = Cache::load([
            'className' => FileCacher::class
        ]);

        $this->assertSame(
            null,
            Cache::getKey($handler)
        );
    }

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

    public function testSetConfig(): void
    {
        Cache::setConfig([
            'test' => [
                'className' => FileCacher::class
            ]
        ]);

        $this->assertSame(
            [
                'className' => FileCacher::class
            ],
            Cache::getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(CacheException::class);

        Cache::setConfig('default', [
            'className' => FileCacher::class
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
            'className' => FileCacher::class,
            'path' => 'cache',
            'prefix' => 'prefix.'
        ]);
    }

}
