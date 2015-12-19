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
if(isset($_GET['search'])&&isset($_POST['level'])&&isset($_POST['joined'])&&isset($_POST['last_login'])&&isset($_POST['name'])&&isset($_POST['email'])&&isset($_POST['web_site'])){
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
	
	//echo vsprintf("SELECT * FROM member WHERE `name` LIKE '%%%s%%' AND `email` LIKE '%%%s%%' AND `web_site` LIKE '%%%s%%' $_last_login $_joined $_level ORDER BY id ASC",array(sc_namefilter($_POST['name']),$_POST['email'],$_POST['web_site']));die();
	
	$member_query = $SQL->query("SELECT * FROM member WHERE `name` LIKE '%%%s%%' AND `email` LIKE '%%%s%%' AND `web_site` LIKE '%%%s%%' $_last_login $_joined $_level ORDER BY id ASC",array(sc_namefilter($_POST['name']),$_POST['email'],$_POST['web_site']));
	$member_row = $member_query->fetch_assoc();
	$member_num_rows = $member_query->num_rows;
}

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'會員管理',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/jquery.validate.js");
$view->addScript("../include/js/channel.js");

?>
<?php if(!isset($_GET['search'])or!isset($_POST['level'])or!isset($_POST['joined'])or!isset($_POST['last_login'])or!isset($_POST['name'])or!isset($_POST['email'])or!isset($_POST['web_site'])){ ?>
<div class="main">
<h2 class="subtitle">會員搜尋</h2>
<form id="form1" name="form1" class="form-horizontal" action="searchmember.php?search" method="POST">	
	<div class="control-group">
		<label class="control-label" for="name">帳號：</label>
		<div class="controls">
			<input name="name" type="text" class="input-xlarge" id="name">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="email">電子信箱：</label>
		<div class="controls">	
			<input name="email" type="text" id="email" class="input-xlarge" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="web_site">個人網站：</label>
		<div class="controls">	
			<input name="web_site" type="text" id="web_site" class="input-xlarge" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="joined">註冊日期：</label>
		<div class="controls">
			<input name="joined[]" type="date" class="input-small" /> - 
			<input name="joined[]" type="date" class="input-small" />(YYYY-MM-DD)
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="last_login">最後登入：</label>
		<div class="controls">
			<input name="last_login[]" type="date" class="input-small" /> - 
			<input name="last_login[]" type="date" class="input-small" />(YYYY-MM-DD)
		</div>
	</div><div class="control-group">
		<label class="control-label" for="level">權限：</label>
		<div class="controls">
			<select name="level" id="level" class="input-xlarge">
				<option value="all">所有</option>
				<?php foreach(sc_member_level_array() as $key=>$value){ ?>
					<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div class="form-actions">
		<input name="button" type="submit" id="button" class="btn btn-success" value="搜尋" />
	</div>
</form>
<?php }else{if ($member_num_rows>0){ ?>
<h2 class="subtitle">會員搜尋</h2>
<table class="table table-striped table-hover">
  <tr>
    <th width="5%">ID</th>
    <th width="20%">帳號名稱</th>
	<th width="15%">電子信箱</th>
	<th width="15%">個人網站</th>
	<th width="15%">註冊日期</th>
	<th width="15%">最後登入</th>
    <th width="5%">權限</th>
    <th width="10%">管理</th>
  </tr>
<?php do { ?>
  <tr>
    <td><?php echo $member_row['id'] ;?></td>
    <td><?php echo $member_row['name'] ;?></td>
	<td><small><?php echo $member_row['email'] ;?></small></td>
	<td><small><?php echo $member_row['web_site'] ;?></small></td>
	<td style="line-height:0.8em;"><small><?php echo $member_row['joined'] ;?></small></td>
	<td style="line-height:0.8em;"><small><?php echo $member_row['last_login'] ;?></small></td>
    <td><?php echo sc_member_level($member_row['level']); ?></td>
    <td><a href="member.php?edit=<?php echo $member_row['id'] ;?>">編輯</a>│<a href="javascript:if(confirm('確定刪除此會員？'))location='member.php?del=<?php echo $member_row['id'] ;?>'">刪除</a></td>
  </tr>
<?php } while ($member_row = $member_query->fetch_assoc()); ?>
</table>

<?php }else{ ?>
	<div class="alert alert-error">很抱歉，沒有符合的資料！</div>
<?php }} ?>
</div>
<?php
$view->render();
?>