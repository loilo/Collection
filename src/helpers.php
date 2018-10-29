<?php namespace Loilo\Collection;

use stdClass;
use Tightenco\Collect\Support\Arr;

if (!function_exists('to_array')) {
    /**
     * Extract an associative array from an array, an iterable object or a stdClass instance
     *
     * @param iterable|stdClass $source  The source to
     * @param mixed $default             The default value to return when the input is not transformable to an array
     * @return mixed
     */
    function to_array($source, $default = null)
    {
        if (is_iterable($source) || $source instanceof \stdClass) {
            return (array) $source;
        } else {
            return $default;
        }
    }
}

if (!function_exists('extract_structure')) {
    /**
     * Extract an array structure from a source
     *
     * @param array|object $source  The source to destructure
     * @param array $structure      The structure to extract from the source
     * @param string $default       The default value to use when the structure cannot be applied to the source. If a closure function is passed, it will be executed to obtain the default value, receiving the key and value as arguments. If omitted, the function will throw on a structure mismatch.
     * @return array
     */
    function extract_structure($source, array $structure, $default = '__THROW__'): array
    {
        if (isset($structure['*']) && sizeof($structure) === 1) {
            $result = [];
            foreach (to_array($source, []) as $key => $value) {
                $result[$key] = extract_structure($value, $structure['*'], $default);
            }
        } elseif (isset($structure[0]) && $structure[0] === '*' && sizeof($structure) === 1) {
            $result = to_array($source, []);
        } else {
            $result = [];
            $isArrayAccessible = Arr::accessible($source);

            foreach ($structure as $k => $v) {
                $keyIsIndex = is_int($k);
                $key = $keyIsIndex ? $v : $k;

                if ($isArrayAccessible && isset($source[$key])) {
                    $value = $keyIsIndex
                        ? $source[$key]
                        : extract_structure($source[$key], $v, $default);
                } elseif (is_object($source) && isset($source->{$key})) {
                    $value = $keyIsIndex
                        ? $source->{$key}
                        : extract_structure($source->{$key}, $v, $default);
                } else {
                    if ($default === '__THROW__') {
                        throw new \InvalidArgumentException(sprintf('Could not find key "%s" to destructure', $key));
                    }

                    $value = $keyIsIndex
                        ? ($default instanceof \Closure
                            ? $default($key, $source)
                            : $default
                        )
                        : extract_structure([], $v, $default);
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }
}
