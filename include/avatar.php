<?php
require_once('../Connections/SQL.php');
require_once('../config.php');

if(isset($_GET['id'])) {
	$avatar = $SQL->query("SELECT avatar FROM member WHERE name = '%s'",array($_GET['id']));
	$row_avatar = $avatar->fetch_assoc();
	$totalRows_avatar = $avatar->num_rows;
	if(isset($row_avatar['avatar'])){
		$avatar = $row_avatar['avatar'];
		header("Location: $avatar");
	}
}