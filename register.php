<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'註冊');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/jquery.validate.js");

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
?>
<script type="text/javascript">
$(function(){
	$("#form1").validate({
		rules:{
			username:{required:true},
			password:{required:true},
			authpassword:{required:true,equalTo: "#password"},
			email:{required:true,email:true},
			web_site:{url:true},
			captcha:{required:true}
		},
		messages:{
			authpassword:{equalTo: "密碼不一致"},
			captcha:{required:"請填入驗證碼"}
		}
	});
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
	<div class="alert alert-error">此帳號或電子信箱已被使用！</div>
<?php }
	if(isset($_GET['captcha'])){
?>
	<div class="alert alert-error">請檢查驗證碼！</div>
<?php }
?>
<form id="form1" action="register.php" method="POST" class="form-horizontal">
	<h2>註冊</h2>
	<div class="control-group">
		<label class="control-label" for="username">* 帳號：</label>
		<div class="controls">	
			<input name="username" type="text" class="input-large" id="username" maxlength="20" required="required">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="username">* 密碼：</label>
		<div class="controls">
			<input name="password" type="password" class="input-large" id="password" maxlength="30" required="required">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="authpassword">* 確認密碼：</label>
		<div class="controls">
			<input name="authpassword" type="password" class="input-large" id="authpassword" maxlength="30" required="required">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="email">* 電子信箱：</label>
		<div class="controls">
			<input name="email" type="text" id="email" class="input-large" value="" maxlength="255" required="required">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="web_site">個人網站：</label>
		<div class="controls">
			<input name="web_site" type="text" id="web_site" class="input-large" maxlength="255" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="captcha">* 驗證碼：</label>
		<div class="controls">
			<img src="include/captcha.php" class="captcha" title="按圖更換驗證碼"/>
			<input name="captcha" type="text" id="captcha" size="10" maxlength="10" required="required">
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<input id="button" name="button" class="btn btn-success btn-large" type="submit" value="送出註冊" />
		</div>
	</div>
<?php } else { ?>
	<div class="alert alert-error">目前關閉註冊！</div>
<?php } ?>
</form>
<?php
	$view->render();
?>