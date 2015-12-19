<?php
if(!session_id()){
	session_start();
}

set_include_path('../');
$includepath = true;

require_once('../../Connections/SQL.php');
require_once('../../config.php');
if(!isset($_SESSION['Center_Username'])){
    exit;
}
if(isset($_GET['read'])){
	$SQL->query("UPDATE `notice` SET `status` = '1' WHERE `send_to`='%s' AND `status`='0' ORDER BY `ptime` DESC LIMIT 5",array($_SESSION['Center_Username']));
}elseif(isset($_GET['unread'])){
	echo $SQL->query("SELECT * FROM `notice` WHERE `send_to`='%s' AND `status`='0'",array($_SESSION['Center_Username']))->num_rows;
}else{
	$notice['all_rows']=$SQL->query("SELECT * FROM `notice` WHERE `send_to`='%s'",array($_SESSION['Center_Username']))->num_rows;

	if($notice['all_rows']>20){
		$SQL->query("DELETE FROM `notice` WHERE `send_to`='%s' AND `status`='1' ORDER BY `ptime` ASC LIMIT 1",array($_SESSION['Center_Username']));
	}

	$notice['query']=$SQL->query("SELECT * FROM `notice` WHERE `send_to`='%s' ORDER BY `ptime` DESC  LIMIT 0,10",array($_SESSION['Center_Username']));
	$notice['row']=$notice['query']->fetch_assoc();
	$notice['num_rows']=$notice['query']->num_rows;

	if($notice['num_rows']>0){
		do{
			$_data[]=json_encode($notice['row']);
		}while($notice['row']=$notice['query']->fetch_assoc());
		echo '['.implode(',',$_data).']';
	}else{
		echo '{"error":"1"}';
	}
}
die;