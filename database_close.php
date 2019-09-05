<?php

    if (!defined('ACCESS') || !defined('PHPMYADMIN') || !defined('REALPATH') || !defined('PATH_DATABASE') || !$GLOBALS['db'])
        die('Not access');

    if ($GLOBALS['db'])
        mysqli_close($GLOBALS['db']);

?>