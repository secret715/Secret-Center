<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

if(!isset($_GET['q'])or trim($_GET['q'])==''){
	header("Location: forum.php");
    exit;
}

$view = new View('include/theme/default.html',$center['site_name'],'論壇搜尋','include/nav.php');
$view->addScript("include/js/notice.js");
?>
<h2>論壇搜尋</h2>
<form id="search" method="GET" action="forumsearch.php" style="margin-bottom:1em;">
	<div class="input-group">
		<input id="q" class="form-control" name="q" type="text" class="search-query" required="required" value="<?php echo sc_xss_filter($_GET['q']); ?>">
		<span class="input-group-btn">
			<span class="btn btn-default" onclick="if(document.getElementById('q').value!=''){document.getElementById('search').submit();}"><i class="glyphicon glyphicon-search"></i></span>
		</span>
	</div>
</form>
<?php if($_post['num_rows']<=0){ ?>
<div class="alert alert-danger">沒有符合的資料！</div>
<?php }else{ ?>
<?php foreach($_post['row'] as $_post['row'][0]){
	$_reply = sc_get_result("SELECT COUNT(*) FROM `forum_reply` WHERE `post_id`='%d'",array($_post['row'][0]['id']));
	$_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_post['row'][0]['author']));
?>
<div class="post">
	<a href="forumview.php?id=<?php echo $_post['row']['id']; ?>" style="font-size:120%;display:block;">
	<?php echo $_post['row'][0]['title']; ?>
	</a>
	<p>
	<?php echo mb_substr(strip_tags($_post['row'][0]['content']),mb_stripos(strip_tags($_post['row'][0]['content']),sc_xss_filter($_GET['q']),0,'UTF-8')-30,60,'UTF-8'); ?>...
	</p>
	<ul class="list-inline" style="font-size:80%;color:rgb(100,100,100);">
		<li><?php echo $_author['row']['username']; ?></li>
		<li><?php echo date('Y-m-d H:i',strtotime($_post['row'][0]['mktime'])); ?></li>
		<li><?php echo implode('',$_reply['row']); ?> 回覆</li>
		<?php if($_post['row']['level']>1){ ?>
		<li><span class="label label-default"><?php echo sc_member_level($_post['row'][0]['level']); ?></span></li>
		<?php } ?>
	</ul>
</div>
<?php } ?>
<?php
$view->render();
?>