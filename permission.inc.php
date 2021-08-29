<?php

    if (!defined('ACCESS')) {
        exit('Not access');
    }

    $script = function_exists('getenv') ? getenv('SCRIPT_NAME') : $_SERVER['SCRIPT_NAME'];
    $script = str_contains($script, '/') ? dirname($script) : null;
    $script = str_replace('\\', '/', $script);

    define('IS_INSTALL_ROOT_DIRECTORY', '.' == $script || '/' == $script);
    define('IS_ACCESS_FILE_IN_FILE_MANAGER', defined('INDEX') && isset($_GET['not']));
    define('DIRECTORY_FILE_MANAGER', str_contains($script, '/') ? @substr($script, strrpos($script, '/') + 1) : null);
    define('PATH_FILE_MANAGER', str_replace('\\', '/', strtolower($_SERVER['DOCUMENT_ROOT'].$script)));
    define('NAME_DIRECTORY_INSTALL_FILE_MANAGER', !IS_INSTALL_ROOT_DIRECTORY ? preg_replace('#(\/+|/\+)(.+?)#s', '$2', $script) : null);
    define('PARENT_PATH_FILE_MANAGER', substr(PATH_FILE_MANAGER, 0, strlen(PATH_FILE_MANAGER) - (NAME_DIRECTORY_INSTALL_FILE_MANAGER == null ? 0 : strlen(NAME_DIRECTORY_INSTALL_FILE_MANAGER) + 1)));

    if (
        IS_INSTALL_ROOT_DIRECTORY ||
        IS_ACCESS_FILE_IN_FILE_MANAGER ||

        ('.' != $script && '/' != $script && isPathNotPermission(processDirectory($dir))) ||
        ('.' != $script && '/' != $script && null != $name && isPathNotPermission(processDirectory($dir.'/'.$name)))
    ) {
        define('NOT_PERMISSION', true);
    } else {
        define('NOT_PERMISSION', false);
    }

    if (!defined('INDEX') && !defined('LOGIN') && NOT_PERMISSION) {
        goURL('index.php?not');
    }

    if (NOT_PERMISSION) {
        $dir = null;
        $dirEncode = null;
    }

    if (null != $dir) {
        define('IS_ACCESS_PARENT_PATH_FILE_MANAGER', strtolower(processDirectory($dir)) == strtolower(processDirectory(PARENT_PATH_FILE_MANAGER)));
    } else {
        define('IS_ACCESS_PARENT_PATH_FILE_MANAGER', strtolower(processDirectory(PARENT_PATH_FILE_MANAGER)) == strtolower(processDirectory($_SERVER['DOCUMENT_ROOT'])));
    }

    function isPathNotPermission($path, $isUseName = false)
    {
        if (null != $path && false == empty($path)) {
            $reg = $isUseName ? NAME_DIRECTORY_INSTALL_FILE_MANAGER : PATH_FILE_MANAGER;
            $reg = null != $reg ? strtolower($reg) : null;
            $path = str_replace('\\', '/', $path);
            $path = strtolower($path);

            if (preg_match('#^'.$reg.'$#si', $path)) {
                return true;
            } elseif (preg_match('#^'.$reg.'/(^\/+|^\\+)(.*?)$#si', $path)) {
                return true;
            } elseif (preg_match('#^'.$reg.'/(.*?)$#si', $path)) {
                return true;
            }

            return false;
        }

        return false;
    }

    unset($script);
