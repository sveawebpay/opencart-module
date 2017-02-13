<?php

function lowerArrayKeys(array $input)
{
    $return = array();

    foreach ($input as $key => $value)
    {
        $key = strtolower($key);

        if (is_array($value))
            $value = lowerArrayKeys($value);

        $return[$key] = $value;
    }

    return $return;
}
