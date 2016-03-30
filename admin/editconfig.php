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

if(isset($_POST['site_name'])){
	if(isset($_POST['register'])){
		$register=1;
	}else{
		$register=0;
	}
	if(isset($_POST['compress'])){
		$compress=1;
	}else{
		$compress=0;
	}
	if(isset($_POST['forum_captcha'])){
		$forum_captcha=1;
	}else{
		$forum_captcha=0;
	}
	$config='../config.php';
	$config_sample='../config-sample.php';
	$put_config = vsprintf(file_get_contents($config_sample),array(
		addslashes($_POST['site_name']),
		$register,
		$_POST['mail'],
		abs($_POST['public']),
		abs($_POST['avatar_max_size']),
		$compress,
		abs($_POST['quality']),
		$forum_captcha,
		abs($_POST['forum_limit']),
		sc_xss_filter($_POST['member_message'])
	));
	file_put_contents($config,$put_config);
	$_GET['ok']=true;
	require('../config.php');
}

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'系統設定',true);
?>
<?php if(isset($_GET['ok'])){?>
	<div class="alert alert-success">編輯成功！</div>
<?php } ?>
<script>
$(function(){
	$('input[name="quality"]').on('change keyup', function(){
		$('#quality_percent').html($(this).val()+' %');
	});
});
</script>
<h2 class="page-header">系統設定</h2>
<form class="form-horizontal" method="post" action="editconfig.php">
	<fieldset>
		<legend>主要</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="site_name">網站名稱：</label>
			<div class="col-sm-6">
				<input class="form-control" name="site_name" type="text" value="<?php echo $center['site_name']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="register">開啟註冊：</label>
			<div class="col-sm-6">
				<label class="checkbox-inline">
					<input name="register" type="checkbox" value="1"<?php if($center['register']){echo ' checked="checked"';} ?>>開啟
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="mail">網站信箱：</label>
			<div class="col-sm-6">
				<input class="form-control" name="mail" type="email" value="<?php echo $center['mail']; ?>">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="member_message">會員中心訊息：</label>
			<div class="col-sm-6">
				<textarea class="form-control" name="member_message" rows="3"><?php echo $center['member']['message']; ?></textarea>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>聊天室</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="public">發言間隔：</label>
			<div class="col-sm-6">
				<div class="input-group" style="width:200px;">
					<input class="form-control" name="public" type="text" value="<?php echo $center['chat']['public']; ?>">
					<span class="input-group-addon">秒</span>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>頭貼</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="avatar_max_size">檔案大小限制：</label>
			<div class="col-sm-6">
				<div class="input-group" style="width:200px;">
					<input class="form-control" name="avatar_max_size" type="text" value="<?php echo $center['avatar']['max_size']; ?>">
					<span class="input-group-addon">KB</span>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="compress">開啟壓縮：</label>
			<div class="col-sm-6">
				<label class="checkbox-inline">
					<input name="compress" type="checkbox" value="1"<?php if($center['avatar']['compress']){echo ' checked="checked"';} ?>>開啟
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="quality">壓縮品質：</label>
			<div class="col-sm-6">
				<p class="form-control-static">
					<span id="quality_percent" style="margin-right:5px;"><?php echo $center['avatar']['quality']; ?> %</span>
					<input name="quality" type="range" min="0" max="100" step="1" value="<?php echo $center['avatar']['quality']; ?>" style="width:calc(90% - 60px );display:inline-block;">
				</p>
			</div>
			<div class="col-sm-4 help-block">
				範圍1~100，越高品質越好
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>論壇</legend>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="forum_captcha">發帖驗證碼：</label>
			<div class="col-sm-6">
				<label class="checkbox-inline">
					<input name="forum_captcha" type="checkbox" value="1"<?php if($center['forum']['captcha']){echo ' checked="checked"';} ?>> 開啟
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="forum_limit">每頁顯示資料：</label>
			<div class="col-sm-6">
				<div class="input-group" style="width:200px;">
					<input class="form-control" name="forum_limit" class="input-mini" type="text" value="<?php echo $center['forum']['limit']; ?>">
					<span class="input-group-addon">筆</span>
				</div>
			</div>
			<div class="col-sm-4 help-block">
				每頁所顯示的帖子/回覆數量
			</div>
		</div>
	</fieldset>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-6">
			<input class="btn btn-success btn-lg" type="submit" value="修改">
		</div>
	</div>
</form>
<?php
$view->render();
?>