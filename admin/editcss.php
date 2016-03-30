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

set_include_path('../include/');
$includepath = true;

require_once('../Connections/SQL.php');
require_once('../config.php');
require_once('view.php');

if(!isset($_SESSION['Center_Username']) or $_SESSION['Center_UserGroup'] != 9){
    header("Location: ../index.php");
	exit;
}

if(isset($_POST['body_font-size'])){
	$_put_array=array(
		addslashes($_POST['body_font-size']),
		addslashes($_POST['body_background-color']),
		addslashes($_POST['body_line-height']),
		addslashes($_POST['main_width']),
		addslashes($_POST['chat_color']),
		addslashes($_POST['chat_background-color'])
	);
	
	$_css='../style.css';
	$_css_sample='../style-sample.css';
	
	$config='../include/admin/cssconfig.php';
	$config_sample='../include/admin/cssconfig-sample.php';
	
	$put_css = vsprintf(str_replace('%;','@',file_get_contents($_css_sample)),$_put_array);
	$put_config = vsprintf(file_get_contents($config_sample),$_put_array);
	
	file_put_contents($_css,str_replace('@','%;',$put_css));
	file_put_contents($config,$put_config);
	
	$_GET['ok']=true;
}
require_once('../include/admin/cssconfig.php');
$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'網站樣式',true);
?>
<?php if(isset($_GET['ok'])){?>
	<div class="alert alert-success">修改成功！</div>
<?php } ?>
<h2 class="page-header">網站樣式</h2>
<p>提醒您，若修改後網站樣式沒有變更，請清除瀏覽器快取後再重新整理頁面</p>
<form class="form-horizontal form-sm" method="post" action="editcss.php">
	<fieldset>
		<legend>主要</legend>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="body_font-size">字體大小：</label>
			<div class="col-sm-3">
				<div class="input-group">
					<input class="form-control" name="body_font-size" type="text" value="<?php echo $center['css']['body']['font-size']; ?>" required="required">
					<span class="input-group-addon">px</span>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="body_background-color">背景顏色：</label>
			<div class="col-sm-3">
				<input class="form-control" name="body_background-color" type="color" value="<?php echo $center['css']['body']['background-color']; ?>" required="required">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="body_line-height">行距：</label>
			<div class="col-sm-3">
				<div class="input-group">
					<input class="form-control" name="body_line-height" type="text" value="<?php echo $center['css']['body']['line-height']; ?>" required="required">
					<span class="input-group-addon">px</span>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="main_width">版面寬度：</label>
			<div class="col-sm-3">
				<div class="input-group">
					<input class="form-control" name="main_width" type="text" value="<?php echo $center['css']['#main']['width']; ?>" required="required">
					<span class="input-group-addon">%</span>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>聊天室</legend>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="chat_color">文字顏色：</label>
			<div class="col-sm-3">
				<input class="form-control" name="chat_color" type="color" value="<?php echo $center['css']['#chat .msg-panel']['color']; ?>" required="required">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label" for="chat_background-color">背景顏色：</label>
			<div class="col-sm-3">
				<input class="form-control" name="chat_background-color" type="color" value="<?php echo $center['css']['#chat .msg-panel']['background-color']; ?>" required="required">
			</div>
		</div>
	</fieldset>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-3">
			<input class="btn btn-success btn-lg" type="submit" value="修改">
		</div>
	</div>
</form>
<?php
$view->render();
?>