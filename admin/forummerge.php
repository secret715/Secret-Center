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

$view = new View('../view/new_theme.html','../include/admin_nav.php',$center['site_name'],'區塊合併',true);
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("../include/js/channel.js");


if((isset($_POST['merge'])&&isset($_POST['block']))&&(abs($_POST['merge'])!=abs($_POST['block']))){
	$SQL->query("UPDATE `forum` SET `block` = '%d' WHERE  `block` = '%d'",array(abs($_POST['block']),abs($_POST['merge'])));
	$SQL->query("DELETE FROM forum_block WHERE id =%d",array(abs($_POST['merge'])));
}


$_block_query=$SQL->query("SELECT * FROM `forum_block` ORDER BY `position` ASC");
$_block =$_block_query->fetch_assoc();

?>
<div class="main">
<h2>區塊合併</h2>
<form action="forummerge.php" method="POST" name="form1">
		<p>轉移舊區塊的所有帖子，並同時刪除舊區塊</p>
		<div class="controls">
			將舊區塊
			<select class="input-large" name="merge" required="required">
			<?php do{ ?>
				<option value="<?php echo $_block['id']; ?>"><?php echo $_block['blockname']; ?></option>
			<?php }while ($_block =  $_block_query->fetch_assoc());  ?>
			</select>
		</div>
		<div class="controls">
			合併到&nbsp;
			<select class="input-large" name="block" required="required">
			<?php
			$_block_query->data_seek(0);
			while ($_block =  $_block_query->fetch_assoc()){
			?>
				<option value="<?php echo $_block['id']; ?>"><?php echo $_block['blockname']; ?></option>
			<?php } ?>
			</select>
		</div>
		
	<input type="submit" name="button" class="btn btn-warning btn-large" value="區塊合併" />
</form>
</div>
<?php
$view->render();
?>