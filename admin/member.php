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

if(isset($_GET['edit']) && $_GET['edit'] != ''){
    $member_sql = sprintf("SELECT * FROM member WHERE id = '%d'", abs($_GET['edit']));
}else{
	$limit_row=30;
	if(isset($_GET['page'])){
		$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
		$member_sql = sprintf("SELECT * FROM member ORDER BY id ASC LIMIT %d,%d",$limit_start,$limit_row);
	}else{
		$limit_start= 0;
		$member_sql = sprintf("SELECT * FROM member ORDER BY id ASC LIMIT %d,%d",$limit_start,$limit_row);
	}
	
}

$member_query = $SQL->query($member_sql);
$member_row = $member_query->fetch_assoc();
$member_num_rows = $member_query->num_rows;


if(isset($_POST['email'])&& filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
	if($_POST['web_site']!='' && !filter_var($_POST['web_site'], FILTER_VALIDATE_URL)){
		$_web_site=$member['row']['web_site'];
	}else{
		$_web_site=$_POST['web_site'];
	}
	if($_POST['password']==''){
		$pass = $member_row['password'];
	}else{
		$pass = sc_password($_POST['password'],$member_row['name']);
	}
	
	$SQL->query("UPDATE member SET password = '%s', email = '%s', web_site = '%s', rekey = '%s', level = '%s' WHERE id = '%s'",array(
		$pass,
		$_POST['email'],
		$_web_site,
		$_POST['rekey'],
		$_POST['level'],
		$_GET['edit']
	));
	header("Location: member.php?edit=".$_GET['edit'].'&ok');
}
elseif(isset($_GET['del']) && $_GET['del'] != '') {
	$member = $SQL->query("SELECT * FROM member WHERE id = '%d'",array($_GET['del']))->fetch_assoc();
	$SQL->query("DELETE FROM member WHERE id = '%d'",array($_GET['del']));
	
	header("Location: member.php?delok");
}

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'會員管理',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/jquery.validate.js");
$view->addScript("../include/js/channel.js");

?>
<script type="text/javascript">
$(function(){
	$("#form1").validate({
		rules:{
			authpassword:{equalTo: "#password"},
			email:{required:true,email:true},
			web_site:{url:true},
			rekey:{required:true}
		},
		messages:{
			authpassword:{equalTo: "密碼不一致"},
		}
	});
});
</script>
<?php if(isset($_GET['ok'])){?>
	<div class="alert alert-success">修改成功！</div>
<?php } ?>
<div class="main">
<?php if(isset($_GET['edit'])) { ?>
<div class="row-fluid">
	<div class="span3 text-center">
		<img src="../include/avatar.php?id=<?php echo $member_row['name']; ?>" class="avatar">
	</div>
	<div class="span9">
		<form id="form1" name="form1" action="member.php?edit=<?php echo abs($_GET['edit']); ?>" method="POST" class="form-horizontal">
			<div class="control-group">
					<label class="control-label">帳號：</label>
					<div class="controls">
						<input id="username" name="username" value="<?php echo htmlspecialchars($member_row['name']); ?>" disabled="disabled" type="text">
					</div>
				</div>
			<div class="control-group">
				<label class="control-label" for="password">密碼：</label>
				<div class="controls">
					<input name="password" type="password" class="input-large" id="password" maxlength="30">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="authpassword">確認密碼：</label>
				<div class="controls">	
					<input name="authpassword" type="password" id="authpassword" class="input-large" maxlength="30" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email">電子信箱：</label>
				<div class="controls">	
					<input name="email" type="text" id="email" class="input-large" maxlength="255" value="<?php echo $member_row['email']; ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="web_site">個人網站：</label>
				<div class="controls">	
					<input name="web_site" type="text" id="web_site" class="input-large" maxlength="255" value="<?php echo $member_row['web_site']; ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="rekey">金鑰：</label>
				<div class="controls">	
					<input name="rekey" type="text" id="rekey" class="input-large" value="<?php echo $member_row['rekey']; ?>" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="level">權限：</label>
				<div class="controls">
					<select name="level" id="level" class="input-large">
					<?php foreach(sc_member_level_array() as $key=>$value){ ?>
						<option value="<?php echo $key; ?>" <?php if($member_row['level']==$key){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
					<?php } ?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">註冊日期：</label>
				<div class="controls">
					<input id="joined" name="joined" value="<?php echo $member_row['joined']; ?>" disabled="disabled" type="text">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">最後登入：</label>
				<div class="controls">
					<input id="last_login" name="last_login" value="<?php echo $member_row['last_login']; ?>" disabled="disabled" type="text">
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input name="button" type="submit" id="button" class="btn btn-success btn-large" value="確認修改" />
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</form>
</div>
<?php } else { ?>
<?php if(isset($_GET['delok'])){?>
	<div class="alert alert-success">成功刪除此會員！</div>
<?php } ?>
<h2 class="subtitle">會員管理</h2>
<table class="table table-striped table-hover">
  <tr>
    <th width="10%">ID</th>
    <th width="20%">帳號名稱</th>
	<th width="20%">電子信箱</th>
	<th width="20%">個人網站</th>
    <th width="15%">權限</th>
    <th width="15%">管理</th>
  </tr>
<?php do { ?>
  <tr>
    <td><?php echo $member_row['id'] ;?></td>
    <td><?php echo $member_row['name'] ;?></td>
	<td><?php echo $member_row['email'] ;?></td>
	<td><?php echo $member_row['web_site'] ;?></td>
    <td><?php echo sc_member_level($member_row['level']); ?></td>
    <td><a href="?edit=<?php echo $member_row['id'] ;?>">編輯</a>│<a href="javascript:if(confirm('確定刪除此會員？'))location='member.php?del=<?php echo $member_row['id'] ;?>'">刪除</a></td>
  </tr>
<?php } while ($member_row = $member_query->fetch_assoc()); ?>
</table>
<div>
<?php
$nav_query = $SQL->query('SELECT * FROM `member` ORDER BY `id` ASC');
$nav_num_rows = $nav_query->num_rows;

$pageTotal = ceil($nav_num_rows/$limit_row);

if($pageTotal > 1){
	echo '<div class="pagination"><ul>';
	for($i=1;$i<=$pageTotal;$i++){
		if(@$_GET['page']!=$i){
				echo '<li><a href="member.php?page='.$i.'">'.$i.'</a></li>';
			}else{
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
		}
	}
	echo '</ul></div><br class="clearfix" />';
}
?>
</div>
<?php } ?>
</div>
<?php
$view->render();
$member_query->free();
?>