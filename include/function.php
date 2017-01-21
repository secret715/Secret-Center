<?php
function sc_ver(){
	return '9.1';
}

function sc_keygen($_value=''){
	return str_shuffle(base64_encode(mt_rand(100,999).time()).sha1(mt_rand().md5($_value).uniqid()));
}
function sc_login($_username,$_password){
	global $SQL;
	if (isset($_username)&&isset($_password)) {
		$login = $SQL->query("SELECT `id`,`username`, `password`, `level` FROM `member` WHERE (`username` = '%s' OR `email` = '%s') AND `password` = '%s'",array(
			$_username,
			$_username,
			sc_password($_password,$_username)
		));
		
		
		//[相容] 7.3 版以前密碼----開始
		if ($login->num_rows <1) {
			$login = $SQL->query("SELECT `id`,`username`, `password`, `level` FROM `member` WHERE (`username` = '%s' OR `email` = '%s') AND `password` = '%s'",array(
			$_username,
			$_username,
			md5(sha1($_password))
			));
			if($login->num_rows > 0){
				$SQL->query("UPDATE `member` SET `password` = '%s' WHERE `username` = '%s'",array(sc_password($_password,$_username),$_username));
			}
		}//[相容] 7.3 版以前密碼----結束
		
		
		if ($login->num_rows > 0) {
			$info = $login->fetch_assoc();
			
			$SQL->query("UPDATE `member` SET `last_login` = now() WHERE `username` = '%s'",array($info['username']));
			
			$_SESSION['Center_Username'] = strtolower($_username);
			$_SESSION['Center_Id'] = $info['id'];
			$_SESSION['Center_UserGroup'] = $info['level'];	      
			setcookie("login", time(), time()+10800);
			return 1;
		}
		else {
			return -1;
		}
	}
}
function sc_loginout(){
	$_SESSION['Center_Username'] = NULL;
	$_SESSION['Center_Id'] = NULL;
	$_SESSION['Center_UserGroup'] = NULL;
	unset($_SESSION['Center_Username']);
	unset($_SESSION['Center_Id']);
	unset($_SESSION['Center_UserGroup']);
	setcookie("login", "", time()-10800);
	return 1;
}

function sc_register($_username,$_password,$_email,$_web_site='',$_level=1){
	global $SQL;
	global $center;
	if($center['register'] == 1){
		if(isset($_username) && (trim(sc_namefilter($_username)) != '') && isset($_password) && (trim($_password) != '')&& filter_var($_email, FILTER_VALIDATE_EMAIL)){
			if($_web_site!='' && !filter_var($_web_site, FILTER_VALIDATE_URL)){
				return -2;
			}
			
			$_username=sc_namefilter($_username);
			
			$auth_name = $SQL->query("SELECT `username` FROM `member` WHERE `username` = '%s' OR `email` = '%s'", array($_username,$_email));
			if($auth_name->num_rows > 0){
				return -1;
				exit;
			}
			
			$SQL->query("INSERT INTO `member` (`username`, `password`, `email`, `web_site`, `avatar`, `rekey`, `level` , `joined` ,`last_login`) VALUES ('%s', '%s', '%s', '%s', 'default.png', '%s', '%d', now(), now())",array(
				sc_namefilter($_username),
				sc_password($_password,$_username),
				$_email,
				$_web_site,
				substr(sc_keygen($_username),0,16),
				$_level
			));
			
			return 1;
		}else{
			return -2;
		}
	}else{
		return -3;
	}
}

function sc_get_result($_query,$_value=array()){
	global $SQL;
	$_result['query'] = $SQL->query($_query,$_value);
	$_result['row'] = $_result['query']->fetch_assoc();
	$_result['num_rows'] = $_result['query']->num_rows;
	
	return $_result;
}

function sc_member_level_array(){
	return array(0=>'禁言',1=>'一般會員',2=>'進階會員',3=>'高級會員',9=>'管理員');
}

function sc_member_level($_level){
	$_level_array=sc_member_level_array();
	return $_level_array[$_level];
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
	}
	$url="$_prefix://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
	$po= strripos($url,'/');
	return substr($url,0,$po).'/';
}

function sc_add_notice($_url,$_content,$_send_from,$_send_to){
	global $SQL;
	$SQL->query("INSERT INTO `notice` ( `url`,`content`, `status`, `send_from`,`send_to`,`mktime`) VALUES ('%s','%s',0,'%d','%d',now())",array($_url,$_content,$_send_from,$_send_to));
	return 1;
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
	global $SQL;
	$SQL->query("INSERT INTO `forum` (`title`, `content`,`block`, `level`, `mktime`, `author`) VALUES ('%s', '%s','%d', '%d', now(),'%d')",array(
		htmlspecialchars($_title),
		sc_xss_filter($_content),
		abs($_block),
		abs($_level),
		abs($_id)
	));
	return 1;
}
function sc_add_forum_block($_blockname,$_position=0){
	global $SQL;
	$SQL->query("INSERT INTO `forum_block` (`blockname`, `position`, `mktime`) VALUES ('%s', '%d', now())",array(sc_namefilter($_blockname),abs($_position)));
	return 1;
}

function sc_tag_member($_content,$_notice_url,$_notice_content,$_id){
	preg_match_all('/@[A-Za-z0-9]{0,30}/',$_content,$_tag);
	foreach ($_tag[0] as $_v){
		$_member = sc_get_result("SELECT `id` FROM `member` WHERE `username` = '%s'",array(ltrim($_v,'@')));
		sc_add_notice($_notice_url,$_notice_content,$_id,$_member['row']['id']);
	}
	return 1;
}
function sc_avatar_url($_id,$_only_file_name=false){
	$_avatar = sc_get_result("SELECT `avatar` FROM `member` WHERE `id` = '%s'",array(abs($_id)));
	if($_avatar['num_rows']>0){
		if($_only_file_name){
			return $_avatar['row']['avatar'];
		}else{
			
			$_headurl = rtrim(rtrim(rtrim(sc_get_headurl(),'/include'),'/admin'),'/ajax').'/';
			return $_headurl.'include/avatar/'.$_avatar['row']['avatar'];
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
function sc_deletedir($dir) {
    if ($handle = opendir($dir)) {
        while(false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir($dir."/".$item)) {
                    deletedir($dir."/".$item);
                } else {
                    unlink($dir."/".$item);
                }
            }
        }    
        closedir($handle);
        rmdir($dir);
      }
}

function sc_page_pagination($_href,$_now_page,$_data_num,$_page_limit,$_href_parameters=''){
	$_return='';
	$_now_page=abs($_now_page);
	$page_num= ceil($_data_num / $_page_limit);
	if($page_num>=7){
		$_return.='<ul class="pagination">';
		$_array=array(1,2,3,$_now_page-2,$_now_page-1,$_now_page,$_now_page+1,$_now_page+2,$page_num-2,$page_num-1,$page_num);
		$_array=array_unique($_array);
		$_last_value=0;
		foreach ($_array as $value){
			if($value>0&&$value<=$page_num){
				if($_last_value+1!=$value){
					$_return.='<li><span>...</span></li>';
				}
				if($_now_page==$value){
					$_return.='<li class="active"><span>'.$value.'</span></li>';
				}else{
					$_return.='<li><a href="'.$_href.'?page='.$value.$_href_parameters.'">'.$value.'</a></li>';
				}
				$_last_value=$value;
			}
		}
		$_return.='</ul>';
	}elseif($page_num>1){
		$_return.='<ul class="pagination">';
		for($i=1;$i<=$page_num;$i++){
			if($_now_page!=$i){
				$_return.='<li><a href="'.$_href.'?page='.$i.$_href_parameters.'">'.$i.'</a></li>';
			}else{
				$_return.='<li class="active"><span>'.$i.'</span></li>';
			}
		}
		$_return.='</ul>';
	}
	return $_return;
}


function lt_replace($str){ 
    return preg_replace("/<([^\/[:alpha:]])/", '&lt;\\1', $str); 
}

function sc_removal_escape_string($data){
	$data = lt_replace($data);
    return stripslashes($data);
}