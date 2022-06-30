<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(9,'../index.php');

if(isset($_GET['del']) && sc_csrf_auth()){
	$SQL=sc_db_conn();
	
	if(!$SQL->query("TRUNCATE TABLE `chat`")){
		echo '發生錯誤';
		die;
	}
}

$view = new View('theme/admin_default.html',$center['site_name'],'聊天室','admin/nav.php');
$view->addScript("../include/js/chat.js");
?>
<script>
$(function(){
	new sc_chat('#chat',0,'<?php echo sc_csrf(); ?>');
	$('#chat_del').click(function(e){
		if(!window.confirm("確定清除所有聊天紀錄？")){
			e.preventDefault();
		}
	});
});
</script>
<h2 class="page-header">聊天管理</h2>
<p>
	<a id="chat_del" class="btn btn-danger" href="chat.php?del&<?php echo sc_csrf(); ?>">清除所有聊天紀錄</a>
</p>
<div id="chat"></div>
<?php
	$view->render();
?>