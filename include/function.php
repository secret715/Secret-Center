<?php
function sc_ver(){
	return '9.5';
}

function sc_keygen($_value=''){
	return str_shuffle(str_replace('=','',base64_encode(mt_rand(100,999).time()).sha1(mt_rand().md5($_value).uniqid())));
}
function sc_db_conn(){
	global $center;
	$SQL = new Database($center['database']['host'],$center['database']['username'],$center['database']['password'],$center['database']['name']);
	return $SQL;

}
function sc_user_exist($_username,$_email=false){
	$SQL=sc_db_conn();
	$SQL->select('member',array('username'=>sc_namefilter($_username)));
	if($SQL->numRows()>0){
		if($_email!=false){
			$SQL->select('member',array('email'=>sc_namefilter($_email)));
			if($SQL->numRows()>0){
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}else{
		return false;
	}
}
function sc_login($_username,$_password){
	$SQL=sc_db_conn();
	if (isset($_username)&&isset($_password)) {
		$login = $SQL->query("SELECT `id`,`username`,`nickname`, `password`, `level`, `avatar` FROM `member` WHERE (`username` = '%s' OR `email` = '%s') AND `password` = '%s'",array(
			$_username,
			$_username,
			sc_password($_password,$_username)
		));
		if ($SQL->numRows() > 0) {
			
			if($SQL->query("INSERT INTO `login` (`owner`,`login_time`, `logout_time`, `ip`) VALUES ('%d',now(), '%s', '%s')",array($login[0]['id'],'0000-00-00 00:00:00',sc_get_ip()))){
				$_SESSION['center']['username'] = strtolower($_username);
				$_SESSION['center']['nickname'] =  $login[0]['nickname'];
				$_SESSION['center']['id'] = $login[0]['id'];
				$_SESSION['center']['level'] = $login[0]['level'];
				$_SESSION['center']['avatar'] = $login[0]['avatar'];
				setcookie("login", time(), time()+10800);
				return true;
			} else{
				return -2;
			}
		} else {
			return -1;
		}
	}
}
function sc_loginout(){
	$SQL=sc_db_conn();
	if(!isset($_SESSION['center']['id'])||!$SQL->query("UPDATE `login` SET `logout_time`=now() WHERE `owner`='%s' AND `logout_time`='0000-00-00 00:00:00' AND `ip`='%s'",array($_SESSION['center']['id'],sc_get_ip()))){
		//return -1;
	}
	unset($_SESSION['center']);
	setcookie("login", "", time()-60);
	return true;
}
function sc_get_result($_query,$_value=array()){
	if(!isset($SQL))$SQL=sc_db_conn();
	$_result['row'] = $SQL->query($_query,$_value);
	$_result['num_rows'] = $SQL->numRows();
	return $_result;
}

function sc_register($_username,$_password,$_email,$_nickname,$_level=1){
	global $center;
	if($center['register'] == 1){
		if(isset($_username) && (trim(sc_namefilter($_username)) != '') && isset($_password) && (trim($_password) != '')&& filter_var($_email, FILTER_VALIDATE_EMAIL)){
			
			if(sc_user_exist($_username,$_email)){
				return -1;
			}
			
			$_username=sc_namefilter($_username);
			
			$SQL=sc_db_conn();
			return $SQL->query("INSERT INTO `member` (`username`, `password`, `email`, `nickname`, `avatar`, `rekey`, `qrcode`,`level` , `joined`) VALUES ('%s', '%s', '%s', '%s', 'default.png', '%s','%s','%d', now())",array(
				$_username,
				sc_password($_password,$_username),
				$_email,
				sc_namefilter($_nickname),
				substr(sc_keygen($_username),0,16),
				substr(sc_keygen(),0,4),
				$_level
			));
		}else{
			return -2;
		}
	}else{
		return -3;
	}
}
function sc_get_ip(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])){
		$_ip=$_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$_ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$_ip=$_SERVER['REMOTE_ADDR'];
	}
	return $_ip;
}
function sc_csrf($_renew=false){
	if(!isset($_SESSION['auth'])||$_renew){
		$_SESSION['auth']=substr(sc_keygen(),0,5);
	}
	return $_SESSION['auth'];
}
function sc_csrf_auth(){
	if(!isset($_SESSION['auth'])){
		return false;
	}
	if(isset($_GET[$_SESSION['auth']])){
		return true;
	}else{
		return false;
	}
}
function sc_form_inputs_exist($_g=array(),$_p=array(),$_reutrn=false){
	$_not_exist=array();
	$_not_exist['GET']=array();
	$_not_exist['POST']=array();
	foreach($_g as $_v){
		if(!isset($_GET[$_v])){
			$_not_exist['GET'][]=$_v;
		}
	}
	foreach($_p as $_v){
		if(!isset($_POST[$_v])){
			$_not_exist['POST'][]=$_v;
		}
	}
	
	if(count($_not_exist['GET'])+count($_not_exist['POST'])>0){
		if($_reutrn){
			return $_not_exist;
		}else{
			return false;
		}
	}else{
		return true;
	}
}
function sc_level_auth($_min_level,$_header_to=false,$_exit=false){
	if(!isset($_SESSION['center'])){
		if($_header_to!=false){
			header("location: $_header_to");
			exit;
		}
		if($_exit)exit;
		return false;
	}else{
		if($_SESSION['center']['level']<$_min_level){
			if($_header_to!=false){
				header("location: $_header_to");
				exit;
			}
			if($_exit)exit;
			return false;
		}else{
			return true;
		}
	}
}
function sc_member_level_array($_value=NULL){
	$_level=array(0=>'禁言',1=>'一般會員',2=>'進階會員',3=>'高級會員',9=>'管理員');
	return $_value==NULL ? $_level : $_level[$_value];
}

function sc_namefilter($_value){
	$_array=array('/' => '' , '\\' => '' , '*' => '' ,':' => '' , '?' => '' , '<'  => '' , '>' => '','│' => '');
	return strtr($_value,$_array);
}

function sc_password($_value,$_salt){
	$salt=substr(sha1(strrev($_value).$_salt),0,24);
	return hash('sha512',$salt.$_value);
}

function sc_get_headurl(){
	$_prefix='http';
	if(isset($_SERVER['HTTPS'])){
		if($_SERVER['HTTPS'] == 'on'){
			$_prefix='https';
		}
	}elseif(isset($_SERVER['HTTP_CF_VISITOR'])){
		$_cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
		if (isset($_cf_visitor->scheme) && $_cf_visitor->scheme == 'https') {
		  $_prefix='https';
		}
	}
	$url="$_prefix://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
	$po= strripos($url,'/');
	return substr($url,0,$po).'/';
}

function sc_avatar($_value=NULL,$_prefix=NULL){
	return $_value==NULL ? sc_get_headurl().$_prefix.'/include/avatar/default.png' : sc_get_headurl().$_prefix.'/include/avatar/'.$_value;
}

function sc_add_notice($_url,$_content,$_send_from,$_send_to){
	global $SQL;
	$SQL->query("INSERT INTO `notice` ( `url`,`content`, `status`, `send_from`,`send_to`,`mktime`) VALUES ('%s','%s',0,'%d','%d',now())",array($_url,$_content,$_send_from,$_send_to));
	return true;
}

function sc_xss_filter($_content){
	require_once('htmlpurifier/HTMLPurifier.auto.php');
    $config = HTMLPurifier_Config::createDefault();
	//$config->set('HTML.AllowedAttributes', array('iframe.src'));
	$config->set('HTML.SafeIframe', true);
	$config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
	$config->set('URI.AllowedSchemes', array('data' => true,'http' => true,'https' => true,'mailto' => true,'ftp' => true,'nntp' => true,'news' => true,'tel' => true));
	
	$purifier = new HTMLPurifier($config);
    $filterContent = $purifier->purify($_content);
    return $filterContent;
}

function sc_add_forum_post($_title,$_content,$_block,$_id,$_level){
	$SQL=sc_db_conn();
	if(array_key_exists($_level,sc_member_level_array())){
		return $SQL->query("INSERT INTO `forum` (`title`, `content`,`block`, `level`, `mktime`, `author`) VALUES ('%s', '%s','%d', '%d', now(),'%d')",array(
			htmlspecialchars($_title),
			sc_xss_filter($_content),
			abs($_block),
			abs($_level),
			abs($_id)
		));
	}else{
		return false;
	}
}
function sc_add_forum_block($_blockname,$_position=0){
	global $SQL;
	$SQL->query("INSERT INTO `forum_block` (`blockname`, `position`, `mktime`) VALUES ('%s', '%d', now())",array(sc_namefilter($_blockname),abs($_position)));
	return true;
}

function sc_tag_member($_content,$_notice_url,$_notice_content,$_id){
	preg_match_all('/@[A-Za-z0-9]{0,30}/',$_content,$_tag);
	foreach ($_tag[0] as $_v){
		$_member = sc_get_result("SELECT `id` FROM `member` WHERE `username` = '%s'",array(ltrim($_v,'@')));
		if($_member['num_rows']>0){
			sc_add_notice($_notice_url,$_notice_content,$_id,$_member['row'][0]['id']);
		}
	}
	return true;
}
function sc_avatar_url($_id,$_only_file_name=false){
	$_avatar = sc_get_result("SELECT `avatar` FROM `member` WHERE `id` = '%s'",array(abs($_id)));
	if($_avatar['num_rows']>0){
		if($_only_file_name){
			return $_avatar['row'][0]['avatar'];
		}else{
			
			$_headurl = str_replace(array('/include','/admin','/ajax'),array('','',''),sc_get_headurl());
			return $_headurl.'include/avatar/'.$_avatar['row'][0]['avatar'];
		}
	}else{
		return -1;
	}
}

function sc_img_compress($_image,$_image_type,$_new_image_file,$_new_height,$_new_image_quality=75){
	switch ($_image_type){
		case 'jpg':
		case 'jpeg':
			$_origin_img = imagecreatefromjpeg($_image);
			break;
		case 'png':
			$_origin_img = imagecreatefrompng($_image);
			break;
		case 'gif':
			$_origin_img = imagecreatefromgif($_image);
			break;
		default:
			return false;
	}
	
	$_origin_width = imagesx($_origin_img);
	$_origin_height = imagesy($_origin_img);

	if($_origin_height>$_new_height){
		$_new_width=intval($_origin_width / $_origin_height * $_new_height);

		//建立新的圖
		$_new_image = imagecreatetruecolor($_new_width, $_new_height);

		//將原始照片縮小並複製到新的圖中
		imagecopyresized($_new_image, $_origin_img, 0, 0, 0, 0, $_new_width, $_new_height, $_origin_width, $_origin_height);
		imagejpeg($_new_image, $_new_image_file, $_new_image_quality);//輸出JPG圖片
		return true;
	
	}else{
		imagejpeg($_origin_img, $_new_image_file, $_new_image_quality);//輸出JPG圖片
		return true;
	}
}

function sc_avatar_crop($_image,$_image_type,$_new_image_file,$_new_size,$_crop_position_x,$_crop_position_y,$_crop_position_w,$_crop_position_h,$_new_image_quality=75){
	switch ($_image_type){
		case 'jpg':
		case 'jpeg':
			$_origin_img = imagecreatefromjpeg($_image);
			break;
		case 'png':
			$_origin_img = imagecreatefrompng($_image);
			break;
		case 'gif':
			$_origin_img = imagecreatefromgif($_image);
			break;
		default:
			return false;
	}
	
	$_new_image = imagecreatetruecolor($_new_size, $_new_size);

	if($_crop_position_w*$_crop_position_h<=0){
		$_origin_width = imagesx($_origin_img);
		$_origin_height = imagesy($_origin_img);
		$_crop_position_x=0;
		$_crop_position_y=0;
		$_crop_position_w=$_origin_width;
		$_crop_position_h=$_origin_height;
	}
	
	//將原始照片縮小並複製到新的圖中
	imagecopyresampled($_new_image, $_origin_img, 0, 0, $_crop_position_x, $_crop_position_y, $_new_size, $_new_size, $_crop_position_w, $_crop_position_h);
	
	if($_new_image_quality>0){
		imagejpeg($_new_image, $_new_image_file, $_new_image_quality);//輸出JPG圖片
	}else{
		imagepng($_new_image, $_new_image_file);//輸出PNG圖片
	}
	return true;
}

function lt_replace($str){ 
    return preg_replace("/<([^\/[:alpha:]])/", '&lt;\\1', $str); 
}

function sc_removal_escape_string($data){
	$data = lt_replace($data);
    return stripslashes($data);
}


function sc_captcha($_length=6,$_type=2){
	// type 為驗證碼字串的形式(是否包含英文數字...)
	$_text[0]=array(0,1,2,3,4,5,6,7,8,9);//純數字
	$_text[1]=array('a','b','c','d','e','f','g','h','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');//純英文
	$_text[2]=array('a','b','c','d','e','f','g','h','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z',2,3,4,5,6,7,8,9);//英文+數字(去除 L 、 1 、 O 跟 0)
	
	if($_length<=0||!isset($_text[$_type]))return false;
	
	
	$_array = $_text[$_type];
	
	$_captcha = '';
	for($_i = 0; $_i < $_length; $_i++){
		$_captcha .= $_array[mt_rand(0, count($_array) - 1)];
	}
	
	$_captcha = strtoupper($_captcha);
    $_SESSION['captcha'] = $_captcha;
}
