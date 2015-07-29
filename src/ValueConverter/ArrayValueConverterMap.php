<?php

namespace Ddeboer\DataImport\ValueConverter;

/**
 * Converts a nested array using a converter-map
 *
 * @author Christoph Rosse <christoph@rosse.at>
 */
class ArrayValueConverterMap
{
    /**
     * @var array
     */
    private $converters;

    /**
     * @param callable[] $converters
     */
    public function __construct(array $converters)
    {
        $this->converters = $converters;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($input)
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Input of a ArrayValueConverterMap must be an array');
        }

        foreach ($input as $key => $item) {
            $input[$key] = $this->convertItem($item, $key);
        }

        return $input;
    }

    /**
     * Convert an item of the array using the converter-map
     *
     * @param $item
     *
     * @return mixed
     */
    protected function convertItem($item, $key)
    {
        if (!isset($this->converters[$key])) {
            return $item;
        }

        foreach ($this->converters[$key] as $converter) {
            $item = call_user_func($converter, $item);
        }

        return $item;
    }
}