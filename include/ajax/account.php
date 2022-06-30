<?php
require_once('../../config.php');

if(!sc_csrf_auth())die;

$return['status']=true;
try{

	if(isset($_POST['auth_username'])){
		if(!sc_level_auth(-1)&&!isset($_POST['auth_username'])){
			throw new Exception('查詢帳號異常');
		}

		$data=array();
		if(sc_user_exist($_POST['auth_username'])){
			$data['user_exist']=true;
		}else{
			$data['user_exist']=false;
		}
		$return=$data;
	}elseif(sc_form_inputs_exist(array('update'),array('password','email'))){
		echo '111';
		$SQL=sc_db_conn();
		
		$data=array();
		
		$data['id']=(isset($_GET['id'])&&sc_level_auth(5)) ? intval($_GET['id']) : $_SESSION['center']['id'];
		
		$member=$SQL->select('member',array('id'=>$data['id']));
		
		$_username=$member[0]['username'];
		if($_POST['password']!='')$data['password']= sc_password($_POST['password'],$_username);
		$data['email']=$_POST['email'];
		
		if(sc_level_auth(5)){
			if(isset($_POST['nickname']))$data['nickname']=sc_namefilter($_POST['nickname']);
			if(isset($_POST['level'])&&is_numeric($_POST['level'])&&array_key_exists($_POST['level'],sc_member_level_array()))$data['level']=intval($_POST['level']);
			if(isset($_POST['remark']))$data['remark']=addslashes($_POST['remark']);
		}
		
		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			throw new Exception('Email不正常');
		}

		$return=array();

		if($SQL->update('member',$data,array('id'=>$data['id']))){
			$return['status']=true;
		}else{
			throw new Exception('修改資料異常');
		}
		
	}elseif(isset($_GET['data'])){
		$SQL=sc_db_conn();
		if(sc_level_auth(5)&&isset($_GET['id'])){
			$_GET['id']=intval($_GET['id']);
		}else{
			$_GET['id']=$_SESSION['center']['id'];
		}
		
		$member=$SQL->query("SELECT `member`.`id`,`member`.`avatar`,`member`.`username`,`member`.`nickname`,`member`.`email`,`member`.`joined`,`member`.`level`,`member`.`qrcode`,`login`.`id` as 'login_id',`login_time` as 'last_login' FROM `member` LEFT JOIN `login` ON `owner`=`member`.`id` WHERE `member`.`id`='%d'  ORDER BY `login_time` DESC",array('id'=>intval($_GET['id'])));

		if($SQL->numRows()<=0){
			throw new Exception('讀取發生異常');
		}

		$data=array();
		foreach($member[0] as $k=>$v){
			if(in_array($k,array('password')))continue;
			if($k=='level')$v=sc_member_level_array($v);

			
			if($k=='avatar')$v=str_replace(array('/include','/admin','/ajax'),array('','',''),sc_get_headurl()).'include/avatar/'.$v;
			$data[]=array($k,$v);
		}
		$return=$data;
		
	}
}catch(Exception $e){
	$return['status']=false;
	$return['error']=$e->getMessage();
}finally{
	echo json_encode($return);
	die;
}