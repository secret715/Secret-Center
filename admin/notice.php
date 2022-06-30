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

if(isset($_POST['status'])&&isset($_POST['mktime'])&&intval($_POST['status'])>=0&&intval($_POST['mktime'])>=0 && isset($_GET[$_SESSION['Center_Auth']])){
	if($_POST['status']!='all'){
		$_status=intval($_POST['status']);
		$_status_SQL=sprintf("`status` = '%d' AND",$_status);
	}else{
		$_status='';
		$_status_SQL='';
	}
	$SQL->query("DELETE FROM `notice` WHERE $_status_SQL `mktime` < '%s'",array(date('Y-m-d H:i:s',time()-60*60*24*30*abs($_POST['mktime']))));
	$_GET['delok']=true;
}
$_notice['num_rows']=$SQL->query("SELECT * FROM `notice`")->num_rows;
$_unread_notice['num_rows']=$SQL->query("SELECT * FROM `notice` WHERE `status`='0'")->num_rows;
$_read_notice['num_rows']=$SQL->query("SELECT * FROM `notice` WHERE `status`='1'")->num_rows;

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'通知管理',true);
?>
<script>
$(function(){
	$('input.btn.btn-danger').click(function(e){	
		if(!window.confirm('確定刪除？')){
			e.preventDefault();
		}
	});
});
</script>
<?php if(isset($_GET['delok'])){ ?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<h2 class="page-header">通知管理</h2>
<p>
	目前總共有 <?php echo $_notice['num_rows']; ?> 筆通知，
	<?php echo $_unread_notice['num_rows']; ?> 筆未讀，
	<?php echo $_read_notice['num_rows']; ?> 筆已讀
</p>
<form class="form-horizontal form-sm" action="notice.php?<?php echo $_SESSION['Center_Auth']; ?>" method="POST">
	<div class="form-group">
	<label class="col-sm-3 control-label" for="mktime">時間：</label>
	<div class="col-sm-6">
			<select class="form-control" name="mktime">
				<option value="12">一年前</option>
				<option value="9">九個月前</option>
				<option value="6">六個月前</option>
				<option value="3" selected>三個月前</option>
				<option value="2">二個月前</option>
				<option value="1">一個月前</option>
				<option value="0">不限</option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="status">狀態：</label>
		<div class="col-sm-6">
			<select class="form-control" name="status">
				<option value="all">所有</option>
				<option value="0">未讀</option>
				<option value="1" selected>已讀</option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<input class="btn btn-danger" type="submit" value="刪除">
		</div>
	</div>
</form>
<?php
$view->render();
?>