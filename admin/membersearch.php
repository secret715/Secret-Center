<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(9,'../index.php');

if(isset($_GET['search'])&&isset($_POST['level'])&&isset($_POST['joined'])&&isset($_POST['last_login'])&&isset($_POST['username'])&&isset($_POST['email'])&&isset($_POST['remark']) && sc_csrf_auth()){
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
		$_last_login=sprintf(" AND `login_time` BETWEEN '%s' AND '%s'",
					date('Y-m-d H:i:s',$POST_last_login['0']),
					date('Y-m-d H:i:s',$POST_last_login['1']));
	}elseif($POST_last_login['0']>0){
		$_last_login=sprintf(" AND `login_time` > '%s'",
					date('Y-m-d H:i:s',$POST_last_login['0']));
	}elseif($POST_last_login['1']>0){
		$_last_login=sprintf(" AND `login_time` < '%s'",
					date('Y-m-d H:i:s',$POST_last_login['1']));
	}
	else{
		$_last_login='';
	}
	
	
	$_member = sc_get_result("SELECT `member`.`id` , `member`.`username` , `member`.`nickname` , `member`.`email` , `member`.`level` ,`member`.`remark` ,`member`.`level` , `member`.`joined` , `login`.`id` AS 'login_id', `login_time` AS 'last_login' FROM `member` LEFT JOIN  `login` ON `member`.`id` = `owner` WHERE `username` LIKE '%%%s%%' AND `email` LIKE '%%%s%%' AND `remark` LIKE '%%%s%%' $_last_login $_joined $_level GROUP BY `member`.`id`",array(sc_namefilter($_POST['username']),$_POST['email'],$_POST['remark']));
}

$view = new View('theme/admin_default.html',$center['site_name'],'會員搜尋','admin/nav.php');
?>
<h2 class="page-header">會員搜尋</h2>
<?php if(!isset($_GET['search'])or !isset($_POST['level'])or !isset($_POST['joined'])or !isset($_POST['last_login'])or !isset($_POST['username'])or !isset($_POST['email'])or !isset($_POST['remark']) or !sc_csrf_auth()){ ?>
<form class="form-horizontal form-sm" action="membersearch.php?search&<?php echo sc_csrf(); ?>" method="POST">	
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
		<label class="col-sm-3 control-label" for="remark">備註：</label>
		<div class="col-sm-9">	
			<input class="form-control" name="remark" type="text">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="joined">註冊日期：</label>
		<div class="col-sm-9">
			<input class="form-control" name="joined[]" type="date" style="width:45%;display:inline-block;" placeholder="(YYYY-MM-DD)"> - 
			<input class="form-control" name="joined[]" type="date" style="width:45%;display:inline-block;" placeholder="(YYYY-MM-DD)">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="last_login">最後登入：</label>
		<div class="col-sm-9">
			<input class="form-control" name="last_login[]" type="date" style="width:45%;display:inline-block;" placeholder="(YYYY-MM-DD)"> - 
			<input class="form-control" name="last_login[]" type="date" style="width:45%;display:inline-block;" placeholder="(YYYY-MM-DD)">
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
<div class="table-responsive">
	<table class="table table-striped table-hover">
	  <tr>
		<th width="5%">ID</th>
		<th width="17%">帳號名稱</th>
		<th width="17%">電子信箱</th>
		<th width="12%">個人網站</th>
		<th width="12%">註冊日期</th>
		<th width="15%">最後登入</th>
		<th width="10%">權限</th>
		<th width="12%">管理</th>
	  </tr>
	<?php foreach($_member['row'] as $_v){ ?>
	  <tr>
		<td><?php echo $_v['id'] ;?></td>
		<td><?php echo $_v['username'] ;?></td>
		<td><small><?php echo $_v['email'] ;?></small></td>
		<td><small><?php echo $_v['remark'] ;?></small></td>
		<td style="font-size:92%;">
			<small><?php echo date('Y-m-d',strtotime($_v['joined'])); ?></small>
		</td>
		<td style="font-size:92%;">
			<small><?php //echo date('Y-m-d H:i',strtotime($_member['row'][0]['last_login'])); ?></small>
		</td>
		<td><?php echo sc_member_level_array($_v['level']); ?></td>
		<td>
			<a href="account.php?id=<?php echo $_v['id'].'&'.sc_csrf(); ?>" class="btn btn-info btn-sm">編輯</a>
			<a href="member.php?del=<?php echo $_v['id'].'&'.sc_csrf(); ?>" class="btn btn-danger btn-sm">刪除</a>
		</td>
	  </tr>
	<?php } ?>
	</table>
</div>
<?php }else{ ?>
	<div class="alert alert-danger">很抱歉，沒有符合的資料！</div>
<?php }} ?>
<?php
$view->render();
?>