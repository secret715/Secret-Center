<?php
require_once('include/database.php');
require_once('include/function.php');

if(!session_id()) {
    session_start();
}

global $center;

date_default_timezone_set("Asia/Taipei"); //時區設定
$center['site_name'] = "Secret Center 9.5"; //網站名稱
$center['register'] = "1"; //是否開啟註冊，0為關閉，1為開啟
$center['mail'] = "hongyin.sec@gmail.com";//"admin@example.com"; //網站Email，用於重設密碼信件

$center['database']['host']="localhost";//資料庫主機
$center['database']['name']="center";//資料庫名稱
$center['database']['username']="root";//資料庫帳號
$center['database']['password']="usbw";//資料庫密碼

$center['chat']['public'] = "0.00001"; //聊天室_發言間隔 單位 秒
$center['avatar']['max_size'] = "3000";//頭貼_檔案大小限制 單位 KB
$center['avatar']['compress'] = "1";//頭貼_是否開啟壓縮，0為關閉，1為開啟
$center['avatar']['quality'] = "20";//頭貼_壓縮品質設定 0~100
$center['forum']['captcha']="0"; //論壇_驗證碼是否開啟，0為關閉，1為開啟
$center['forum']['limit']="30"; //論壇_文章&回覆每頁顯示數量
$center['member']['message']=
<<<MSG
歡迎使用 Secret Center 9.5！
MSG;
;//會員中心訊息


if(!isset($_COOKIE['login']) && sc_level_auth(-1)){//如果已登入COOKIE不存在，但SESSION存在
    sc_loginout();
    header('Location: index.php?logout');//直接消除SESSION並登出
    exit;
}
