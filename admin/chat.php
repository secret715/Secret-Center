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

if(isset($_GET['del']) && $_GET['del'] == 'public'){
	$delete_data = "TRUNCATE TABLE chat";
	$SQL->query($delete_data);
}

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'聊天管理',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");
?>
<script>
$(function(){
	window.user = <?php echo isset($_GET['user']) ? "'" . $_GET['user'] . "'" : 'null'; ?>;
	new Chat("#target",false,<?php echo $center['chat']['public'] * 1000; ?>);
});
$(function(){
	$('#chat_del_public').click(function(e){
		if(!window.confirm("確定清除所有聊天紀錄？")){
			e.preventDefault();
		}
	});
});
</script>
<div class="main">
	<h2 class="subtitle">聊天室管理</h2>
	<a id="chat_del_public" class="btn btn-danger" href="chat.php?del=public">清除所有公共聊天紀錄</a>
	<div id="target" class="chat"></div>
</div>
<?php
$view->render();
?>