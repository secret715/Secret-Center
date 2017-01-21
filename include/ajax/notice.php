<?php
/*
<Secret Center, open source member management system>
Copyright (C) 2012-2017 Secret Center開發團隊 <http://center.gdsecret.net/#team>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

set_include_path('../');
$includepath = true;
require_once('../../Connections/SQL.php');
require_once('../../config.php');

$_SESSION_scratch = $_SESSION;
session_write_close();
if(!isset($_SESSION_scratch['Center_Username'])){
	exit;
}
if(isset($_POST['last_count'])){
	$_last=intval($_POST['last_count']);
	$_timeout=20;
	$i=0;
	while($i<$_timeout){
		$_unread_count = $SQL->query("SELECT `id` FROM `notice` WHERE `status`=0 AND `send_to`='%d'",array($_SESSION_scratch['Center_Id']))->num_rows;
		
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
	
	$_last=intval($_POST['last']);
	$_all_rows=$SQL->query("SELECT * FROM `notice` WHERE `send_to`='%d'",array($_SESSION_scratch['Center_Id']))->num_rows;
	
	if($_all_rows>30){
		$SQL->query("DELETE FROM `notice` WHERE `send_to`='%d' ORDER BY `mktime` ASC LIMIT 1",array($_SESSION_scratch['Center_Id']));
	}
	
	$_result = sc_get_result("SELECT * FROM `notice` WHERE `send_to`='%d' AND `mktime` > '%s' ORDER BY `mktime` ASC",array($_SESSION_scratch['Center_Id'],date('Y-m-d H:i:s',$_last)));
	
	$_data=array();
	$_data['last']=time();
	
	if($_result['num_rows'] > 0){
		do{
			$_send_from = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_result['row']['send_from']));
			$_send_to = sc_get_result("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_result['row']['send_to']));
			
			$_data['data'][]=array(
			'id'=>$_result['row']['id'],
			'url'=>$_result['row']['url'],
			'content'=>$_result['row']['content'],
			'status'=>$_result['row']['status'],
			'send_from'=>$_send_from['row']['username'],
			'send_from_avatar'=>sc_avatar_url($_result['row']['send_from'],true),
			'send_to'=>$_send_to['row']['username'],
			'mktime'=>$_result['row']['mktime']
			);
			
		}while($_result['row'] = $_result['query']->fetch_assoc());
	}
	
	$SQL->query("UPDATE `notice` SET `status` = '1' WHERE `send_to`='%d' AND `status`='0'",array($_SESSION_scratch['Center_Id']));
	header("Content-type: application/json");
	echo json_encode($_data);
}
die;