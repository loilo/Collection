<?php namespace Loilo\Collection\Test;

use PHPUnit\Framework\TestCase;
use Loilo\Collection\Collection;

class ExtractTest extends TestCase
{
    protected $data;

    protected function setUp()
    {
        $this->data = new Collection([
            'a' => [
                'd' => 3,
                'e' => 4
            ],
            'b' => [
                'd' => 5,
                'e' => 6
            ],
            'c' => [
                'd' => 7
            ]
        ]);
    }

    public function testSimple()
    {
        $this->assertEquals([ 1, 2 ], (new Collection([ 1, 2, 3 ]))->extract([ 0, 1 ])->all());
    }

    public function testSimpleAssociative()
    {
        $this->assertEquals([ 'a' => [ 'd' => 3, 'e' => 4 ] ], $this->data->extract([ 'a' ])->all());
    }

    public function testMissingKeyWithoutDefault()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->data->extract([ 'c' => [ 'e' ] ]);
    }

    public function testMissingKeyWithDefaultValue()
    {
        // Check guaranteed structure
        $this->assertEquals(
            [ 'c' => [ 'e' => null ] ],
            $this->data->extract([ 'c' => [ 'e' ] ], null)->all()
        );
        $this->assertEquals(
            [ 'c' => [ 'e' => [ 'f' => null ] ] ],
            $this->data->extract([ 'c' => [ 'e' => [ 'f' ] ] ], null)->all()
        );

        // Check empty arrays for non-existing asterisks
        $this->assertEquals(
            [ 'c' => [ 'e' => [] ] ],
            $this->data->extract([ 'c' => [ 'e' => [ '*' => [ 'f' ] ] ] ], null)->all()
        );
    }

    public function testMissingKeyWithDefaultFunction()
    {
        $this->assertEquals(
            [ 'c' => [ 'e' => 'e in {"d":7}' ] ],
            $this->data->extract([ 'c' => [ 'e' ] ], function ($key, $source) {
                return sprintf('%s in %s', $key, json_encode($source));
            })->all()
        );
    }

    public function testAsteriskAsKey()
    {
        $this->assertEquals(
            [
                'a' => [ 'd' => 3 ],
                'b' => [ 'd' => 5 ],
                'c' => [ 'd' => 7 ]
            ],
            $this->data->extract([ '*' => [ 'd' ] ])->all()
        );
    }

    public function testAsteriskAsValue()
    {
        $this->assertEquals(
            [
                'a' => [
                    'd' => 3,
                    'e' => 4
                ],
                'b' => [
                    'd' => 5,
                    'e' => 6
                ],
                'c' => [
                    'd' => 7
                ]
            ],
            $this->data->extract([ '*' ])->all()
        );
    }

    public function testOrder()
    {
        $c = new Collection([
            'a' => 1,
            'b' => 2,
            'c' => 3
        ]);

        $this->assertEquals([
            'b' => 2,
            'a' => 1,
            'c' => 3
        ], $c->extract([ 'b', 'a', 'c' ])->all());
    }
}
