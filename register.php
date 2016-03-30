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

require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if($center['register'] == 1){
	if(isset($_POST['username']) && trim($_POST['username']) != ''){
		if(strtoupper($_POST['captcha']) != strtoupper($_SESSION['captcha'])){
			header("Location: register.php?captcha");
			exit;
		}
		unset($_SESSION['captcha']);
		
		$reg=sc_register($_POST['username'],$_POST['password'],$_POST['email'],$_POST['web_site']);
		if($reg>0){
			header("Location: index.php?reg");
		}elseif($reg==-1){
			$_GET['requsername']=true;
		}else{
			$_GET['false']=true;
		}
	}
}

$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'註冊');
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
	
	$('.captcha').on('click', function(e){
		e.preventDefault();
		$(this).attr('src', 'include/captcha.php?_=' + (new Date).getTime());
	});
});
</script>
<?php
if($center['register'] == 1){
	if(isset($_GET['requsername'])){
?>
	<div class="alert alert-danger">此帳號或電子信箱已被使用！</div>
<?php }
	if(isset($_GET['captcha'])){
?>
	<div class="alert alert-danger">請檢查驗證碼！</div>
<?php }
?>
<h2 class="page-header">註冊</h2>
<form class="form-horizontal form-sm" action="register.php" method="POST">
	<div class="form-group">
		<label class="col-sm-3 control-label" for="username">* 帳號：</label>
		<div class="col-sm-6">	
			<input name="username" type="text" class="form-control" maxlength="20" required="required">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="username">* 密碼：</label>
		<div class="col-sm-6">
			<input class="form-control" name="password" type="password" maxlength="30" required="required">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="authpassword">* 確認密碼：</label>
		<div class="col-sm-6">
			<input class="form-control" name="authpassword" type="password" required="required">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="email">* 電子信箱：</label>
		<div class="col-sm-6">
			<input class="form-control" name="email" type="email" maxlength="255" required="required">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="web_site">個人網站：</label>
		<div class="col-sm-6">
			<input class="form-control" name="web_site" type="text" maxlength="255" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="captcha">* 驗證碼：</label>
		<div class="col-sm-6">
			<img src="include/captcha.php" class="captcha" title="按圖更換驗證碼"/>
			<input class="form-control" name="captcha" type="text" size="10" maxlength="10" required="required">
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<input class="btn btn-success btn-lg" type="submit" value="送出註冊">
		</div>
	</div>
</form>
<?php } else { ?>
	<div class="alert alert-error">目前關閉註冊！</div>
<?php } ?>
<?php
	$view->render();
?>