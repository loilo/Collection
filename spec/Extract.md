# Specification `extract()`
> **Note:** In all code examples the `Collection` class refers to `Loilo\Collection\Collection`

`extract()` is like [`pluck()`](https://laravel.com/docs/collections#method-pluck), but it defines a whole tree structure to extract, instead of just a single property. It tries to copy values in the provided structure from the collection to the return value.

## Signature
The signature of the `extract()` method is the following:

```php
public function extract (array $structure [, mixed $default]): Collection;
```

...where `$structure` is the desired tree structure to extract from the collection and `$default` is a default value (or generator, see below) for structures not matching the collection.

## The Source
The `extract()` method can read structured data from (associative) arrays and objects:

```php
Collection::make([
    'a' => (object) [
        'd' => 3,
        'e' => 4
    ]
])
    ->extract([ 'a' => [ 'd' ] ])
    ->all() === [ 'a' => [ 'd' => 3 ] ];
```

## The `$structure`
The provided `$structure` is required to be an array. For nested structures, the child nodes must also be arrays:

```php
$source = [ 'a' => [ 'b' => true ] ];

// Incorrect:
$structure = [ 'a' => 'b' ];

// Correct:
$structure = [ 'a' => [ 'b' ] ];
```

## `$default` values
When a property of the desired `$structure` cannot be found in the collection, `extract()`'s default behavior is to throw an `InvalidArgumentException`.

To avoid this, a default value can be passed as the second method parameter. It will be used in place of the non-existing properties:

```php
Collection::make([])
    ->extract([ 'a' ], 'default')
    ->all() === [ 'a' => 'default' ];
```

### Nested Missing Properties
Providing a default value *guarantees* to return the desired `$structure`. So if there are nested non-existing properties, the returned collection will have the default value on *each leaf* of the missing structure:

```php
Collection::make([])
    ->extract([ 'a' => [ 'b', 'c' ], 'd' ], 'default')
    ->all() === [
        'a' => [
            'b' => 'default',
            'c' => 'default'
        ],
        'd' => 'default'
    ];
```

### Anonymous Functions as Default Values
If the provided `$default` value is an anonymous function (i.e. a [`Closure`](http://php.net/manual/class.closure.php) instance), it will be called for every instance where a default value is needed. The provided parameters will be the `$key` missing from the collection and the array/object it was searched on.

```php
Collection::make([ 'a' => [ 1, 2, 3 ] ])
    ->extract([ 'a' => [ 'b' ] ], function ($key, $source) {
        print_r($key, $source);
        return 'default';
    })
    ->all();

/*
Returns:
--------
[
    'a' => [
        'b' => 'default'
    ]
]

Outputs:
--------
string(1) "b"
array(3) {
  [0] => int(1)
  [1] => int(2)
  [2] => int(3)
}
*/
```

## Select All Child Keys/Values
Using the asterisk `*` as a key/value in the `$structure` has a special effect: It represents all keys/values of the source:

```php
// Asterisk as value
Collection::make([ 1, 2, 3 ])->extract([ '*' ])
    ->all() === [ 1, 2, 3 ];

// Asterisk as key
Collection::make([
    [ 'id' => 1, 'name' => 'foo' ],
    [ 'id' => 2, 'name' => 'bar' ],
    [ 'id' => 3, 'name' => 'baz' ]
])
    ->extract([ '*' => [ 'id' ] ])
    ->all() === [
        [ 'id' => 1 ],
        [ 'id' => 2 ],
        [ 'id' => 3 ]
    ];
```

* Note that the asterisk can only be applied to [iterable](http://php.net/manual/function.is-iterable.php) sources.
* As the asterisk is used as special token, there's currently no way to extract a literal `*` key from a collection.