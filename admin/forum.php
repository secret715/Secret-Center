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

if(isset($_POST['del']) && $_POST['del'] != ''){
    $_del[] = sprintf("DELETE FROM `forum` WHERE `id` IN (%s)",implode(",",$_POST['del']));
    $_del[] = sprintf("DELETE FROM `forum_reply` WHERE `post_id` IN (%s)",implode(",",$_POST['del']));
    foreach($_del as $val){
		$SQL->query($val);
	}
	header("Location: forum.php?del&fid=".$_GET['fid']);
}elseif(isset($_GET['delblock']) && abs($_GET['delblock']) != ''){
	$_post=sc_get_result("SELECT `id` FROM `forum` WHERE `block` = '%d'",array(abs($_GET['delblock'])));
	if($_post['num_rows']>0){
		do{
			$_list[]=$_post['row']['id'];
		}while($_post['row']=$_post['query']->fetch_assoc());
		
		$_del[] = sprintf("DELETE FROM `forum_reply` WHERE `post_id` IN (%s)",implode(",",$_list));
	}
	$_del[] = sprintf("DELETE FROM `forum_block` WHERE `id` =%d",abs($_GET['delblock']));
    $_del[] = sprintf("DELETE FROM `forum` WHERE `block` = %d",abs($_GET['delblock']));
    foreach($_del as $val){
		$SQL->query($val);
	}
	header("Location: forum.php?del");
}elseif(isset($_GET['newblock'])&&sc_namefilter($_POST['blockname'])!=''){

	sc_add_forum_block(sc_namefilter($_POST['blockname']));
	
}elseif(isset($_GET['edit']) &&abs($_GET['edit'])!='' && isset($_POST['blockname'])&&isset($_POST['position'])){
	$SQL->query("UPDATE `forum_block` SET `blockname` = '%s',`position` = '%d' WHERE `id` = '%d'",array(sc_namefilter($_POST['blockname']),abs(intval($_POST['position'])),abs($_GET['edit'])));
	$_GET['edit']=false;
}


if(isset($_GET['fid'])){
	$_block = sc_get_result("SELECT * FROM `forum_block` WHERE `id`='%d'",array(abs(intval($_GET['fid']))));
	
	if($_block['num_rows']<1){
		header("Location: forum.php");
	}
	
	$limit_row=$center['forum']['limit'];
	
	if(isset($_GET['page'])){
		$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
		$_forum = sc_get_result("SELECT * FROM `forum` WHERE `block`='%d' ORDER BY `mktime` DESC LIMIT %d,%d",array($_block['row']['id'],$limit_start,$limit_row));
	} else {
		$limit_start=0;
		$_forum = sc_get_result("SELECT * FROM `forum` WHERE `block`='%d' ORDER BY `mktime` DESC LIMIT %d,%d",array($_block['row']['id'],$limit_start,$limit_row));
	}
}else{
	$_forum = sc_get_result("SELECT * FROM `forum_block` ORDER BY `position` ASC");
}

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'論壇管理',true);
?>
<?php if(isset($_GET['del'])){ ?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<?php if(isset($_GET['fid'])){ ?>
<ul class="breadcrumb">
	<li><a href="forum.php">論壇</a></li>
	<li class="active"><a href="forum.php?fid=<?php echo $_block['row']['id']; ?>"><?php echo $_block['row']['blockname']; ?></a></li>
</ul>
<h2 class="page-header"><?php echo $_block['row']['blockname']; ?></h2>
<?php if($_forum['num_rows'] == 0){ ?>
<div class="alert alert-danger">沒有帖子！</div>
<?php }else{ ?>
<script>
$(function(){
	$('input.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除所選取的帖子？\n\n提醒您，該帖子的回覆也會一並刪除！")){
			e.preventDefault();
		}
	});
});
</script>
<form action="forum.php?fid=<?php echo abs($_GET['fid']); ?>" method="POST">
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th width="30"><input class="btn btn-danger btn-sm" type="submit" value="刪除"></th>
			<th>帖子</th>
			<th>作者/發表時間</th>
			<th>回覆</th>
			<th>最後回覆</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php do{
			$_reply = sc_get_result("SELECT * FROM `forum_reply` WHERE `post_id`='%d' ORDER BY `mktime` DESC",array($_forum['row']['id']));
			$_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_forum['row']['author']));
			$_reply_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_reply['row']['author']));
		?>
		<tr>
			<td><input name="del[]" type="checkbox" value="<?php echo $_forum['row']['id']; ?>" /></td>
			<td>
				<a href="forumview.php?id=<?php echo $_forum['row']['id']; ?>">
					<?php echo $_forum['row']['title']; ?>
				</a>
				<?php if($_forum['row']['level']>1){ ?>
				&nbsp;&nbsp;
				<span class="label label-default"><?php echo sc_member_level($_forum['row']['level']); ?></span>
				<?php } ?>
			</td>
			<td style="line-height:0.8em;font-size:92%;">
				<?php echo $_author['row']['username']; ?>
				<br><span style="font-size:66%;"><?php echo date('Y-m-d H:i',strtotime($_forum['row']['mktime'])); ?></span>
			</td>
			<td>
				<?php echo $_reply['num_rows']; ?>
			</td>
			<td>
				<?php
					if($_reply['num_rows']>0){
						echo '<div style="line-height:0.8em;font-size:92%;">'.$_reply_author['row']['username'].'<br><span style="font-size:66%;">'.date('Y-m-d H:i',strtotime($_reply['row']['mktime'])).'</span></div>';
					}else{
						echo '無';
					}
				?>
				
			</td>
		</tr>
		<?php }while ($_forum['row'] = $_forum['query']->fetch_assoc()); ?>
	</tbody>
</table>
</form>
<?php
	$_all_forum=sc_get_result("SELECT COUNT(*) FROM `forum` WHERE `block`='%d'",array($_block['row']['id']));
	echo sc_page_pagination('forum.php',@$_GET['page'],implode('',$_all_forum['row']),$center['forum']['limit'],'&fid='.$_block['row']['id']);
}
?>
<?php
}elseif(isset($_GET['edit'])&& abs($_GET['edit']) != ''){
	$_block=sc_get_result("SELECT * FROM `forum_block` WHERE `id`='%d'",array(abs(intval($_GET['edit']))));
	if($_block['num_rows']<1){
		die;
	}
?>
<h2 class="page-header">區塊編輯</h2>
<form class="form-horizontal form-sm" action="forum.php?edit=<?php echo $_block['row']['id']; ?>" method="POST">
	<div class="form-group">
			<label class="col-sm-3 control-label" for="blockname">區塊名稱：</label>
			<div class="col-sm-7">
				<input class="form-control" name="blockname" type="text" value="<?php echo $_block['row']['blockname']; ?>" required="required">
			</div>
		</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="position">區塊位置：</label>
		<div class="col-sm-3">
			<input class="form-control" name="position" type="text" value="<?php echo $_block['row']['position']; ?>">
		</div>
		<div class="col-sm-6 help-block">
			越低排序越上面，預設為 0
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-6">
			<input type="submit" class="btn btn-success" value="確認修改" />
			<a class="btn btn-default" href="forum.php">取消</a>
		</div>
	</div>
</form>
<?php }else{ ?>
<h2 class="page-header">論壇</h2>
<form class="form-inline" method="POST" action="forum.php?newblock">
	<div class="input-group">
		<input class="form-control" name="blockname" type="text" placeholder="區塊名稱" required="required">
		<span class="input-group-btn">
			<input type="submit" class="btn btn-success" value="新增區塊">
		</span>
	</div>
</form>
<?php if($_forum['num_rows'] == 0){ ?>
<div class="alert alert-danger">沒有區塊！</div>
<?php }else{ ?>
<script>
$(function(){
	$('a.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除所選取的區域？\n\n提醒您，該區塊的帖子也會一並刪除！")){
			e.preventDefault();
		}
	});
});
</script>
<table class="table table-striped">
	<thead>
		<tr>
			<th>區塊</th>
			<th>位置</th>
			<th>帖數</th>
			<th>最後發帖</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php do{
			$_block_post = sc_get_result("SELECT * FROM `forum` WHERE `block`='%d' ORDER BY `mktime` DESC",array($_forum['row']['id']));
		?>
		<tr>
			<td>
				<a href="forum.php?fid=<?php echo $_forum['row']['id']; ?>">
				<?php echo $_forum['row']['blockname']; ?>
				</a>
			</td>
			<td><?php echo $_forum['row']['position']; ?></td>
			<td><?php echo $_block_post['num_rows']; ?></td>
			<td>
				<?php
				if($_block_post['num_rows']>0){
					echo date('Y-m-d H:i',strtotime($_block_post['row']['mktime']));
				}else{
					echo '無';
				}?>
			</td>
			<td>
				<a class="btn btn-info" href="forum.php?edit=<?php echo $_forum['row']['id']; ?>">編輯</a>
				<?php if($_forum['num_rows']>1){ ?>
				<a class="btn btn-danger" href="forum.php?delblock=<?php echo $_forum['row']['id']; ?>">刪除</a>
				<?php } ?>
			</td>
		</tr>
		<?php }while($_forum['row'] = $_forum['query']->fetch_assoc()); ?>
	</tbody>
</table>
<?php } ?>
<?php } ?>
<?php
$view->render();
?>