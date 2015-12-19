<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?login");
	exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'我的帳號');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");
$view->addScript("include/js/jquery.validate.js");

$member = sc_get_member_data($_SESSION['Center_Username']);


if(isset($_POST['email'])&& filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
	if($_POST['web_site']!='' && !filter_var($_POST['web_site'], FILTER_VALIDATE_URL)){
		$_web_site=$member['row']['web_site'];
	}else{
		$_web_site=$_POST['web_site'];
	}
	if($_POST['password'] == ''){
		$pass = $member['row']['password'];
	}
	else {
		$pass = sc_password($_POST['password'], $member['row']['name']);
	}
	
	$SQL->query("UPDATE member SET password = '%s', email = '%s', web_site = '%s' WHERE name = '%s'",array(
			$pass,
			$_POST['email'],
			$_web_site,
			$_SESSION['Center_Username']
	));
	header("Location: account.php?ok");
}
?>
<script type="text/javascript">
$(function(){
	$("#form1").validate({
		rules:{
			authpassword:{equalTo: "#password"},
			email:{required:true,email:true},
			web_site:{url:true},
		},
		messages:{
			authpassword:{equalTo: "密碼不一致"},
		}
	});
});
</script>
<div class="main">
<?php if(isset($_GET['ok'])){?>
<div class="alert alert-success">修改成功！</div>
<?php } ?>
	<h2 class="subtitle">我的帳號</h2>
<div class="row-fluid">
	<div class="span3 text-center">
		<img src="include/avatar.php?id=<?php echo $member['row']['name']; ?>" class="avatar">
		<p><a href="avatar.php">修改頭像</a></p>
	</div>
	<div class="span9">
		<form id="form1" name="form1" action="account.php" method="POST" class="form-horizontal">
			<div class="control-group">
					<label class="control-label">帳號：</label>
					<div class="controls"><?php echo htmlspecialchars($member['row']['name']); ?></div>
			</div>
			<div class="control-group">
				<label class="control-label" for="password">密碼：</label>
				<div class="controls">
					<input name="password" type="password" class="input-large" id="password" maxlength="30">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="authpassword">確認密碼：</label>
				<div class="controls">	
					<input name="authpassword" type="password" id="authpassword" class="input-large" maxlength="30" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email">* 電子信箱：</label>
				<div class="controls">	
					<input name="email" type="text" id="email" class="input-large" maxlength="255" value="<?php echo $member['row']['email']; ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="web_site">個人網站：</label>
				<div class="controls">	
					<input name="web_site" type="text" id="web_site" class="input-large" maxlength="255" value="<?php echo $member['row']['web_site']; ?>" />
				</div>
			</div>
			<div class="control-group">
					<label class="control-label">權限：</label>
					<div class="controls"><?php echo sc_member_level($member['row']['level']); ?></div>
			</div>
			<div class="control-group">
				<label class="control-label">註冊日期：</label>
				<div class="controls"><?php echo $member['row']['joined']; ?></div>
			</div>
			<div class="control-group">
				<label class="control-label">最後登入：</label>
				<div class="controls"><?php echo $member['row']['last_login']; ?></div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input name="button" type="submit" id="botton" class="btn btn-success btn-large" value="確認修改" />
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</form>
</div>
</div>
<?php
	$view->render();
?>