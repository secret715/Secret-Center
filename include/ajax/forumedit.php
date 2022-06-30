<?php
require_once('../../config.php');


if(!sc_level_auth(-1))die;

if(!sc_csrf_auth())die;

$return['status']=true;
	try{
	$data=array();
	if(sc_form_inputs_exist(array('edit'),array('content','level','title'))){
		$SQL=sc_db_conn();
		if(sc_form_inputs_exist(array('pid'),array('block'))){
			$_GET['pid']=intval($_GET['pid']);
			$_POST['title']=sc_xss_filter($_POST['title']);
			$_POST['block']=abs(intval($_POST['block']));
			$_POST['level']=intval($_POST['level']);
			$author=sc_level_auth(9) ? (isset($_GET['author'])? intval($_GET['author']): $_SESSION['center']['id']):  $_SESSION['center']['id'];

			$block=$SQL->select('forum_block',array('id'=>$_POST['block']));
			if($SQL->numRows()<=0){
				throw new Exception('查無區塊');
			}
			if(!array_key_exists($_POST['level'],sc_member_level_array())){
				throw new Exception('閱讀權限設定錯誤');
			}
			if(!$SQL->update('forum',array('title'=>$_POST['title'],'content'=>sc_xss_filter($_POST['content']),'block'=>$_POST['block'],'level'=>$_POST['level']),array('id'=>$_GET['pid'],'author'=>$author))){
				throw new Exception('修改時發生錯誤');
			}


		}else{
			throw new Exception('非法使用');
		}
	}elseif(sc_form_inputs_exist(array('edit','rid'),array('content'))){
		$SQL=sc_db_conn();
		$_GET['rid']=intval($_GET['rid']);
		$_POST['content']=sc_xss_filter($_POST['content']);
		$author=sc_level_auth(9) ? (isset($_GET['author'])? intval($_GET['author']): $_SESSION['center']['id']):  $_SESSION['center']['id'];

		$block=$SQL->select('forum_reply',array('id'=>$_GET['rid']));
		if($SQL->numRows()<=0){
			throw new Exception('查無此回覆');
		}
		if(!$SQL->update('forum_reply',array('content'=>sc_xss_filter($_POST['content'])),array('id'=>$_GET['rid'],'author'=>$author))){
			throw new Exception('修改回覆時發生錯誤');
		}
		
	}elseif(isset($_GET['pid'])){
		$post = sc_get_result("SELECT `forum`.`id`,`forum`.`title`,`forum`.`content`,`forum`.`block`,`forum`.`level`,`forum`.`mktime`,`forum`.`author`,`member`.`nickname`   FROM `forum`,`member` WHERE `forum`.`id` = '%d' and `forum`.`author`=`member`.`id`",array(abs(intval($_GET['pid']))));
		
		if($post['num_rows']<=0){
			die;
		}
		$tmp=array();
		foreach($post['row'][0] as $k=>$v){
			
			/*switch($k){
				case '':
				break;
				default:
					$tmp[$k]=$v;
			}*/
			$tmp[$k]=$v;
		}
		$data=$tmp;
	}elseif(isset($_GET['rid'])){
		$_GET['rid']=abs(intval($_GET['rid']));
		$page=(isset($_GET['page'])) ? abs(intval($_GET['page'])) : 1;
		$reply = sc_get_result("SELECT `forum_reply`.`id`,`forum`.`level`,`forum_reply`.`post_id`,`forum_reply`.`mktime`,`forum_reply`.`author`,`forum_reply`.`content`,`nickname` FROM `forum`,`member`,`forum_reply` WHERE `forum_reply`.`post_id`='%d' AND  `forum_reply`.`post_id`=`forum`.`id` AND `forum_reply`.`author`=`member`.`id` ORDER BY `mktime` ASC limit %d,%d",array($_GET['pid'],($page-1)*$center['forum']['limit'],($page)*$center['forum']['limit']));
		
		if($reply['num_rows']<=0||!sc_level_auth($reply['row'][0]['level'])){
			die;
		}
		
		$count = sc_get_result("SELECT COUNT(*) AS count FROM `forum_reply` WHERE `post_id` = '%s'",array($_GET['pid']));
		
		$data['reply_num']=intval($count['row'][0]['count']);
		$data['limit']=intval($center['forum']['limit']);
		$data['page']=intval($page);


		$i=2+($page-1)*$center['forum']['limit'];
		foreach($reply['row'] as $v){
			$tmp=array();
			$tmp=$v;
			unset($tmp['level']);
			unset($tmp['post_id']);
			$tmp['floor']=$i;
			$tmp['content']=sc_removal_escape_string($v['content']);
			$tmp['own']=($v['author']==$_SESSION['center']['id']);
			$tmp['avatar']=sc_avatar_url($v['author']);
			$data['reply'][]=$tmp;
			$i++;
		}
	}
}catch(Exception $e){
	$return['status']=false;
	$return['error']=$e->getMessage();
}finally{
	$return=array_merge($return,$data);
	echo json_encode($return);
	die;
}