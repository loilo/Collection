<?php namespace Loilo\Collection\Test;

use PHPUnit\Framework\TestCase;
use Loilo\Collection\Collection;
use function Loilo\Collection\to_array;

class HelpersTest extends TestCase
{
    public function testToArray()
    {
        $arr = [ 'a' => 1, 'b' => 2, 'c' => 3 ];

        // Copy to avoid in-place-edit errors
        $ref = $arr;

        // Test common array
        $this->assertEquals($ref, to_array($arr));

        // Test stdClass instance
        $this->assertEquals($ref, to_array((object) $arr));

        // Test Traversable instance
        $this->assertEquals($ref, to_array(new class implements \IteratorAggregate {
            public $a = 1;
            public $b = 2;
            public $c = 3;

            public function getIterator()
            {
                return new ArrayIterator($this);
            }
        }));
    }
}
