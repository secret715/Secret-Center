<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?login");
	exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'聊天室');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");
?>
<script>
$(function(){
	window.user = <?php echo isset($_GET['user']) ? "'" . $_GET['user'] . "'" : 'null'; ?>;
	new Chat("#target",false,<?php echo $center['chat']['public'] * 1000; ?>);
});
</script>
<div class="main">
<h2>聊天室</h2>
	<div id="target" class="chat"></div>
</div>
<?php
	$view->render();
?>