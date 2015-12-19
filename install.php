<?php
@error_reporting(0);
$error = false;

function check($val){
    global $error;
    if($val){
    	echo "<span style=\"color:green;\">√</span>";
	}
	else {
		$error = true;
		echo "<span style=\"color:red;\">Χ</span>";
	}
}

function check_php_version($version){
	check(phpversion() >= $version);
}

function check_extension($ext){
	check(extension_loaded($ext));
}
if(isset($_GET['step'])&&$_GET['step']==4){
	if($_POST["radio"]=="rename"){
		rename("install.php","install.txt");
		if(file_exists('upgrade.php')){
			rename("upgrade.php","upgrade.txt");
		}
	}else{
		unlink("install.php");
		if(file_exists('upgrade.php')){
			unlink("upgrade.php");
		}
	}
	header("Location: index.php");
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="utf-8">
		<title>『Secret會員系統』安裝程序</title>
		<style>
			body{
				background-color:rgb(225,240,255);
				font-family:"微軟正黑體","新細明體",Arial;
			}
			form {
				margin: 1em;
				width: 550px;
			}
			fieldset{
				width: 650px;
				margin: 15px 0 15px 0;
			}
			fieldset table {
				width: 100%;
				text-align: center;
			}
			label.error{
				margin-left: 5px;
				font-size: 80%;
				color: red;
			}
			fieldset{
				border:0;
			}
			fieldset legend{
				font-size:120%;
			}
			#licenses {
				width:650px;
				padding: 1em;
				background: rgb(190, 240 ,190);
				font-size: 0.8em;
				line-height: 1.5em;
				word-wrap: break-word;
				border-radius:0.25em;
			}
			table {
				border: 1px #ccc solid;
				border-radius: 5px;
				border-collapse: collapse;
			}
			table th {
				background: #e9e9e9;
				padding: 0.25em;
			}
			table td {
				background: rgb(248,248,248);
				font-size:96%;
				padding: 0.25em;
			}
			#main{
				width:800px;
				background-color:rgba(255,255,255,0.9);
				margin:2em auto;
				padding:1.5em;
				box-shadow:0px 0px 20px rgb(210,225,245);
				border-radius:0.5em;
			}
			.page-title{
				font-size:180%;
				margin-bottom:1em;
				font-family:"微軟正黑體","新細明體",Arial;
				font-weight:100;
			}
			input[type="text"], input[type="password"] {
    			width: 250px;
    			border: 1px solid rgb(200,200,200);
    			padding: 0.3em;
    			border-radius: 0.25em;
			}
		</style>
	</head>
	
	<body>
	<div id="main">
		<?php if(@$_GET['step']==NULL){ ?>
		<h2 class="page-title">『Secret會員系統』安裝程序-授權條款</h2>
		<p>『Secret會員系統』的授權條款，請務必詳細閱讀條款後再進行安裝。</p>
		<form name="form1" method="post" action="install.php?step=1">
            <div id="licenses">
                <?php echo nl2br(file_get_contents('./licenses.txt')); ?>
            </div>
			<p><input type="submit" name="button" id="button" value="我已經閱讀完畢並同意授權條款，開始下一步"></p>
		</form>
		<?php }
		if(@$_GET['step']==1){ ?>
		<h2 class="page-title">『Secret會員系統』安裝程序-第一步</h2>
		<form name="form1" method="post" action="install.php?step=2">
			<fieldset>
				<legend>安裝環境檢測</legend>
				<table>
					<tr>
					  <th width="30%">項目</th>
					  <th width="25%">最低配置</th>
					  <th width="25%">最佳配置</th>
					  <th width="20%">檢測結果</th>
					</tr>
					<tr>
					  <td>PHP</td>
					  <td>5.3</td>
					  <td>5.3~5.6</td>
					  <td><?php check_php_version(5.3); ?></td>
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
				</fieldset>
				<fieldset>
				<legend>權限檢測</legend>
				  <table>
					<tr>
					  <th width="30%">項目</th>
					  <th width="25%">所需權限</th>
					  <th width="20%">檢測結果</th>
					</tr>
					<tr>
					  <td>style.css</td>
					  <td>可寫</td>
					  <td><?php check(is_writable('./style.css')); ?></td>
					</tr>
                    <tr>
					  <td>style-sample.css</td>
					  <td>可讀</td>
					  <td><?php check(is_readable('./style-sample.css')); ?></td>
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
					  <td>Connections/SQL.php</td>
					  <td>可寫</td>
					  <td><?php check(is_writable('./Connections/SQL.php')); ?></td>
					</tr>
					<tr>
					  <td>Connections/SQL-sample.php</td>
					  <td>可讀</td>
					  <td><?php check(is_readable('./Connections/SQL-sample.php')); ?></td>
					</tr>
					<tr>
					  <td>include/file</td>
					  <td>可寫</td>
					  <td><?php check(is_writable('./include/file')); ?></td>
					</tr>
				  </table>
				</fieldset>
				  <p><input type="submit" name="button" id="button" value="下一步"<?php if($error){ ?> disabled="disabled"<?php } ?>><?php if($error){ ?><span style="color:red;">您必須解決以上問題才能繼續安裝！</span><?php } ?></p>
				</form>
				<?php } ?>

				<?php if(@$_GET['step']==2){ ?>
				<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
				<script type="text/javascript" src="include/js/jquery.validate.js"></script>
				<script type="text/javascript">
				$(function(){
					$("#form1").validate({
						rules:{
							mysql_database:{required:true},
							mysql_username:{required:true},
							mysql_host:{required:true}
						}
					});
				});
				</script>
				<h2 class="page-title">『Secret會員系統』安裝程序-第二步</h2>
				<form id="form1" name="form1" method="post" action="install.php?step=3">
				<fieldset>
					<legend>MySQL 連線資料</legend>
					<p>資料庫名稱：<input type="text" name="mysql_database" id="mysql_database" value=""></p>
					<p>連線帳號：<input type="text" name="mysql_username" id="mysql_username" value=""></p>
					<p>連線密碼：<input type="text" name="mysql_password" id="mysql_password" value=""></p>
					<p>MySQL伺服器：<input type="text" name="mysql_host" id="mysql_host" value=""></p>
				</fieldset>
				<fieldset>
					<legend>管理員資料</legend>
					<p>管理員帳號：
					<input type="text" name="admin_id" id="admin_id" value="">&nbsp;(留空預設admin)</p>
					<p>管理員密碼：<input type="password" name="admin_psd" id="admin_psd" value="">&nbsp;(留空預設admin)</p>
				</fieldset>
					<p><input type="submit" name="button" id="button" value="下一步"></p>
				</form>
				<?php }
				if(@$_GET['step']==3){
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
						}
						
						if($_POST['admin_id'] == NULL){
							$admin_id = "admin";
						}else{
							$admin_id = $_POST['admin_id'];
						}
						
						if($_POST['admin_psd'] == NULL){
							$admin_password = md5(sha1('admin'));
						}else{
							$admin_password = md5(sha1($_POST['admin_psd']));
						}
						
						mysqli_connect(addslashes($_POST['mysql_host']),addslashes($_POST['mysql_username']),addslashes($_POST['mysql_password']),addslashes($_POST['mysql_database']));
						
						if(mysqli_connect_errno()){
						$error = true;
						$errormsg = '資料庫連線失敗<br>'.mysqli_connect_error();	
						}else{
							require_once('Connections/SQL.php');
							
							$query = file('table.sql');
							$query[] = sprintf("INSERT INTO `member` (`id`, `name`, `password`, `email`, `web_site`, `avatar`, `rekey`, `level`, `joined`,`last_login`) VALUES (1,'%s','%s','admin','admin','../images/default_avatar.png','%s',9,now(),now())", $admin_id, $admin_password,str_shuffle(substr(base64_encode(mt_rand(100,999)).sha1(time().$admin_id.uniqid()),0,20)));
							
							
							foreach($query as $val){
								$SQL->query($val);
							}
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
				
					<h2 class="page-title" style="color:green;">『Secret會員系統』安裝成功！</h2>
					<p>會員系統已安裝成功，為了保障您網站的安全，請在此選擇一種方式來處理此程序。</p>
					<form name="form1" method="post" action="install.php?step=4">
						<p><input type="radio" name="radio" id="radio" value="rename" checked="checked">重新命名此安裝程序</p>
						<p><input type="radio" name="radio" id="radio2" value="unlink">刪除此安裝程序</p>
						<p><input type="submit" name="button" id="button" value="確定！"></p>
					</form>
				<?php
					}
					else {
				?>
					<h2 class="page-title" style="color:red;">『Secret會員系統』安裝失敗！</h2>
					<p>Secret會員系統安裝時發生錯誤！</p>
					<p>參考代碼：</p>
					<pre id="licenses"><?php echo $errormsg; ?></pre>
				<?php
					}
				}
				?>
	</div>
	</body>
</html>