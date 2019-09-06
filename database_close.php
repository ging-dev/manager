<?php

    if (!defined('ACCESS') || !defined('PHPMYADMIN') || !defined('REALPATH') || !defined('PATH_DATABASE') || !$conn)
        die('Not access');

    if ($conn)
        mysqli_close($conn);

?>