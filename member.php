<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?n");
	exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'會員中心');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");
?>
<div class="main">
<?php if((isset($_COOKIE['login']))&&(isset($_GET['login']))){?>
	<div class="alert alert-success">登入成功！</div>
<?php } ?>
	<h2 class="subtitle">會員中心</h2>
    <div class="row-fluid">
		<div class="span4">
			<ul class="nav nav-tabs nav-stacked">
				<li><a href="account.php">我的帳號</a></li>
				<li><a href="avatar.php">修改頭像</a></li>
				<li><a href="chat.php">聊天室</a></li>
				<li><a href="forum.php">論壇</a></li>
				<li><a href="file.php">文件夾</a></li>
				<br class="clearfix" />
			</ul>
		</div>
		<div class="span8">
			<?php if($center['member']['message']!=''){ ?>
			<div class="well">
			<?php echo $center['member']['message']; ?>
			</div>
			<?php } ?>
		</div>
    </div>


</div>
<?php
	$view->render();
?>