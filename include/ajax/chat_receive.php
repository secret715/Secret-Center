<?php
if(!session_id()){
	session_start();
}

$user = $_SESSION['Center_Username'];
session_write_close();

set_include_path('../');
$includepath = true;

require_once('../../Connections/SQL.php');

try {
	$Chat = $SQL->query("SELECT * FROM chat ORDER BY id ASC");
	$row_Chat = $Chat->fetch_assoc();
	$totalRows_Chat = $Chat->num_rows;
	
	if($totalRows_Chat > 50){
	    $SQL->query("TRUNCATE TABLE chat");
	}
	$SQL->query("INSERT INTO chat (name, data, ptime) VALUES ('%s', '%s', now())",array(
		$user,
		htmlspecialchars($_POST['data'])
	));
	
	header("Content-type: application/json");
	echo json_encode(array("success" => true));
	die();
}
catch (Exception $e){
	header("Content-type: application/json");
	echo json_encode(array("success" => false, "error" => base64_encode($e->getMessage())));
	die();
}