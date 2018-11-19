# Collection
[![Travis](https://img.shields.io/travis/Loilo/Collection.svg)](https://travis-ci.org/Loilo/Collection) [![Packagist](https://img.shields.io/packagist/v/loilo/collection.svg)](https://packagist.org/packages/loilo/collection)
> An extended version of Laravel's collections

This is my very personal extension of Laravel's amazing [Collection](https://laravel.com/docs/collections) class (as extracted by [tightenco/collect](https://github.com/tightenco/collect)). It adds some methods that I need regularly.

The declared aim of this project is to feed its methods back into Laravel by making proposals in [`laravel/ideas`](https://github.com/laravel/ideas).

## Install
```bash
composer require loilo/collection
```

## Methods
> **Note:** In all code examples below, `Collection` refers to `Loilo\Collection\Collection`.

**Table of Contents:**
* [`insertBefore()`/`insertAfter()`](#insertbeforeinsertafter)
* [`mergeInBefore()`/`mergeInAfter()`](#mergeinbeforemergeinafter)
* [`extract()`](#extract)
* [`rearrange()`](#rearrange)

### `insertBefore()`/`insertAfter()`
Inserts items before/after a reference item (modifies the collection).

**Proposal:** [#650](https://github.com/laravel/ideas/issues/650) (declined)

**Details:** [Specification](spec/InsertBeforeAfter.md)

**Examples:**

Insert a list of items after a certain value:
```php
$c = Collection::make([ 10, 20, 30, 40 ]);
$c->insertAfter(20, [ 24, 25, 26 ]);
$c->all() === [ 10, 20, 24, 25, 26, 30, 40 ];
```

Insert data after a certain key:
```php
$c = Collection::make([
    'name' => 'loilo/collection',
    'version' => '1.0.0'
]);

$c->insertAfter(function ($value, $key) {
    return $key === 'name';
}, [ 'description' => "An extended version of Laravel's collections" ]);

$c->all() === [
    'name' => 'loilo/collection',
    'description' => "An extended version of Laravel's collections",
    'version' => '1.0.0'
];
```

### `mergeInBefore()`/`mergeInAfter()`
These are equivalent to `insertBefore()`/`insertAfter()` but do not modify the collection.

### `extract()`
Extract structures from the collection, kind of an advanced [`pluck()`](https://laravel.com/docs/collections#method-pluck).

**Details:** [Specification](spec/Extract.md)

**Examples:**

Take certain items from a list:
```php
$c = Collection::make([ 1, 2, 3 ])->extract([ 0, 1 ])
$c->all() === [ 1, 2 ];
```

Transform a collection to a certain structure:
```php
$c = Collection::make([
    'a' => [
        'd' => 3,
        'e' => 4
    ],
    'b' => [
        'd' => 5,
        'e' => 6
    ],
    'c' => [
        'd' => 7,
        'e' => 8
    ]
]);

$c->extract([ 'a' ])->all() === [
    'a' => [
        'd' => 3,
        'e' => 4
    ]
];

$c->extract([ '*' => [ 'd' ] ])->all() === [
    'a' => [ 'd' => 3 ],
    'b' => [ 'd' => 5 ],
    'c' => [ 'd' => 7 ]
];
```

### `rearrange()`
This method re-orders a collection, according to a predefined order.

```php
Collection::make([ 'a', 'b', 'c' ]);
    ->rearrange([ 2, 0, 1 ])
    ->all() === [ 'c', 'a', 'b' ];
```

#### Behavior of Unarrangeable Items
When a collection item is not matched by the new order, it will be appended to the ordered items:

```php
Collection::make([ 'a', 'b', 'c' ]);
    ->rearrange([ 1 ])
    ->all() === [ 'b', 'a', 'c' ];
```

This behavior is controlled by the second argument of the `rearrange()` method. The default behavior `$c->rearrange([ ... ])` is equivalent to `$c->rearrange([ ... ], Collection::UNARRANGEABLE_APPEND)`.

Possible values for this parameter are:

Class Constant            | Description
--------------------------|------------------------
`UNARRANGEABLE_APPEND`    | Appends unarrangeable items after the rearranged ones.
`UNARRANGEABLE_PREPEND`   | Prepends unarrangeable items before the rearranged ones.
`UNARRANGEABLE_PARTITION` | Partitions the return value and maps rearranged and unarrangeable items to the `rearranged` respectively the `unarrangeable` key.
`UNARRANGEABLE_DISCARD`   | Omits the unarrangeable items from the returned collection.
`UNARRANGEABLE_THROW`     | Throws an `UnexpectedValueException` when an unarrangeable item is encountered.

#### Map to Reordered Items
By default, the new order passed to `rearrange()` will map to the collection's keys. However, you may pass any callable as the 3rd parameter to the method to map the order values yourself:

```php
Collection::make([ 'a', 'b', 'c' ]);
    ->rearrange(
        [ 'b', 'a', 'c' ],
        Collection::UNARRANGEABLE_APPEND,
        function ($value, $key) {
            return $value;
        }
    ),
    ->all() === [ 'b', 'a', 'c' ];
```
