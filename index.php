<?php

    define('ACCESS', true);
    define('INDEX', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = !IS_INSTALL_ROOT_DIRECTORY ? 'Danh sách' : 'Lỗi File Manager';
        $dir = NOT_PERMISSION == false && isset($_GET['dir']) && false == empty($_GET['dir']) ? rawurldecode($_GET['dir']) : $_SERVER['DOCUMENT_ROOT'];
        $dir = processDirectory($dir);
        $handler = null;

        include_once 'header.php';

        if (!IS_INSTALL_ROOT_DIRECTORY) {
            $handler = @scandir($dir);

            if (false === $handler) {
                $dir = $_SERVER['DOCUMENT_ROOT'];
                $dir = processDirectory($dir);

                $handler = @scandir($dir);
            }
        }

        if (!is_array($handler)) {
            $handler = [];
        }

        $dirEncode = rawurlencode($dir);
        $count = count($handler);
        $lists = [];

        if (!IS_INSTALL_ROOT_DIRECTORY && $count > 0) {
            $folders = [];
            $files = [];

            foreach ($handler as $entry) {
                if ('.' != $entry && '..' != $entry) {
                    if (DIRECTORY_FILE_MANAGER == $entry && IS_ACCESS_PARENT_PATH_FILE_MANAGER);
                    /* Is hide directory File Manager */
                    elseif (is_dir($dir.'/'.$entry)) {
                        $folders[] = $entry;
                    } else {
                        $files[] = $entry;
                    }
                }
            }

            if (count($folders) > 0) {
                asort($folders);

                foreach ($folders as $entry) {
                    $lists[] = ['name' => $entry, 'is_directory' => true];
                }
            }

            if (count($files) > 0) {
                asort($files);

                foreach ($files as $entry) {
                    $lists[] = ['name' => $entry, 'is_directory' => false];
                }
            }
        }

        $count = count($lists);
        $html = null;

        if (!IS_INSTALL_ROOT_DIRECTORY && '/' != $dir && str_contains($dir, '/')) {
            $array = explode('/', preg_replace('|^/(.*?)$|', '\1', $dir));
            $html = null;
            $item = null;
            $url = null;

            foreach ($array as $key => $entry) {
                if (0 === $key) {
                    $seperator = preg_match('|^\/(.*?)$|', $dir) ? '/' : null;
                    $item = $seperator.$entry;
                } else {
                    $item = '/'.$entry;
                }

                if ($key < count($array) - 1) {
                    $html .= '/<a href="index.php?dir='.rawurlencode($url.$item).'">';
                } else {
                    $html .= '/';
                }

                $url .= $item;
                $html .= substring($entry, 0, NAME_SUBSTR, NAME_SUBSTR_ELLIPSIS);

                if ($key < count($array) - 1) {
                    $html .= '</a>';
                }
            }
        }

        if (!IS_INSTALL_ROOT_DIRECTORY) {
            echo '<script language="javascript" src="checkbox.js"></script>';
            echo '<div class="title">'.$html.'</div>';
        }

        if (NOT_PERMISSION) {
            if (IS_INSTALL_ROOT_DIRECTORY) {
                echo '<div class="title">Lỗi File Manager</div>
                <div class="list">Bạn đang cài đặt File Manager trên thư mục gốc, hãy chuyển vào một thư mục</div>';
            } elseif (IS_ACCESS_FILE_IN_FILE_MANAGER) {
                echo '<div class="notice_info">Bạn không thể xem tập tin của File Manager nó đã bị chặn</div>';
            } else {
                echo '<div class="notice_info">Bạn không thể xem thư mục của File Manager nó đã bị chặn</div>';
            }
        }

        if (!IS_INSTALL_ROOT_DIRECTORY) {
            echo '<form action="action.php?dir='.$dirEncode.$pages['paramater_1'].'" method="post" name="form"><ul class="list_file">';

            if ('/' != preg_replace('|[a-zA-Z]+:|', '', str_replace('\\', '/', $dir))) {
                $path = strrchr($dir, '/');

                if (false !== $path) {
                    $path = 'index.php?dir='.rawurlencode(substr($dir, 0, strlen($dir) - strlen($path)));
                } else {
                    $path = 'index.php';
                }

                echo '<li class="normal">
                    <img src="icon/back.png" style="margin-left: 5px; margin-right: 5px"/> 
                    <a href="'.$path.'">
                        <strong class="back">...</strong>
                    </a>
                </li>';
            }

            if ($count <= 0) {
                echo '<li class="normal"><img src="icon/empty.png"/> <span class="empty">Không có thư mục hoặc tập tin</span></li>';
            } else {
                $start = 0;
                $end = $count;

                if ($configs['page_list'] > 0 && $count > $configs['page_list']) {
                    $pages['total'] = ceil($count / $configs['page_list']);

                    if ($pages['total'] <= 0 || $pages['current'] > $pages['total']) {
                        goURL('index.php?dir='.$dirEncode.($pages['total'] <= 0 ? null : '&page_list='.$pages['total']));
                    }

                    $start = ($pages['current'] * $configs['page_list']) - $configs['page_list'];
                    $end = $start + $configs['page_list'] >= $count ? $count : $start + $configs['page_list'];
                }

                for ($i = $start; $i < $end; ++$i) {
                    $name = $lists[$i]['name'];
                    $path = $dir.'/'.$name;
                    $perms = getChmod($path);

                    if ($lists[$i]['is_directory']) {
                        echo '<li class="folder">
                            <div>
                                <input type="checkbox" name="entry[]" value="'.$name.'"/>
                                <a href="folder_edit.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'">
                                    <img src="icon/folder.png"/>
                                </a>
                                <a href="index.php?dir='.rawurlencode($path).'">'.$name.'</a>
                                <div class="perms">
                                    <a href="folder_chmod.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'" class="chmod">'.$perms.'</a>
                                </div>
                            </div>
                        </li>';
                    } else {
                        $edit = [null, '</a>'];
                        $icon = 'unknown';
                        $type = getFormat($name);
                        $isEdit = false;

                        if (in_array($type, $formats['other'])) {
                            $icon = $type;
                        } elseif (in_array($type, $formats['text'])) {
                            $icon = $type;
                            $isEdit = true;
                        } elseif (in_array($type, $formats['archive'])) {
                            $icon = $type;
                        } elseif (in_array($type, $formats['audio'])) {
                            $icon = $type;
                        } elseif (in_array($type, $formats['font'])) {
                            $icon = $type;
                        } elseif (in_array($type, $formats['binary'])) {
                            $icon = $type;
                        } elseif (in_array($type, $formats['document'])) {
                            $icon = $type;
                        } elseif (in_array($type, $formats['image'])) {
                            $icon = 'image';
                        } elseif (in_array(strtolower(str_contains($name, '.') ? substr($name, 0, strpos($name, '.')) : $name), $formats['source'])) {
                            $icon = strtolower(str_contains($name, '.') ? substr($name, 0, strpos($name, '.')) : $name);
                            $isEdit = true;
                        } elseif (isFormatUnknown($name)) {
                            $icon = 'unknown';
                            $isEdit = true;
                        }

                        if ('error_log' == strtolower($name) || $isEdit) {
                            $edit[0] = '<a href="edit_text.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'">';
                        } elseif (in_array($type, $formats['zip'])) {
                            $edit[0] = '<a href="file_unzip.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'">';
                        } else {
                            $edit[0] = '<a href="file_rename.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'">';
                        }

                        echo '<li class="file">
                            <p>
                                <input type="checkbox" name="entry[]" value="'.$name.'"/>
                                '.$edit[0].'<img src="icon/mime/'.$icon.'.png"/>'.$edit[1].'
                                <a href="file.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'">'.$name.'</a>
                            </p>
                            <p>
                                <span class="size">'.size(filesize($dir.'/'.$name)).'</span>,
                                <a href="file_chmod.php?dir='.$dirEncode.'&name='.$name.$pages['paramater_1'].'" class="chmod">'.$perms.'</a>
                            </p>
                        </li>';
                    }
                }

                echo '<li class="normal"><input type="checkbox" name="all" value="1" onClick="javascript:onCheckItem();"/> <strong class="form_checkbox_all">Chọn tất cả</strong></li>';

                if ($configs['page_list'] > 0 && $pages['total'] > 1) {
                    echo '<li class="normal">'.page($pages['current'], $pages['total'], [PAGE_URL_DEFAULT => 'index.php?dir='.$dirEncode, PAGE_URL_START => 'index.php?dir='.$dirEncode.'&page_list=']).'</li>';
                }
            }

            echo '</ul>';

            if ($count > 0) {
                echo '<div class="list">
                    <select name="option">
                        <option value="0">Sao chép</option>
                        <option value="1">Di chuyển</option>
                        <option value="2">Xóa</option>
                        <option value="3">Nén zip</option>
                        <option value="4">Chmod</option>
                        <option value="5">Đổi tên</option>
                    </select>
                    <input type="submit" name="submit" value="Thực hiện"/>
                </div>';
            }

            echo '</form>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/create.png"/> <a href="create.php?dir='.$dirEncode.$pages['paramater_1'].'">Tạo mới</a></li>
                <li><img src="icon/upload.png"/> <a href="upload.php?dir='.$dirEncode.$pages['paramater_1'].'">Tải lên tập tin</a></li>
                <li><img src="icon/import.png"/> <a href="import.php?dir='.$dirEncode.$pages['paramater_1'].'">Nhập khẩu tập tin</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }
