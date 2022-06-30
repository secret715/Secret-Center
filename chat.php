<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

$view = new View('include/theme/default.html',$center['site_name'],'聊天室','include/nav.php');
$view->addScript("include/js/chat.js");
$view->addScript("include/js/notice.js");
?>
<script>
$(function(){
	new sc_chat('#chat',<?php echo $center['chat']['public']*1000; ?>,'<?php echo sc_csrf(); ?>');
});
</script>
<h2 class="page-header">聊天室</h2>
<div id="chat"></div>
<?php
	$view->render();
?>