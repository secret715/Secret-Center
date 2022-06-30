<?php
require_once('../../config.php');

if(!sc_level_auth(-1))die;
session_write_close();

if(isset($_GET['sent'])&& sc_csrf_auth()){
	if(isset($_POST['content'])&&trim($_POST['content'])!=''){
		$SQL=sc_db_conn();
		if($SQL->query("INSERT INTO `chat` (`content`, `mktime`, `author`) VALUES ('%s', now(), '%s')",array(htmlspecialchars($_POST['content']),$_SESSION['center']['id']))){
			sc_tag_member(
				htmlspecialchars($_POST['content']),
				rtrim(sc_get_headurl(),'include/ajax').'/chat.php',
				$_SESSION['center']['username'].'在聊天室提到你',
				$_SESSION['center']['id']
			);
		
			header("Content-type: application/json");
			echo json_encode(array("success" => true));
		}else{
			echo json_encode(array("success" => false));
		}
		
	}
}elseif(isset($_REQUEST['last'])){
    require_once('../../include/sse.php');
    
    @ini_set('max_execution_time', 0);
    header("Content-Type: text/event-stream");
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');//nginx

    function load_msg($_last){
        $_login=sc_get_result("SELECT `logout_time` FROM `login` WHERE `logout_time`='0000-00-00 00:00:00' AND `owner`='%s' AND `ip`='%s' ORDER BY `login_time` DESC LIMIT 0,1",array($_SESSION['center']['id'],sc_get_ip()));
        if($_login['num_rows']<=0){
            echo 'Not login';
            die;
        }
        $_result = sc_get_result("SELECT * FROM `chat` WHERE `mktime` > '%s'",array(date('Y-m-d H:i:s',$_last)));
        
        
        $_data=array();
        $_data['last']=time();
        if($_result['num_rows'] > 0){
            foreach($_result['row'] as $_v){
                $_member = sc_get_result("SELECT `username`,`nickname`,`avatar` FROM `member` WHERE `id` = '%d'",array($_v['author']));
                $t = strtotime($_v['mktime']);
                $own_message=($_v['author']==$_SESSION['center']['id']) ? true : false;
                if(date('d') == date('d', $t)){
                    $_data['data'][]=array('id'=>$_v['id'],'content'=>$_v['content'],'mktime'=>date('H:i:s',$t),'author'=>$_member['row'][0]['username'],'author_nickname'=>$_member['row'][0]['nickname'],'avatar'=>sc_avatar($_member['row'][0]['avatar'],'../../'),'self'=>$own_message);
                }else{
                    $_data['data'][]=array('id'=>$_v['id'],'content'=>$_v['content'],'mktime'=>$_v['mktime'],'author'=>$_member['row'][0]['username'],'author_nickname'=>$_member['row'][0]['nickname'],'avatar'=>sc_avatar($_member['row'][0]['avatar'],'../../'),'self'=>$own_message);
                }
            }
            return 'data: '.json_encode($_data)."\n\r";
        }
    }

	$sse=new sse('load_msg');
    $sse->start();
}
die;