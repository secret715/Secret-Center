<?php
require_once('Connections/SQL.php');
require_once('config.php');
require_once('include/view.php');

if(!isset($_SESSION['Center_Username'])){
	header("Location: index.php?login");
	exit;
}

$view = new View('view/new_theme.html','include/nav.php',$center['site_name'],'文件夾');
$view->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js");
$view->addScript("include/js/channel.js");
$view->addScript("include/js/notice.js");
$view->addScript("include/js/fileupload/jquery.ui.widget.js");
$view->addScript("include/js/fileupload/jquery.iframe-transport.js");
$view->addScript("include/js/fileupload/jquery.fileupload.js");

if(!is_dir('include/file/' . $_SESSION['Center_Username'] . '/')){
	@mkdir('include/file/' . $_SESSION['Center_Username'] .'/');
}

if(isset($_FILES['files']) && $_FILES['files']['error'] == 0){
	$dir = 'include/file/'.$_SESSION['Center_Username'];
	
	if(!is_dir($dir.'/')) {
		if(!mkdir($dir)){
			echo '{"status":"error","msg":"上傳目錄不存在，並且創建失敗"}';
			exit;
		}
	}
	
	$filequantity = count(glob($dir . "/*.*"));
	$_extend = pathinfo($_FILES['files']['name'], PATHINFO_EXTENSION);
	$_size = $_FILES['files']['size'];
	
	if($filequantity >= $center['file']['max_files']){
		echo '{"status":"error","msg":"文件數量已達最高上限"}';
		exit;
	}
	if(!in_array($_extend,$center['file']['limitedext'])){
		echo '{"status":"error","msg":"不允許此檔案格式"}';
		exit;
	}
	if($_size>$center['file']['max_size']*1000){
		echo '{"status":"error","msg":"檔案大小超過限制"}';
		exit;
	}
	move_uploaded_file($_FILES['files']['tmp_name'],$dir.'/'.sc_namefilter($_FILES['files']['name']));
	
	echo '{"status":"success"}';
	exit;
}
?>
<script>
$(function(){
	$(document.body).on('dblclick','.alert',function(){
		$(this).remove();
	});
	$('.file li').on('dblclick contextmenu',function(e){
		e.preventDefault();
		var file = $(this).attr('data-file');
		$('.context-menu span a').each(function(){
			$(this).attr('data-file',encodeURIComponent(file));
		});
		$('.context-menu').css({
			top: e.pageY,
			left: e.pageX
		}).fadeIn(200);
	});
	$('.context-menu span a').on('click',function(e){
		e.preventDefault();
		var file = $(this).attr('data-file');
		if($(this).attr('data-action')=='delete'){
			if(window.confirm('確定刪除?')){
				$('.main').prepend('<div class="loading alert alert-info">正在刪除中...</div>');
				$.ajax({
					url:"include/ajax/file.php?del="+file+"&"+ Math.random(),
					dataType: 'text',
					success: function(data){
						if(data==1){
							$('.loading').remove();
							$('.file').find("[data-file='"+file+"']").remove();
							$('.main').prepend('<div class="alert alert-success">成功刪除'+file+'！</div>');
							$('#file_count').text($('#file_count').text()-1);
						}
					}
				});
			}
		} else if($(this).attr('data-action')=='rename'){
			$('.renaming').hide();
			$('#rename').modal('show');
			$('#rename .modal-body p').text('您正準備重新命名：'+ $(this).attr('data-file'));
			$('#newname').val($(this).attr('data-file').substring(0,$(this).attr('data-file').lastIndexOf('.')));
			$('#file_ext').text($(this).attr('data-file').substring($(this).attr('data-file').lastIndexOf('.')));
			$('#rename .btn.btn-success').on('click',function(e){
				e.preventDefault();
				if($('#newname').val()!=''){
					$('.renaming').show();
					$.ajax({
						url:"include/ajax/file.php?rename="+file+"&newname="+encodeURIComponent($('#newname').val())+'&'+ Math.random(),
						dataType: 'json',
						success: function(data){
							if(data!=''){
								$('.renaming').removeClass('alert-info').addClass('alert-success').text('重新命名成功！');
								$('.file').find("[data-file='"+file+"']").attr('data-file',data.file).find('a').attr('href',data.url).text(data.file);
								setTimeout(function(){
									$('#rename').modal('hide');
									$('.renaming').removeClass('alert-success').addClass('alert-info').text('處理中，請稍後...');
									$('.renaming').hide();
									},1500
								);
							}
						}
					});
				}
			});
		}
	});
	$(':not(.context-menu)').on('click',function(){
		$('.context-menu').fadeOut(200);
	});
	
	var max_file_size=<?php echo $center['file']['max_size']; ?>*1000;
	var limitedext=['<?php echo implode("','",$center['file']['limitedext']); ?>'];
	
	$('#fileupload').fileupload({
		dropZone: $('#drop'),
		url: 'file.php',
		dataType: 'json',
		add: function (e, data) {
	            var tpl = $('<div class="uploading"><ul class="inline"><li class="cancel"><i class="icon icon-remove"></i></li><li class="pro"></li><li class="info"></li></ul></div>');
	            tpl.find('.info').text(data.files[0].name);
	            data.context = tpl.appendTo($('.item'));
				
				$('#progress .bar').width(0).text('');
				
				var extend=data.files[0].name.split('.').pop();
				
				for(var i=0;i<limitedext.length;i++){
					if(limitedext[i]==extend){
						var in_array = true;
						break;
					}else{
						var in_array = false;
					}
				}
				
				var error=0;
				if(in_array!=true){
					alert(data.files[0].name+' 不允許此格式');
					error=1;
				}
				if(data.files[0].size>max_file_size){
					alert(data.files[0].name+' 的大小過大');
					error=1;
				}
				if(error==0){
					var jqXHR = data.submit();
				}else{
					tpl.remove();
				}
				
	            tpl.find('.cancel').click(function(){
	                tpl.fadeOut(function(){
						if(tpl.hasClass('uploading')){
							jqXHR.abort();  //終止上傳
						}
	                    tpl.remove();
	                });
	            });
	        },

	        //單一檔案進度
	        progress: function(e, data){
	            var progress = parseInt(data.loaded / data.total * 100, 10);
	            data.context.find('.pro').text(progress+'%').change();
	            if(progress == 100){
	                data.context.addClass('uploaded').removeClass('uploading');
	            }
	        },

			//總進度
			progressall: function (e, data) {
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('#progress .bar').css('width', progress + '%');
				$('#progress .bar').text(progress + '%');
			},

	        //上傳失敗
	        fail:function(e, data){
	            data.context.addClass('error');
				data.context.find('.pro').text();
	        },

	        //單一檔案上傳完成
	        done: function (e, data) {
				if(data.result.status=='error'){
					data.context.addClass('error');
					data.context.find('.pro').text(data.result.msg);
				}
	        },

	        //全部上傳完畢
	        /*stop: function (e) {
				window.document.location='file.php?uploading';
	        }*/
    });
});
</script>
<div class="main">
	<h2>文件夾</h2>
	<?php if(isset($_GET['upload'])){
		$dir = 'include/file/'.$_SESSION['Center_Username'];
		$filequantity = count(glob($dir . "/*.*"));
		if($filequantity >= $center['file']['max_files']){
	?>
		<div class="alert alert-error">文件數量已達最高上限！</div>
	<?php }else{ ?>
		<div class="remarks">檔案上傳相關規定：
			<ol>
				<li>禁止上傳違法的檔案、圖片</li>
				<li>檔案大小限制：<?php echo floor($center['file']['max_size']); ?> KB</li>
				<li>允許的檔案類型：<?php echo implode(",",$center['file']['limitedext']); ?></li>
			</ol>
		</div>
		<div id="uploader">
			<div id="drop" class="well">
				<p>將檔案拖曳到這裡</p>
				<input type="file" id="fileupload" name="files" multiple>
			</div>
			總進度
			<div id="progress" class="progress progress-success" style="margin:0 auto;width: 50%;">
				<div class="bar" style="width: 0%;"></div>
			</div>
			<div class="item"></div>
		</div>
	<?php }
	} else {
		if(isset($_GET['uploading'])){
	?>
		<div class="alert alert-success">檔案上傳成功！</div>
	<?php }
		$dir = 'include/file/'.$_SESSION['Center_Username'];
		$filequantity = count(glob($dir."/*.*"));
		
		if(!is_dir($dir.'/')) {      //檢查會員資料夾是否存在
			if(!mkdir($dir)){  //不存在的話就創建會員資料夾
				die ("上傳目錄不存在，並且創建失敗");
			}
		}
		
		$handle = @opendir($dir) or die("無法打開" . $dir);
		
		if($filequantity >= $center['file']['max_files']){
			echo '<div class="alert alert-error">文件數量已達最高上限！</div>';
		}
		
		echo "<strong>在 " . $_SESSION['Center_Username'] . " 文件夾中的檔案</strong>，目前有<span id='file_count'>" . $filequantity . "</span>個文件，上限".$center['file']['max_files'] ."個！<br>";
	?>
		<ul class="file">
	<?php
		while($file = readdir($handle)){
			if($file != "." && $file != ".."){ 
			$myfile = $dir."/".$file;
			$extend = pathinfo($myfile, PATHINFO_EXTENSION);//取得副檔名
	?>
		<li data-file="<?php echo $file ?>">
			<img src="./images/file.png">
			<a href="<?php echo $myfile ?>" target="_blank"><?php echo $file ?></a><br>
			<span style="font-size:84%;">
			<?php
				$ext = array('位元組','KB','MB','GB','TB','PB','EB','YB');
				$size = filesize($myfile);
				$i = floor(log($size,1000));
				echo sprintf('%.2f '.$ext[$i], ($size/pow(1000, floor($i))));
			?>
			</span>
		</li>
	<?php }
	}
	?>
		<br class="clearfix" />
	</ul>
	<div class="context-menu">
		<span><a data-action="delete"><i class="icon-trash"></i>刪除</a></span>
		<span><a data-action="rename"><i class="icon-pencil"></i>重新命名</a></span>
	</div>
	<?php
	clearstatcache();
	closedir($handle);
	}?>
	<div id="rename" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3>重新命名</h3>
		</div>
		<div class="modal-body">
			<div class="renaming alert alert-info">處理中，請稍後...</div>
			<p></p>
			<input id="newname" name="newname" type="text" class="input-xlarge" maxlength="25">
			<span id="file_ext"></span>
		</div>
		<div class="modal-footer">
			<span class="btn btn-success"><i class="icon-ok icon-white"></i> 確定修改</span>
			<span class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i> 關閉</span>
		</div>
	</div>
</div>
<?php
	$view->render();
?>