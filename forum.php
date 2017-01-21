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

if(isset($_POST['title']) && isset($_POST['content']) && isset($_POST['block']) && isset($_POST['level']) && trim(htmlspecialchars($_POST['title'])) != '' && trim($_POST['content'],"&nbsp;") != '') {
	$_POST['block']=abs(intval($_POST['block']));
	if($center['forum']['captcha']==1){
		if(strtoupper($_POST['captcha']) != strtoupper($_SESSION['captcha'])){
			setcookie('content',$_POST['content'],time()+600);
			header("Location: forum.php?newpost&captcha");
			exit;
		}
	}
	
	if($SQL->query("SELECT * FROM `forum_block` WHERE `id`='%d'",array($_POST['block']))->num_rows>0){
		sc_add_forum_post($_POST['title'],$_POST['content'],$_POST['block'],$_SESSION['Center_Id'],$_POST['level']);
		header("Location: forum.php?success&fid=".$_POST['block']);
	}
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
$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'論壇');
$view->addScript("include/js/notice.js");
?>
<?php if(isset($_GET['success'])){?>
	<div class="alert alert-success">發佈成功！</div>
<?php }elseif(isset($_GET['captcha'])){ ?>
	<div class="alert alert-danger">請檢查驗證碼！</div>
<?php }elseif(isset($_GET['banned'])){ ?>
	<div class="alert alert-danger">您被禁言無法發文！</div>
<?php }elseif(isset($_GET['level'])){ ?>
	<div class="alert alert-danger">權限不足！</div>
<?php }
if(isset($_GET['newpost'])) {
$view->addCSS("include/js/summernote/summernote.css");
$view->addScript("include/js/summernote/summernote.min.js");
$view->addScript("include/js/summernote/lang/summernote-zh-TW.min.js");
?>
<script>
$(function(){
	$("#summernote").summernote({width:'99%', height:300, focus: true, lang: 'zh-TW'});
	$('.captcha').on('click', function(e){
		e.preventDefault();
		$(this).attr('src', 'include/captcha.php?_=' + (new Date).getTime());
	});
});
</script>
<h2 class="page-header">發表文章</h2>
<form action="forum.php?newpost" method="POST">
	<div class="form-group">
		<input class="form-control" name="title" type="text" placeholder="標題" required="required">
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label for="block">區塊：</label>
				<select class="form-control" name="block" required="required">
				<?php do{ ?>
					<option value="<?php echo $_forum['row']['id']; ?>" <?php if($_forum['row']['id']==$_GET['block']){ ?>selected="selected"<?php } ?>>
						<?php echo $_forum['row']['blockname']; ?>
					</option>
				<?php }while ($_forum['row'] = $_forum['query']->fetch_assoc());  ?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label for="level">權限：</label>
				<select class="form-control" name="level" required="required">
				<?php foreach(sc_member_level_array() as $key=>$value){if($key>0){ ?>
					<option value="<?php echo $key; ?>" <?php if($key==1){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
				<?php }} ?>
				</select>
			</div>
		</div>
	</div>
	<div class="form-group">
		<textarea id="summernote" name="content" rows="10" required="required">
			<?php
			if(isset($_COOKIE['content'])){
				echo $_COOKIE['content'];
				setcookie('content','',time()-600);
			}
			?>
		</textarea>
	</div>
	<?php if($center['forum']['captcha']==1){ ?>
	<div class="form-group">
		<label for="captcha">驗證碼：</label>
		<img src="include/captcha.php" class="captcha" title="按圖更換驗證碼"/>
		<input id="captcha" class="form-control" name="captcha" type="text" size="10" maxlength="10" required="required">
	</div>
	<?php } ?>
	<br><input name="button" class="btn btn-primary" type="submit" value="送出發表！">
</form>
<?php }elseif(isset($_GET['fid'])){ ?>
<ul class="breadcrumb">
	<li><a href="forum.php">論壇</a></li>
	<li class="active"><a href="forum.php?fid=<?php echo $_block['row']['id']; ?>"><?php echo $_block['row']['blockname']; ?></a></li>
</ul>
<div class="row">
	<div class="col-md-9 col-sm-8">
		<h2 class="page-header">
			<?php echo $_block['row']['blockname']; ?>
			<a href="forum.php?block=<?php echo $_block['row']['id']; ?>&newpost" class="btn btn-primary btn-xs">發表文章</a>
		</h2>
	</div>
	<div class="col-md-3 col-sm-4 text-right">
		<form id="search" class="form-inline" method="GET" action="forumsearch.php">
			<div class="input-group">
				<input id="q" class="form-control" name="q" type="text" class="search-query" required="required">
				<span class="input-group-btn">
					<span class="btn btn-default" onclick="if(document.getElementById('q').value!=''){document.getElementById('search').submit();}"><i class="glyphicon glyphicon-search"></i></span>
				</span>
			</div>
		</form>
	</div>
</div>
<?php if($_forum['num_rows'] == 0){ ?>
<div class="alert alert-danger">沒有文章！</div>
<?php }else{ ?>
<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>文章</th>
				<th>作者/發表時間</th>
				<th>回覆</th>
				<th>最後回覆</th>
			</tr>
		</thead>
		<tbody>
			<?php do{
				$_reply = sc_get_result("SELECT * FROM `forum_reply` WHERE `post_id`='%d' ORDER BY `mktime` DESC",array($_forum['row']['id']));
				$_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_forum['row']['author']));
				$_reply_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_reply['row']['author']));
			?>
			<tr>
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
</div>
<?php
	$_all_forum=sc_get_result("SELECT COUNT(*) FROM `forum` WHERE `block`='%d'",array($_block['row']['id']));
	echo sc_page_pagination('forum.php',@$_GET['page'],implode('',$_all_forum['row']),$center['forum']['limit'],'&fid='.$_block['row']['id']);
}}else{ ?>
<h2 class="page-header">論壇</h2>
<?php if($_forum['num_rows'] == 0){ ?>
<div class="alert alert-danger">沒有區塊！</div>
<?php }else{ ?>
<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>區塊</th>
				<th>文章數</th>
				<th>最後發文</th>
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
				<td><?php echo $_block_post['num_rows']; ?></td>
				<td>
					<?php
					if($_block_post['num_rows']>0){
						echo date('Y-m-d H:i',strtotime($_block_post['row']['mktime']));
					}else{
						echo '無';
					}?>
				</td>
			</tr>
			<?php }while($_forum['row'] = $_forum['query']->fetch_assoc()); ?>
		</tbody>
    </table>
</div>
<?php } ?>
<?php } ?>
<?php
$view->render();
?>