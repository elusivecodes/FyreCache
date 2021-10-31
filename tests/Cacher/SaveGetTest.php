<?php
declare(strict_types=1);

namespace Tests\Cacher;

use
    Fyre\Cache\Exceptions\CacheException;

trait SaveGetTest
{

    public function testSaveGetArray(): void
    {
        $this->cache->save('test', ['key' => 'value']);

        $this->assertEquals(
            ['key' => 'value'],
            $this->cache->get('test')
        );
    }

    public function testSaveGetObject(): void
    {
        $object = (object) ['key' => 'value'];

        $this->cache->save('test', $object);

        $this->assertEquals(
            $object,
            $this->cache->get('test')
        );
    }

    public function testSaveGetString(): void
    {
        $this->cache->save('test', 'value');

        $this->assertEquals(
            'value',
            $this->cache->get('test')
        );
    }

    public function testSaveGetInteger(): void
    {
        $this->cache->save('test', 5);

        $this->assertEquals(
            5,
            $this->cache->get('test')
        );
    }

    public function testSaveGetFloat(): void
    {
        $this->cache->save('test', .5);

        $this->assertEquals(
            .5,
            $this->cache->get('test')
        );
    }

    public function testSaveGetBooleanTrue(): void
    {
        $this->cache->save('test', true);

        $this->assertEquals(
            true,
            $this->cache->get('test')
        );
    }

    public function testSaveGetBooleanFalse(): void
    {
        $this->cache->save('test', false);

        $this->assertEquals(
            false,
            $this->cache->get('test')
        );
    }

    public function testSaveExpiry(): void
    {
        $this->cache->save('test', 'value', 1);

        sleep(2);

        $this->assertEquals(
            null,
            $this->cache->get('test')
        );
    }

    public function testGetMissing(): void
    {
        $this->assertEquals(
            null,
            $this->cache->get('test')
        );
    }

    public function testSaveInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->save('test/', 'value', 1);
    }

    public function testGetInvalidKey(): void
    {
        $this->expectException(CacheException::class);

        $this->cache->get('test/');
    }

}
