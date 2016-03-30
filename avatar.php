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

$_member = sc_get_result("SELECT * FROM `member` WHERE `id` = '%d'",array($_SESSION['Center_Id']));
$_avatar_dir='include/avatar/';
$upload_error = null;

if(@$_GET['step'] == 2 && !isset($_GET['no']) && isset($_FILES['upload'])) {
	try {
		//檢查頭貼資料夾是否存在
		if(!is_dir($_avatar_dir)) {
			//不存在的話就創建頭貼資料夾
			if(!mkdir($_avatar_dir)){
				die("頭貼資料夾不存在，並且創建失敗");
			}
		}
		if($_FILES['upload']['name'] != "" && is_uploaded_file($_FILES['upload']['tmp_name'])){
			if((!isset($_FILES['upload']['error']) > 0)){
				throw new Exception("檔案上傳失敗");
			}
			
			if($center['avatar']['max_size'] <= $_FILES['upload']['size'] / 1000){
				throw new Exception("檔案大小超出限制");
			}
			
			$limitedext = array('jpeg','jpg','gif','png');//允許的副檔名
			$extend = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);//檔案副檔名
			$new_name=$_SESSION['Center_Id'];//檔案名(不含副檔名)
			$file=$_avatar_dir.$new_name.'.'.$extend;
			
			if(!in_array($extend,$limitedext)){
				throw new Exception("不允許此檔案格式");
			}
			if($center['avatar']['compress']){
				$file=$_avatar_dir.$new_name.'.jpg';
				sc_avatar_compress($_FILES['upload']['tmp_name'],$extend,$file,240,$center['avatar']['quality']);//壓縮圖片為JPG格式
			}else{
				move_uploaded_file($_FILES['upload']['tmp_name'],$file);//複製檔案
			}
			if($_member['row']['avatar']!='default.png'&&$_avatar_dir.$_member['row']['avatar']!=$file){
				unlink($_avatar_dir.$_member['row']['avatar']);//刪除舊頭貼
			}
			$SQL->query("UPDATE `member` SET `avatar` = '%s' WHERE `id` = '%s'",array(
				ltrim($file,$_avatar_dir),
				$_SESSION['Center_Id']
			));
			header("Location: avatar.php?ok");
		}
		else {
			throw new Exception("你沒有上傳任何相片");
		}
	}
	catch(Exception $e){
		$upload_error = $e->getMessage();
	}
}

$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'修改頭貼');
$view->addScript("include/js/notice.js");
?>
<div class="main">
	<h2 class="page-header">修改頭貼</h2>
<?php if($upload_error !== null){ ?>
<div class="alert alert-danger"><?php echo $upload_error; ?></div>
<?php }elseif(isset($_GET['no'])) { ?>
<div class="alert alert-danger">修改頭貼失敗！</div>
<?php }elseif(isset($_GET['ok'])){ ?>
<div class="alert alert-success">修改頭貼成功！</div>
<?php }if(!isset($_GET['step'])) { ?>
	<form action="avatar.php?step=2" method="post" enctype="multipart/form-data" name="form1">
		<p>你目前的頭貼：</p>
		<img src="<?php echo sc_avatar_url($_member['row']['id']); ?>" class="avatar avatar-lg">
		<br>
		<div class="controls">
			<p><input name="upload" type="file" id="upload" /></p>
			<input type="submit" name="button" class="btn btn-info" value="上傳" />
		</div>
		<br>
		<p>上傳頭貼僅允許 jpg.gif.png 格式圖片，且檔案大小不得超過 <?php echo $center['avatar']['max_size']; ?> KB，建議尺寸 100x100 ~ 200x200(px)。</p>
	</form>
<?php } ?>
</div>
<?php
$view->render();
?>