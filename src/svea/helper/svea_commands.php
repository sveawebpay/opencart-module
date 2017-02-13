<?php

function deleteDirectory($dir)
{
    chmod_r($dir, 0777);

    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function chmod_r($path, $fileMode)
{
    if (!is_dir($path)) {
        if (is_file($path)) {
            return chmod($path, $fileMode);
        } else {
            return false;
        }
    }

    $dh = opendir($path);
    while (($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..') {
            $fullPath = $path . '/' . $file;
            if (is_link($fullPath)) {
                return false;
            } elseif (!is_dir($fullPath) && !chmod($fullPath, $fileMode)) {
                return false;
            } elseif (!chmod_r($fullPath, $fileMode)) {
                return false;
            }
        }
    }

    closedir($dh);

    if (chmod($path, $fileMode)) {
        return true;
    } else {
        return false;
    }
}