<?php

define('ACCESS', true);

include_once 'function.php';

if (IS_LOGIN) {
    unset($_SESSION[SESS]);

    $ref = $_SERVER['REQUEST_URI'];
    $ref = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] != $ref ? $ref : null;

    if (IS_LOGIN) {
        goURL('login.php');
    } else {
        goURL(null != $ref ? $ref : 'index.php');
    }
} else {
    goURL('login.php');
}
