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

$dir = '../file/'.$_SESSION['Center_Username'].'/';

if((isset($_GET['rename'])) && ($_GET['rename']!='') && (isset($_GET['newname'])) && (sc_namefilter($_GET['newname'])!='')){
	$_GETrename=sc_namefilter($_GET['rename']);
	$file = $dir.$_GETrename;
	$newname =sc_namefilter($_GET['newname']).'.' . pathinfo(sc_namefilter($_GETrename), PATHINFO_EXTENSION);
	$newfile= $dir.$newname;
	if(@rename($file, $newfile)){
		$_data['url']='include/file/'.$_SESSION['Center_Username'].'/'.$newname;
		$_data['file']=$newname;
		echo json_encode($_data);
	}
}elseif((isset($_GET['del'])) && ($_GET['del'] !='')){
	if(@unlink($dir.sc_namefilter($_GET['del']))){
		echo 1;
	}
}
exit;