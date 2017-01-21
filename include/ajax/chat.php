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

if(isset($_GET['sent'])){
	if(isset($_POST['content'])&&trim($_POST['content'])!=''){
		$_chat = sc_get_result("SELECT * FROM `chat` ORDER BY `mktime` ASC");
		
		if($_chat['num_rows'] > 300){
			$SQL->query("TRUNCATE TABLE `chat`");
		}
		$SQL->query("INSERT INTO `chat` (`content`, `mktime`, `author`) VALUES ('%s', now(), '%s')",array(htmlspecialchars($_POST['content']),$_SESSION_scratch['Center_Id']));
		
		sc_tag_member(
			htmlspecialchars($_POST['content']),
			rtrim(sc_get_headurl(),'include/ajax').'/chat.php',
			$_SESSION_scratch['Center_Username'].'在聊天室提到你',
			$_SESSION_scratch['Center_Id']
		);
	
		header("Content-type: application/json");
		echo json_encode(array("success" => true));
	}
}elseif(isset($_POST['last'])){
	$_last=intval($_POST['last']);
	$_timeout=20;
	$i=0;
	while($i<$_timeout){
		$_result = sc_get_result("SELECT * FROM `chat` WHERE `mktime` > '%s'",array(date('Y-m-d H:i:s',$_last)));
		
		$_data=array();
		$_data['last']=time();
		if($_result['num_rows'] > 0){
			do{
				$_member = $SQL->query("SELECT `username` FROM `member` WHERE `id` = '%d'",array($_result['row']['author']))->fetch_assoc();
				$t = strtotime($_result['row']['mktime']);
				if(date('d') == date('d', $t)){
					$_data['data'][]=array('id'=>$_result['row']['id'],'content'=>$_result['row']['content'],'mktime'=>date('H:i:s',$t),'author'=>$_member['username']);
				}else{
					$_data['data'][]=array('id'=>$_result['row']['id'],'content'=>$_result['row']['content'],'mktime'=>$_result['row']['mktime'],'author'=>$_member['username']);
				}
			}while($_result['row'] = $_result['query']->fetch_assoc());
			break;
		}
		$i++;
		sleep(1);
	}
	header("Content-type: application/json");
	echo json_encode($_data);
}
die;