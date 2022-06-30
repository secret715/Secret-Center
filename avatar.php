<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'login.php');
$SQL=sc_db_conn();

$member=$SQL->select('member',array('id'=>$_SESSION['center']['id']));

$view = new View('include/theme/default.html',$center['site_name'],'我的頭貼','include/nav.php');
$view->addScript("https://cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.4/js/Jcrop.min.js");
$view->addCSS("https://cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.4/css/Jcrop.min.css");
$view->addScript('include/js/fileupload/jquery.ui.widget.js');
$view->addScript('include/js/fileupload/jquery.iframe-transport.js');
$view->addScript('include/js/fileupload/jquery.fileupload.js');
$view->addScript('include/js/avatar.js');
?>
<script>
auth="<?php echo sc_csrf();?>";
</script>
<div class="main">
	<h2 class="page-header">修改頭貼</h2>
<?php
if(isset($_GET['step'])&&$_GET['step']==2) {
?>
裁切您的相片：
<img id="jcrop" src="<?php echo sc_avatar($member[0]['avatar']).'?'.uniqid(); ?>">
<span id="crop" class="mt-2 btn btn-success btn-lg btn-rounded">確定</span>
<?php
}else{
?>
	<div class="row no-gutters">
		<div class="col-md-4 col-12">
			<img class="w-100" src="<?php echo sc_avatar($member[0]['avatar']); ?>">
		</div>
		<div class="col-md-8 col-12">
			<form action="avatar.php?step=2&<?php echo sc_csrf(); ?>" method="post" enctype="multipart/form-data">
			  <div class="custom-file">
				<input name="upload" type="file" id="upload">
			  </div>
			  <p>上傳頭貼僅允許尺寸大於 100 x 100 的 jpg.gif.png 格式圖片，且檔案大小不得超過  <?php echo $center['avatar']['max_size']; ?> KB。</p>
			</form>
		</div>
	</div>
<?php } ?>
</div>
<?php
$view->render();
?>