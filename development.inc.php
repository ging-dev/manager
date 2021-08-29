<?php

    if (!defined('ACCESS') || !defined('DEVELOPMENT')) {
        exit('Not access');
    }

    define('DEVELOPMENT_FILE', 'development.count');
    define('DEVELOPMENT_INC', 'development.inc.php');
    define('VERSION_INC', 'version.inc.php');

    $files = [];
    $times = [];
    $count = 1;
    $version = '0.0.1';
    $isCreator = true;
    $isModifier = false;

    if (DEVELOPMENT) {
        $handler = @scandir(REALPATH);

        foreach ($handler as $entry) {
            if ('.' != $entry &&
                '..' != $entry &&
                $entry != basename(PATH_CONFIG) &&
                $entry != basename(PATH_DATABASE) &&
                $entry != basename(DEVELOPMENT_FILE) &&
                $entry != basename(DEVELOPMENT_INC) &&
                $entry != basename(VERSION_INC) && is_file(REALPATH.'/'.$entry)) {
                $files[] = $entry;
                $times[] = filemtime(REALPATH.'/'.$entry);
            }
        }

        unset($handler);

        if (is_file(REALPATH.'/'.DEVELOPMENT_FILE)) {
            $json = jsonDecode(file_get_contents(DEVELOPMENT_FILE), true);

            if (null !== $json) {
                $entryFiles = $json['files'];
                $entryTimes = $json['times'];
                $count = intval($json['count']);
                $version = $json['version'];
                $isCreator = false;

                if (count($files) != (is_countable($entryFiles) ? count($entryFiles) : 0) || count($times) != (is_countable($entryTimes) ? count($entryTimes) : 0)) {
                    $isModifier = true;
                } else {
                    for ($i = 0; $i < (is_countable($entryFiles) ? count($entryFiles) : 0); ++$i) {
                        $file = $entryFiles[$i];
                        $time = intval($entryTimes[$i]);

                        if (!in_array($file, $files) || intval($times[array_search($file, $files)]) > intval($time)) {
                            $isModifier = true;
                            break;
                        }
                    }
                }

                if ($isModifier) {
                    ++$count;
                    $length = strlen($count);
                    $version = null;
                    $isCreator = true;

                    if ($length > 4) {
                        $version = intval(substr($count, 0, $length - 4));
                    } else {
                        $version = 0;
                    }

                    if ($length > 2) {
                        $version .= '.'.intval(substr($count, 3 == $length ? 0 : $length - 4, $length > 3 ? 2 : 1));
                    } else {
                        $version .= '.'. 0;
                    }

                    $version .= '.'.intval(substr($count, 1 == $length ? 0 : $length - 2, 2));
                } elseif (!is_file(VERSION_INC)) {
                    $isModifier = true;
                }
            }
        } elseif (is_file(VERSION_INC)) {
            require_once VERSION_INC;
        }

        if ($isCreator) {
            file_put_contents(REALPATH.'/'.DEVELOPMENT_FILE, jsonEncode(['files' => $files, 'times' => $times, 'count' => $count, 'version' => $version]));
        }

        if ($isCreator || $isModifier) {
            file_put_contents(REALPATH.'/'.VERSION_INC, '<?php if (!defined(\'ACCESS\')) { die(\'Not acces\'); } else { $count = '.$count.'; $version = \''.$version.'\'; } ?>');
        }
    } elseif (is_file(VERSION_INC)) {
        require_once VERSION_INC;
    }

    if (!DEVELOPMENT && is_file(REALPATH.'/'.DEVELOPMENT_FILE)) {
        @unlink(REALPATH.'/'.DEVELOPMENT_FILE);
    }

    define('AUTHOR', 'Izero & Itachi');
    define('VERSION', $version);

    unset($files);
    unset($times);
    unset($count);
    unset($version);
