<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
    header("Location: index.php?login");
    exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'論壇');
$view->addCSS("include/js/cleditor/jquery.cleditor.css");
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/cleditor/jquery.cleditor.js");
$view->addScript("include/js/cleditor/jquery.cleditor.icon.js");
$view->addScript("include/js/cleditor/jquery.cleditor.table.js");
$view->addScript("include/js/cleditor/jquery.cleditor.serverImg.js");
$view->addScript("include/js/jquery.validate.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");

if(isset($_GET['newpost'])){
	if($_SESSION['Center_UserGroup']==0){
		header("Location: forum.php?banned");
		exit;
	}
	if(isset($_GET['block'])){
		$_GET['block']=abs(intval($_GET['block']));
	}else{
		$_GET['block']=0;
	}
}
if(isset($_POST['title']) && isset($_POST['post']) && trim(htmlspecialchars($_POST['title'])) != '' && trim($_POST['post'],"&nbsp;") != '') {;
	if($center['forum']['captcha']==1){
		if(strtoupper($_POST['captcha']) != strtoupper($_SESSION['captcha'])){
			setcookie('post',$_POST['post'],time()+300);
			header("Location: forum.php?newpost&captcha");
			exit;
		}
	}
		
	if($SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array(abs(intval($_POST['block']))))->num_rows>0){
		sc_add_forum_post($_POST['title'],$_POST['post'],$_POST['block'],$_SESSION['Center_Username'],$_POST['level']);
		header("Location: forum.php?posting&fid=".$_POST['block']);
	}
}
if(isset($_GET['fid'])){
	if($SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array(abs(intval($_GET['fid']))))->num_rows<1){
		header("Location: forum.php");
	}
	$limit_row=$center['forum']['limit'];
	if(isset($_GET['page'])){
		$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
		$post_sql = sprintf("SELECT * FROM `forum` WHERE `block`='%d' ORDER BY `ptime` DESC LIMIT %d,%d",abs(intval($_GET['fid'])),$limit_start,$limit_row);
	} else {
		$limit_start=0;
		$post_sql = sprintf("SELECT * FROM `forum` WHERE `block`='%d' ORDER BY `ptime` DESC LIMIT %d,%d",abs(intval($_GET['fid'])),$limit_start,$limit_row);
	}
	$_block = $SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array(abs(intval($_GET['fid']))))->fetch_assoc();
}else{
	$post_sql = sprintf("SELECT * FROM `forum_block` ORDER BY `position` ASC");
}

$post = $SQL->query($post_sql);
$post_row = $post->fetch_assoc();
$post_num_rows = $post->num_rows;
?>
<div class="main">
<?php if(isset($_GET['posting'])){?>
	<div class="alert alert-success">發佈成功！</div>
<?php }elseif(isset($_GET['captcha'])){ ?>
	<div class="alert alert-error">請檢查驗證碼！</div>
	
<?php }elseif(isset($_GET['banned'])){ ?>
	<div class="alert alert-error">您被禁言無法發帖！</div>
<?php }elseif(isset($_GET['gbanned'])){ ?>
	<div class="alert alert-error">權限不足！</div>
<?php }
if(isset($_GET['newpost'])) {
?>
<script type="text/javascript">
$(function(){
    $("#cleditor").cleditor({width:'99%', height:350, useCSS:true})[0].focus();
	$("#form1").validate({
		rules:{
			title:{required:true},
			post:{required:true}
		}
	});
	$('.captcha').on('click', function(e){
		e.preventDefault();
		$(this).attr('src', 'include/captcha.php?_=' + (new Date).getTime());
	});
});
</script>
<h2>發表帖子</h2>
<form action="forum.php?newpost" method="POST" name="form1">
	<input name="title" class="input-block-level" type="text" placeholder="標題">
	<div class="controls controls-row">
		<div class="span6">
			<label class="control-label" for="block">區塊：</label>
			<select class="input-xlarge" name="block" required="required">
			<?php do{ ?>
				<option value="<?php echo $post_row['id']; ?>" <?php if($post_row['id']==$_GET['block']){ ?>selected="selected"<?php } ?>>
					<?php echo $post_row['blockname']; ?>
				</option>
			<?php }while ($post_row = $post->fetch_assoc());  ?>
			</select>
		</div>
		<div class="span6">
			<label class="control-label" for="level">權限：</label>
			<select name="level" id="level" class="input-xlarge">
			<?php foreach(sc_member_level_array() as $key=>$value){if($key>0){ ?>
				<option value="<?php echo $key; ?>" <?php if($key==1){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
			<?php }} ?>
			</select>
		</div>
	</div>
	<textarea id="cleditor" name="post" class="input-block-level" rows="10"><?php if(isset($_COOKIE['post'])){echo $_COOKIE['post'];} ?></textarea>
	<?php if($center['forum']['captcha']==1){ ?>
	<div class="control">
		<label class="control-label" for="captcha">驗證碼：</label>
			<img src="include/captcha.php" class="captcha" title="按圖更換驗證碼"/>
			<input name="captcha" type="text" id="captcha" size="10" maxlength="10" required="required">
	</div>
	<?php } ?>
	<br><input id="button" name="button" class="btn btn-primary" type="submit" value="送出發表！">
</form>
<?php } elseif(isset($_GET['fid'])){ ?>
<ul class="breadcrumb">
	<li><a href="forum.php">論壇</a><span class="divider">/</span></li>
	<li class="active"><a href="forum.php?fid=<?php echo $_block['id']; ?>"><?php echo $_block['blockname']; ?></a></li>
</ul>
<div class="row-fluid">
	<div class="span8">
		<h2>
			<?php echo $_block['blockname']; ?>
			<a href="forum.php?block=<?php echo $_block['id']; ?>&newpost" class="btn btn-primary btn-mini">發表帖子</a>
		</h2>
	</div>
	<div class="span4 text-right" style="margin:15px 0 0 -15px;">
		<form id="search" class="form-search" method="GET" action="forumsearch.php">
			<div class="input-append">
				<input id="q" name="q" type="text" class="input-block-level search-query" required="required">
				<span class="btn" onclick="if(document.getElementById('q').value!=''){document.getElementById('search').submit();}"><i class="icon-search"></i></span>
				<!--input type="submit" class="btn" value="搜尋"-->
			</div>
		</form>
	</div>
</div>
<?php
if($post_num_rows == 0){
?>
<div class="alert alert-error">沒有帖子！</div>
<?php
}else{
?>
		<table class="table table-striped table-hover">
			<thead>
				<tr>
					<th>帖子</th>
					<th>作者/發表時間</th>
					<th>回覆</th>
					<th>最後回覆</th>
				</tr>
			</thead>
			<tbody>
				<?php do{
					$_post_reply_query = $SQL->query("SELECT * FROM `forum_reply` WHERE `post`='%d' ORDER BY `ptime` DESC",array($post_row['id']));
					$_post_reply_row=$_post_reply_query->fetch_assoc();
					$_post_reply_num_rows = $_post_reply_query->num_rows;
				?>
				<tr>
					<td>
						<a href="forumview.php?id=<?php echo $post_row['id']; ?>">
						<?php echo $post_row['post_title']; ?>
						</a>
						<?php if($post_row['level']>1){ ?>
						&nbsp;&nbsp;
						<span class="label"><?php echo sc_member_level($post_row['level']); ?></span>
						<?php } ?>
					</td>
					<td style="line-height:0.8em;font-size:92%;">
						<?php echo $post_row['posted']; ?>
						<br><span style="font-size:66%;"><?php echo date('Y-m-d H:i',strtotime($post_row['ptime'])); ?></span>
					</td>
					<td>
						<?php echo $_post_reply_num_rows; ?>
					</td>
					<td>
						<?php
							if($_post_reply_num_rows>0){
								
								echo '<div style="line-height:0.8em;font-size:92%;">'.$_post_reply_row['posted'].'<br><span style="font-size:66%;">'.date('Y-m-d H:i',strtotime($_post_reply_row['ptime'])).'</span></div>';
							}else{
								echo '無';
							}
						?>
						
					</td>
				</tr>
				<?php }while ($post_row = $post->fetch_assoc()); ?>
			</tbody>
		</table>
<?php
$nav_num_rows = $SQL->query("SELECT * FROM forum WHERE block = '%d'",array($_block['id']))->num_rows;

$pageTotal=ceil($nav_num_rows / $limit_row);

if($pageTotal > 1){
	echo '<div class="pagination"><ul>';
	for($i=1;$i<=$pageTotal;$i++){
		if(@$_GET['page'] != $i){
				echo '<li><a href="forum.php?page='.$i.'&fid='.$_block['id'].'">'.$i.'</a></li>';
			}else{
				echo '<li class="active"><a href="#">'.$i.'</a></li>';
		}
	}
	echo '</ul></div><br class="clearfix" />';
}}
}else{ ?>
<h2>論壇</h2>
<?php
if($post_num_rows == 0){
?>
<div class="alert alert-error">沒有區塊！</div>
<?php }else{ ?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>區塊</th>
				<th>帖數</th>
				<th>最後發帖</th>
			</tr>
		</thead>
		<tbody>
			<?php do{
				$_block_post_query = $SQL->query("SELECT * FROM `forum` WHERE `block`='%d' ORDER BY `ptime` DESC",array($post_row['id']));
				$_block_post_row=$_block_post_query->fetch_assoc();
				$_block_post_num_rows = $_block_post_query->num_rows;
			?>
			<tr>
				<td>
					<a href="forum.php?fid=<?php echo $post_row['id']; ?>">
					<?php echo $post_row['blockname']; ?>
					</a>
				</td>
				<td><?php echo $_block_post_num_rows; ?></td>
				<td>
					<?php
					if($_block_post_num_rows>0){
						echo date('Y-m-d H:i',strtotime($_block_post_row['ptime']));
					}else{
						echo '無';
					}?>
				</td>
			</tr>
			<?php }while($post_row = $post->fetch_assoc()); ?>
		</tbody>
    </table>
<?php } ?>
<?php } ?>
</div>
<?php
$view->render();
?>