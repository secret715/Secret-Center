<?php if(sc_level_auth(9)){ ?>
<div id="nav-collapse" class="collapse navbar-collapse">
	<ul class="navbar-nav mr-auto">
		<li class="nav-item"><a class="nav-link" href="index.php">後臺首頁</a></li>
		<li class="dropdown nav-item">
			<a class="nav-link dropdown-toggle" href="member.php" data-target="#" data-toggle="dropdown">會員管理</a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="member.php">會員管理</a>
				<a class="dropdown-item" href="membersearch.php">會員搜尋</a>
			</div>
		</li>
		<li class="nav-item"><a class="nav-link" href="chat.php">聊天管理</a>
		<li class="dropdown nav-item">
			<a class="nav-link dropdown-toggle" href="forum.php" data-target="#" data-toggle="dropdown">論壇管理</a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="forum.php">論壇管理</a>
				<a class="dropdown-item" href="forumsearch.php">論壇搜尋</a>
				<a class="dropdown-item" href="forummerge.php">區塊合併</a>
			</div>
		</li>
		<li class="nav-item"><a class="nav-link" href="notice.php">通知管理</a>
		<li class="dropdown nav-item">
			<a class="nav-link dropdown-toggle" href="editconfig.php" data-target="#" data-toggle="dropdown">系統設定</a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="editconfig.php">系統設定</a>
				<a class="dropdown-item" href="editcss.php">網站樣式</a>
			</div>
		</li>
		<li class="nav-item"><a class="nav-link" href="../member.php">會員中心</a></li>
	</ul>
</div>
<?php } ?>