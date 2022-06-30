<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(9,'../index.php');

if(isset($_GET['logout'])){
	sc_loginout();
	header("Location: ../index.php?out");
	exit;
}

global $SQL;
$SQL=sc_db_conn();

$view = new View('theme/admin_default.html',$center['site_name'],'系統管理','admin/nav.php');
?>
<h2 class="page-header">系統管理</h2>
<p>歡迎來到系統管理介面！</p>
<div class="row">
	<div class="col-md-6 mt-3">
		<div class="card text-white bg-primary">
			<div class="card-body">
				<h4 class="card-title">系統</h4>
				Secret Center <?php echo sc_ver(); ?>&nbsp;&nbsp;<span id="ver_check"></span>
			</div>
		</div>
	</div>
	<div class="col-md-6 mt-3">
		<div class="card text-white bg-warning">
			<div class="card-body">
				<h4 class="card-title">會員</h4>
				<?php echo $SQL->query("SELECT COUNT(*) AS `count` FROM `member`")[0]['count']; ?> 人
			</div>
		</div>
	</div>
	<div class="col-md-6 mt-3">
		<div class="card text-white bg-success">
			<div class="card-body">
				<h4 class="card-title">論壇</h4>
				<?php echo $SQL->query("SELECT COUNT(*) AS `count` FROM `forum`")[0]['count']; ?> 篇文章
			</div>
		</div>
	</div>
	<div class="col-md-6 mt-3">
		<div class="card text-white bg-info">
			<div class="card-body">
				<h4 class="card-title">通知</h4>
				<?php echo $SQL->query("SELECT COUNT(*) AS `count` FROM `notice`")[0]['count']; ?> 筆
			</div>
		</div>
	</div>
</div>
<?php
$view->render();
?>