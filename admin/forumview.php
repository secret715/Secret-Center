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

if((!isset($_GET['id']))or($_GET['id']=='')){
    header("Location: forum.php");
	exit;
}else{
	$_GET['id']=intval($_GET['id']);
}

$_post = sc_get_result("SELECT * FROM `forum` WHERE `id` = '%d'",array($_GET['id']));

if($_post['num_rows']<=0){
	header("Location: forum.php");
	exit;
}


if(isset($_GET['del']) && $_GET['del'] != '' && isset($_GET[$_SESSION['Center_Auth']])){
    $_del[] = sprintf("DELETE FROM `forum` WHERE `id` = '%d'",$_GET['del']);
    $_del[] = sprintf("DELETE FROM `forum_reply` WHERE `post_id` = '%d'",$_GET['del']);
    foreach($_del as $val){
		$SQL->query($val);
	}
	header('Location: forum.php?del&fid='.$_post['row']['block']);
}elseif(isset($_GET['delreply']) && $_GET['delreply'] != '' && isset($_GET[$_SESSION['Center_Auth']])){
	$SQL->query("DELETE FROM `forum_reply` WHERE `id` = '%d'",array($_GET['delreply']));
	header("Location: forumview.php?delreply&id=".$_post['row']['id']);
}

$_block = sc_get_result("SELECT * FROM `forum_block` WHERE `id`='%d'",array($_post['row']['block']));

$limit_row=$center['forum']['limit'];
	
if(isset($_GET['page'])){
	$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
	$_reply = sc_get_result("SELECT * FROM `forum_reply` WHERE `post_id`='%d' ORDER BY `mktime` ASC LIMIT %d,%d",array($_post['row']['id'],$limit_start,$limit_row));
} else {
	$limit_start=0;
	$_reply = sc_get_result("SELECT * FROM `forum_reply` WHERE `post_id`='%d' ORDER BY `mktime` ASC LIMIT %d,%d",array($_post['row']['id'],$limit_start,$limit_row));
}
$_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_post['row']['author']));
$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],$_post['row']['title'],true);
?>
<script>
$(function(){
	$('a.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除？")){
			e.preventDefault();
		}
	});
});
</script>
<?php if(isset($_GET['editok'])){ ?>
	<div class="alert alert-success">編輯成功！</div>
<?php }elseif(isset($_GET['delreply'])){ ?>
	<div class="alert alert-success">刪除成功！</div> 
<?php } ?>
<ul class="breadcrumb">
	<li><a href="forum.php">論壇</a></li>
	<li><a href="forum.php?fid=<?php echo $_block['row']['id']; ?>"><?php echo $_block['row']['blockname']; ?></a></li>
	<li class="active"><?php echo sc_removal_escape_string($_post['row']['title']); ?></li>
</ul>
<ul class="list-inline">
	<li>
		<h2><?php echo $_post['row']['title']; ?></h2>
	</li>
	<?php if($_post['row']['level']>1){ ?>
	<li>
		<span class="label label-default"><?php echo sc_member_level($_post['row']['level']); ?></span>
	</li>
	<?php } ?>
</ul>
<div id="1" class="post">
	<ul class="list-inline">
		<li>
			<img src="<?php echo sc_avatar_url($_post['row']['author']); ?>" class="avatar">
		</li>
		<li style="font-size:120%;"><?php echo $_author['row']['username']; ?></li>
		<li>發表於&nbsp;<?php echo $_post['row']['mktime']; ?></li>
		<li>1&nbsp;樓</li>
		<?php if($_post['row']['author'] == $_SESSION['Center_Id']){ ?>
		<li>
			<a href="forumedit.php?post&id=<?php echo $_post['row']['id']; ?>" class="btn btn-info btn-sm">
				編輯
			</a>
			<a href="forumview.php?del=<?php echo $_post['row']['id']; ?>&id=<?php echo $_post['row']['id'].'&'.$_SESSION['Center_Auth']; ?>" class="btn btn-danger btn-sm">
				刪除
			</a>
		</li>
		<?php } ?>
	</ul>
    <div class="con"><?php echo sc_removal_escape_string($_post['row']['content']); ?></div>
</div>
<?php
if($_reply['num_rows']>0){
	$_floor = 1+$limit_start;
	do{
		$_floor++;
		$_reply_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_reply['row']['author']));
?>
<div id="<?php echo $_floor; ?>" class="post">
	<ul class="list-inline">
		<li>
			<img src="<?php echo sc_avatar_url($_reply['row']['author']); ?>" class="avatar">
		</li>
		<li style="font-size:130%;"><?php echo $_reply_author['row']['username']; ?></li>
		<li>發表於&nbsp;<?php echo $_reply['row']['mktime']; ?></li>
		<li><?php echo $_floor; ?>&nbsp;樓</li>
		<?php if($_reply['row']['author']==$_SESSION['Center_Id']){ ?>
		<li>
			<a href="forumedit.php?reply&id=<?php echo $_reply['row']['id']; ?>" class="btn btn-info btn-sm">
				編輯
			</a>
			<a href="forumview.php?delreply=<?php echo $_reply['row']['id']; ?>&id=<?php echo $_post['row']['id'].'&'.$_SESSION['Center_Auth']; ?>" class="btn btn-danger btn-sm">
				刪除
			</a>
		</li>
		<?php } ?>
	</ul>
	<div class="con"><?php echo sc_removal_escape_string($_reply['row']['content']); ?></div>
</div>
<?php
	}while($_reply['row'] = $_reply['query']->fetch_assoc());
}
$_all_reply=sc_get_result("SELECT COUNT(*) FROM `forum_reply` WHERE `post_id`='%d'",array($_post['row']['id']));
echo sc_page_pagination('forumview.php',@$_GET['page'],implode('',$_all_reply['row']),$center['forum']['limit'],'&id='.$_post['row']['id']);
?>
<?php
$view->render();
?>