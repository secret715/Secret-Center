<?php
error_reporting(0);
if(isset($_GET['step'])&&$_GET['step']>0&&$_GET['step']<=4){
	$_step=abs($_GET['step']);
}else{
	$_step=0;
}

$error = false;

function check($val){
    global $error;
    if($val){
    	echo '<span style="color:green;">√</span>';
	}
	else {
		$error = true;
		echo '<span style="color:red;">Χ</span>';
	}
}

function check_php_version($version){
	check(phpversion() >= $version);
}

function check_extension($ext){
	check(extension_loaded($ext));
}

if($_step==4){
	if($_POST['radio']=='rename'){
		rename('install.php','install.txt');
		if(file_exists('upgrade.php')){
			rename('upgrade.php','upgrade.txt');
		}
	}else{
		unlink('install.php');
		if(file_exists('upgrade.php')){
			unlink('upgrade.php');
		}
	}
	header('Location: index.php');
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<title>Secret Center安裝程式</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body{
			background-color:rgb(225,240,255);
			font-family:"微軟正黑體","新細明體",Arial;
		}
		h2,h3{
			font-weight:100;
		}
		#main{
			width:800px;
			background-color:rgba(255,255,255,0.9);
			margin:2em auto;
			padding: 0.5em 1.5em 1.5em 1.5em;
			box-shadow:0px 0px 20px rgb(210,225,245);
			border-radius:0.5em;
		}
		.message {
			width:80%;
			max-height:500px;
			overflow:auto;
			padding: 1em;
			margin-bottom:1em;
			background: rgb(190, 240 ,190);
			font-size: 90%;
			line-height: 1.5em;
			word-wrap: break-word;
			border-radius:0.25em;
		}
		.radio{
			font-size:96%;
			margin-bottom:2em;
		}
		input[type='text'],input[type='password']{
			max-width:250px;
			display:inline-block;
		}
		fieldset+fieldset{
			margin-top:2em;
		}
	</style>
</head>
<body>
	<div id="main">
		<h2 class="text-center">Secret Center安裝程式</h2>
		<?php if($_step==0){ ?>
		<h3>授權條款</h3>
		<p>Secret Center的授權條款，請務必詳細閱讀條款後再進行安裝。</p>
		<div class="message">
			<?php echo nl2br(file_get_contents('./license.txt')); ?>
		</div>
		<a class="btn btn-primary" href="install.php?step=1">我已經閱讀完畢並同意授權條款，開始下一步</a>
		<?php }elseif($_step==1){ ?>
		<h3>安裝環境檢測</h3>
		<table class="table table-striped">
			<tr>
			  <th width="30%">項目</th>
			  <th width="25%">最低配置</th>
			  <th width="25%">最佳配置</th>
			  <th width="20%">檢測結果</th>
			</tr>
			<tr>
			  <td>PHP</td>
			  <td>5.6</td>
			  <td>7~</td>
			  <td><?php check_php_version(5.6); ?></td>
			</tr>
			<tr>
			  <td>GD函式庫</td>
			  <td>必須支援</td>
			  <td>必須支援</td>
			  <td><?php check_extension('gd'); ?></td>
			</tr>
			<tr>
			  <td>Multibyte String函式庫</td>
			  <td>必須支援</td>
			  <td>必須支援</td>
			  <td><?php check_extension('mbstring'); ?></td>
			</tr>
			<tr>
			  <td>Mysqli函式庫</td>
			  <td>必須支援</td>
			  <td>必須支援</td>
			  <td><?php check_extension('mysqli'); ?></td>
			</tr>
		</table>
		<h3>權限檢測</h3>
		<table class="table table-striped">
			<tr>
			  <th width="30%">項目</th>
			  <th width="25%">所需權限</th>
			  <th width="20%">檢測結果</th>
			</tr>
			<tr>
			  <td>config.php</td>
			  <td>可寫</td>
			  <td><?php check(is_writable('./config.php')); ?></td>
			</tr>
			<tr>
			  <td>config-sample.php</td>
			  <td>可讀</td>
			  <td><?php check(is_readable('./config-sample.php')); ?></td>
			</tr>
			<tr>
			  <td>inlude/avatar/</td>
			  <td>可寫</td>
			  <td><?php check(is_writable('./include/avatar/')); ?></td>
			</tr>
			<tr>
			  <td>include/admin/cssconfig.php</td>
			  <td>可寫</td>
			  <td><?php check(is_writable('./include/admin/cssconfig.php')); ?></td>
			</tr>
			<tr>
			  <td>include/admin/cssconfig-sample.php</td>
			  <td>可讀</td>
			  <td><?php check(is_readable('./include/admin/cssconfig-sample.php')); ?></td>
			</tr>
		</table>
		<a class="btn btn-primary<?php if($error){ ?> disabled"<?php }else{ ?>" href="install.php?step=2"<?php } ?>>下一步</a>
		<?php if($error){ ?><span style="color:red;">您必須解決以上問題才能繼續安裝！</span><?php } ?>
		<?php }elseif($_step==2){ ?>
		<form method="post" action="install.php?step=3">
			<fieldset>
				<legend>MySQL 連線資料</legend>
				<div class="form-group">
					<label for="mysql_database">資料庫名稱：</label>
					<input class="form-control" name="mysql_database" type="text" required="required">
				</div>
				<div class="form-group">
					<label for="mysql_username">連線帳號：</label>
					<input class="form-control" name="mysql_username" type="text" required="required">
				</div>
				<div class="form-group">
					<label for="mysql_password">連線密碼：</label>
					<input class="form-control" name="mysql_password" type="text" required="required">
				</div>
				<div class="form-group">
					<label for="mysql_host">MySQL伺服器：</label>
					<input class="form-control" name="mysql_host" type="text" required="required">
				</div>
			</fieldset>
			<fieldset>
				<legend>管理員資料</legend>
				<div class="form-group">
					<label for="admin_id">管理員帳號：</label>
					<input class="form-control" name="admin_id" type="text">&nbsp;(留空預設admin)
				</div>
				<div class="form-group">
					<label for="admin_psd">管理員密碼：</label>
					<input class="form-control" name="admin_psd" type="password">&nbsp;(留空預設admin)
				</div>
			</fieldset>
			<input type="submit" class="btn btn-primary" value="下一步">
		</form>
		<?php }elseif($_step==3){
			$error = false;
			$errormsg = null;
			
			try {
				if(isset($_POST['mysql_database'])&&($_POST['mysql_database']!='')){
					$mysql_file = 'Connections/SQL.php';
					$mysql_sample_file = 'Connections/SQL-sample.php';
					$mysql_config = vsprintf(file_get_contents($mysql_sample_file), array(
						addslashes($_POST['mysql_database']),
						addslashes($_POST['mysql_username']),
						addslashes($_POST['mysql_password']),
						addslashes($_POST['mysql_host'])
					));
					file_put_contents($mysql_file,$mysql_config);
					mysqli_connect(addslashes($_POST['mysql_host']),addslashes($_POST['mysql_username']),addslashes($_POST['mysql_password']),addslashes($_POST['mysql_database']));
				
					if(mysqli_connect_errno()){
						$error = true;
						$errormsg = '資料庫連線失敗<br>'.mysqli_connect_error();
					}else{
						require_once('Connections/SQL.php');
					
						if($_POST['admin_id'] == NULL){
							$admin_id = 'admin';
						}else{
							$admin_id = $_POST['admin_id'];
						}
						
						if($_POST['admin_psd'] == NULL){
							$admin_password = md5(sha1('admin'));
						}else{
							$admin_password = md5(sha1($_POST['admin_psd']));
						}
					
						$query=explode(';',str_replace("\r",'',str_replace("\n",'',file_get_contents('table.sql'))));
						$query[] = sprintf("INSERT INTO `member` (`username`, `password`, `email`, `web_site`, `avatar`, `rekey`, `level` , `joined` ,`last_login`) VALUES ('%s', '%s', '', '', 'default.png', '%s', '9', now(), now())", $admin_id, $admin_password,hash('sha224',uniqid().time()));
						
						foreach($query as $val){
							if($val!=''){
								$SQL->query($val);
							}
						}
					}
				}else{
					$error = true;
					$errormsg = '您沒有填入完整的MySQL連線資料';
				}
			}
			catch (Exception $e) {
				$error = true;
				$errormsg = base64_encode(json_encode(array(
					'type' => 'SQL Insert Error',
					'line' => __LINE__,
					'file' => dirname(__FILE__) . ';' . __FILE__,
					'errormsg' => $e->getMessage(),
				)));
			}
			
			if($error === false){
		?>
		<h3 class="text-success">安裝成功！</h3>
		<p>Secret Center已安裝成功，為了保障您網站的安全，請在此選擇一種方式來處理此程式。</p>
		<form name="form1" method="post" action="install.php?step=4">
			<div class="radio">
				<label>
					<input name="radio" type="radio" value="unlink" checked="checked">刪除此安裝程式
				</label>
			</div>
			<div class="radio">
				<label>
					<input name="radio" type="radio" value="rename">重新命名此安裝程式
				</label>
			</div>
			<input class="btn btn-primary" type="submit" value="確定！">
		</form>
		<?php } else { ?>
		<h3 class="text-danger">Secret Center安裝失敗！</h3>
		<p>Secret Center安裝時發生錯誤！</p>
		<p>參考代碼：</p>
		<div class="message"><?php echo $errormsg; ?></div>
		<?php }} ?>
	</div>
</body>
</html>