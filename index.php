<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'會員登入');

if(isset($_SESSION['Center_Username'],$_SESSION['Center_UserGroup'])){
	header('Location: member.php');
}

if (isset($_POST['username'])) {
	if(sc_login($_POST['username'],$_POST['password'])==1){
		header("Location: member.php?login");
	}else{
		header("Location: index.php?login");
	}
	die();
}
else if(isset($_GET['logout'])) {
	sc_loginout();
}

switch(true){
	case isset($_GET['reg']):
?>
	<div class="alert alert-success">註冊成功！</div>
<?php
	break;
	case isset($_GET['out']):
?>
	<div class="alert alert-success">您已經登出！</div>
<?php
	break;
	case isset($_GET['getpassword']):
?>
	<div class="alert alert-success">密碼重設成功！</div>
<?php
	break;
	case isset($_GET['login']):
?>
	<div class="alert alert-error">登入失敗</div>
<?php
	break;
}
?>
<div class="login-form">
	<h2 class="page-header">會員登入</h2>
	<form class="form-horizontal" action="index.php" method="post">
		<div class="control-group">
			<label class="control-label" for="username">帳號：</label>
			<div class="controls">
				<input id="username" name="username" type="text">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="password">密碼：</label>
			<div class="controls">
				<input id="password" name="password" type="password">
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<input type="submit" value="登入" class="btn btn-primary btn-large">
				<a href="register.php" class="btn btn-info">註冊</a>
				<a href="getpassword.php">忘了您的密碼？</a>
			</div>
		</div>
	</form>
</div>
<?php
	$view->render();
?>