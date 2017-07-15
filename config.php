<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2017 Secret Center開發團隊 <http://center.gdsecret.net/#team>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

if(!session_id()) {
    session_start();
}

if(!isset($_COOKIE['login']) && isset($_SESSION['Center_Username'])){//如果已登入COOKIE不存在，但SESSION存在
	header('Location: index.php?logout');//直接消除SESSION並登出
}

global $center;

date_default_timezone_set("Asia/Taipei"); //時區設定
$center['site_name'] = "Secret Center 9.2"; //網站名稱
$center['register'] = "1"; //是否開啟註冊，0為關閉，1為開啟
$center['mail'] = "admin@example.com"; //網站Email，用於重設密碼信件

$center['chat']['public'] = "3"; //聊天室_發言間隔 單位 秒
$center['avatar']['max_size'] = "300";//頭貼_檔案大小限制 單位 KB
$center['avatar']['compress'] = "1";//頭貼_是否開啟壓縮，0為關閉，1為開啟
$center['avatar']['quality'] = "90";//頭貼_壓縮品質設定 0~100
$center['forum']['captcha']="0"; //論壇_驗證碼是否開啟，0為關閉，1為開啟
$center['forum']['limit']="30"; //論壇_文章&回覆每頁顯示數量
$center['member']['message']=
<<<MSG
歡迎使用 Secret Center ！
MSG;
;//會員中心信息
require_once('function.php');