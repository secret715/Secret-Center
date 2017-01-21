<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2017 Secret Center開發團隊 <http://center.gdsecret.net/#team>

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

if(isset($_GET['search'])&&isset($_POST['level'])&&isset($_POST['joined'])&&isset($_POST['last_login'])&&isset($_POST['username'])&&isset($_POST['email'])&&isset($_POST['web_site'])){
	if(is_numeric($_POST['level'])){
		$_level= sprintf("AND `level` = '%d'",abs($_POST['level']));
 	}else{
		$_level='';
	}
	$POST_joined['0']=strtotime($_POST['joined']['0']);
	$POST_joined['1']=strtotime($_POST['joined']['1']);
	$POST_last_login['0']=strtotime($_POST['last_login']['0']);
	$POST_last_login['1']=strtotime($_POST['last_login']['1']);
	if($POST_joined['0']>0&&$POST_joined['1']>0){
		$_joined=sprintf(" AND `joined` BETWEEN '%s' AND '%s'",
					date('Y-m-d H:i:s',$POST_joined['0']),
					date('Y-m-d H:i:s',$POST_joined['1']));
	}elseif($POST_joined['0']>0){
		$_joined=sprintf(" AND `joined` > '%s'",
					date('Y-m-d H:i:s',$POST_joined['0']));
	}elseif($POST_joined['1']>0){
		$_joined=sprintf(" AND `joined` < '%s'",
					date('Y-m-d H:i:s',$POST_joined['1']));
	}
	else{
		$_joined='';
	}
	if($POST_last_login['0']>0&&$POST_last_login['1']>0){
		$_last_login=sprintf(" AND `last_login` BETWEEN '%s' AND '%s'",
					date('Y-m-d H:i:s',$POST_last_login['0']),
					date('Y-m-d H:i:s',$POST_last_login['1']));
	}elseif($POST_last_login['0']>0){
		$_last_login=sprintf(" AND `last_login` > '%s'",
					date('Y-m-d H:i:s',$POST_last_login['0']));
	}elseif($POST_last_login['1']>0){
		$_last_login=sprintf(" AND `last_login` < '%s'",
					date('Y-m-d H:i:s',$POST_last_login['1']));
	}
	else{
		$_last_login='';
	}
	
	$_member = sc_get_result("SELECT * FROM `member` WHERE `username` LIKE '%%%s%%' AND `email` LIKE '%%%s%%' AND `web_site` LIKE '%%%s%%' $_last_login $_joined $_level ORDER BY `id` ASC",array(sc_namefilter($_POST['username']),$_POST['email'],$_POST['web_site']));
}

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'會員搜尋',true);
?>
<h2 class="page-header">會員搜尋</h2>
<?php if(!isset($_GET['search'])or!isset($_POST['level'])or!isset($_POST['joined'])or!isset($_POST['last_login'])or!isset($_POST['username'])or!isset($_POST['email'])or!isset($_POST['web_site'])){ ?>
<form class="form-horizontal form-sm" action="membersearch.php?search" method="POST">	
	<div class="form-group">
		<label class="col-sm-3 control-label" for="username">帳號：</label>
		<div class="col-sm-9">
			<input class="form-control" name="username" type="text">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="email">電子信箱：</label>
		<div class="col-sm-9">	
			<input class="form-control" name="email" type="text">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="web_site">個人網站：</label>
		<div class="col-sm-9">	
			<input class="form-control" name="web_site" type="text">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="joined">註冊日期：</label>
		<div class="col-sm-9">
			<input class="form-control" name="joined[]" type="date" style="width:30%;display:inline-block;"> - 
			<input class="form-control" name="joined[]" type="date" style="width:30%;display:inline-block;"><small>(YYYY-MM-DD)</small>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="last_login">最後登入：</label>
		<div class="col-sm-9">
			<input class="form-control" name="last_login[]" type="date" style="width:30%;display:inline-block;"> - 
			<input class="form-control" name="last_login[]" type="date" style="width:30%;display:inline-block;"><small>(YYYY-MM-DD)</small>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="level">權限：</label>
		<div class="col-sm-9">
			<select class="form-control" name="level">
				<option value="all">所有</option>
				<?php foreach(sc_member_level_array() as $key=>$value){ ?>
					<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<input class="btn btn-success btn-lg" type="submit" value="搜尋">
		</div>
	</div>
</form>
<?php }else{if ($_member['num_rows']>0){ ?>
<script>
$(function(){
	$('a.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除此會員？")){
			e.preventDefault();
		}
	});
});
</script>
<table class="table table-striped table-hover">
  <tr>
    <th width="7%">ID</th>
    <th width="17%">帳號名稱</th>
	<th width="17%">電子信箱</th>
	<th width="12%">個人網站</th>
	<th width="12%">註冊日期</th>
	<th width="15%">最後登入</th>
    <th width="10%">權限</th>
    <th width="10%">管理</th>
  </tr>
<?php do { ?>
  <tr>
    <td><?php echo $_member['row']['id'] ;?></td>
    <td><?php echo $_member['row']['username'] ;?></td>
	<td><small><?php echo $_member['row']['email'] ;?></small></td>
	<td><small><?php echo $_member['row']['web_site'] ;?></small></td>
	<td style="font-size:92%;">
		<small><?php echo date('Y-m-d',strtotime($_member['row']['joined'])); ?></small>
	</td>
	<td style="font-size:92%;">
		<small><?php echo date('Y-m-d H:i',strtotime($_member['row']['last_login'])); ?></small>
	</td>
    <td><?php echo sc_member_level($_member['row']['level']); ?></td>
    <td>
		<a href="member.php?edit=<?php echo $_member['row']['id'] ;?>" class="btn btn-info btn-xs">編輯</a>
		<a href="member.php?del=<?php echo $_member['row']['id'] ;?>" class="btn btn-danger btn-xs">刪除</a>
	</td>
  </tr>
<?php } while ($_member['row'] = $_member['query']->fetch_assoc()); ?>
</table>
<?php }else{ ?>
	<div class="alert alert-danger">很抱歉，沒有符合的資料！</div>
<?php }} ?>
<?php
$view->render();
?>