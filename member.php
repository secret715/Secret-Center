<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2016 Secret Center開發團隊 <http://center.gdsecret.net/#team>

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

require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?n");
	exit;
}

$view = new View('include/theme/default.html','include/nav.php',NULL,$center['site_name'],'會員中心');
$view->addScript("include/js/notice.js");
?>
<?php if((isset($_COOKIE['login']))&&(isset($_GET['login']))){?>
	<div class="alert alert-success">登入成功！</div>
<?php } ?>
<h2 class="page-header">會員中心</h2>
<div class="row">
	<div class="col-md-4">
		<div class="list-group">
			<a href="account.php" class="list-group-item">我的帳號</a>
			<a href="chat.php" class="list-group-item">聊天室</a>
			<a href="forum.php" class="list-group-item">論壇</a>
		</div>
	</div>
	<div class="col-md-8">
		<?php if($center['member']['message']!=''){ ?>
		<div class="well">
		<?php echo $center['member']['message']; ?>
		</div>
		<?php } ?>
	</div>
</div>
<?php
	$view->render();
?>