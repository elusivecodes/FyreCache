<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Cache\Handlers\NullCacher;
use PHPUnit\Framework\TestCase;

final class CacheManagerTest extends TestCase
{
    protected CacheManager $cache;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            FileCacher::class,
            $this->cache->build([
                'className' => FileCacher::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->build([
            'className' => 'Invalid',
        ]);
    }

    public function testDisable(): void
    {
        $this->assertSame(
            $this->cache,
            $this->cache->disable()
        );

        $this->assertFalse(
            $this->cache->isEnabled()
        );

        $this->assertInstanceOf(
            NullCacher::class,
            $this->cache->use()
        );
    }

    public function testEnable(): void
    {
        $this->cache->disable();

        $this->assertSame(
            $this->cache,
            $this->cache->enable()
        );

        $this->assertTrue(
            $this->cache->isEnabled()
        );

        $this->assertInstanceOf(
            FileCacher::class,
            $this->cache->use()
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => FileCacher::class,
                    'path' => 'cache',
                    'prefix' => 'prefix.',
                ],
                'data' => [
                    'className' => FileCacher::class,
                    'path' => 'data',
                    'prefix' => 'data.',
                ],
            ],
            $this->cache->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => FileCacher::class,
                'path' => 'data',
                'prefix' => 'data.',
            ],
            $this->cache->getConfig('data')
        );
    }

    public function testIsLoaded(): void
    {
        $this->cache->use();

        $this->assertTrue(
            $this->cache->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->cache->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->cache->use('data');

        $this->assertTrue(
            $this->cache->isLoaded('data')
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->cache,
            $this->cache->setConfig('test', [
                'className' => FileCacher::class,
            ])
        );

        $this->assertSame(
            [
                'className' => FileCacher::class,
            ],
            $this->cache->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->setConfig('default', [
            'className' => FileCacher::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->cache->use();

        $this->assertSame(
            $this->cache,
            $this->cache->unload()
        );

        $this->assertFalse(
            $this->cache->isLoaded()
        );
        $this->assertFalse(
            $this->cache->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->cache,
            $this->cache->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->cache->use('data');

        $this->assertSame(
            $this->cache,
            $this->cache->unload('data')
        );

        $this->assertFalse(
            $this->cache->isLoaded('data')
        );
        $this->assertFalse(
            $this->cache->hasConfig('data')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->cache->use();
        $handler2 = $this->cache->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            FileCacher::class,
            $handler1
        );
    }

    protected function setUp(): void
    {
        $this->cache = new CacheManager([
            'default' => [
                'className' => FileCacher::class,
                'path' => 'cache',
                'prefix' => 'prefix.',
            ],
            'data' => [
                'className' => FileCacher::class,
                'path' => 'data',
                'prefix' => 'data.',
            ],
        ]);
    }
}
