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

if((isset($_POST['merge'])&&isset($_POST['block']))&&(abs($_POST['merge'])!=abs($_POST['block'])) && isset($_GET[$_SESSION['Center_Auth']])){
	$SQL->query("UPDATE `forum` SET `block` = '%d' WHERE  `block` = '%d'",array(abs($_POST['block']),abs($_POST['merge'])));
	$SQL->query("DELETE FROM `forum_block` WHERE `id` ='%d'",array(abs($_POST['merge'])));
}


$_block=sc_get_result("SELECT * FROM `forum_block` ORDER BY `position` ASC");

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'區塊合併',true);
?>
<h2 class="page-header">區塊合併</h2>
<p>轉移舊區塊的所有文章，並同時刪除舊區塊</p>
<form class="form-xs" action="forummerge.php?<?php echo $_SESSION['Center_Auth']; ?>" method="POST">
		<div class="form-group">
			將舊區塊
			<select class="form-control" name="merge" required="required">
			<?php do{ ?>
				<option value="<?php echo $_block['row']['id']; ?>"><?php echo $_block['row']['blockname']; ?></option>
			<?php }while ($_block['row'] = $_block['query']->fetch_assoc());  ?>
			</select>
		</div>
		<div class="form-group">
			合併到&nbsp;
			<select class="form-control" name="block" required="required">
			<?php
			$_block['query']->data_seek(0);
			while ($_block['row'] = $_block['query']->fetch_assoc()){
			?>
				<option value="<?php echo $_block['row']['id']; ?>"><?php echo $_block['row']['blockname']; ?></option>
			<?php } ?>
			</select>
		</div>
		
	<input type="submit" class="btn btn-warning" value="區塊合併">
</form>
<?php
$view->render();
?>