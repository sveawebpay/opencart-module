<?php

/*
 *
 * This file is used if you want to autoload project classes without using Composer
 * */

/**
 * Return desired class
 * @param string $className
 */
function __autoload_svea_checkout_connection_classes($className)
{
    /* Skip other namespaces */
    if (!preg_match('#^(Svea\\\\Checkout)#', $className)) {
        return;
    }

    $filename = str_replace('Svea\\Checkout\\', '', $className);
    $fullPath = str_replace('\\', '/', __DIR__ . '\\src\\' . $filename . ".php");

    if (file_exists($fullPath)) {
        include_once $fullPath;
    }
}

spl_autoload_register('__autoload_svea_checkout_connection_classes');
