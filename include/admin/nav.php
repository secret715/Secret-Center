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
 if((isset($_SESSION['Center_Username']))&&($_SESSION['Center_UserGroup']==9)){ ?>
	<li><a href="index.php">後臺首頁</a></li>
	<li class="dropdown">
		<a href="member.php" data-target="#" data-toggle="dropdown">會員管理 ▼</a>
		<ul class="dropdown-menu">
			<li><a href="member.php">會員管理</a></li>
			<li><a href="membersearch.php">會員搜尋</a></li>
		</ul>
	</li>
	<li><a href="chat.php">聊天管理</a></li>
	<li class="dropdown">
		<a href="forum.php" data-target="#" data-toggle="dropdown">論壇管理 ▼</a>
		<ul class="dropdown-menu">
			<li><a href="forum.php">論壇管理</a></li>
			<li><a href="forumsearch.php">論壇搜尋</a></li>
			<li><a href="forummerge.php">區塊合併</a></li>
		</ul>
	</li>
	<li><a href="notice.php">通知管理</a></li>
	<li class="dropdown">
		<a href="editconfig.php" data-target="#" data-toggle="dropdown">系統設定 ▼</a>
		<ul class="dropdown-menu">
			<li><a href="editconfig.php">系統設定</a></li>
			<li><a href="editcss.php">網站樣式</a></li>
		</ul>
	</li>
	<li><a href="../member.php">會員中心</a></li>
<?php } ?>