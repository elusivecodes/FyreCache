<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Handlers\RedisCacher;
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

final class RedisTest extends TestCase
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
                    'password' => '',
                    'port' => '*****',
                    'database' => '',
                    'timeout' => 0,
                    'persist' => true,
                    'tls' => false,
                    'ssl' => [
                        'key' => null,
                        'cert' => null,
                        'ca' => null,
                    ],
                    'className' => RedisCacher::class,
                ],
            ],
            $data
        );
    }

    public function testInvalidAuth(): void
    {
        $this->expectException(CacheException::class);

        Container::getInstance()
            ->use(CacheManager::class)
            ->build([
                'className' => RedisCacher::class,
                'host' => getenv('REDIS_HOST'),
                'password' => 'invalid',
            ]);
    }

    public function testInvalidConnection(): void
    {
        $this->expectException(CacheException::class);

        Container::getInstance()
            ->use(CacheManager::class)
            ->build([
                'className' => RedisCacher::class,
                'port' => 1234,
            ]);
    }

    public function testSize(): void
    {
        $this->cache->set('test', 'value');

        $this->assertSame(
            873328,
            $this->cache->size()
        );
    }

    public function testSizeEmpty(): void
    {
        $this->assertSame(
            873184,
            $this->cache->size()
        );
    }

    protected function setUp(): void
    {
        $this->cache = Container::getInstance()
            ->use(CacheManager::class)
            ->build([
                'className' => RedisCacher::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
                'prefix' => 'prefix.',
            ]);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
    }
}
