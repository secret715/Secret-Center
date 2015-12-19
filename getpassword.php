<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'重設密碼');

if(isset($_POST['username'])&&isset($_POST['email'])&&trim($_POST['username'])!=''&&trim($_POST['email'])!=''){
	$_username=sc_namefilter($_POST['username']);
	
	$member = sc_get_member_data($_username);
	
	if($member['num_rows'] < 1 or $_POST['email']!=$member['row']['email']){
		$_GET['nouser']=true;
	}else{
		
		$_subject="重設密碼 - {$center['site_name']}";
		$_body="{$member['row']['name']} 您好
		\n 請點擊以下連結重設您的密碼 
		\n ".sc_get_headurl()."getpassword.php?user={$member['row']['id']}&auth=".md5($member['row']['rekey'])."
		\n (若是您沒有申請重設密碼，請忽略此信件)";
		
		$_header="From: {$center['site_name']} <{$center['mail']}> \n";
		$_header.='Content-type:text/plain; charset=UTF-8';
		mb_internal_encoding('UTF-8');
		$_subject=mb_encode_mimeheader($_subject,'UTF-8');
		
		if(mail($member['row']['email'],$_subject,$_body,$_header)){
			$_step=2;
		}
	}
}elseif(isset($_GET['auth'])&&trim($_GET['auth'])!=''&&isset($_GET['user'])&&abs($_GET['user'])!=''){
	$_uid=abs($_GET['user']);
	$member['query'] = $SQL->query("SELECT * FROM member WHERE `id` = '%d'",array($_uid));
	$member['row'] = $member['query']->fetch_assoc();
	$member['num_rows'] = $member['query']->num_rows;
	if($member['num_rows']>0){
		if(md5($member['row']['rekey'])==$_GET['auth']){
			$_rekey_SQL=sprintf(",`rekey` = '%s'",substr(sc_keygen($_GET['auth']),0,16));
			$_step=3;
			
			if(isset($_POST['password'])&&trim($_POST['password'])!=''){
				$SQL->query("UPDATE member SET `password` = '%s' $_rekey_SQL WHERE `id` = '%d'",array(sc_password($_POST['password'],$member['row']['name']),$_uid));
				$_step=4;
				header("Location: index.php?getpassword");
				exit;
			}
		}
	}
}
?>
<div class="main">
<?php if(isset($_GET['nouser'])){ ?>
	<div class="alert alert-error">帳號或電子信箱出現錯誤</div>
<?php } ?>
<h2>重設密碼</h2>
<?php if(!isset($_step)){ ?>
<form action="getpassword.php" method="POST">
	<div class="controls">
		<label for="username">請輸入您的帳號：</label>
		<input class="input-large" id="username" name="username" type="text" required>
	</div>
	
	<div class="controls">
		<label for="email">請輸入您的電子信箱：</label>
		<input class="input-large" id="email" name="email" type="email" required>
	</div>
	
	<input class="btn btn-primary" name="login" type="submit" id="login" value="下一步" />
</form>
<?php }elseif($_step==2){ ?>
	<div class="alert alert-success">密碼重設連結已經發送至您的電子信箱囉！</div>
<?php }elseif($_step==3){ ?>
<form id="form1" name="form1" action="getpassword.php?user=<?php echo $_uid.'&auth='.$_GET['auth']; ?>" method="POST">
	<p>會員「<?php echo $member['row']['name']; ?>」歡迎你</p>
	<div class="controls">
		<label>請重新設定你的密碼：</label>
		<input class="input-large" id="password" name="password" type="password" />
	</div>
	<input class="btn btn-primary" name="login" type="submit" id="login" value="送出修改" />
</form>
<?php } ?>
</div>
<?php
$view->render();
?>