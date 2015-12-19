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

if(isset($_GET['del'])&& abs($_GET['del'])!=''){
	$del[] = sprintf("DELETE FROM forum WHERE id = '%d'",abs($_GET['del']));
    $del[] = sprintf("DELETE FROM forum_reply WHERE post = '%d'",abs($_GET['del']));
    foreach($del as $val){
		$SQL->query($val);
	}
	header("Location: forum.php?del");
	exit;
}

if((!isset($_GET['id']))or($_GET['id']=='')){
    header("Location: forum.php");
	exit;
}

$post = $SQL->query("SELECT * FROM forum WHERE id = '%s'",array($_GET['id']));
$post_row = $post->fetch_assoc();
$post_num_rows = $post->num_rows;

if($post_num_rows<=0){
	header("Location: forum.php");
	exit;
}


if(isset($_GET['delreply']) && $_GET['delreply'] != ''){
	$SQL->query("DELETE FROM forum_reply WHERE id = %d",array($_GET['delreply']));
	header("Location: forumview.php?delreply&id=".$_GET['id']);
}


$_block = $SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array(abs(intval($post_row['block']))))->fetch_assoc();

$limit_row=$center['forum']['limit'];
if(isset($_GET['page'])){
	$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
	$reply_sql = sprintf("SELECT * FROM forum_reply WHERE post = '%s' ORDER BY id ASC LIMIT %d,%d",$_GET['id'],$limit_start,$limit_row);
} else {
	$limit_start = 0;
	$reply_sql = sprintf("SELECT * FROM forum_reply WHERE post = '%s' ORDER BY id ASC LIMIT %d,%d",$_GET['id'],$limit_start,$limit_row);
}
$reply_query = $SQL->query($reply_sql);
$reply_num_rows = $reply_query->num_rows;


$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],$post_row['post_title'],true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");
?>
<div class="main">
<?php if(isset($_GET['editok'])){ ?>
	<div class="alert alert-success">編輯成功！</div>
<?php }
if(isset($_GET['delreply']) && $_GET['delreply'] == ''){?>
	<div class="alert alert-success">刪除成功！</div> 
<?php } ?>
<ul class="breadcrumb">
	<li><a href="forum.php">論壇</a><span class="divider">/</span></li>
	<li><a href="forum.php?fid=<?php echo $_block['id']; ?>"><?php echo $_block['blockname']; ?></a><span class="divider">/</span></li>
	<li class="active"><?php echo lt_replace($post_row['post_title']); ?></li>
</ul>
<h2>
	<?php echo $post_row['post_title']; ?>
	<?php if($post_row['level']>1){ ?>
	<span class="label"><?php echo sc_member_level($post_row['level']); ?></span>
	<?php } ?>
</h2>
<div id="1" class="post">
	<ul class="inline">
		<li>
			<img src="../include/avatar.php?id=<?php echo $post_row['posted']; ?>" class="avatar">
		</li>
		<li style="font-size:120%;"><?php echo $post_row['posted']; ?></li>
		<li>發表於&nbsp;<?php echo $post_row['ptime']; ?></li>
		<li>1&nbsp;樓</li>
		<li>
			<a href="forumedit.php?post&id=<?php echo $post_row['id']; ?>" class="btn btn-info btn-small">
				編輯
			</a>
			<a href="javascript:if(confirm('確定刪除？'))location='forumview.php?del=<?php echo $post_row['id']; ?>'" class="btn btn-danger btn-small">
				刪除
			</a>	
		</li>
	</ul>
    <div class="con"><?php echo removal_escape_string($post_row['post']); ?></div>
</div>
<?php
if($reply_num_rows != 0){
	$reply_floor = 1+$limit_start;
	while ($reply_row = $reply_query->fetch_assoc()){
		$reply_floor++;
?>
<div id="<?php echo $reply_floor; ?>" class="post">
	<ul class="inline">
		<li>
			<img src="../include/avatar.php?id=<?php echo $reply_row['posted']; ?>" class="avatar">
		</li>
		<li style="font-size:130%;"><?php echo $reply_row['posted']; ?></li>
		<li>發表於&nbsp;<?php echo $reply_row['ptime']; ?></li>
		<li><?php echo $reply_floor; ?>&nbsp;樓</li>
		<li>
			<a href="forumedit.php?reply&id=<?php echo $reply_row['id']; ?>" class="btn btn-info btn-small">
				編輯
			</a>
			<a href="forumview.php?delreply=<?php echo $reply_row['id']; ?>&id=<?php echo $_GET['id']; ?>" class="btn btn-danger btn-small">
				刪除此回覆
			</a>
		</li>
	</ul>
	<div class="con"><?php echo removal_escape_string($reply_row['reply']); ?></div>
</div>
<?php
	}
}
$nav = $SQL->query("SELECT * FROM forum_reply WHERE post = '%s'",array($_GET['id']));
$pageTotal = ceil($nav->num_rows / $limit_row);

if($pageTotal>1){
	echo '<div class="pagination"><ul>';
	for($i=1;$i<=$pageTotal;$i++){
		if(@$_GET['page']!=$i){
				echo '<li><a href="forumview.php?id='.$_GET['id'].'&page='.$i.'">'.$i.'</a></li>';
			}else{
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
		}
	}
	echo '</ul></div><br class="clearfix" />';
}
?>
</div>
<?php
$view->render();
?>