# Specification `insertBefore()`/`insertAfter()`
> **Side note:** Explanations and examples below only refer to `insertAfter`. Of course, the `insertBefore` method behaves analogical.

## Basics
The signature of the method is the following:

```php
public function insertAfter (mixed $position, mixed $items): Collection;
```

In words: Calling the `insertAfter()` method on a `$collection` takes a `$position` and some `$items` (being anything that can be handled by the [`getArrayableItems`](https://github.com/illuminate/support/blob/5.7/Collection.php#L1882-L1899) method). It merges the given `$items` into the `$collection`.

The position where the items are put is determined by the given `$position` (equivalent to [`Collection::search()`](https://github.com/illuminate/support/blob/5.7/Collection.php#L1366-L1379)):

* If `$position` is a non-string `callable`:
  * Iterate over the `$collection`.
  * Call `$position($item, $key)` on each entry.
  * If the call returns a truthy value, insert the `$items` after that entry.

* Otherwise:
  * Check for the first occurrence of `$position` in the `$collection`.
  * Insert `$items` directly after that.

### Basic Example
To get a more visual grasp of what the `insertAfter()` method does, have a look at the following example — it shows the case that led me to thinking about this specification in the first place, which is generation of a `composer.json`:

```php
$composer = Loilo\Collection\Collection::make([
  'name' => 'loilo/my-package',
  'description' => 'An example package to prove a point',
  'require' => [
    'laravel/laravel' => '^5.4'
  ],
  'autoload' => [
    'psr-4' => [
      'Loilo\\MyPackage\\' => 'src/'
    ]
  ]
]);

$composer->insertAfter(
  function ($item, $key) {
    return $key === 'require';
  },
  [
    'require-dev' => [
      'loilo/some-other-dependency' => '^1.0'
    ]
  ]
);

/*
[
  'name' => 'loilo/my-package',
  'description' => 'An example package to prove a point',
  'require' => [
    'laravel/laravel' => '^5.4'
  ],
  'require-dev' => [
    'loilo/some-other-dependency' => '^1.0'
  ],
  'autoload' => [
    'psr-4' => [
      'Loilo\\MyPackage\\' => 'src/'
    ]
  ]
]
*/
```

## Advanced
Below are rules about the behavior of the `insertBefore()`/`insertAfter()` methods in general and in some edge cases.

* In analogy to `push()` and `prepend()`, these methods modify the original Collection.
* If the check for a position to insert before/after does not yield any results, the `$items` are pushed to the end of the Collection:

  ```php
  Loilo\Collection\Collection::make([
    'foo' => 1,
    'bar' => 2
  ])
    ->insertAfter(
      'this value does not exist',
      [ 'baz' => 3 ]
    );

  // [ 'foo' => 1, 'bar' => 2, 'baz' => 3 ]
  ```

* Each associative key-value pair of the inserted `$items` does only override entries previously associated with their respective key (if present), but also moves the key to the correct position:

  ```php
  Loilo\Collection\Collection::make([
    'foo' => 1,
    'bar' => 2,
    'qux' => 3,
    'baz' => 4 // 'baz' goes from here...
  ])
    ->insertAfter(
      function ($item, $key) {
        return $key === 'foo';
      },
      [
        'flob' => 5,
        'baz' => 'gotcha!'
      ]
    );

  /*
  [
    'foo' => 1,
    'flob' => 5,
    'baz' => 'gotcha!', // ...to here.
    'bar' => 2,
    'qux' => 3
  ]
  */
  ```

* No matter whether previous associative pairs are going to be removed, the position calculation of the new items must happen before that removal to correctly insert items.
