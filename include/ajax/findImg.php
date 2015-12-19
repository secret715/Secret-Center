<?php
if(!session_id()){
	session_Start();
}

$result = glob(sprintf('../file/%s/*.{jpg,jpeg,jfif,png,gif,JPG,JPEG,JFIF,PNG,GIF}', $_SESSION['Center_Username']), GLOB_BRACE);

foreach($result as &$item){
	$item = str_replace('../', 'include/', $item);
}

header('Content-type: application/json');
echo json_encode($result);