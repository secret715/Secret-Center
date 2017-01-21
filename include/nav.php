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
 if(isset($_SESSION['Center_Username'])){ ?>
	<li><a href="member.php">會員中心</a></li>
	<li><a href="chat.php">聊天室</a></li>
	<li class="dropdown">
		<a href="forum.php" data-target="#" data-toggle="dropdown">論壇 ▼</a>
		<ul class="dropdown-menu">
			<li><a href="forum.php">論壇</a></li>
			<li><a href="forum.php?newpost">發表文章</a></li>
			<li><a href="mypost.php">我的文章</a></li>
		</ul>
	</li>
	<?php if($_SESSION['Center_UserGroup']==9){?>
		<li><a href="admin/index.php">系統管理</a></li>
	<?php } ?>
	<li><a href="index.php?logout">登出</a></li>
<?php }else{ ?>
	<li><a href="index.php">登入</a></li>
	<li><a href="register.php">註冊</a></li>
<?php } ?>