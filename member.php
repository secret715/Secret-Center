<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

$view = new View('include/theme/default.html',$center['site_name'],'會員中心','include/nav.php');
$view->addScript("include/js/notice.js");
?>
<?php if((isset($_COOKIE['login']))&&(isset($_GET['login']))){?>
	<div class="alert alert-success">登入成功！</div>
<?php } ?>
<h2 class="page-header">會員中心</h2>
<div class="row">
	<div class="col-md-4">
		<div class="list-group">
			<a href="account.php" class="list-group-item">我的帳號</a>
			<a href="chat.php" class="list-group-item">聊天室</a>
			<a href="forum.php" class="list-group-item">論壇</a>
		</div>
	</div>
	<div class="col-md-8">
		<?php if($center['member']['message']!=''){ ?>
			<div class="card">
				<div class="card-body">
					<?php echo $center['member']['message']; ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<?php
	$view->render();
?>