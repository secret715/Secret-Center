<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
    header("Location: index.php?login");
    exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'論壇搜尋');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");

if(!isset($_GET['q'])or trim($_GET['q'])==''){
	header("Location: forum.php");
    exit;
}

$limit_row=$center['forum']['limit'];
if(isset($_GET['page'])){
	$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
	$post=$SQL->query("SELECT * FROM `forum` WHERE `post_title` LIKE '%%%s%%' OR `post` LIKE '%%%s%%' ORDER BY `ptime` DESC LIMIT %d,%d",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$limit_start,$limit_row));
} else {
	$limit_start=0;
	$post=$SQL->query("SELECT * FROM `forum` WHERE `post_title` LIKE '%%%s%%' OR `post` LIKE '%%%s%%' ORDER BY `ptime` DESC LIMIT %d,%d",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$limit_start,$limit_row));
}
$post_row = $post->fetch_assoc();
$post_num_rows = $post->num_rows;
?>
<div class="main">
<h2>論壇搜尋</h2>
<form id="search" method="GET" action="forumsearch.php">
	<div class="input-append">
		<input name="q" type="text" class="input-xlarge" value="<?php echo sc_xss_filter($_GET['q']); ?>" required="required">
		<input type="submit" class="btn btn-success" value="搜尋">
	</div>
</form>
<?php if($post_num_rows<=0){ ?>
<div class="alert alert-error">沒有符合的資料！</div>
<?php }else{ ?>
<?php do{
	$_post_reply_query = $SQL->query("SELECT * FROM `forum_reply` WHERE `post`='%d' ORDER BY `ptime` DESC",array($post_row['id']));
	$_post_reply_row=$_post_reply_query->fetch_assoc();
	$_post_reply_num_rows = $_post_reply_query->num_rows;
?>
<div id="1" class="post">
	<a href="forumview.php?id=<?php echo $post_row['id']; ?>" style="font-size:120%;display:block;">
	<?php echo $post_row['post_title']; ?>
	</a>
	<p>
	<?php echo mb_substr(strip_tags($post_row['post']),mb_stripos(strip_tags($post_row['post']),sc_xss_filter($_GET['q']),0,'UTF-8')-30,60,'UTF-8'); ?>...
	</p>
	<ul class="inline" style="font-size:80%;color:rgb(100,100,100);">
		<?php if($post_row['level']>1){ ?>
		<li><span class="label"><?php echo sc_member_level($post_row['level']); ?></span></li>
		<?php } ?>
		<li><?php echo $post_row['posted']; ?></li>
		<li><?php echo date('Y-m-d H:i',strtotime($post_row['ptime'])); ?></li>
		<li><?php echo $_post_reply_num_rows; ?> 回覆</li>
	</ul>
</div>
<?php }while ($post_row = $post->fetch_assoc()); ?>
<?php
$nav_num_rows = $SQL->query("SELECT * FROM `forum` WHERE `post_title` LIKE '%%%s%%' OR `post` LIKE '%%%s%%' ORDER BY `ptime`",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q'])))->num_rows;

$pageTotal=ceil($nav_num_rows / $limit_row);

if($pageTotal > 1){
	echo '<div class="pagination"><ul>';
	for($i=1;$i<=$pageTotal;$i++){
		if(@$_GET['page'] != $i){
				echo '<li><a href="forumsearch.php?q='.sc_xss_filter($_GET['q']).'&page='.$i.'">'.$i.'</a></li>';
			}else{
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
		}
	}
	echo '</ul></div><br class="clearfix" />';
}}
?>
</div>
<?php
$view->render();
?>