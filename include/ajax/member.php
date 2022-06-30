<?php
require_once('../../config.php');

sc_level_auth(5,null,true);

if(!sc_csrf_auth())die;

$return=array();
$return['status']=true;
try{
	if(sc_level_auth(5)){
		$SQL=sc_db_conn();

		if(sc_form_inputs_exist(array('search'),array('level','joined','last_login','username','email','remark'))){
			$_level=(is_numeric($_POST['level'])&&array_key_exists($_POST['level'],sc_member_level_array()))? sprintf("AND `level` = '%d'",$_POST['level']):'';

			$_joined='';
			$_last_login='';
			$_POST['remark']=htmlspecialchars($_POST['remark']);
			$POST_joined['0']=strtotime($_POST['joined']['0']);
			$POST_joined['1']=strtotime($_POST['joined']['1']);
			$POST_last_login['0']=strtotime($_POST['last_login']['0']);
			$POST_last_login['1']=strtotime($_POST['last_login']['1']);
			if($POST_joined['0']>0&&$POST_joined['1']>0){
				$_joined=sprintf(" AND `joined` BETWEEN '%s' AND '%s'",
							date('Y-m-d H:i:s',$POST_joined['0']),
							date('Y-m-d H:i:s',$POST_joined['1']));
			}elseif($POST_joined['0']>0){
				$_joined=sprintf(" AND `joined` > '%s'",
							date('Y-m-d H:i:s',$POST_joined['0']));
			}elseif($POST_joined['1']>0){
				$_joined=sprintf(" AND `joined` < '%s'",
							date('Y-m-d H:i:s',$POST_joined['1']));
			}

			if($POST_last_login['0']>0&&$POST_last_login['1']>0){
				$_last_login=sprintf(" AND `login_time` BETWEEN '%s' AND '%s'",
							date('Y-m-d H:i:s',$POST_last_login['0']),
							date('Y-m-d H:i:s',$POST_last_login['1']));
			}elseif($POST_last_login['0']>0){
				$_last_login=sprintf(" AND `login_time` > '%s'",
							date('Y-m-d H:i:s',$POST_last_login['0']));
			}elseif($POST_last_login['1']>0){
				$_last_login=sprintf(" AND `login_time` < '%s'",
							date('Y-m-d H:i:s',$POST_last_login['1']));
			}
			
			$_member = sc_get_result("SELECT `member`.`id` , `member`.`username` , `member`.`nickname` , `member`.`email` , `member`.`level` ,`member`.`remark` ,`member`.`level` , `member`.`joined` , `login`.`id` AS 'login_id', `login_time` AS 'last_login' FROM `member` LEFT JOIN  `login` ON `member`.`id` = `owner` WHERE `username` LIKE '%%%s%%' AND `email` LIKE '%%%s%%' AND `remark` LIKE '%%%s%%' $_last_login $_joined $_level GROUP BY `member`.`id`",array(sc_namefilter($_POST['username']),$_POST['email'],$_POST['remark']));
			if($SQL->numRows()<=0){
				throw new Exception('查無資料');
			}
		}else{	
			$member=$SQL->query("SELECT `member`.`id` , `member`.`username` , `member`.`nickname` , `member`.`email` , `member`.`level` ,`member`.`remark` ,`member`.`level` , `member`.`joined` , `login`.`id` AS 'login_id', `login_time` AS 'last_login' FROM `member` LEFT JOIN  `login` ON `member`.`id` = `owner` GROUP BY `member`.`id` ORDER BY `login_time` DESC");
			//$member=$SQL->query("SELECT * FROM `member` ORDER BY `joined` DESC");

			

			if($SQL->numRows()<=0){
				throw new Exception('讀取發生異常');
			}
		}

		$data=array();
		foreach($member as $k=>$v){
            $tmp=array();
		    $tmp=$v;
			unset($tmp['password']);
            $tmp['level']=sc_member_level_array($v['level']);
			$tmp['joined']=date('Y-m-d H:i',strtotime($v['joined']));
			$tmp['last_login']=$tmp['last_login']==null ? '':$tmp['last_login'];
			$data[]=$tmp;
		}
		$return['member']=$data;
		
	}
}catch(Exception $e){
	$return['status']=false;
	$return['error']=$e->getMessage();
}finally{
	echo json_encode($return);
	die;
}