<?php namespace Loilo\Collection;

use UnexpectedValueException;
use InvalidArgumentException;
use Tightenco\Collect\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{
    /**
     * Extract an array structure from the collection
     *
     * @param array $structure  The structure to extract
     * @param string $default   The default value to use when the structure cannot be applied to the collection. If a closure function is passed, it will be executed to obtain the default value, receiving the key and value as arguments. If omitted, the method will throw on a structure mismatch.
     * @return static
     *
     * @throws InvalidArgumentExpression If the structure doesn't match the collection and no default was specified
     */
    public function extract(array $structure, $default = '__THROW__')
    {
        return new static(extract_structure($this->items, $structure, $default));
    }

    const UNARRANGEABLE_THROW = 0;
    const UNARRANGEABLE_DISCARD = 1;
    const UNARRANGEABLE_PREPEND = 2;
    const UNARRANGEABLE_APPEND = 3;
    const UNARRANGEABLE_PARTITION = 4;

    /**
     * Rearranges the items of the collection, matching the passed order
     *
     * @param array $order                  The order to apply to the collection
     * @param integer $handleUnarrangeable  The behaviour for items that could not be arranged accordin to the $order
     * @param callable|null $mapper         A function mapping collection items to the values passed in the $order. By default, it maps the order to the collection's keys.
     * @return void
     */
    public function rearrange(array $order, int $handleUnarrangeable = self::UNARRANGEABLE_APPEND, ?callable $mapper = null)
    {
        $equivalents = $this
            ->map($mapper ?? function ($_value, $key) {
                return $key;
            })
            ->map(function ($equivalent, $index) {
                return [
                    'index' => $index,
                    'equivalent' => $equivalent
                ];
            });

        list($arrangeable, $unarrangeable) = $equivalents->partition(function ($item) use ($order) {
            return in_array($item['equivalent'], $order);
        });

        if ($handleUnarrangeable === static::UNARRANGEABLE_THROW && !empty($unarrangeable)) {
            throw new UnexpectedValueException('Item not found in the rearrange instruction.');
        }

        // Map back unarrangeables
        $unarrangeable = $unarrangeable
            ->map(function ($item) {
                return $this->items[$item['index']];
            })
            ->merge([]);

        $arranged = $arrangeable
            ->sortBy(function ($item) use ($order) {
                return array_search($item['equivalent'], $order);
            })
            ->map(function ($item) {
                return $this->items[$item['index']];
            })
            ->merge([]);

        switch ($handleUnarrangeable) {
            case static::UNARRANGEABLE_DISCARD:
                return $arranged;

            case static::UNARRANGEABLE_PREPEND:
                return $unarrangeable->merge($arranged);

            case static::UNARRANGEABLE_APPEND:
                return $arranged->merge($unarrangeable);

            case static::UNARRANGEABLE_PARTITION:
                return new static([
                    'rearranged' => $arranged,
                    'unarrangeable' => $unarrangeable
                ]);

            default:
                throw new InvalidArgumentException(sprintf('Invalid unarrangeability handler %s', $handleUnarrangeable));
        }
    }


    /**
     * Insert new items at a calculated position, modifies the collection
     *
     * @param  mixed $position   The item in the collection to insert next to. If a callback is passed, it will be used as a filter function as used in Collection::serach()
     * @param  mixed $items      The items to insert at the position
     * @param  int   $offset     A numeric offset applied to the position determined by the $position
     * @return $this
     */
    protected function insertNear($position, $items, int $offset)
    {
        // Get items to insert as array
        $itemsArray = $this->getArrayableItems($items);

        // Get associative keys to be removed from current items if necessary
        $keysToIgnore = array_filter(array_keys($itemsArray), 'is_string');

        // Determine inserting position of new items
        $oldItemsKeys = array_keys($this->items);
        $oldItemsLength = sizeof($this->items);

        $insertKey = $this->search($position);
        $insertPos = $insertKey === false
            ? $oldItemsLength
            : array_search($insertKey, $oldItemsKeys, true)
        ;
        $insertAtEnd = $insertPos + $offset >= $oldItemsLength;

        // Build the new items iteratively
        $results = [];
        for ($index = 0; $index < $oldItemsLength; $index++) {
            $key = $oldItemsKeys[$index];

            // Insert new items if in the right place
            if (!$insertAtEnd && $index === $insertPos + $offset) {
                $results = array_merge($results, $itemsArray);
            }

            // Exclude existing associative pairs if necessary
            if (is_string($key)) {
                if (!in_array($key, $keysToIgnore, true)) {
                    $results[$key] = $this->items[$key];
                }

            // Always append non-associative items
            } else {
                $results[] = $this->items[$key];
            }
        }

        // Append new items after old ones if necessary
        if ($insertAtEnd) {
            $results = array_merge($results, $itemsArray);
        }

        $this->items = $results;

        return $this;
    }

    /**
     * Insert new items before another item, modifies the collection
     *
     * @param  mixed $position  The item in the collection to insert before
     * @param  mixed $items     The items to insert at the position
     * @return $this
     */
    public function insertBefore($position, $items)
    {
        return $this->insertNear($position, $items, 0);
    }

    /**
     * Insert new items after another item, modifies the collection
     *
     * @param  mixed $position  The item in the collection to insert after
     * @param  mixed $items     The items to insert at the position
     * @return $this
     */
    public function insertAfter($position, $items)
    {
        return $this->insertNear($position, $items, 1);
    }

    /**
     * Insert new items before another item
     *
     * @param  mixed $position  The item in the collection to insert before
     * @param  mixed $items     The items to insert at the position
     * @return static
     */
    public function mergeInBefore($position, $items)
    {
        return (new static($this->items))->insertNear($position, $items, 0);
    }

    /**
     * Insert new items after another item
     *
     * @param  mixed $position  The item in the collection to insert after
     * @param  mixed $items     The items to insert at the position
     * @return static
     */
    public function mergeInAfter($position, $items)
    {
        return (new static($this->items))->insertNear($position, $items, 1);
    }
}
