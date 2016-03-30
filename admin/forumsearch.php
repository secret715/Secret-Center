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

if(isset($_GET['q'])&&trim($_GET['q'])!=''&&isset($_GET['level'])&&isset($_GET['mktime'])&&isset($_GET['author'])&&isset($_GET['block'])){
	if(is_numeric($_GET['level'])){
		$_level= sprintf("AND `level` = '%d'",abs($_GET['level']));
 	}else{
		$_level='';
	}
	if(is_numeric($_GET['block'])){
		$_block= sprintf("AND `block` = '%d'",abs($_GET['block']));
 	}else{
		$_block='';
	}
	$GET_mktime['0']=strtotime($_GET['mktime']['0']);
	$GET_mktime['1']=strtotime($_GET['mktime']['1']);
	if($GET_mktime['0']>0&&$GET_mktime['1']>0){
		$_mktime=sprintf(" AND `mktime` BETWEEN '%s' AND '%s'",
					date('Y-m-d H:i:s',$GET_mktime['0']),
					date('Y-m-d H:i:s',$GET_mktime['1']));
	}elseif($GET_mktime['0']>0){
		$_mktime=sprintf(" AND `mktime` > '%s'",
					date('Y-m-d H:i:s',$GET_mktime['0']));
	}elseif($GET_mktime['1']>0){
		$_mktime=sprintf(" AND `mktime` < '%s'",
					date('Y-m-d H:i:s',$GET_mktime['1']));
	}
	else{
		$_mktime='';
	}
	$limit_row=$center['forum']['limit'];
	if(isset($_GET['page'])){
		$limit_start = abs(intval(($_GET['page']-1)*$limit_row));
		$_post=sc_get_result("SELECT * FROM `forum` WHERE `title` LIKE '%%%s%%' OR `content` LIKE '%%%s%%' OR `author` LIKE '%%%s%%' $_block $_level $_mktime ORDER BY `mktime` DESC LIMIT %d,%d",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$_GET['author'],$limit_start,$limit_row));
	} else{
		$limit_start=0;
		$_post=sc_get_result("SELECT * FROM `forum` WHERE `title` LIKE '%%%s%%' OR `content` LIKE '%%%s%%' OR `author` LIKE '%%%s%%' $_block $_level $_mktime ORDER BY `mktime` DESC LIMIT %d,%d",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$_GET['author'],$limit_start,$limit_row));
	}
}else{
	$_block=sc_get_result("SELECT * FROM `forum_block` ORDER BY `position` ASC");
}

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'論壇搜尋',true);
?>
<h2 class="page-header">論壇搜尋</h2>
<?php if(!isset($_GET['q'])or trim($_GET['q'])==''or!isset($_GET['level'])or!isset($_GET['mktime'])or!isset($_GET['author'])or!isset($_GET['block'])){ ?>
<form class="form-horizontal form-sm" action="forumsearch.php" method="GET">	
	<div class="form-group">
		<label class="col-sm-3 control-label" for="q">關鍵字：</label>
		<div class="col-sm-9">
			<input class="form-control" name="q" type="text">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="author">發帖人：</label>
		<div class="col-sm-9">
			<input class="form-control" name="author" type="text">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="block">區塊：</label>
		<div class="col-sm-9">
			<select class="form-control" name="block" required="required">
				<option value="all" selected="selected">所有</option>
			<?php do{ ?>
				<option value="<?php echo $_block['row']['id']; ?>">
					<?php echo $_block['row']['blockname']; ?>
				</option>
			<?php }while ($_block =  $_block['query']->fetch_assoc()); ?>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="mktime">發表日期：</label>
		<div class="col-sm-9">
			<input class="form-control" name="mktime[]" type="date" style="width:30%;display:inline-block;"> - 
			<input class="form-control" name="mktime[]" type="date" style="width:30%;display:inline-block;"><small>(YYYY-MM-DD)</small>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label" for="level">權限：</label>
		<div class="col-sm-9">
			<select class="form-control" name="level">
				<option value="all">所有</option>
				<?php foreach(sc_member_level_array() as $key=>$value){ ?>
					<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-3 col-sm-9">
			<input class="btn btn-success btn-lg" type="submit" value="搜尋">
		</div>
	</div>
</form>
<?php 
}else{
if($_post['num_rows']<=0){ ?>
<div class="alert alert-danger">沒有符合的資料！</div>
<?php }else{ ?>
<?php do{
	$_reply = sc_get_result("SELECT COUNT(*) FROM `forum_reply` WHERE `post_id`='%d'",array($_post['row']['id']));
	$_author = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_post['row']['author']));
?>
<div class="post">
	<a href="forumview.php?id=<?php echo $_post['row']['id']; ?>" style="font-size:120%;display:block;">
	<?php echo $_post['row']['title']; ?>
	</a>
	<p>
	<?php echo mb_substr(strip_tags($_post['row']['content']),mb_stripos(strip_tags($_post['row']['content']),sc_xss_filter($_GET['q']),0,'UTF-8')-30,60,'UTF-8'); ?>...
	</p>
	<ul class="list-inline" style="font-size:90%;color:rgb(100,100,100);">
		<?php if($_post['row']['level']>1){ ?>
		<li><span class="label"><?php echo sc_member_level($_post['row']['level']); ?></span></li>
		<?php } ?>
		<li><?php echo $_author['row']['username']; ?></li>
		<li><?php echo date('Y-m-d H:i',strtotime($_post['row']['mktime'])); ?></li>
		<li><?php echo implode('',$_reply['row']); ?> 回覆</li>
	</ul>
</div>
<?php
	}while ($_post['row'] = $_post['query']->fetch_assoc());
	$_all_post=sc_get_result("SELECT COUNT(*) FROM `forum` WHERE `title` LIKE '%%%s%%' OR `content` LIKE '%%%s%%' OR `author` LIKE '%%%s%%' $_block $_level $_mktime",array(sc_xss_filter($_GET['q']),sc_xss_filter($_GET['q']),$_GET['author']));
	echo sc_page_pagination('forumsearch.php',@$_GET['page'],implode('',$_all_post['row']),$center['forum']['limit'],'&q='.sc_xss_filter($_GET['q']).'&author='.urlencode(sc_namefilter($_GET['author'])).'&block='.urlencode(abs($_GET['block'])).'&level='.urlencode(abs($_GET['level'])).'&mktime[]='.$GET_mktime['0'].'&mktime[]='.$GET_mktime['1']);
}}
?>
<?php
$view->render();
?>