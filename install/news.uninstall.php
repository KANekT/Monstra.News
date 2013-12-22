<?php defined('MONSTRA_ACCESS') or die('No direct script access.');

// Delete Options
Option::delete('news_template');
Option::delete('news_limit');
Option::delete('news_limit_admin');
Option::delete('news_w');
Option::delete('news_h');
Option::delete('news_wmax');
Option::delete('news_hmax');
Option::delete('news_resize');

Table::drop('news');

function RemoveDir($dir) {
    if ($objs = glob($dir."/*")) {
        foreach($objs as $obj) {
            is_dir($obj) ? RemoveDir($obj) : unlink($obj);
        }
    }
    rmdir($dir);
}

RemoveDir(ROOT . DS . 'storage' . DS . 'news');
RemoveDir(ROOT . DS . 'public' . DS . 'uploads' . DS . 'news' . DS);