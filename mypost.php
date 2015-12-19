<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
    header("Location: index.php?login");
    exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'我的帖子');
$view->addCSS("include/js/cleditor/jquery.cleditor.css");
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");

if(isset($_GET['del'])&& abs($_GET['del'])!=''){
	$del[] = sprintf("DELETE FROM forum WHERE id = '%d'",abs($_GET['del']));
    $del[] = sprintf("DELETE FROM forum_reply WHERE post = '%d'",abs($_GET['del']));
    foreach($del as $val){
		$SQL->query($val);
	}
	$_GET['delok']=true;
}



$mypost['query'] = $SQL->query("SELECT * FROM forum WHERE posted = '%s' ORDER BY `id` DESC",array($_SESSION['Center_Username']));
$mypost['row'] = $mypost['query']->fetch_assoc();
$mypost['num_rows'] = $mypost['query']->num_rows;
?>
<div class="main">
<?php if(isset($_GET['del'])){?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<h2>我的帖子</h2>
<?php if($mypost['num_rows'] == 0){ ?>
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
	$mypost_reply['query'] = $SQL->query("SELECT * FROM forum_reply WHERE post = '%d' ORDER BY `ptime` DESC",array($mypost['row']['id']));
	$mypost_reply['row'] = $mypost_reply['query']->fetch_assoc();
	$mypost_reply['num_rows'] = $mypost_reply['query']->num_rows;
	
	
	$_block = $SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array($mypost['row']['block']))->fetch_assoc();
?>
<tr>
	<td>
	<a href="forumview.php?id=<?php echo $mypost['row']['id']; ?>"><?php echo $mypost['row']['post_title']; ?></a>
	<?php if($mypost['row']['level']>1){ ?>
	&nbsp;&nbsp;
	<span class="label"><?php echo sc_member_level($mypost['row']['level']); ?></span>
	<?php } ?>
	</td>
	<td><?php echo $_block['blockname']; ?></td>
	<td><?php echo $mypost_reply['num_rows']; ?></td>
	<td>
	<?php if($mypost_reply['num_rows']>0){
		echo '<div style="line-height:0.8em;font-size:92%;">'.$mypost_reply['row']['posted'].'<br><span style="font-size:66%;">'.date('Y-m-d H:i',strtotime($mypost_reply['row']['ptime'])).'</span></div>';
		}else{
			echo '無';
		}
	?>
	</td>
	<td><?php echo $mypost['row']['ptime']; ?></td>
	<td>
		<a href="forumedit.php?post&id=<?php echo $mypost['row']['id']; ?>" class="btn btn-info btn-small">編輯</a>
		<a href="javascript:if(confirm('確定刪除？'))location='mypost.php?del=<?php echo $mypost['row']['id']; ?>'" class="btn btn-danger btn-small">刪除</a>
	</td>
</tr>
<?php }while ($mypost['row'] = $mypost['query']->fetch_assoc()); ?>
</tbody>
</table>
<?php } ?>
</div>
<?php
$view->render();
?>