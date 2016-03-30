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

require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
    header("Location: index.php?login");
    exit;
}

if(isset($_GET['del'])&& abs($_GET['del'])!=''){
	$_del[] = sprintf("DELETE FROM `forum` WHERE `id` = '%d'",abs($_GET['del']));
    $_del[] = sprintf("DELETE FROM `forum_reply` WHERE post_id = '%d'",abs($_GET['del']));
    foreach($_del as $val){
		$SQL->query($val);
	}
	$_GET['delok']=true;
}


$_mypost = sc_get_result("SELECT * FROM `forum` WHERE `author` = '%s' ORDER BY `id` DESC",array($_SESSION['Center_Id']));

$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'我的帖子');
$view->addScript("include/js/notice.js");
?>
<?php if(isset($_GET['del'])){?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<h2 class="page-header">我的帖子</h2>
<?php if($_mypost['num_rows'] == 0){ ?>
	<div class="alert alert-success">沒有帖子！趕快去<a href="forum.php?newpost">發表帖子</a>吧。</div>
<?php }else{ ?>
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>帖子</th>
			<th>區塊</th>
			<th>回覆</th>
			<th>最後回覆</th>
			<th>發表時間</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php do{
	$_reply = sc_get_result("SELECT * FROM `forum_reply` WHERE `post_id` = '%d' ORDER BY `mktime` DESC",array($_mypost['row']['id']));
	$_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_reply['row']['author']));
	$_block = $SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array($_mypost['row']['block']))->fetch_assoc();
?>
<tr>
	<td>
	<a href="forumview.php?id=<?php echo $_mypost['row']['id']; ?>"><?php echo $_mypost['row']['title']; ?></a>
	<?php if($_mypost['row']['level']>1){ ?>
	&nbsp;&nbsp;
	<span class="label label-default"><?php echo sc_member_level($_mypost['row']['level']); ?></span>
	<?php } ?>
	</td>
	<td><?php echo $_block['blockname']; ?></td>
	<td><?php echo $_reply['num_rows']; ?></td>
	<td>
	<?php if($_reply['num_rows']>0){
		echo '<div style="line-height:0.8em;font-size:92%;">'.$_author['row']['username'].'<br><span style="font-size:66%;">'.date('Y-m-d H:i',strtotime($_reply['row']['mktime'])).'</span></div>';
		}else{
			echo '無';
		}
	?>
	</td>
	<td><?php echo $_mypost['row']['mktime']; ?></td>
	<td>
		<a href="forumedit.php?post&id=<?php echo $_mypost['row']['id']; ?>" class="btn btn-info btn-sm">編輯</a>
		<a href="javascript:if(confirm('確定刪除？'))location='mypost.php?del=<?php echo $_mypost['row']['id']; ?>'" class="btn btn-danger btn-sm">刪除</a>
	</td>
</tr>
<?php }while ($_mypost['row'] = $_mypost['query']->fetch_assoc()); ?>
</tbody>
</table>
<?php } ?>
<?php
$view->render();
?>