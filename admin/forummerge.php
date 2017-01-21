<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2017 Secret Center開發團隊 <http://center.gdsecret.net/#team>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

set_include_path('../include/');
$includepath = true;

require_once('../Connections/SQL.php');
require_once('../config.php');
require_once('view.php');

if(!isset($_SESSION['Center_Username']) or $_SESSION['Center_UserGroup'] != 9){
    header("Location: ../index.php");
    exit;
}

if((isset($_POST['merge'])&&isset($_POST['block']))&&(abs($_POST['merge'])!=abs($_POST['block']))){
	$SQL->query("UPDATE `forum` SET `block` = '%d' WHERE  `block` = '%d'",array(abs($_POST['block']),abs($_POST['merge'])));
	$SQL->query("DELETE FROM `forum_block` WHERE `id` ='%d'",array(abs($_POST['merge'])));
}


$_block=sc_get_result("SELECT * FROM `forum_block` ORDER BY `position` ASC");

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'區塊合併',true);
?>
<h2 class="page-header">區塊合併</h2>
<p>轉移舊區塊的所有文章，並同時刪除舊區塊</p>
<form class="form-xs" action="forummerge.php" method="POST">
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