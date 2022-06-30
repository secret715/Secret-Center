
<div id="nav-collapse" class="collapse navbar-collapse">
	<ul class="navbar-nav mr-auto">
	<?php if(sc_level_auth(-1)){ ?>
		<li class="nav-item"><a class="nav-link" href="member.php">會員中心</a></li>
		<li class="nav-item"><a class="nav-link" href="chat.php">聊天室</a></li>
		<li class="nav-item dropdown">
			<a href="forum.php" class="nav-link dropdown-toggle" data-target="#" data-toggle="dropdown">論壇</a>
			<div class="dropdown-menu">
				<a class="dropdown-item" href="forum.php">論壇</a>
				<a class="dropdown-item" href="forumedit.php?new">發表文章</a>
				<a class="dropdown-item" href="mypost.php">我的文章</a>
			</div>
		</li>
		<?php if(sc_level_auth(9)){ ?>
			<li class="nav-item"><a class="nav-link" href="admin/index.php">系統管理</a></li>
		<?php } ?>
	</ul>
	<ul class="navbar-nav">
		<li class="nav-item dropdown">
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
					<img class="rounded-circle" src="<?php echo sc_get_headurl().'include/avatar/'.$_SESSION['center']['avatar']; ?>" style="width:2rem;height:2rem;"><span class="ml-1"><?php echo $_SESSION['center']['nickname']; ?></span>
				</a>
				<div class="avatar-menu dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
					<a class="dropdown-item" href="account.php">我的帳號</a>
					<a class="dropdown-item disabled" href="#"><?php echo sc_member_level_array($_SESSION['center']['level']); ?></a>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="index.php?logout">登出</a> 
				</div>
			</li>
	<?php }else{ ?>
		<li class="nav-item"><a class="nav-link" href="index.php">登入</a></li>
		<li class="nav-item"><a class="nav-link" href="register.php">註冊</a></li>
	<?php } ?>
	</ul>
</div>
<?php if(sc_level_auth(-1)){ ?>
</nav>
<nav class="app-like-navbar fixed-bottom bg-light d-md-none">
	<a class="item" href="member.php"><span class="material-icons">home</span> 會員中心</a>
	<a class="item" href="chat.php"><span class="material-icons">chat</span> 聊天室</a>
	<a class="item" href="forum.php"><span class="material-icons">forum</span> 論壇</a>
	<a class="item" href="account.php"><span class="material-icons">account_circle</span> 我的帳號</a>
<?php } ?>