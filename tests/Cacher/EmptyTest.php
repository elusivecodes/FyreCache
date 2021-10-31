<?php
declare(strict_types=1);

namespace Tests\Cacher;

trait EmptyTest
{

    public function testEmpty(): void
    {
        $this->cache->save('test1', 'value');
        $this->cache->save('test2', 'value');

        $this->assertEquals(
            true,
            $this->cache->empty()
        );

        $this->assertEquals(
            false,
            $this->cache->has('test')
        );

        $this->assertEquals(
            false,
            $this->cache->has('test2')
        );
    }

}
