<?php

define('ACCESS', true);

    include_once 'function.php';

    $path = isset($_GET['path']) && !empty($_GET['path']) ? rawurldecode($_GET['path']) : null;

    if (IS_LOGIN && is_file($path) && false == isPathNotPermission($path) && false !== getimagesize($path)) {
        readfile($path);
    } else {
        exit('Not read image');
    }
