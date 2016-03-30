<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2016 Secret Center開發團隊 <http://center.gdsecret.net/#team>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

set_include_path('../include/');
$includepath = true;

require_once('../Connections/SQL.php');
require_once('../config.php');
require_once('view.php');

if(!isset($_SESSION['Center_Username']) or $_SESSION['Center_UserGroup'] != 9){
	header("Location: ../index.php");
	exit;
}

if(isset($_POST['status'])&&isset($_POST['mktime'])&&intval($_POST['status'])>=0&&intval($_POST['mktime'])>=0){
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
<form class="form-horizontal form-sm" action="notice.php" method="POST">
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