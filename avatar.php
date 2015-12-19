<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?login");
	exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'修改頭像');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");

$member = sc_get_member_data($_SESSION['Center_Username']);
$upload_error = null;

if((@$_GET['step'] == 2) && (!isset($_GET['no'])) && isset($_FILES['upload'])) {
	try {
		//檢查頭像資料夾是否存在
		if(!is_dir("images/avatar")) {
			//不存在的話就創建頭像資料夾
			if(!mkdir("images/avatar")){
				die("頭像資料夾不存在，並且創建失敗");
			}
		}
		if($_FILES['upload']['name'] != "" && is_uploaded_file($_FILES['upload']['tmp_name'])){
			if((!isset($_FILES['upload']['error']) > 0)){
				throw new Exception("檔案上傳失敗");
			}
			
			if($center['avatar']['max_size'] <= $_FILES['upload']['size'] / 1000){
				throw new Exception("檔案大小超出限制");
			}
			
			$limitedext = array("jpeg","jpg","gif","png");//允許的副檔名
			$extend = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION);//檔案副檔名
			$new_name=$_SESSION['Center_Username'].'_'.substr(sc_keygen(),0,8);//檔案亂數名(不含副檔名)
			$file='images/avatar/'.$new_name.'.'.$extend;
			
			if(!in_array($extend,$limitedext)){
				throw new Exception("不允許此檔案格式");
			}
			if($center['avatar']['img_tiny']){
				$file='images/avatar/'.$new_name.'.jpg';
				sc_avatar_image_tiny($_FILES['upload']['tmp_name'],$extend,$file,240,80);//壓縮圖片為JPG格式
			}else{
				move_uploaded_file($_FILES['upload']['tmp_name'],$file);//複製檔案
			}
			if($member['row']['avatar']!='../images/default_avatar.png'){
				unlink(str_replace('../','',$member['row']['avatar']));//刪除舊頭像
			}
			$SQL->query("UPDATE member SET avatar = '%s' WHERE name = '%s'",array(
				'../'.$file,
				$_SESSION['Center_Username']
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
?>
<div class="main">
	<h2>修改頭像</h2>
<?php
if($upload_error !== null){
?>
	<div class="alert alert-error"><?php echo $upload_error; ?></div>
<?php }
if(isset($_GET['no'])) { ?>
	<div class="alert alert-error">修改頭像失敗！</div>
<?php }
if(isset($_GET['ok'])) {
?>
	<div class="alert alert-success">修改頭像成功！</div>
<?php }
if(!isset($_GET['step'])) {
?>
	<form action="avatar.php?step=2" method="post" enctype="multipart/form-data" name="form1">
		<p>你目前的頭像：</p>
		<img src="include/avatar.php?id=<?php echo $_SESSION['Center_Username'] ?>" class="avatar avatar-xlarge">
		<br>
		<div class="controls">
			<p><input name="upload" type="file" id="upload" /></p>
			<input type="submit" name="button" class="btn btn-info" value="上傳" />
		</div>
		<br>
		<p>上傳頭像僅允許 jpg.gif.png 格式圖片，且檔案大小不得超過 <?php echo $center['avatar']['max_size']; ?> KB，建議尺寸 100x100 ~ 200x200(px)。</p>
	</form>
<?php } ?>
</div>
<?php
$view->render();
?>