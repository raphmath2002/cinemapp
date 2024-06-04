<?php

namespace Infrastructure\Helper;

abstract class ObjectHydrator
{
    public static function hydrate(array $content, object $input, bool $ingnoreNullValues): object
    {
        foreach ($content as $key => $value) {
            if (property_exists($input, $key) || ($ingnoreNullValues && !is_null($value))) {
                $input->$key = $value;
            }
        }
        return $input;
    }
}
