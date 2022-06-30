<?php
require_once('config.php');
require_once('include/view.php');

if($center['register'] == 1){
	if(isset($_POST['username']) && trim($_POST['username']) != ''&&sc_csrf_auth()){
		if(strtoupper($_POST['captcha']) != strtoupper($_SESSION['captcha'])){
			header("Location: register.php?captcha");
			exit;
		}
		unset($_SESSION['captcha']);
		
		$reg=sc_register($_POST['username'],$_POST['password'],$_POST['email'],$_POST['nickname']);
		if($reg>0){
			header("Location: index.php?reg");
			exit;
		}elseif($reg==-1){
			$_GET['requsername']=true;
		}else{
			$_GET['false']=true;
		}
	}
}

$view = new View('include/theme/login.html',$center['site_name'],'註冊','include/nav.php');
$view->addScript('include/js/register.js');
?>
<script>auth="<?php echo sc_csrf(); ?>"</script>
<?php
if($center['register'] == 1){ ?>
<form id="register" class="form-signin" action="register.php?<?php echo sc_csrf(); ?>" method="POST">
<?php 
	if(isset($_GET['requsername'])){
?>
	<div class="alert alert-danger">此帳號或電子信箱已被使用！</div>
<?php }
	if(isset($_GET['captcha'])){
?>
	<div class="alert alert-danger">請檢查驗證碼！</div>
<?php } ?>
	<h1>註冊</h1>
	<div class="form-label-group">
	    <input type="text" id="username" name="username" class="form-control" placeholder="帳號" pattern="[a-zA-Z0-9]{1,30}" required>
  		<label for="username">帳號</label>
	</div>
	<div class="form-label-group">
		<input name="nickname" id="nickname" type="text" class="form-control" maxlength="20" required="required" placeholder="暱稱">
		<label for="nickname">暱稱</label>
	</div>
	<div class="form-label-group">
	<input class="form-control" id="password" name="password" type="password" maxlength="30" required="required" placeholder="密碼">
		<label for="password">密碼</label>
	</div>
	<div class="form-label-group">
	<input class="form-control" id="authpassword" name="authpassword" type="password" maxlength="30" required="required" placeholder="確認密碼">
		<label for="authpassword">確認密碼</label>
	</div>
	<div class="form-label-group">
	<input class="form-control" id="email" name="email" type="email" maxlength="255" required="required" placeholder="電子信箱">
		<label for="email">電子信箱</label>
	</div>
	<div class="form-label-group form-row ml-1">
		<input class="form-control col-6" id="captcha" name="captcha" type="text" maxlength="10" required="required" placeholder="驗證碼" value="">
		<label for="email">驗證碼</label>
		<div class="col-6"><img src="include/captcha.php" class="captcha" title="按圖更換驗證碼"></div>
	</div>
	<input class="btn btn-lg btn-primary btn-block" type="submit" value="送出註冊">
</form>
<?php } else { ?>
	<div class="alert alert-danger">目前關閉註冊！</div>
<?php } ?>
<?php
	$view->render();
?>