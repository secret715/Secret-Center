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

if(isset($_GET['logout'])){
	$_SESSION['Center_Username'] = NULL;
	$_SESSION['Center_UserGroup'] = NULL;
	unset($_SESSION['Center_Username']);
	unset($_SESSION['Center_UserGroup']);
	setcookie("login","",time()-7200);
	header("Location: ../index.php?out");
	exit;
}

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'系統管理',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");
?>
<script>
$(function(){
	$.ajax({
		url: $('#check').attr('href'),
		dataType: 'jsonp',
		success: function(data){
			if(!data.error){
				if(!data.latest){
					$('#results').html(data.msg);
				}
				else {
					$('#results').text(data.msg);
					$('#link').html(data.link);
				}
			}
			else {
				$('#results').html(data.msg);
			}
		}
	});
});
</script>
<div class="main">
	<?php if((isset($_COOKIE['login']))&&(isset($_GET['login']))){?>
		<div class="prompt">登入成功！</div>
	<?php } ?>
	<h2>系統管理</h2>
	歡迎來到系統管理介面！<br>
	您目前所使用的 Secret會員系統版本：<?php echo sc_ver(); ?>
	<div id="results" class="well" style="margin: 1em auto; width: 50%; text-align: center;">
		<a href="http://center.gdsecret.com/update.php?json&ver=<?php echo sc_ver(); ?>&url=<?php echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" id="check">檢查更新</a>
	</div>
	<div id="link"></div>
</div>
<?php
$view->render();
?>