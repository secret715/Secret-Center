<?php if(isset($_SESSION['Center_Username'])){ ?>
	<li><a href="member.php">會員中心</a></li>
	<li><a href="chat.php">聊天室</a></li>
	<li class="dropdown">
		<a href="forum.php" data-target="#" data-toggle="dropdown">論壇 ▼</a>
		<ul class="dropdown-menu">
			<li><a href="forum.php">論壇</a></li>
			<li><a href="forum.php?newpost">發表帖子</a></li>
			<li><a href="mypost.php">我的帖子</a></li>
		</ul>
	</li>
	<li class="dropdown">
		<a href="file.php" data-target="#" data-toggle="dropdown">文件夾 ▼</a>
		<ul class="dropdown-menu">
			<li><a href="file.php">文件夾</a></li>
			<li><a href="file.php?upload">檔案上傳</a></li>
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