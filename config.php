<?php
if(!session_id()) {
    session_start();
}

if(!isset($_COOKIE['login']) && isset($_SESSION['Center_Username'])){//如果已登入COOKIE不存在，但SESSION存在
	header('Location: index.php?logout');//直接消除SESSION並登出
}

global $center;

date_default_timezone_set("Asia/Taipei"); //時區設定
$center['site_name'] = "Secret會員系統"; //網站名稱
$center['register'] = "1"; //是否開啟註冊，0為關閉，1為開啟
$center['mail'] = "admin@example.com"; //網站Email，用於重設密碼信件

$center['file']['limitedext'] = array("png","gif","jpg","zip","exe","txt","rar","doc","ppt","xls","docx","pptx","xlsx","odt","odp","ods");//文件夾_允許上傳的檔案格式
$center['file']['max_files'] = "10"; //文件夾_最多檔案數量
$center['file']['max_size'] = "100"; //文件夾_檔案大小限制 單位 KB
$center['chat']['public'] = "3"; //聊天室_發言間隔 單位 秒
$center['avatar']['max_size'] = "100";//頭像_檔案大小限制 單位 KB
$center['avatar']['img_tiny'] = "1";//頭像_是否開啟壓縮，0為關閉，1為開啟
$center['forum']['captcha']="0"; //論壇_驗證碼是否開啟，0為關閉，1為開啟
$center['forum']['limit']="30"; //論壇_帖子&回覆每頁顯示數量
$center['member']['message']=
<<<MSG
歡迎來到 Secret會員系統！
MSG;
;//會員中心信息
require_once('function.php');