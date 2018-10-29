<?php namespace Loilo\Collection\Test;

use PHPUnit\Framework\TestCase;
use Loilo\Collection\Collection;

class InsertBeforeTest extends TestCase
{
    protected $data;

    protected function setUp()
    {
        $this->data = new Collection(['one', 'two', 'three']);
    }

    public function testSimple()
    {
        $this->assertEquals(
            ['zero', 'one', 'two', 'three'],
            $this->data->insertBefore('one', ['zero'])->all()
        );
    }

    public function testBeforeCallbackPosition()
    {
        // Insertion before callback position
        $this->assertEquals(
            ['one', 'two', 'zero', 'three'],
            $this->data->insertBefore(function ($item) {
                return $item === 'three';
            }, ['zero'])->all()
        );
    }

    public function testMixedAssociativePairs()
    {
        // Mixed associative and non-associative pairs
        $this->assertEquals(
            ['one', 'four', 'five' => 5, 'two', 'three'],
            $this->data->insertBefore('two', ['four', 'five' => 5 ])->all()
        );
    }

    public function testOverrideKeys()
    {
        // Insert overridden keys at correct position
        $c = new Collection(['one', 'two' => 2, 'three']);
        $this->assertEquals(
            ['two' => 22, 'four', 'one', 'three'],
            $c->insertBefore('one', ['two' => 22, 'four'])->all()
        );
    }

    public function testInsertBeforeOverridden()
    {
        // Insert at position of later removed keys
        $c = new Collection(['one', 'two' => 2, 'three']);
        $this->assertEquals(
            ['one', 'two' => 22, 'four', 'three'],
            $c->insertBefore(2, ['two' => 22, 'four'])->all()
        );
    }

    public function testFallbackToEnd()
    {
        // Insert after all if no insert position was found
        $this->assertEquals(
            ['one', 'two', 'three', 'four'],
            $this->data->insertBefore(function () {
                return false;
            }, ['four'])->all()
        );
    }
}
