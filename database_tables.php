<?php

    define('ACCESS', true);
    define('PHPMYADMIN', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Danh sách bảng';

        include_once 'database_connect.php';

        if (IS_CONNECT) {
            $title .= ': ' . DATABASE_NAME;
            $query = mysqli_query($conn, 'SHOW TABLE STATUS');

            include_once 'header.php';

            if (is_object($query)) {
                if (isset($_GET['action']) && trim($_GET['action']) == 'selected_table') {
                    $title = 'Chọn lựa: ' . DATABASE_NAME;
                    $entrys = isset($_POST['entry']) && is_array($_POST['entry']) && count($_POST['entry']) > 0 ? $_POST['entry'] : null;
                    if (isset($_POST['delete']) && $entrys != null) {
                        $title = 'Xóa bảng: ' . DATABASE_NAME;
                        $isAllExists = true;
                        $entryHtml = null;
                        $listEntryHtml = null;
          
                        foreach ($entrys AS $v) {               
                            if (isTableExists(addslashes($v)) == false) {
                                $isAllExists = false;
                                break;
                            } else {
                                $entryHtml .= '<input type="hidden" name="entry[]" value="' . $v . '"/>';
                                $listEntryHtml .= '<li><img src="icon/database_table.png"/> <span>' . $v . '</span></li>';
                            }
                        }
                        
                        include_once 'header.php';
    
                        if ($isAllExists) {
                            echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>';
    
                            if (isset($_POST['accept'])) {
                                $isDeleteAll = true;
    
                                foreach ($entrys AS $v) {
                                    if (!mysqli_query($conn, "DROP TABLE `" . addslashes($v) . "`")) {
                                        $isDeleteAll = false;
    
                                        echo '<div class="notice_failure">Xóa [<strong>' . $v . '</strong>] thất bại: ' . mysqli_error($conn) . '</div>';
                                    } else {
                                        echo '<div class="notice_succeed">Xóa [<strong>' . $v . '</strong>] thành công</div>';
                                    }
                                }
    
                                if ($isDeleteAll)
                                    goURL('database_tables.php?db_name=' . DATABASE_NAME);
                            } else if (isset($_POST['not'])) {
                                goURL('database_tables.php?db_name=' . DATABASE_NAME);
                            }
    
                            echo '<ul class="list">' . $listEntryHtml . '</ul>';
    
                            echo '<div class="list">
                                <form action="database_tables.php?action=selected_table&db_name=' . DATABASE_NAME . '" method="post">
                                    <span>Bạn có thật sự muốn xóa những bảng đã chọn không?</span><hr/>
                                    <input type="hidden" name="delete" value="1"/>
                                    ' . $entryHtml . '
                                    <center>
                                        <input type="submit" name="accept" value="Xóa"/>
                                        <input type="submit" name="not" value="Huỷ"/>
                                    </center>
                                </form>
                            </div>';
                        } else {
                            echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                            <div class="list">Bảng không tồn tại</div>';
                        }
                    } else if ($entrys == null) {
                        include_once 'header.php';
    
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">Không có mục nào được chọn</div>';
                    } else {
                        include_once 'header.php';
                        
                        echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                        <div class="list">Không có lựa chọn</div>';
                    }
                } else {
                    $count = mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM `information_schema`.`tables` WHERE `table_schema`="' . DATABASE_NAME . '"'))[0];
                    
                    echo '<script language="javascript" src="checkbox.js"></script><div class="title"><div class="ellipsis">' . $title . '</div></div>
                    <form action="database_tables.php?action=selected_table&db_name=' . DATABASE_NAME . '" method="post" name="form"><ul class="list_database">';
                            
                    if ($count == 0) {
                        echo '<li class="normal"><img src="icon/empty.png"/> <span class="empty">Không có bảng nào</span></li>';
                    } else {                                                                             
                        $total_size = 0;
    
                        while ($assoc = mysqli_fetch_assoc($query)) {
                            $name = $assoc['Name'];
                            $total_size += intval($assoc['Data_length']);
    
                            echo '<li>
                                <p>
                                    <input type="checkbox" name="entry[]" value="' . $name . '"/>
                                    <a href="database_table.php?action=rename&name=' . $name . DATABASE_NAME_PARAMATER_1 . '">
                                        <img src="icon/database_table.png"/>
                                    </a>
                                    <a href="database_table.php?start&name=' . $name . DATABASE_NAME_PARAMATER_1 . '">
                                        <strong>' . $name . '</strong>
                                    </a>
                                </p>
                                <p>
                                    <span class="size">' . size($assoc['Data_length']) . '</span>, 
                                    <span class="count_columns">' . ($assoc['Rows'] == 0 ? mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM `$name`")) : $assoc['Rows']) . '</span>
                                    <span>cột</span>
                                </p>
                            </li>';
                        }
                        
                        echo '<li class="normal"><strong>Dung lượng</strong>: <span class="size">' . size($total_size) . '</span>, <strong>Bảng</strong>: <span class="count_tables">' . $count . '</span></li>';
                        
                        echo '<li class="normal"><input type="checkbox" name="all" value="1" onClick="javascript:onCheckItem();"/> <strong class="form_checkbox_all">Chọn tất cả</strong></li>';
                                              
                    }
                    echo '</ul>';
                    if ($count > 0 ) {
                        echo '<div class="list">
                            <input type="submit" name="delete" value="Xóa"/>
                        </div>';
                    }
                    echo '</form>';     
                }
                echo '<div class="title">Chức năng</div>
                <ul class="list">
                    <li><img src="icon/database_table_create.png"/> <a href="database_table_create.php' . DATABASE_NAME_PARAMATER_0 . '">Tạo bảng</a></li>';
                    if (isset($_GET['action']))
                        echo '<li><img src="icon/database_table.png"/> <a href="database_tables.php?db_name=' . DATABASE_NAME . '">Danh sách bảng</a></li>';
                    if (IS_DATABASE_ROOT)
                        echo '<li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>';

                echo '</ul>';
            } else {
                echo '<div class="title"><div class="ellipsis">' . $title . '</div></div>
                <div class="list">Không thể lấy danh sách bảng</div>
                <div class="title">Chức năng</div>
                <ul class="list">';

                    if (IS_DATABASE_ROOT)
                        echo '<li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>';
                    else
                        echo '<li><img src="icon/disconnect.png"/> <a href="database_disconnect.php">Ngắt kết nối database</a></li>';

                echo '</ul>';
            }
        } else if (ERROR_CONNECT == false && ERROR_SELECT_DB && IS_DATABASE_ROOT) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list">Không thể chọn database</div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/database.png"/> <a href="database_lists.php">Danh sách database</a></li>
            </ul>';
        } else {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list">Lỗi cấu hình hoặc không kết nối được</div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/disconnect.png"/> <a href="database_disconnect.php">Ngắt kết nối database</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }

    include_once 'database_close.php';

?>
