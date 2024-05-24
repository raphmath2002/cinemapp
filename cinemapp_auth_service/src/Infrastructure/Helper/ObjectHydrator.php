<?php

namespace Infrastructure\Helper;

abstract class ObjectHydrator
{
    public static function hydrate(array $content, object $input): object
    {
        foreach ($content as $key => $value) {
            if (property_exists($input, $key)) {
                $input->$key = $value;
            }
        }
        return $input;
    }
}
