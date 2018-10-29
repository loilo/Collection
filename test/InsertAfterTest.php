<?php namespace Loilo\Collection\Test;

use PHPUnit\Framework\TestCase;
use Loilo\Collection\Collection;

class InsertAfterTest extends TestCase
{
    protected $data;

    protected function setUp()
    {
        $this->data = new Collection(['one', 'two', 'three']);
    }

    public function testSimple()
    {
        $this->assertEquals(
            ['one', 'zero', 'two', 'three'],
            $this->data->insertAfter('one', ['zero'])->all()
        );
    }

    public function testAfterCallbackPosition()
    {
        // Insertion after callback position
        $this->assertEquals(
            ['one', 'two', 'three', 'zero'],
            $this->data->insertAfter(function ($item) {
                return $item === 'three';
            }, ['zero'])->all()
        );
    }

    public function testMixedAssociativePairs()
    {
        // Mixed associative and non-associative pairs
        $this->assertEquals(
            ['one', 'two', 'four', 'five' => 5, 'three'],
            $this->data->insertAfter('two', ['four', 'five' => 5 ])->all()
        );
    }

    public function testOverrideKeys()
    {
        // Insert overridden keys at correct position
        $c = new Collection(['one', 'two' => 2, 'three']);
        $this->assertEquals(
            ['one', 'two' => 22, 'four', 'three'],
            $c->insertAfter('one', ['two' => 22, 'four'])->all()
        );
    }

    public function testInsertAfterOverridden()
    {
        // Insert at position of later removed keys
        $c = new Collection(['one', 'two' => 2, 'three']);
        $this->assertEquals(
            ['one', 'two' => 22, 'four', 'three'],
            $c->insertAfter(2, ['two' => 22, 'four'])->all()
        );
    }

    public function testFallbackToEnd()
    {
        // Insert after all if no insert position was found
        $this->assertEquals(
            ['one', 'two', 'three', 'four'],
            $this->data->insertAfter(function () {
                return false;
            }, ['four'])->all()
        );
    }
}
