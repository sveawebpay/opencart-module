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

function getSveaVersion()
{
    $jsonData = json_decode(file_get_contents('https://raw.githubusercontent.com/sveawebpay/opencart-module/master/src/svea/version.json'),true);
    return $jsonData['version'];
}

function getModuleVersion()
{
    $jsonData = json_decode(file_get_contents(DIR_APPLICATION . '../svea/version.json'), true);
    return $jsonData['version'];
}

function getNewVersionAvailable()
{
    $sveaVersion = getSveaVersion();
    $moduleVersion = getModuleVersion();

    if ($sveaVersion <= $moduleVersion)
    {
        return false;
    }
    else
    {
        return true;
    }
}