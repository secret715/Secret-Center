<?php
require_once('../../config.php');

sc_level_auth(-1,false,true);

$_SESSION_tmp = $_SESSION;
session_write_close();

if(isset($_GET['sent'])&& sc_csrf_auth()){
	if(isset($_POST['content'])&&trim($_POST['content'])!=''){
		$SQL=sc_db_conn();
		if($SQL->query("INSERT INTO `chat` (`content`, `mktime`, `author`) VALUES ('%s', now(), '%s')",array(htmlspecialchars($_POST['content']),$_SESSION_tmp['center']['id']))){
			sc_tag_member(
				htmlspecialchars($_POST['content']),
				rtrim(sc_get_headurl(),'include/ajax').'/chat.php',
				$_SESSION_tmp['center']['username'].'在聊天室提到你',
				$_SESSION_tmp['center']['id']
			);
		
			header("Content-type: application/json");
			echo json_encode(array("success" => true));
		}else{
			echo json_encode(array("success" => false));
		}
		
	}
}elseif(isset($_POST['last'])){
	$SQL=sc_db_conn();
	$_last=intval($_POST['last']);
	$_timeout=20;
	$i=0;
	while($i<$_timeout){
		$_result = sc_get_result("SELECT * FROM `chat` WHERE `mktime` > '%s'",array(date('Y-m-d H:i:s',$_last)));
		
		$_data=array();
		$_data['last']=time();
		if($_result['num_rows'] > 0){
			foreach($_result['row'] as $_v){
				$_member = sc_get_result("SELECT `username`,`nickname`,`avatar` FROM `member` WHERE `id` = '%d'",array($_v['author']));
				$t = strtotime($_v['mktime']);
				$own_message=($_v['author']==$_SESSION_tmp['center']['id']) ? true : false;
				if(date('d') == date('d', $t)){
					$_data['data'][]=array('id'=>$_v['id'],'content'=>$_v['content'],'mktime'=>date('H:i:s',$t),'author'=>$_member['row'][0]['username'],'author_nickname'=>$_member['row'][0]['nickname'],'avatar'=>sc_avatar($_member['row'][0]['avatar'],'../../'),'self'=>$own_message);
				}else{
					$_data['data'][]=array('id'=>$_v['id'],'content'=>$_v['content'],'mktime'=>$_v['mktime'],'author'=>$_member['row'][0]['username'],'author_nickname'=>$_member['row'][0]['nickname'],'avatar'=>sc_avatar($_member['row'][0]['avatar'],'../../'),'self'=>$own_message);
				}
			}
			break;
		}
		$i++;
		sleep(1);
	}
	header("Content-type: application/json");
	echo json_encode($_data);
}
die;