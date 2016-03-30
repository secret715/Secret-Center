<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2016 Secret Center開發團隊 <http://center.gdsecret.net/#team>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

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
	
	if(isset($_POST['title']) && isset($_POST['content']) && trim(htmlspecialchars($_POST['title'])) != '' && trim(strip_tags($_POST['content']),"&nbsp;") != '') {
		
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
	
	if(isset($_POST['content']) && trim(strip_tags($_POST['content']),"&nbsp;") != '') {
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
$view->addCSS("../include/js/cleditor/jquery.cleditor.css");
$view->addScript("../include/js/cleditor/jquery.cleditor.min.js");
$view->addScript("../include/js/cleditor/jquery.cleditor.table.js");
?>
<script>
$(function(){
	$("#cleditor").cleditor({width:'99%', height:300, useCSS:true})[0].focus();
});
</script>
<?php if(isset($_GET['reply'])){ ?>
<h2>編輯回覆</h2>
<form action="forumedit.php?reply&id=<?php echo $_reply['row']['id']; ?>" method="POST">
	<div class="form-group">
		<label for="content">回覆內容：</label>
		<textarea id="cleditor" class="form-control" name="content" cols="65" rows="10" required="required"><?php echo sc_removal_escape_string($_reply['row']['content']); ?></textarea>
	</div>
	<p><input name="button" class="btn btn-primary" type="submit" value="儲存"></p>
</form>
<?php } elseif(isset($_GET['post'])){ ?>
<h2>編輯帖子</h2>
<form action="forumedit.php?post&id=<?php echo $_post['row']['id']; ?>" method="POST">
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
		<textarea id="cleditor" name="content" rows="10" required="required">
			<?php echo $_post['row']['content']; ?>
		</textarea>
	</div>
	<br><input name="button" class="btn btn-primary" type="submit" value="儲存">
</form>
<?php } ?>
<?php
$view->render();
?>