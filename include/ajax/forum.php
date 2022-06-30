<?php
require_once('../../config.php');

sc_level_auth(-1);

$data=array();
if(isset($_GET['fid'])){
	$block = sc_get_result("SELECT `id`,`blockname` FROM `forum_block` WHERE `id`='%d'",array(abs(intval($_GET['fid']))));
	
	if($block['num_rows']<=0){
		die;
	}
	$block_post = sc_get_result("SELECT `forum`.`id`,`title`,`forum`.`level`,`mktime`,`author`,`nickname` FROM `forum`,`member` WHERE `block`='%d' AND `forum`.`author`=`member`.`id` ORDER BY `mktime` DESC",array($block['row'][0]['id']));
	if($block_post['num_rows']<=0){
		$data[]='';
	}else{
			
		$data['blockname']=$block['row'][0]['blockname'];
		foreach($block_post['row'] as $v){
			$count=sc_get_result("SELECT COUNT(*) AS `count` FROM `forum_reply` WHERE `post_id`='%d'",array($v['id']));
			$post_reply = sc_get_result("SELECT * FROM `forum_reply`,`member` WHERE `post_id`='%d' AND `forum_reply`.`author`=`member`.`id` ORDER BY `mktime` DESC limit 0,1",array($v['id']));

			$tmp=array();
			$tmp=$v;
			$tmp['last_reply']=($post_reply['num_rows']>0)? array('author_nickname'=>$post_reply['row'][0]['nickname'],'mktime'=>date("Y-m-d H:i:s",strtotime($post_reply['row'][0]['mktime']))): '';
			$tmp['reply_num']=$count['row'][0]['count'];
			$tmp['level']=sc_member_level_array($v['level']);
			$data['post'][]=$tmp;
		}
	}
}elseif(isset($_GET['member'])){
	$_GET['member']  = sc_level_auth(9)&&$_GET['member']!=NULL ? $_GET['member']: $_SESSION['center']['id'];
	$post = sc_get_result("SELECT `forum`.`id`,`title`,`forum`.`level`,`mktime`,`forum`.`block`,`author`,`nickname` FROM `forum`,`member` WHERE `author`='%d' AND `forum`.`author`=`member`.`id` ORDER BY `mktime` DESC",array($_GET['member']));

	if($post['num_rows']<=0){
		$data[]='';
	}else{
		
		foreach($post['row'] as $v){
			
			$block = sc_get_result("SELECT `id`,`blockname` FROM `forum_block` WHERE `id`='%d'",array($v['block']));

			$count=sc_get_result("SELECT COUNT(*) AS `count` FROM `forum_reply` WHERE `post_id`='%d'",array($v['id']));
			$post_reply = sc_get_result("SELECT * FROM `forum_reply`,`member` WHERE `post_id`='%d' AND `forum_reply`.`author`=`member`.`id` ORDER BY `mktime` DESC limit 0,1",array($v['id']));

			$tmp=array();
			$tmp=$v;
			$tmp['last_reply']=($post_reply['num_rows']>0)? array('author_nickname'=>$post_reply['row'][0]['nickname'],'mktime'=>date("Y-m-d H:i:s",strtotime($post_reply['row'][0]['mktime']))): '';
			$tmp['reply_num']=$count['row'][0]['count'];
			$tmp['level']=sc_member_level_array($v['level']);
			$tmp['blockname']=$block['row'][0]['blockname'];
			
			$data[]=$tmp;
		}
	}
}elseif(isset($_GET['pid'])){
	$_GET['pid']=abs(intval($_GET['pid']));
	$page=(isset($_GET['page'])) ? abs(intval($_GET['page'])) : 1;
	$reply = sc_get_result("SELECT `forum_reply`.`id`,`forum`.`level`,`forum_reply`.`post_id`,`forum_reply`.`mktime`,`forum_reply`.`author`,`forum_reply`.`content`,`nickname` FROM `forum`,`member`,`forum_reply` WHERE `forum_reply`.`post_id`='%d' AND  `forum_reply`.`post_id`=`forum`.`id` AND `forum_reply`.`author`=`member`.`id` ORDER BY `mktime` ASC limit %d,%d",array($_GET['pid'],($page-1)*$center['forum']['limit'],($page)*$center['forum']['limit']));
	
	if($reply['num_rows']<=0||!sc_level_auth($reply['row'][0]['level'])){
		echo "[]";
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
}else{
	$block = sc_get_result("SELECT * FROM `forum_block` ORDER BY `position` ASC");
	foreach($block['row'] as $v){
		$count=sc_get_result("SELECT COUNT(*) AS `count` FROM `forum` WHERE `block`='%d'",array($v['id']));

		$block_post = sc_get_result("SELECT `mktime` FROM `forum` WHERE `block`='%d' ORDER BY `mktime` DESC limit 0,1",array($v['id']));

		$tmp=array();
		$tmp=$v;
		$tmp['last_post']=($block_post['num_rows']>0)? date("Y-m-d H:i:s",strtotime($block_post['row'][0]['mktime'])): '';
		$tmp['post_num']=$count['row'][0]['count'];

		$data[]=$tmp;
		
	}
}

echo json_encode($data);
die;