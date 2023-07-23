<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\Cache;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\FileCacher;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => FileCacher::class,
                    'path' => 'cache',
                    'prefix' => 'prefix.'
                ],
                'data' => [
                    'className' => FileCacher::class,
                    'path' => 'data',
                    'prefix' => 'data.'
                ]
            ],
            Cache::getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => FileCacher::class,
                'path' => 'data',
                'prefix' => 'data.'
            ],
            Cache::getConfig('data')
        );
    }

    public function testGetKey(): void
    {
        $handler = Cache::use();

        $this->assertSame(
            'default',
            Cache::getKey($handler)
        );
    }

    public function testGetKeyInvalid(): void
    {
        $handler = Cache::load([
            'className' => FileCacher::class
        ]);

        $this->assertNull(
            Cache::getKey($handler)
        );
    }

    public function testIsLoaded(): void
    {
        Cache::use();
        
        $this->assertTrue(
            Cache::isLoaded()
        );
    }

    public function testIsLoadedKey(): void
    {
        Cache::use('data');
        
        $this->assertTrue(
            Cache::isLoaded('data')
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            Cache::isLoaded('test')
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
        Cache::setConfig('test', [
            'className' => FileCacher::class
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

    public function testUnload(): void
    {
        Cache::use();

        $this->assertTrue(
            Cache::unload()
        );

        $this->assertFalse(
            Cache::isLoaded()
        );
        $this->assertFalse(
            Cache::hasConfig()
        );
    }

    public function testUnloadKey(): void
    {
        Cache::use('data');

        $this->assertTrue(
            Cache::unload('data')
        );

        $this->assertFalse(
            Cache::isLoaded('data')
        );
        $this->assertFalse(
            Cache::hasConfig('data')
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertFalse(
            Cache::unload('test')
        );
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

        Cache::setConfig([
            'default' => [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.'
            ],
            'data' => [
                'className' => FileCacher::class,
                'path' => 'data',
                'prefix' => 'data.'
            ]
        ]);
    }

}
