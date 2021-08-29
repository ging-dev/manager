<?php

if (!defined('ACCESS') || !defined('PHPMYADMIN') || !defined('REALPATH') || !defined('PATH_DATABASE')) {
    exit('Not access');
}

define('PATH_JSON', REALPATH.'/json');
define('PATH_MYSQL_COLLECTION', PATH_JSON.'/mysql_collection.json');
define('PATH_MYSQL_ATTRIBUTES', PATH_JSON.'/mysql_attributes.json');
define('PATH_MYSQL_FIELD_KEY', PATH_JSON.'/mysql_field_key.json');
define('PATH_MYSQL_DATA_TYPE', PATH_JSON.'/mysql_data_type.json');
define('PATH_MYSQL_ENGINE_STORAGE', PATH_JSON.'/mysql_engine_storage.json');

define('MYSQL_DATA_TYPE_NONE', 'none');
define('MYSQL_COLLECTION_NONE', 'none');
define('MYSQL_COLLECTION_SPLIT', '--');
define('MYSQL_ATTRIBUTES_NONE', 'none');
define('MYSQL_FIELD_KEY_NONE', 'none');

define('MYSQL_AFTER_POSITION', 'after');
define('MYSQL_AFTER_FIRST', 'first');
define('MYSQL_AFTER_LAST', 'last');
define('MYSQL_AFTER_SPLIT', '--');

$MYSQL_COLLECTION = [];
$MYSQL_ATTRIBUTES = [];
$MYSQL_FIELD_KEY = [];
$MYSQL_DATA_TYPE = [];
$MYSQL_ENGINE_STORAGE = [];

if (is_file(REALPATH.'/'.PATH_DATABASE)) {
    include PATH_DATABASE;

    if (isDatabaseVariable($databases)) {
        define('IS_VALIDATE', true);
        define('IS_DATABASE_ROOT', empty($databases['db_name']) || null == $databases['db_name']);
        $conn = @mysqli_connect($databases['db_host'], $databases['db_username'], $databases['db_password']);

        if (false != $conn) {
            define('ERROR_CONNECT', false);

            function printDataType($default = null)
            {
                global $MYSQL_DATA_TYPE;

                if (is_file(PATH_MYSQL_DATA_TYPE) && (is_countable($MYSQL_DATA_TYPE) ? count($MYSQL_DATA_TYPE) : 0) <= 0) {
                    $json = jsonDecode(file_get_contents(PATH_MYSQL_DATA_TYPE), true);

                    if (null != $json && (is_countable($json) ? count($json) : 0) > 0) {
                        $MYSQL_DATA_TYPE = $json;
                    }
                }

                $html = null;

                if (false == is_array($MYSQL_DATA_TYPE) || count($MYSQL_DATA_TYPE) <= 0) {
                    $html .= '<option value="'.MYSQL_DATA_TYPE_NONE.'">Không có lựa chọn</option>';
                } elseif (is_array($MYSQL_DATA_TYPE) && count($MYSQL_DATA_TYPE) > 0) {
                    foreach ($MYSQL_DATA_TYPE['data'] as $label => $type) {
                        $html .= '<optgroup label="'.$label.'">';

                        foreach ($type as $entry) {
                            $html .= '<option value="'.$entry.'"'.((null != $default && $default == $entry) || (null == $default && null != $MYSQL_DATA_TYPE['default'] && $MYSQL_DATA_TYPE['default'] == $entry) ? ' selected="selected"' : null).'>'.$entry.'</option>';
                        }

                        $html .= '</optgroup>';
                    }
                }

                return $html;
            }

            function printCollection($default = null)
            {
                global $MYSQL_COLLECTION;

                if (is_file(PATH_MYSQL_COLLECTION) && (is_countable($MYSQL_COLLECTION) ? count($MYSQL_COLLECTION) : 0) <= 0) {
                    $json = jsonDecode(file_get_contents(PATH_MYSQL_COLLECTION), true);

                    if (null != $json && (is_countable($json) ? count($json) : 0) > 0) {
                        $MYSQL_COLLECTION = $json;
                    }
                }

                $html = null;

                if (false == is_array($MYSQL_COLLECTION) || count($MYSQL_COLLECTION) <= 0) {
                    $html .= '<option value="'.MYSQL_COLLECTION_NONE.'">Không có lựa chọn</option>';
                } elseif (is_array($MYSQL_COLLECTION) && count($MYSQL_COLLECTION) > 0) {
                    $html .= '<option value="'.MYSQL_COLLECTION_NONE.'"'.((null != $default && MYSQL_COLLECTION_NONE == $default) || (null == $default && null != $MYSQL_COLLECTION['default'] && MYSQL_COLLECTION_NONE == $MYSQL_COLLECTION['default']) ? ' selected="selected"' : null).'></option>';

                    foreach ($MYSQL_COLLECTION['data'] as $charset => $collection) {
                        $html .= '<optgroup label="'.$charset.'">';

                        foreach ($collection as $entry) {
                            $html .= '<option value="'.$charset.MYSQL_COLLECTION_SPLIT.$entry.'"'.((null != $default && $default == $entry) || (null == $default && null != $MYSQL_COLLECTION['default'] && $MYSQL_COLLECTION['default'] == $entry) ? ' selected="selected"' : null).'>'.$entry.'</option>';
                        }

                        $html .= '</optgroup>';
                    }
                }

                return $html;
            }

            function printAttributes($default = null)
            {
                global $MYSQL_ATTRIBUTES;

                if (is_file(PATH_MYSQL_ATTRIBUTES) && (is_countable($MYSQL_ATTRIBUTES) ? count($MYSQL_ATTRIBUTES) : 0) <= 0) {
                    $json = jsonDecode(file_get_contents(PATH_MYSQL_ATTRIBUTES), true);

                    if (null != $json && (is_countable($json) ? count($json) : 0) > 0) {
                        $MYSQL_ATTRIBUTES = $json;
                    }
                }

                $html = null;

                if (false == is_array($MYSQL_ATTRIBUTES) || count($MYSQL_ATTRIBUTES) <= 0) {
                    $html .= '<option value="'.MYSQL_ATTRIBUTES_NONE.'">Không có lựa chọn</option>';
                } elseif (is_array($MYSQL_ATTRIBUTES) && count($MYSQL_ATTRIBUTES) > 0) {
                    $html .= '<option value="'.MYSQL_ATTRIBUTES_NONE.'"'.((null != $default && MYSQL_ATTRIBUTES_NONE == $default) || (null == $default && null != $MYSQL_ATTRIBUTES['default'] && MYSQL_ATTRIBUTES_NONE == $MYSQL_ATTRIBUTES['default']) ? ' selected="selected"' : null).'></option>';

                    foreach ($MYSQL_ATTRIBUTES['data'] as $key => $attr) {
                        $html .= '<option value="'.$key.'"'.((null != $default && $default == $key) || (null == $default && null != $MYSQL_ATTRIBUTES['default'] && $MYSQL_ATTRIBUTES['default'] == $key) ? ' selected="selected"' : null).'>'.$attr.'</option>';
                    }
                }

                return $html;
            }

            function printFieldKey($name, $default = null)
            {
                global $MYSQL_FIELD_KEY;

                if (is_file(PATH_MYSQL_FIELD_KEY) && (is_countable($MYSQL_FIELD_KEY) ? count($MYSQL_FIELD_KEY) : 0) <= 0) {
                    $json = jsonDecode(file_get_contents(PATH_MYSQL_FIELD_KEY), true);

                    if (null != $json && (is_countable($json) ? count($json) : 0) > 0) {
                        $MYSQL_FIELD_KEY = $json;
                    }
                }

                $html = null;

                if (false == is_array($MYSQL_FIELD_KEY) || count($MYSQL_FIELD_KEY) <= 0) {
                    $html .= '<input type="radio" name="'.$name.'" value="'.MYSQL_FIELD_KEY_NONE.'" checked="checked">Không có lựa chọn</option>';
                } elseif (is_array($MYSQL_FIELD_KEY) && count($MYSQL_FIELD_KEY) > 0) {
                    $html .= '<input type="radio" name="'.$name.'" value="'.MYSQL_FIELD_KEY_NONE.'"'.((null != $default && MYSQL_FIELD_KEY_NONE == $default) || (null == $default && null != $MYSQL_FIELD_KEY['default'] && MYSQL_FIELD_KEY_NONE == $MYSQL_FIELD_KEY['default']) ? ' checked="checked"' : null).'/>Trống';

                    foreach ($MYSQL_FIELD_KEY['data'] as $key => $value) {
                        $html .= '<br/><input type="radio" name="'.$name.'" value="'.$key.'"'.((null != $default && $default == $key) || (null == $default && null != $MYSQL_FIELD_KEY['default'] && $MYSQL_FIELD_KEY['default'] == $key) ? ' checked="checked"' : null).'/>'.$value;
                    }
                }

                return $html;
            }

            function printEngineStorage($default = null)
            {
                global $MYSQL_ENGINE_STORAGE;

                if (is_file(PATH_MYSQL_ENGINE_STORAGE) && (is_countable($MYSQL_ENGINE_STORAGE) ? count($MYSQL_ENGINE_STORAGE) : 0) <= 0) {
                    $json = jsonDecode(file_get_contents(PATH_MYSQL_ENGINE_STORAGE), true);

                    if (null != $json && (is_countable($json) ? count($json) : 0) > 0) {
                        $MYSQL_ENGINE_STORAGE = $json;
                    }
                }

                $html = null;

                if (false == is_array($MYSQL_ENGINE_STORAGE) || count($MYSQL_ENGINE_STORAGE) <= 0) {
                    $html .= '<option value="'.MYSQL_ENGINE_STORAGE_NONE.'">Không có lựa chọn</option>';
                } elseif (is_array($MYSQL_ENGINE_STORAGE) && count($MYSQL_ENGINE_STORAGE) > 0) {
                    foreach ($MYSQL_ENGINE_STORAGE['data'] as $engine) {
                        $html .= '<option value="'.$engine.'"'.((null != $default && $default == $engine) || (null == $default && null != $MYSQL_ENGINE_STORAGE['default'] && $MYSQL_ENGINE_STORAGE['default'] == $engine) ? ' selected="selected"' : null).'>'.$engine.'</option>';
                    }
                }

                return $html;
            }

            function isDatabaseExists($name, $igone = null, $isLowerCase = false, &$output = false)
            {
                global $conn;
                if ($isLowerCase) {
                    $name = strtolower($name);

                    if (null != $igone) {
                        $igone = strtolower($igone);
                    }
                }

                $query = mysqli_query($conn, 'SHOW DATABASES');

                if (is_object($query)) {
                    while ($assoc = mysqli_fetch_assoc($query)) {
                        $db = $isLowerCase ? strtolower($assoc['Database']) : $assoc['Database'];

                        if ($name == $db) {
                            if (false != $assoc) {
                                $output = $assoc;
                            }

                            if (null == $igone || $igone != $db) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            }

            function isTableExists($name, $igone = null, $isLowerCase = false, &$output = false)
            {
                global $conn;
                if ($isLowerCase) {
                    $name = strtolower($name);

                    if (null != $igone) {
                        $igone = strtolower($igone);
                    }
                }

                $query = mysqli_query($conn, 'SHOW TABLE STATUS');

                if (is_object($query)) {
                    while ($assoc = mysqli_fetch_assoc($query)) {
                        $table = $isLowerCase ? strtolower($assoc['Name']) : $assoc['Name'];

                        if ($name == $table) {
                            if (false != $assoc) {
                                $output = $assoc;
                            }

                            if (null == $igone || $igone != $table) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            }

            function isColumnsExists($name, $table, $igone = null, $isLowerCase = false, &$output = false)
            {
                global $conn;
                if ($isLowerCase) {
                    $name = strtolower($name);

                    if (null != $igone) {
                        $igone = strtolower($igone);
                    }
                }

                $query = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");

                if (is_object($query)) {
                    while ($assoc = mysqli_fetch_assoc($query)) {
                        $field = $isLowerCase ? strtolower($assoc['Field']) : $assoc['Field'];

                        if ($name == $field) {
                            if (false != $assoc) {
                                $output = $assoc;
                            }

                            if (null == $igone || $igone != $field) {
                                return true;
                            }
                        }
                    }
                }

                return false;
            }

            function isDataTypeHasLength($type)
            {
                return !preg_match('/^(DATE|DATETIME|TIME|TINYBLOB|TINYTEXT|BLOB|TEXT|MEDIUMBLOB|MEDIUMTEXT|LONGBLOB|LONGTEXT|SERIAL|BOOLEAN|UUID)$/i', $type);
            }

            function isDataTypeNumeric($type)
            {
                global $MYSQL_DATA_TYPE;

                if (is_file(PATH_MYSQL_DATA_TYPE) && (is_countable($MYSQL_DATA_TYPE) ? count($MYSQL_DATA_TYPE) : 0) <= 0) {
                    $json = jsonDecode(file_get_contents(PATH_MYSQL_DATA_TYPE), true);

                    if (null != $json && (is_countable($json) ? count($json) : 0) > 0) {
                        $MYSQL_DATA_TYPE = $json;
                    }
                }

                if (null != $MYSQL_DATA_TYPE && is_array($MYSQL_DATA_TYPE)) {
                    return in_array(strtoupper($type), $MYSQL_DATA_TYPE['data']['Numeric']);
                } else {
                    return false;
                }
            }

            function getColumnsKey($table)
            {
                global $conn;
                $query = mysqli_query($conn, "SHOW INDEXES FROM `$table` WHERE `Key_name`='PRIMARY'");
                $key = null;

                if (mysqli_num_rows($query) > 0) {
                    $key = mysqli_fetch_assoc($query);
                    $key = $key['Column_name'];
                } else {
                    $query = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
                    $key = mysqli_fetch_assoc($query);
                    $key = $key['Field'];
                }

                return $key;
            }

            if (empty($databases['db_name']) || null == $databases['db_name']) {
                if (false == isset($_GET['db_name']) || true == empty($_GET['db_name'])) {
                    define('IS_CONNECT', true);
                    define('ERROR_SELECT_DB', false);
                } elseif (isset($_GET['db_name']) && false == empty($_GET['db_name']) && mysqli_select_db($conn, $_GET['db_name'])) {
                    define('IS_CONNECT', true);
                    define('ERROR_SELECT_DB', false);
                    define('DATABASE_NAME', $_GET['db_name']);
                }
            } elseif (false == empty($databases['db_name']) && null != $databases['db_name'] && mysqli_select_db($conn, $databases['db_name'])) {
                define('IS_CONNECT', true);
                define('ERROR_SELECT_DB', false);
                define('DATABASE_NAME', $databases['db_name']);
            }
        }
    }
}

if (!defined('IS_CONNECT')) {
    define('IS_CONNECT', false);
}

if (!defined('IS_VALIDATE')) {
    define('IS_VALIDATE', false);
}

if (!defined('IS_DATABASE_ROOT')) {
    define('IS_DATABASE_ROOT', false);
}

if (!defined('ERROR_CONNECT')) {
    define('ERROR_CONNECT', true);
}

if (!defined('ERROR_SELECT_DB')) {
    define('ERROR_SELECT_DB', true);
}

if (!defined('DATABASE_NAME')) {
    define('DATABASE_NAME', null);
}

define('DATABASE_NAME_PARAMATER_0', IS_DATABASE_ROOT ? '?db_name='.DATABASE_NAME : null);
define('DATABASE_NAME_PARAMATER_1', IS_DATABASE_ROOT ? '&db_name='.DATABASE_NAME : null);
