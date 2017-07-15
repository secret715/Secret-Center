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

if(isset($_GET['del']) && isset($_GET[$_SESSION['Center_Auth']])){
	$SQL->query("TRUNCATE TABLE `chat`");
}

$view = new View('theme/admin_default.html','admin/nav.php','',$center['site_name'],'聊天室',true);
$view->addScript("../include/js/chat.js");
?>
<script>
$(function(){
	new sc_chat('#chat',1,'<?php echo $_SESSION['Center_Auth']; ?>');
	$('#chat_del').click(function(e){
		if(!window.confirm("確定清除所有聊天紀錄？")){
			e.preventDefault();
		}
	});
});
</script>
<h2 class="page-header">聊天管理</h2>
<p>
	<a id="chat_del" class="btn btn-danger" href="chat.php?del&<?php echo $_SESSION['Center_Auth']; ?>">清除所有聊天紀錄</a>
</p>
<div id="chat"></div>
<?php
	$view->render();
?>