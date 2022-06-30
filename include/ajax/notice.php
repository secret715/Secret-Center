<?php
require_once('../../config.php');

if(!sc_level_auth(-1)){
	echo json_encode(array('count'=>-1));
	die;
}

$_SESSION_tmp = $_SESSION;
session_write_close();

if(isset($_POST['last_count'])){
	$_last=intval($_POST['last_count']);
	$_timeout=20;
	$i=0;
	while($i<$_timeout){
		$_unread_count = sc_get_result("SELECT `id` FROM `notice` WHERE `status`=0 AND `send_to`='%d'",array($_SESSION_tmp['center']['id']));
		$_unread_count=$_unread_count['num_rows'];

		$_data=array();
		if($_unread_count != $_last){
			$_data['count']=$_unread_count;
			break;
		}
		$i++;
		sleep(1);
	}
	header("Content-type: application/json");
	echo json_encode($_data);
	
}elseif(isset($_POST['last'])){
	$SQL=sc_db_conn();
	$_last=intval($_POST['last']);
	$_all_rows=$SQL->query("SELECT COUNT(*) as count FROM `notice` WHERE `send_to`='%d'",array($_SESSION_tmp['center']['id']))[0]['count'];
	
	if($_all_rows>30){
		$SQL->query("DELETE FROM `notice` WHERE `send_to`='%d' ORDER BY `mktime` ASC LIMIT 1",array($_SESSION_tmp['center']['id']));
	}
	
	$_result = sc_get_result("SELECT * FROM `notice` WHERE `send_to`='%d' AND `mktime` > '%s' ORDER BY `mktime` ASC",array($_SESSION_tmp['center']['id'],date('Y-m-d H:i:s',$_last)));
	
	$_data=array();
	$_data['last']=time();
	
	if($_result['num_rows'] > 0){
		foreach($_result['row'] as $_v){
			$_send_from = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_v['send_from']));
			$_send_to = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_v['send_to']));
			
			$_data['data'][]=array(
			'id'=>$_v['id'],
			'url'=>$_v['url'],
			'content'=>$_v['content'],
			'status'=>$_v['status'],
			'send_from'=>$_send_from['row'][0]['username'],
			'send_from_avatar'=>sc_avatar_url($_v['send_from'],true),
			'send_to'=>$_send_to['row'][0]['username'],
			'mktime'=>$_v['mktime']
			);
			
		}
	}
	
	$SQL->query("UPDATE `notice` SET `status` = '1' WHERE `send_to`='%d' AND `status`='0'",array($_SESSION_tmp['center']['id']));
	header("Content-type: application/json");
	echo json_encode($_data);
}
die;