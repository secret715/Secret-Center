<?php
set_include_path('../include/');
$includepath = true;

require_once('../Connections/SQL.php');
require_once('../config.php');
require_once('view.php');

if(!isset($_SESSION['Center_Username']) or $_SESSION['Center_UserGroup'] != 9){
    header("Location: ../index.php");
	exit;
}

if(isset($_POST['body_font_size'])){
	$_css='../style.css';
	$_css_sample='../style-sample.css';
	$put_css = vsprintf(str_replace('%;','@',file_get_contents($_css_sample)),array(
		addslashes($_POST['body_font_size']),
		addslashes($_POST['body_background_color']),
		addslashes($_POST['body_line_height']),
		addslashes($_POST['container_width'])
	));
	file_put_contents($_css,str_replace('@','%;',$put_css));
	$_GET['ok']=true;
}

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'網站樣式',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");
$view->addScript("../include/js/jquery.validate.js");
?>
<script type="text/javascript">
$(function(){
	$('#body_font_size').val(parseInt($('body').css('font-size')));
	$('#body_background_color').val(rgb2hex($('body').css('background-color')));
	$('#body_line_height').val(parseInt($('body').css('line-height')));
	$('#container_width').val(Math.round(parseInt($('.container').css('width'))/parseInt($('body').css('width'))*1000)/10);
	
	$('#body_background_color').change($('#new_color').text($('#body_background_color').val()));
	
	function rgb2hex(rgb){
		var hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		function hex(x) {
			return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
		}
		return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	}
});
</script>
<div class="main">
<?php if(isset($_GET['ok'])){?>
	<div class="alert alert-success">修改成功！</div>
<?php } ?>
<h2 class="subtitle">網站樣式</h2>
<p>提醒您，若修改後網站樣式沒有變更，請清除瀏覽器快取後再重新整理頁面</p>
<form id="form1" name="form1" class="form-horizontal" method="post" action="editcss.php">
	<fieldset>
		<legend>主要</legend>
		<div class="control-group">
			<label class="control-label" for="body_font_size">字體大小：</label>
			<div class="controls">
				<div class="input-append">
					<input id="body_font_size" name="body_font_size" class="input-mini" type="text" maxlength="2" required="required">
					<span class="add-on">px</span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="body_background_color">背景色：</label>
			<div class="controls">
				<input id="body_background_color" name="body_background_color" class="input-mini" type="color" required="required">
				<span id="new_color" class="help-inline"></span>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="body_line_height">行距：</label>
			<div class="controls">
				<div class="input-append">
					<input id="body_line_height" name="body_line_height" class="input-mini" type="text" maxlength="2" required="required">
					<span class="add-on">px</span>
				</div>
			</div>
				
		</div>
		<div class="control-group">
			<label class="control-label" for="container_width">版面寬度：</label>
			<div class="controls">
				<div class="input-append">
					<input id="container_width" name="container_width" class="input-mini" type="text" maxlength="4" required="required">
					<span class="add-on">%</span>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="form-actions">
		<input name="button" type="submit" id="button" class="btn btn-success" value="修改" />
	</div>
</form>
</div>
<?php
$view->render();
?>