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
	sc_loginout();
	header("Location: ../index.php?out");
	exit;
}
$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'系統管理',true);
?>
<h2 class="page-header">系統管理</h2>
<p>歡迎來到系統管理介面！</p>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">系統</h3>
			</div>
			<div class="panel-body">
				目前版本：Secret Center <?php echo sc_ver(); ?>&nbsp;&nbsp;<span id="ver_check"></span>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<h3 class="panel-title">會員</h3>
			</div>
			<div class="panel-body">
				目前會員數量：
				<?php echo implode('',$SQL->query("SELECT COUNT(*) FROM `member`")->fetch_assoc()); ?> 人
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<h3 class="panel-title">論壇</h3>
			</div>
			<div class="panel-body">
				目前帖子總數：
				<?php echo implode('',$SQL->query("SELECT COUNT(*) FROM `forum`")->fetch_assoc()); ?> 篇
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title">通知</h3>
			</div>
			<div class="panel-body">
				目前通知數量：
				<?php echo implode('',$SQL->query("SELECT COUNT(*) FROM `notice`")->fetch_assoc()); ?> 筆
			</div>
		</div>
	</div>
</div>
<?php
$view->render();
?>