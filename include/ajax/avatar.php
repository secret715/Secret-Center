<?php
require_once('../../config.php');

if(!sc_level_auth(-1))die;

if(!sc_csrf_auth())die;

if(isset($_GET['step']) && sc_csrf_auth()) {
	$return=array();
	$SQL=sc_db_conn();
	if(sc_level_auth(5)&&isset($_GET['id'])){
		$member=$SQL->select('member',array('id'=>intval($_GET['id'])));
	}else{
		$member=$SQL->select('member',array('id'=>$_SESSION['center']['id']));
	}
	$dir='../../include/avatar/';
	try {
	
		if(!is_dir($dir)) {	//檢查頭貼資料夾是否存在
			if(!mkdir($dir)){
				throw new Exception("頭貼資料夾不存在，並且建立失敗");
			}
		}
		if($_GET['step'] == 1 && isset($_FILES['upload'])){
			if($_FILES['upload']['name'] != "" && is_uploaded_file($_FILES['upload']['tmp_name'])){
				if(!isset($_FILES['upload']['error'])||($_FILES['upload']['error'] > 0)){
					$_GET['step']=false;
					throw new Exception("檔案上傳失敗");
				}
				
				if(  $center['avatar']['max_size'] < $_FILES['upload']['size'] / 1000){
					throw new Exception("檔案大小超出限制");
				}
				
				$limitedext = array('jpeg','jpg','gif','png');//允許的副檔名
				$extend = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));//檔案副檔名
				$new_name=substr(sc_keygen(),0,10);//檔案名(不含副檔名)
				$file=$new_name.'.'.$extend;
				
				if(!in_array($extend,$limitedext)){
					throw new Exception("不允許此檔案格式");
				}
				if(move_uploaded_file($_FILES['upload']['tmp_name'],$dir.$file)){//複製檔案
				
					if($member[0]['avatar']!=''&&$member[0]['avatar']!=$file){
						unlink($dir.$member[0]['avatar']);//刪除舊頭貼
					}
					
					$SQL->query("UPDATE `member` SET `avatar` = '%s' WHERE `id` = '%s'",array(
						$file,
						$member[0]['id']
					));
					if($member[0]['id']==$_SESSION['center']['id'])$_SESSION['center']['avatar']=$file;
			
					$return['status']=true;
				}else{
					throw new Exception("上傳檔案複製失敗");
				}
				
			} else {
				throw new Exception("你沒有上傳任何相片");
			}
		}elseif($_GET['step'] == 3 && isset($_POST['x'])&& isset($_POST['y'])&& isset($_POST['w'])&& isset($_POST['h'])&&$member[0]['avatar']!='default.png'){
			$_POST['x']=intval($_POST['x']);
			$_POST['y']=intval($_POST['y']);
			$_POST['w']=intval($_POST['w']);
			$_POST['h']=intval($_POST['h']);
			
			$file=pathinfo($member[0]['avatar'],PATHINFO_FILENAME).'.jpg';
			
			sc_avatar_crop($dir.$member[0]['avatar'],pathinfo($member[0]['avatar'], PATHINFO_EXTENSION),$dir.$file,300,$_POST['x'],$_POST['y'],$_POST['w'],$_POST['h'],70);
			
			if($member[0]['avatar']!='default.png'&&$member[0]['avatar']!=$file){
				unlink($dir.$member[0]['avatar']);//刪除舊頭貼
			}
			
			$SQL->query("UPDATE `member` SET `avatar` = '%s' WHERE `id` = '%s'",array(
				$file,
				$member[0]['id']
			));
			if($member[0]['id']==$_SESSION['center']['id'])$_SESSION['center']['avatar']=$file;
			
			$return['status']=true;
		}else{
			throw new Exception("發生錯誤");
		}
		
	}
	catch(Exception $e){
		$return['status']=false;
		$return['error']=$e->getMessage();
	}
	echo json_encode($return);
}