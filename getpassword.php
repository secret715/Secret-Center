<?php
require_once('config.php');
require_once('include/view.php');

if(isset($_POST['username'])&&isset($_POST['email'])&&trim($_POST['username'])!=''&&trim($_POST['email'])!=''){
	$_username=sc_namefilter($_POST['username']);
	
	$_member = sc_get_result("SELECT * FROM `member` WHERE `username` = '%s'",array($_username));
	
	if($_member['num_rows'] < 1 or $_POST['email']!=$_member['row'][0]['email']){
		$_GET['nouser']=true;
	}else{
		
		$_subject="重設密碼 - {$center['site_name']}";
		$_body="{$_member['row'][0]['username']} 您好
		\n 請點擊以下連結重設您的密碼 
		\n ".sc_get_headurl()."getpassword.php?id={$_member['row'][0]['id']}&auth=".md5($_member['row'][0]['rekey'])."
		\n (若是您沒有申請重設密碼，請忽略此信件)";
		
		$_header="From: {$center['site_name']} <{$center['mail']}> \n";
		$_header.='Content-type:text/plain; charset=UTF-8';
		mb_internal_encoding('UTF-8');
		$_subject=mb_encode_mimeheader($_subject,'UTF-8');
		
		if(mail($_member['row'][0]['email'],$_subject,$_body,$_header)){
			$_step=2;
		}
	}
}elseif(isset($_GET['auth'])&&trim($_GET['auth'])!=''&&isset($_GET['id'])&&abs($_GET['id'])!=''){
	$SQL=sc_db_conn();
	$_uid=abs($_GET['id']);
	$_member = sc_get_result("SELECT * FROM member WHERE `id` = '%d'",array($_uid));
	if($_member['num_rows']>0){
		if(md5($_member['row'][0]['rekey'])===$_GET['auth']){
			$_rekey_SQL=sprintf(",`rekey` = '%s'",substr(sc_keygen($_GET['auth']),0,16));
			$_step=3;
			
			if(isset($_POST['password'])&&trim($_POST['password'])!=''){
				$SQL->query("UPDATE member SET `password` = '%s' $_rekey_SQL WHERE `id` = '%d'",array(sc_password($_POST['password'],$_member['row'][0]['username']),$_uid));
				$_step=4;
				header("Location: index.php?getpassword");
				exit;
			}
		}
	}
}
$view = new View('include/theme/default.html',$center['site_name'],'重設密碼','include/nav.php');
?>
<?php if(isset($_GET['nouser'])){ ?>
<div class="alert alert-danger">帳號或電子信箱出現錯誤</div>
<?php } ?>
<h2>重設密碼</h2>
<?php if(!isset($_step)){ ?>
<form class="form-xs" action="getpassword.php" method="POST">
	<div class="form-group">
		<label for="username">請輸入您的帳號：</label>
		<input class="form-control" name="username" type="text" required="required">
	</div>
	<div class="form-group">
		<label for="email">請輸入您的電子信箱：</label>
		<input class="form-control" name="email" type="email" required="required">
	</div>
	<input class="btn btn-primary" type="submit" value="下一步">
</form>
<?php }elseif($_step==2){ ?>
	<div class="alert alert-success">密碼重設連結已經發送至您的電子信箱囉！</div>
<?php }elseif($_step==3){ ?>
<form class="form-xs" action="getpassword.php?id=<?php echo $_uid.'&auth='.$_GET['auth']; ?>" method="POST">
	<p>會員「<?php echo $_member['row'][0]['username']; ?>」歡迎你</p>
	<div class="form-group">
		<label>請重新設定你的密碼：</label>
		<input class="form-control" name="password" type="password">
	</div>
	<input class="btn btn-primary" type="submit" value="送出修改">
</form>
<?php } ?>
<?php
$view->render();
?>