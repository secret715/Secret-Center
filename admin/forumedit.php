<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(9,'../index.php');

if((!isset($_GET['id']))or($_GET['id']=='')){
    header("Location: forum.php");
	exit;
}

if(isset($_GET['post'])){
	if(isset($_GET['reply'])){
		header("Location: forum.php");
		exit;
	}
	
	$_post = sc_get_result("SELECT * FROM `forum` WHERE `id` = '%d'",array($_GET['id']));

	$_block = sc_get_result("SELECT * FROM `forum_block` ORDER BY `position` ASC");
	
	if($_post['num_rows']<=0){
		header("Location: forum.php");
		exit;
	}
	
	if(isset($_POST['title']) && isset($_POST['content']) && trim(htmlspecialchars($_POST['title'])) != '' && trim(strip_tags($_POST['content']),"&nbsp;") != '' && isset($_GET[$_SESSION['Center_Auth']])) {
		
		$_block_auth = $SQL->query("SELECT * FROM `forum_block` WHERE `id` = '%d'",array(abs($_POST['block'])))->num_rows;
		if($_block_auth<=0){
			die;
		}
		
		$SQL->query("UPDATE `forum` SET `title` = '%s', `content` = '%s',`block`='%d',`level`='%d' WHERE `id` = '%d'",array(
			htmlspecialchars($_POST['title']),
			sc_xss_filter($_POST['content']),
			abs($_POST['block']),
			abs($_POST['level']),
			$_GET['id']
		));
		header("Location: forumview.php?editok&id=".$_post['row']['id']);
	}
	
}elseif(isset($_GET['reply'])) {
	if(isset($_GET['post'])){
		header("Location: forum.php");
		exit;
	}
	
	$_reply = sc_get_result("SELECT * FROM `forum_reply` WHERE `id` = '%d'",array($_GET['id']));

	if($_reply['num_rows']<=0){
		header("Location: forum.php");
		exit;
	}
	
	if(isset($_POST['content']) && trim(strip_tags($_POST['content']),"&nbsp;") != '' && isset($_GET[$_SESSION['Center_Auth']])) {
		$SQL->query("UPDATE `forum_reply` SET `content` = '%s' WHERE `id` = '%d'",array(
			sc_xss_filter($_POST['content']),
			$_GET['id']
		));
		header("Location: forumview.php?editok&id=".$_reply['row']['post_id']);
	}
	
}else{
	header("Location: forum.php");
	exit;
}

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'論壇編輯',true);
$view->addCSS("../include/js/summernote/summernote.css");
$view->addScript("../include/js/summernote/summernote.min.js");
$view->addScript("../include/js/summernote/lang/summernote-zh-TW.min.js");
?>
<script>
$(function(){
	$("#summernote").summernote({width:'99%', height:300, focus: true, lang: 'zh-TW'});
});
</script>
<?php if(isset($_GET['reply'])){ ?>
<h2>編輯回覆</h2>
<form action="forumedit.php?reply&id=<?php echo $_reply['row']['id'].'&'.$_SESSION['Center_Auth']; ?>" method="POST">
	<div class="form-group">
		<label for="content">回覆內容：</label>
		<textarea id="summernote" class="form-control" name="content" cols="65" rows="10" required="required"><?php echo sc_removal_escape_string($_reply['row']['content']); ?></textarea>
	</div>
	<p><input name="button" class="btn btn-primary" type="submit" value="儲存"></p>
</form>
<?php } elseif(isset($_GET['post'])){ ?>
<h2>編輯文章</h2>
<form action="forumedit.php?post&id=<?php echo $_post['row']['id'].'&'.$_SESSION['Center_Auth']; ?>" method="POST">
	<div class="form-group">
		<input class="form-control" name="title" type="text" placeholder="標題" required="required" value="<?php echo $_post['row']['title']; ?>">
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label for="block">區塊：</label>
				<select class="form-control" name="block" required="required">
				<?php do{ ?>
					<option value="<?php echo $_block['row']['id']; ?>" <?php if($_block['row']['id']==$_post['row']['block']){ ?>selected="selected"<?php } ?>>
						<?php echo $_block['row']['blockname']; ?>
					</option>
				<?php }while ($_block['row'] = $_block['query']->fetch_assoc());  ?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label for="level">權限：</label>
				<select class="form-control" name="level" required="required">
				<?php foreach(sc_member_level_array() as $key=>$value){if($key>0){ ?>
					<option value="<?php echo $key; ?>" <?php if($key==$_post['row']['level']){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
				<?php }} ?>
				</select>
			</div>
		</div>
	</div>
	<div class="form-group">
		<textarea id="summernote" name="content" rows="10" required="required">
			<?php echo $_post['row']['content']; ?>
		</textarea>
	</div>
	<br><input name="button" class="btn btn-primary" type="submit" value="儲存">
</form>
<?php } ?>
<?php
$view->render();
?>