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

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'論壇搜尋',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");

if(isset($_GET['q'])&&trim($_GET['q'])!=''&&isset($_GET['level'])&&isset($_GET['ptime'])&&isset($_GET['posted'])&&isset($_GET['block'])){
	if(is_numeric($_GET['level'])){
		$_level= sprintf("AND `level` = '%d'",abs($_GET['level']));
 	}else{
		$_level='';
	}
	if(is_numeric($_GET['block'])){
		$_block= sprintf("AND `block` = '%d'",abs($_GET['block']));
 	}else{
		$_block='';
	}
	$GET_ptime['0']=strtotime($_GET['ptime']['0']);
	$GET_ptime['1']=strtotime($_GET['ptime']['1']);
	if($GET_ptime['0']>0&&$GET_ptime['1']>0){
		$_ptime=sprintf(" AND `ptime` BETWEEN '%s' AND '%s'",
					date('Y-m-d H:i:s',$GET_ptime['0']),
					date('Y-m-d H:i:s',$GET_ptime['1']));
	}elseif($GET_ptime['0']>0){
		$_ptime=sprintf(" AND `ptime` > '%s'",
					date('Y-m-d H:i:s',$GET_ptime['0']));
	}elseif($GET_ptime['1']>0){
		$_ptime=sprintf(" AND `ptime` < '%s'",
					date('Y-m-d H:i:s',$GET_ptime['1']));
	}
	else{
		$_ptime='';
	}
	$limit_row=$center['forum']['limit'];
	if(isset($_GET['page'])){
		$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
		$post=$SQL->query("SELECT * FROM `forum` WHERE `post_title` LIKE '%%%s%%' OR `post` LIKE '%%%s%%' OR `posted` LIKE '%%%s%%' $_block $_level $_ptime ORDER BY `ptime` DESC LIMIT %d,%d",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$_GET['posted'],$limit_start,$limit_row));
	} else{
		$limit_start=0;
		$post=$SQL->query("SELECT * FROM `forum` WHERE `post_title` LIKE '%%%s%%' OR `post` LIKE '%%%s%%' OR `posted` LIKE '%%%s%%' $_block $_level $_ptime ORDER BY `ptime` DESC LIMIT %d,%d",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$_GET['posted'],$limit_start,$limit_row));
	}
	$post_row = $post->fetch_assoc();
	$post_num_rows = $post->num_rows;
}else{
	$_block_query=$SQL->query("SELECT * FROM `forum_block` ORDER BY `position` ASC");
	$_block =$_block_query->fetch_assoc();
}
?>
<div class="main">
<h2>論壇搜尋</h2>
<?php if(!isset($_GET['q'])or trim($_GET['q'])==''or!isset($_GET['level'])or!isset($_GET['ptime'])or!isset($_GET['posted'])or!isset($_GET['block'])){ ?>
<form id="search" class="form-horizontal" action="forumsearch.php" method="GET">	
	<div class="control-group">
		<label class="control-label" for="q">標題：</label>
		<div class="controls">
			<input name="q" type="text" class="input-xlarge" id="q">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="posted">發帖人：</label>
		<div class="controls">
			<input name="posted" type="text" class="input-xlarge" id="posted">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="block">區塊：</label>
		<div class="controls">
			<select class="input-xlarge" name="block" required="required">
				<option value="all" selected="selected">所有</option>
			<?php do{ ?>
				<option value="<?php echo $_block['id']; ?>">
					<?php echo $_block['blockname']; ?>
				</option>
			<?php }while ($_block =  $_block_query->fetch_assoc()); ?>
			</select>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="ptime">發表日期：</label>
		<div class="controls">
			<input name="ptime[]" type="date" class="input-small" /> - 
			<input name="ptime[]" type="date" class="input-small" />(YYYY-MM-DD)
		</div>
	</div>
	<div class="control-group">
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
		<input type="submit" id="button" class="btn btn-success" value="搜尋" />
	</div>
</form>
<?php }else{if($post_num_rows<=0){ ?>
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
$nav_num_rows = $SQL->query("SELECT * FROM `forum` WHERE `post_title` LIKE '%%%s%%' OR `post` LIKE '%%%s%%' OR `posted` LIKE '%%%s%%' $_block $_level $_ptime ",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$_GET['posted']))->num_rows;

$pageTotal=ceil($nav_num_rows / $limit_row);

if($pageTotal > 1){
	echo '<div class="pagination"><ul>';
	for($i=1;$i<=$pageTotal;$i++){
		if(@$_GET['page'] != $i){
				echo '<li><a href="forumsearch.php?q='.sc_xss_filter($_GET['q']).'&posted='.urlencode(sc_namefilter($_GET['posted'])).'&block='.urlencode(abs($_GET['block'])).'&level='.urlencode(abs($_GET['level'])).'&ptime[]='.$GET_ptime['0'].'&ptime[]='.$GET_ptime['1'].'&page='.$i.'">'.$i.'</a></li>';
			}else{
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
		}
	}
	echo '</ul></div><br class="clearfix" />';
}}}
?>
</div>
<?php
$view->render();
?>