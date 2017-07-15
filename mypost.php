<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2017 Secret Center開發團隊 <http://center.gdsecret.net/#team>

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

if(isset($_GET['del'])&& abs($_GET['del'])!='' && isset($_GET[$_SESSION['Center_Auth']])){
	$_del[] = sprintf("DELETE FROM `forum` WHERE `id` = '%d'",abs($_GET['del']));
    $_del[] = sprintf("DELETE FROM `forum_reply` WHERE post_id = '%d'",abs($_GET['del']));
    foreach($_del as $val){
		$SQL->query($val);
	}
	$_GET['delok']=true;
}


if(!isset($_GET['sort'])){
	$_GET['sort']='01';
}
if(isset($_GET['sort'])){
	$_GET['sort']=intval($_GET['sort']);
	if(strlen($_GET['sort'])!=2){
		$_GET['sort']=str_pad($_GET['sort'],2,0,STR_PAD_LEFT);
	}
	$_table=array('mktime','title','block');
	$_a=str_split($_GET['sort'],1);
	if(!isset($_table[$_a[0]])){
		$_a[0]=0;
	}
	
	$_sort='`'.$_table[$_a[0]].'` ';
	
	if($_a[1]==1){
		$_sort.='DESC';
	}else{
		$_sort.='ASC';
	}
}

$_mypost = sc_get_result("SELECT * FROM `forum` WHERE `author` = '%d' ORDER BY %s",array($_SESSION['Center_Id'],$_sort));

$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'我的文章');
$view->addScript("include/js/notice.js");
?>
<?php if(isset($_GET['delok'])){?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<h2 class="page-header">我的文章</h2>
<?php if($_mypost['num_rows'] == 0){ ?>
	<div class="alert alert-success">沒有文章！趕快去<a href="forum.php?newpost">發表文章</a>吧。</div>
<?php }else{ ?>
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th><a href="?fid=<?php echo abs($_GET['fid']); ?>&sort=1<?php if($_a[0]==1)echo ($_a[1]+1)%2; else echo 0; ?>">標題<?php if($_a[0]==1){ ?><span class="glyphicon glyphicon-menu-<?php if($_a[1]==0){ ?>down<?php }else{ ?>up<?php } ?>"></span><?php } ?></a></th>
			<th><a href="?fid=<?php echo abs($_GET['fid']); ?>&sort=2<?php if($_a[0]==2)echo ($_a[1]+1)%2; else echo 0; ?>">區塊<?php if($_a[0]==2){ ?><span class="glyphicon glyphicon-menu-<?php if($_a[1]==0){ ?>down<?php }else{ ?>up<?php } ?>"></span><?php } ?></a></th>
			<th>回覆</th>
			<th>最後回覆</th>
			<th><a href="?fid=<?php echo abs($_GET['fid']); ?>&sort=0<?php if($_a[0]==0)echo ($_a[1]+1)%2; else echo 0; ?>">發表時間<?php if($_a[0]==0){ ?><span class="glyphicon glyphicon-menu-<?php if($_a[1]==0){ ?>down<?php }else{ ?>up<?php } ?>"></span><?php } ?></a></th>
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
		<a href="javascript:if(confirm('確定刪除？'))location='mypost.php?del=<?php echo $_mypost['row']['id'].'&'.$_SESSION['Center_Auth']; ?>'" class="btn btn-danger btn-sm">刪除</a>
	</td>
</tr>
<?php }while ($_mypost['row'] = $_mypost['query']->fetch_assoc()); ?>
</tbody>
</table>
<?php } ?>
<?php
$view->render();
?>