<?php namespace Loilo\Collection\Test;

use PHPUnit\Framework\TestCase;
use Loilo\Collection\Collection;

class RearrangeTest extends TestCase
{
    protected $data;

    protected function setUp()
    {
        $this->data = new Collection([ 'a', 'b', 'c', 'd', 'e' ]);
    }

    public function testSimple()
    {
        $this->assertEquals(
            ['c', 'a', 'b', 'e', 'd'],
            $this->data->rearrange([ 2, 0, 1, 4, 3 ])->all()
        );
    }

    public function testMapped()
    {
        $this->assertEquals(
            ['c', 'a', 'b', 'e', 'd'],
            $this->data->rearrange([ 'c', 'a', 'b', 'e', 'd' ], $this->data::UNARRANGEABLE_APPEND, function ($value) {
                return $value;
            })->all()
        );
    }

    public function testUnarrangeableThrow()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->data->rearrange([ 2, 0, 1 ], $this->data::UNARRANGEABLE_THROW);
    }

    public function testUnarrangeableDiscard()
    {
        $this->assertEquals(
            ['c', 'a', 'b'],
            $this->data->rearrange([ 2, 0, 1 ], $this->data::UNARRANGEABLE_DISCARD)->all()
        );
    }

    public function testUnarrangeablePrepend()
    {
        $this->assertEquals(
            ['d', 'e', 'c', 'a', 'b'],
            $this->data->rearrange([ 2, 0, 1 ], $this->data::UNARRANGEABLE_PREPEND)->all()
        );
    }

    public function testUnarrangeableAppend()
    {
        $this->assertEquals(
            ['c', 'a', 'b', 'd', 'e'],
            $this->data->rearrange([ 2, 0, 1 ], $this->data::UNARRANGEABLE_APPEND)->all()
        );
    }

    public function testUnarrangeablePartition()
    {
        $this->assertEquals(
            [
                'rearranged' => ['c', 'a', 'b'],
                'unarrangeable' => ['d', 'e']
            ],
            $this->data->rearrange([ 2, 0, 1 ], $this->data::UNARRANGEABLE_PARTITION)->toArray()
        );
    }

    public function testUnarrangeableDefault()
    {
        $this->assertEquals(
            ['c', 'a', 'b', 'd', 'e'],
            $this->data->rearrange([ 2, 0, 1 ])->all()
        );
    }
}
