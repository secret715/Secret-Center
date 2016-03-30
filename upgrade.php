<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2016 Secret Center開發團隊 <http://center.gdsecret.net/#team>

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

error_reporting(0);
if(isset($_GET['step'])&&$_GET['step']>0&&$_GET['step']<=4){
	$_step=abs($_GET['step']);
}else{
	$_step=0;
}

$error = false;

function check($val){
    global $error;
    if($val){
    	echo '<span style="color:green;">√</span>';
	}
	else {
		$error = true;
		echo '<span style="color:red;">Χ</span>';
	}
}

function check_php_version($version){
	check(phpversion() >= $version);
}

function check_extension($ext){
	check(extension_loaded($ext));
}

if($_step==2){
	if($_POST['radio']=='rename'){
		rename('upgrade.php','upgrade.txt');
		if(file_exists('install.php')){
			rename('install.php','install.txt');
		}
	}else{
		unlink('upgrade.php');
		if(file_exists('install.php')){
			unlink('install.php');
		}
	}
	header('Location: index.php');
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<title>Secret Center升級程序</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body{
			background-color:rgb(225,240,255);
			font-family:"微軟正黑體","新細明體",Arial;
		}
		h2,h3{
			font-weight:100;
		}
		#main{
			width:800px;
			background-color:rgba(255,255,255,0.9);
			margin:2em auto;
			padding: 0.5em 1.5em 1.5em 1.5em;
			box-shadow:0px 0px 20px rgb(210,225,245);
			border-radius:0.5em;
		}
		.message {
			width:80%;
			max-height:500px;
			overflow:auto;
			padding: 1em;
			margin-bottom:1em;
			background: rgb(190, 240 ,190);
			font-size: 90%;
			line-height: 1.5em;
			word-wrap: break-word;
			border-radius:0.25em;
		}
		.radio{
			font-size:96%;
			margin-bottom:2em;
		}
		input[type='text'],input[type='password']{
			max-width:250px;
			display:inline-block;
		}
		fieldset+fieldset{
			margin-top:2em;
		}
	</style>
</head>
<body>
	<div id="main">
		<h2 class="text-center">Secret Center升級程序</h2>
		<?php if($_step==0){ ?>
		<h3>授權條款</h3>
		<p>本程序僅適用於 Secret會員系統 8.1 升級至 Secret Center 9.0</p>
		<p class="text-danger">升級之後，文件夾功能將無法使用(但是檔案仍會保留)<p>
		<a class="btn btn-primary" href="upgrade.php?step=1">開始升級</a>
		<?php }elseif($_step==1){
			$error = false;
			$errormsg = null;
			
			try {
				require_once('Connections/SQL.php');
				require_once('config.php');
				if(mysqli_connect_errno()){
					$error = true;
					$errormsg = '資料庫連線失敗<br>'.mysqli_connect_error();
				}else{
					$_member = sc_get_result("SELECT `id`,`name` FROM `member`");
					$_m_id=array();
					do{
						$_m_id[$_member['row']['name']]=$_member['row']['id'];
					}while($_member['row'] = $_member['query']->fetch_assoc());
					
					$_chat = sc_get_result("SELECT * FROM `chat`");
					if($_chat['num_rows']>0){
						do{
							$query[]=sprintf("UPDATE `chat` SET `name` = '%d' WHERE `name` = '%s'",$_m_id[$_chat['row']['name']],$_chat['row']['name']);
						}while($_chat['row'] = $_chat['query']->fetch_assoc());
					}
					$query[]="ALTER TABLE `chat` CHANGE `id` `id` int NOT NULL AUTO_INCREMENT FIRST,CHANGE `data` `content` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`,CHANGE `ptime` `mktime` datetime NOT NULL AFTER `content`,CHANGE `name` `author` int NOT NULL AFTER `mktime`";
					
					
					
					$_forum = sc_get_result("SELECT * FROM `forum`");
					if($_forum['num_rows']>0){
						do{
							$query[]=sprintf("UPDATE `forum` SET `posted` = '%d' WHERE `posted` = '%s'",$_m_id[$_forum['row']['posted']],$_forum['row']['posted']);
						}while($_forum['row'] = $_forum['query']->fetch_assoc());
					}
					$query[]="ALTER TABLE `forum` CHANGE `id` `id` int NOT NULL AUTO_INCREMENT FIRST, CHANGE `post_title` `title` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`, CHANGE `post` `content` text COLLATE 'utf8_unicode_ci' NOT NULL AFTER `title`, CHANGE `level` `level` int(4) NOT NULL AFTER `block`, CHANGE `ptime` `mktime` datetime NOT NULL AFTER `level`, CHANGE `posted` `author` int NOT NULL AFTER `mktime`";
					
					
					
					$_forum_reply = sc_get_result("SELECT * FROM `forum_reply`");
					if($_forum_reply['num_rows']>0){
						do{
							$query[]=sprintf("UPDATE `forum_reply` SET `posted` = '%d' WHERE `posted` = '%s'",$_m_id[$_forum_reply['row']['posted']],$_forum_reply['row']['posted']);
						}while($_forum_reply['row'] = $_forum_reply['query']->fetch_assoc());
					}
					$query[]="ALTER TABLE `forum_reply` CHANGE `id` `id` int NOT NULL AUTO_INCREMENT FIRST, CHANGE `post` `post_id` int NOT NULL AFTER `id`, CHANGE `reply` `content` text COLLATE 'utf8_unicode_ci' NOT NULL AFTER `post_id`, CHANGE `ptime` `mktime` datetime NOT NULL AFTER `content`, CHANGE `posted` `author` int NOT NULL AFTER `mktime`";
					
					
					$query[]="ALTER TABLE `forum_block` CHANGE `ptime` `mktime` datetime NOT NULL AFTER `position`";
				
					
					$_member = sc_get_result("SELECT * FROM `member`");
					if($_member['num_rows']>0){
						do{
							if($_member['row']['avatar']=='../images/default_avatar.png'){
								$_avatar='default.png';
							}else{
								$_avatar=ltrim($_member['row']['avatar'],'../images/avatar/');
							}
							$query[]=sprintf("UPDATE `member` SET `avatar` = '%s' WHERE `id` = '%d'",$_avatar,$_member['row']['id']);
						}while($_member['row'] = $_member['query']->fetch_assoc());
					}
					$query[]="ALTER TABLE `member` CHANGE `id` `id` int NOT NULL AUTO_INCREMENT FIRST, CHANGE `name` `username` varchar(30) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `id`, CHANGE `level` `level` tinyint NOT NULL AFTER `rekey`;";
					
					
					
					$_notice = sc_get_result("SELECT * FROM `notice`");
					if($_notice['num_rows']>0){
						do{
							$query[]=sprintf("UPDATE `notice` SET `send_from` = '%d' WHERE `send_from` = '%s'",$_m_id[$_notice['row']['send_from']],$_notice['row']['send_from']);
							$query[]=sprintf("UPDATE `notice` SET `send_to` = '%d' WHERE `send_to` = '%s'",$_m_id[$_notice['row']['send_to']],$_notice['row']['send_to']);
						}while($_notice['row'] = $_notice['query']->fetch_assoc());
					}
					$query[]="ALTER TABLE `notice` CHANGE `id` `id` int NOT NULL AUTO_INCREMENT FIRST,CHANGE `ptime` `mktime` datetime NOT NULL AFTER `send_to`,CHANGE `send_from` `send_from` int NOT NULL AFTER `status`,CHANGE `send_to` `send_to` int NOT NULL AFTER `send_from`";
					
					foreach($query as $val){
						if($val!=''){
							$SQL->query($val);
						}
					}
					
					rename('include/avatar/default.png','images/avatar/default.png');
					sc_deletedir('include/avatar/');
					rename('images/avatar/','include/avatar/');
					sc_deletedir('view/');
					sc_deletedir('images/');
					unlink('licenses.txt');
					unlink('include/admin_nav.php');
					unlink('include/avatar.php');
					unlink('include/index.php');
					unlink('include/js/channel.js');
					unlink('include/js/jquery.validate.js');
					sc_deletedir('include/js/fileupload/');
				}
			}
			catch (Exception $e) {
				$error = true;
				$errormsg = base64_encode(json_encode(array(
					'type' => 'SQL Insert Error',
					'line' => __LINE__,
					'file' => dirname(__FILE__) . ';' . __FILE__,
					'errormsg' => $e->getMessage(),
				)));
			}
			
			if($error === false){
		?>
		<h3 class="text-success">升級成功！</h3>
		<p>Secret Center已升級成功，為了保障您網站的安全，請在此選擇一種方式來處理此程序。</p>
		<form name="form1" method="post" action="upgrade.php?step=2">
			<div class="radio">
				<label>
					<input name="radio" type="radio" value="unlink" checked="checked">刪除此升級程序
				</label>
			</div>
			<div class="radio">
				<label>
					<input name="radio" type="radio" value="rename">重新命名此升級程序
				</label>
			</div>
			<input class="btn btn-primary" type="submit" value="確定！">
		</form>
		<?php } else { ?>
		<h3 class="text-danger">Secret Center升級失敗！</h3>
		<p>Secret Center升級時發生錯誤！</p>
		<p>參考代碼：</p>
		<div class="message"><?php echo $errormsg; ?></div>
		<?php }} ?>
	</div>
</body>
</html>