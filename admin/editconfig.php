<?php
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
	if(isset($_POST['img_tiny'])){
		$img_tiny=1;
	}else{
		$img_tiny=0;
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
		implode('","',explode(',',$_POST['limitedext'])),
		abs($_POST['max_files']),
		abs($_POST['max_size']),
		abs($_POST['public']),
		abs($_POST['avatar_max_size']),
		$img_tiny,
		$forum_captcha,
		abs($_POST['forum_limit']),
		sc_xss_filter($_POST['member_message'])
	));
	file_put_contents($config,$put_config);
	$_GET['ok']=true;
	require('../config.php');
}

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'系統設定',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");
$view->addScript("../include/js/jquery.validate.js");
?>
<script type="text/javascript">
$(function(){
	$("#form1").validate({
		rules:{
			site_name:{required:true},
			mail:{required:true},
			public:{required:true,min:0},
			limitedext:{required:true},
			max_files:{required:true,min:0},
			max_size:{required:true,min:0}
		},
	});
});
</script>
<div class="main">
<?php if(isset($_GET['ok'])){?>
	<div class="alert alert-success">編輯成功！</div>
<?php } ?>
<h2 class="subtitle">系統設定</h2>
<form id="form1" name="form1" class="form-horizontal" method="post" action="editconfig.php">
	<fieldset>
		<legend>主要</legend>
		<div class="control-group">
			<label class="control-label" for="site_name">網站名稱：</label>
			<div class="controls">
				<input id="site_name" name="site_name" class="input-xlarge" type="text" value="<?php echo $center['site_name']; ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="register">開啟註冊：</label>
			<div class="controls">
				<input id="register" name="register" type="checkbox" value="1"<?php if($center['register']){echo ' checked="checked"';} ?>> <label class="checkbox inline" for="register">開啟</label>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="mail">網站信箱：</label>
			<div class="controls">
				<input id="mail" name="mail" class="input-xlarge" type="email" value="<?php echo $center['mail']; ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="member_message">會員中心訊息：</label>
			<div class="controls">
				<textarea id="member_message" name="member_message" class="input-block-level" rows="3"><?php echo $center['member']['message']; ?></textarea>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>聊天室</legend>
		<div class="control-group">
			<label class="control-label" for="public">發言間隔：</label>
			<div class="controls">
				<div class="input-append">
					<input id="public" name="public" class="input-mini" type="text" value="<?php echo $center['chat']['public']; ?>">
					<span class="add-on">秒</span>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>文件夾</legend>
		<div class="control-group">
			<label class="control-label" for="limitedext">允許上傳的檔案格式：</label>
			<div class="controls">
				<input id="limitedext" name="limitedext" class="input-xxlarge" type="text" value="<?php echo implode(",", $center['file']['limitedext']); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="max_files">最多檔案數量：</label>
			<div class="controls">
				<div class="input-append">
					<input id="max_files" name="max_files" class="input-mini" type="text" value="<?php echo $center['file']['max_files']; ?>">
					<span class="add-on">個</span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="max_size">檔案大小限制：</label>
			<div class="controls">
				<div class="input-append">
					<input id="max_size" name="max_size" class="input-mini" type="text" value="<?php echo $center['file']['max_size']; ?>">
					<span class="add-on">KB</span>
				</div>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>頭像</legend>
		<div class="control-group">
			<label class="control-label" for="avatar_max_size">檔案大小限制：</label>
			<div class="controls">
				<div class="input-append">
					<input id="avatar_max_size" name="avatar_max_size" class="input-mini" type="text" value="<?php echo $center['avatar']['max_size']; ?>">
					<span class="add-on">KB</span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="img_tiny">開啟壓縮：</label>
			<div class="controls">
				<input id="img_tiny" name="img_tiny" type="checkbox" value="1"<?php if($center['avatar']['img_tiny']){echo ' checked="checked"';} ?>> <label class="checkbox inline" for="img_tiny">開啟</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>論壇</legend>
		<div class="control-group">
			<label class="control-label" for="forum_captcha">發帖驗證碼：</label>
			<div class="controls">
				<input id="forum_captcha" name="forum_captcha" type="checkbox" value="1"<?php if($center['forum']['captcha']){echo ' checked="checked"';} ?>> <label class="checkbox inline" for="forum_captcha">開啟</label>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="forum_limit">每頁顯示資料：</label>
			<div class="controls">
				<div class="input-append">
					<input id="forum_limit" name="forum_limit" class="input-mini" type="text" value="<?php echo $center['forum']['limit']; ?>">
					<span class="add-on">筆</span>
				</div>
				<span class="help-inline">每頁所顯示的帖子/回覆數量</span>
			</div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input name="button" type="submit" id="button" class="btn btn-success" value="修改" />
	</div>
</form>
</div>
<?php
$view->render();
?>