<?php
if(isset($_GET['logout'])){
	$_SESSION['Center_Username'] = NULL;
	$_SESSION['Center_UserGroup'] = NULL;
	unset($_SESSION['Center_Username']);
	unset($_SESSION['Center_UserGroup']);
	setcookie("login","",time()-7200);
	header("Location: index.php?out");
	exit;
}
?>