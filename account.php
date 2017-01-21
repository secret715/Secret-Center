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

require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?login");
	exit;
}

$_member = sc_get_result("SELECT * FROM `member` WHERE `id` = '%d'",array($_SESSION['Center_Id']));

if(isset($_POST['email'])&& filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
	if($_POST['web_site']!='' && !filter_var($_POST['web_site'], FILTER_VALIDATE_URL)){
		$_web_site=$_member['row']['web_site'];
	}else{
		$_web_site=$_POST['web_site'];
	}
	if($_POST['password'] == ''){
		$_password = $_member['row']['password'];
	}else {
		$_password = sc_password($_POST['password'], $_member['row']['username']);
	}
	
	$SQL->query("UPDATE `member` SET `password` = '%s', `email` = '%s', `web_site` = '%s' WHERE `id` = '%d'",array($_password,$_POST['email'],$_web_site,$_SESSION['Center_Id']));
	header("Location: account.php?ok");
}


$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'我的帳號');
$view->addScript("include/js/notice.js");
?>
<script>
$(function(){
	$('input[name="authpassword"]').on('keyup', function(){
		var $_error_msg=$(this).parent().siblings('.help-block');
		if($(this).val()!=$('input[name="password"]').val()){
			$_error_msg.html('<span class="text-danger">密碼不一致</span>'); 
			$('input[type="submit"]').attr('disabled','disabled');
		}else{
			$_error_msg.html('');
			$('input[type="submit"]').attr('disabled',false);
		}
	}).parent().parent().append('<div class="col-sm-3 help-block"></div>');
});
</script>
<?php if(isset($_GET['ok'])){?>
<div class="alert alert-success">修改成功！</div>
<?php } ?>
<h2 class="page-header">我的帳號</h2>
<div class="row">
	<div class="col-sm-3 text-center">
		<img src="<?php echo sc_avatar_url($_member['row']['id']); ?>" class="avatar">
		<p><a href="avatar.php">修改頭貼</a></p>
	</div>
	<div class="col-sm-9">
		<form class="form-horizontal form-sm" action="account.php" method="POST">
			<div class="form-group">
				<label class="col-sm-3 control-label">帳號：</label>
				<div class="col-sm-6">
					<p class="form-control-static"><?php echo $_member['row']['username']; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="username">密碼：</label>
				<div class="col-sm-6">
					<input class="form-control" name="password" type="password" maxlength="30">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="authpassword">確認密碼：</label>
				<div class="col-sm-6">
					<input class="form-control" name="authpassword" type="password">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="email">* 電子信箱：</label>
				<div class="col-sm-6">
					<input class="form-control" name="email" type="email" maxlength="255" required="required" value="<?php echo $_member['row']['email']; ?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="web_site">個人網站：</label>
				<div class="col-sm-6">
					<input class="form-control" name="web_site" type="text" maxlength="255" value="<?php echo $_member['row']['web_site']; ?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">權限：</label>
				<div class="col-sm-6">
					<p class="form-control-static"><?php echo sc_member_level($_member['row']['level']); ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">註冊日期：</label>
				<div class="col-sm-6">
					<p class="form-control-static"><?php echo $_member['row']['joined']; ?></p>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">最後登入：</label>
				<div class="col-sm-6">
					<p class="form-control-static"><?php echo $_member['row']['last_login']; ?></p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-6">
					<input class="btn btn-success btn-lg" type="submit" value="確認修改">
				</div>
			</div>
		</form>
	</div>
</div>
<?php
	$view->render();
?>