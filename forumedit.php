<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

if(isset($_GET['new'])){
	if(!sc_level_auth(0)){
		header("Location: forum.php?banned");
		exit;
	}
}

if(isset($_GET['id'])){
	$_GET['id']=abs(intval($_GET['id']));
}

$_GET['block']=isset($_GET['block']) ? abs(intval($_GET['block'])):0;

if(isset($_POST['title']) && isset($_POST['content']) && isset($_POST['block']) && isset($_POST['level']) && trim(htmlspecialchars($_POST['title'])) != '' && trim($_POST['content'],"&nbsp;") != '' && sc_csrf_auth()) {
	$_POST['block']=abs(intval($_POST['block']));
	if($center['forum']['captcha']==1){
		if(strtoupper($_POST['captcha']) != strtoupper($_SESSION['captcha'])){
			setcookie('content',$_POST['content'],time()+600);
			header("Location: forum.php?newpost&captcha");
			exit;
		}
	}
	
	$_block=sc_get_result("SELECT * FROM `forum_block` WHERE `id`='%d'",array($_POST['block']));
	if($_block['num_rows']>0){
		sc_add_forum_post($_POST['title'],$_POST['content'],$_POST['block'],$_SESSION['center']['id'],$_POST['level']);
		header("Location: forum.php?success&fid=".$_POST['block']);
		exit;
	}
}






$view = new View('include/theme/default.html',$center['site_name'],'新增','include/nav.php');
$view->addScript("include/js/notice.js");
$view->addCSS("include/js/summernote/summernote-bs4.css");
$view->addScript("include/js/summernote/summernote-bs4.min.js");
$view->addScript("include/js/summernote/lang/summernote-zh-TW.min.js");
?>

<script>
$(function(){;
	$('.captcha').on('click', function(e){
		e.preventDefault();
		$(this).attr('src', 'include/captcha.php?_=' + (new Date).getTime());
	});
	
	$("#summernote").summernote({width:'99%', height:400, focus: true, lang: 'zh-TW',toolbar: [
	  ['misc',['undo','redo']],
  ['style', ['style']],
  ['fontname', ['fontname','fontsize']],
  ['font', ['bold', 'underline', 'clear']],
  ['color', ['color']],
  ['para', ['ul', 'ol', 'paragraph']],
  ['table', ['table']],
  ['insert', ['link', 'picture', 'video']],
  ['view', ['fullscreen', 'codeview', 'help']],
]});
	<?php if(isset($_GET['id'])){  ?>
		$('.page-header').text('修改文章');
		$.ajax({
			url:  window.location.href.indexOf("/admin") > -1 ? '../include/ajax/forumedit.php' : 'include/ajax/forumedit.php',
			type: 'GET',
			data:{"<?php echo sc_csrf(); ?>": true,"pid":<?php echo $_GET['id']; ?>},
			dataType: 'json',
			cache: false,
			success: function(data) {
				if(data!=''){
					$.each(data,function(i,v){
						if(i=='content'){
							$('#summernote').summernote('pasteHTML', v);
							return;
						}
						$('*[name='+i+']').val(v);
					});
				}else{
					alert('無法編輯。');
				}
			},error:function(){
				alert('文章不存在！無法編輯。');
			},complete:function(){	
				$('#loading').addClass('d-none');
				$('form').removeClass('d-none');
			}
		});

		$('form').submit(function(e){
			e.preventDefault();
			$.ajax({
				url:  (window.location.href.indexOf("/admin") > -1 ? '../':'') +'include/ajax/forumedit.php?<?php echo sc_csrf(); ?>&pid=<?php echo $_GET['id']; ?>&edit',
				type: 'POST',
				data:$('form').serialize(),
				dataType: 'json',
				cache: false,
				beforeSend: function(){
					$('button[type="submit"]').prop('disabled',true).prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
				},
				success: function(data) {
					if(data.status){
						alert('編輯成功！');
						history.back();
					}else{
						alert('編輯異常。');
					}
				},error:function(){
					alert('編輯異常，請洽詢問處。');
				},
				complete:function(){
					$('button[type="submit"]').prop('disabled',false).find('.spinner-border').remove();
				}
			});
		});
	<?php }else{ ?>
		$('form').removeClass('d-none');
		$('#loading').addClass('d-none');
	<?php } ?>
});
</script>
<h2 class="page-header">發表文章</h2>
<form action="?newpost&<?php echo sc_csrf(); ?>" method="POST" class="d-none">
	<div class="form-group">
		<input class="form-control" name="title" type="text" placeholder="標題" required="required">
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<label for="block">區塊：</label>
				<select class="form-control" name="block" required="required">
				<?php 
				$_block = sc_get_result("SELECT * FROM `forum_block`");
				foreach($_block['row'] as $v){ 
					
					?>
					<option value="<?php echo $v['id']; ?>" <?php if($v['id']==$_GET['block']){ ?>selected="selected"<?php } ?>>
						<?php echo $v['blockname']; ?>
					</option>
				<?php }  ?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<label for="level">權限：</label>
				<select class="form-control" name="level" required="required">
				<?php foreach(sc_member_level_array() as $key=>$value){if($key>0){ ?>
					<option value="<?php echo $key; ?>" <?php if($key==1){ ?>selected="selected"<?php } ?>><?php echo $value; ?></option>
				<?php }} ?>
				</select>
			</div>
		</div>
	</div>
	<div class="form-group">
		<textarea id="summernote" name="content" rows="10" required="required">
			<?php
			if(isset($_COOKIE['content'])){
				echo $_COOKIE['content'];
				setcookie('content','',time()-600);
			}
			?>
		</textarea>
	</div>
	<?php if($center['forum']['captcha']==1){ ?>
	<div class="form-group">
		<label for="captcha">驗證碼：</label>
		<img src="include/captcha.php" class="captcha" title="按圖更換驗證碼"/>
		<input id="captcha" class="form-control" name="captcha" type="text" size="10" maxlength="10" required="required">
	</div>
	<?php } ?>
	<br>
	<button class="btn btn-primary" type="submit">送出</button>
</form>
<div id="loading" class="text-center m-5">
	<div class="spinner-border"></div><br><p>讀取中</p>
</div>
<?php
$view->render();
?>