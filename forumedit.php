<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
    header("Location: index.php?login");
	exit;
}

if(!isset($_GET['id']) || $_GET['id'] == ''){
	header("Location: forum.php");
	exit;
}

if(isset($_GET['post'])){
	if(isset($_GET['reply'])){
		header("Location: forum.php");
		exit;
	}
	
	$post = $SQL->query("SELECT * FROM forum WHERE id = '%d' AND posted = '%s'",array($_GET['id'],$_SESSION['Center_Username']));
	$post_row = $post->fetch_assoc();
	$post_num_rows = $post->num_rows;

	$_block_query=$SQL->query("SELECT * FROM `forum_block` ORDER BY `position` ASC");
	$_block =$_block_query->fetch_assoc();
	
	if($post_num_rows<=0){
		header("Location: forum.php");
		exit;
	}
	
	if(isset($_POST['title']) && isset($_POST['post']) && trim(htmlspecialchars($_POST['title'])) != '' && trim(strip_tags($_POST['post']),"&nbsp;") != '') {
		
		$_block_auth = $SQL->query("SELECT * FROM `forum_block` WHERE id = '%d'",array(abs($_POST['block'])))->num_rows;
		if($_block_auth<0){
			die;
		}
		
		$SQL->query("UPDATE forum SET `post_title` = '%s', `post` = '%s',`block`='%d',`level`='%d' WHERE `id` = '%d' AND `posted` = '%s'",array(
			htmlspecialchars($_POST['title']),
			sc_xss_filter($_POST['post']),
			abs($_POST['block']),
			abs($_POST['level']),
			$_GET['id'],
			$_SESSION['Center_Username']
		));
		header("Location: forumview.php?editok&id=".$post_row['id']);
	}
	
}elseif(isset($_GET['reply'])) {
	if(isset($_GET['post'])){
		header("Location: forum.php");
		exit;
	}
	
	$reply = $SQL->query("SELECT * FROM `forum_reply` WHERE `id` = '%d' AND `posted`='%s'",array($_GET['id'],$_SESSION['Center_Username']));
	$reply_row = $reply->fetch_assoc();
	$reply_num_rows = $reply->num_rows;

	if($reply_num_rows<=0){
		header("Location: forum.php");
		exit;
	}
	
	if(isset($_POST['reply']) && trim(strip_tags($_POST['reply']),"&nbsp;") != '') {
	$SQL->query("UPDATE `forum_reply` SET `reply` = '%s' WHERE `id` = '%d' AND `posted` = '%s'",array(
		sc_xss_filter($_POST['reply']),
		$_GET['id'],
		$_SESSION['Center_Username']
	));
	header("Location: forumview.php?id=".$reply_row['post']);
	}
	
}else{
	header("Location: forum.php");
	exit;
}


$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'論壇編輯');
$view->addCSS("include/js/cleditor/jquery.cleditor.css");
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/cleditor/jquery.cleditor.js");
$view->addScript("include/js/cleditor/jquery.cleditor.icon.js");
$view->addScript("include/js/cleditor/jquery.cleditor.table.js");
$view->addScript("include/js/cleditor/jquery.cleditor.serverImg.js");
$view->addScript("include/js/jquery.validate.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");
?>
<div class="main">
<?php if(isset($_GET['reply'])){ ?>
<script type="text/javascript">
$(function(){
	$("#reply").cleditor({width:'99%', height:300, useCSS:true})[0].focus();
	$("#form1").validate({
		rules:{
			reply:{required:true}
		}
	});
});
</script>
<h2>編輯回覆</h2>
<form action="forumedit.php?reply&id=<?php echo $_GET['id']; ?>" method="POST" name="form1">
	<div class="controls">
		<label for="reply">回覆內容：</label>
	</div>
	<div style="margin:auto;">
		<textarea name="reply" cols="65" rows="10" id="reply" required="required"><?php echo removal_escape_string($reply_row['reply']); ?></textarea>
	</div>
	<p><input type="submit" name="button" class="btn btn-primary" value="編輯回覆" /></p>
</form>
<?php } elseif(isset($_GET['post'])){ ?>
<script type="text/javascript">
$(function(){
    $("#cleditor").cleditor({width:'99%', height:350, useCSS:true})[0].focus();
	$("#form1").validate({
		rules:{
			title:{required:true},
			post:{required:true}
		}
	});
});
</script>
<h2>編輯帖子</h2>
<form action="forumedit.php?post&id=<?php echo $_GET['id']; ?>" method="POST" name="form1">
	<input name="title" class="input-block-level" type="text" value="<?php echo $post_row['post_title']; ?>" required="required" placeholder="標題">
	<div class="controls controls-row">
		<div class="span6">
			<label class="control-label" for="block">區塊：</label>
			<select class="input-xlarge" name="block" required="required">
			<?php do{ ?>
				<option value="<?php echo $_block['id']; ?>" <?php if($_block['id']==$post_row['block']){ ?>selected="selected"<?php } ?>>
					<?php echo $_block['blockname']; ?>
				</option>
			<?php }while ($_block =  $_block_query->fetch_assoc()); ?>
			</select>
		</div>
		<div class="span6">
			<label for="level">權限：</label>
			<select name="level" id="level" class="input-xlarge">
			<?php foreach(sc_member_level_array() as $key=>$value){if($key>0){ ?>
				<option value="<?php echo $key; ?>" <?php if($key==$post_row['level']){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
			<?php }} ?>
			</select>
		</div>
	</div>
	<textarea id="cleditor" name="post" class="input-block-level" rows="10"><?php echo htmlspecialchars(removal_escape_string($post_row['post'])); ?></textarea>
	</div>
	<p><input type="submit" name="button" class="btn btn-primary" value="編輯帖子" /></p>
</form>
<?php } ?>
</div>
<?php
$view->render();
?>