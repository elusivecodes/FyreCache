<?php
declare(strict_types=1);

namespace Tests\Cacher;

trait EmptyTest
{

    public function testEmpty(): void
    {
        $this->cache->save('test1', 'value');
        $this->cache->save('test2', 'value');

        $this->assertTrue(
            $this->cache->empty()
        );

        $this->assertFalse(
            $this->cache->has('test')
        );

        $this->assertFalse(
            $this->cache->has('test2')
        );
    }

}
