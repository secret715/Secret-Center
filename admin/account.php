<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(5,'../index.php');


$view = new View('theme/admin_default.html',$center['site_name'],'編輯帳號','admin/nav.php');
$view->addCSS("../include/css/form.css");
$view->addScript('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js');
$view->addScript('https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js');
$view->addCSS('https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css');
$view->addScript('../include/js/register.js');
?>
<script>
auth="<?php echo sc_csrf() ?>";
id="<?php echo intval($_GET['id']); ?>";
$(function(){
	if(typeof(id)!="undefined"){
		id = '&id='+id;
	}else{
		id='';
	}
	$('#joined').datetimepicker({
      locale: 'zh-tw',//選擇語系
	  viewMode: 'times',
	  minDate: false,
	  maxDate: moment().add(1, 'hours'),
	  useCurrent: true,
	  dayViewHeaderFormat:'YYYY 年 MMMM'
   });
	$.ajax({
		url: (window.location.href.indexOf("/admin") > -1 ? '../include/ajax/account.php?':'include/ajax/account.php?')+auth+id,
		type: 'GET',
		data:{data:true},
		dataType: 'json',
		success:function(data){
			for(i=0;i<data.length;i++){
				if(data[i][1]!=null){
					if($('#'+data[i][0]).is('select')){
						$('#'+data[i][0] + ' option:contains('+data[i][1].replace(/<br.*>/, '')+')').prop('selected',true);
					}else{
						$('#'+data[i][0]).val(data[i][1].replace(/<br.*>/, ''));
						if(data[i][0]=='username'){
							username=data[i][1];
							$('#username:not([readonly])').off('blur'); 
							$('#username:not([readonly])').on('blur',function(){
								if($(this).val()!=username){
									auth_username();
								}
							}); 
						}else if(data[i][0]=='avatar'){
							$('#'+data[i][0]).attr({'src':data[i][1].replace(/<br.*>/, '')});
						}
	
					}
				}
				
			}
		},
		error:function(){
			alert('發生錯誤，請重新整理！');
		}
	});
	$('#member-data').submit(function(e){
		e.preventDefault();
		$('input[name="authpassword"]').trigger('blur');
		if($('#member-data input[type=submit]').prop('disabled'))return;
		if($(this).find('input.is-invalid').length>0){
			alert('請再次確認您的資料是否正確');
			return;
		}
		$.ajax({
			url: (window.location.href.indexOf("/admin") > -1 ? '../':'')+'include/ajax/account.php?update&'+auth+id,
			type: 'POST',
			data:$('#member-data').serialize(),
			dataType: 'json',
			beforeSend:function(){
				$('#member-data input[type=submit]').prop('disabled',true);
			},
			success:function(data){
				if(data.status){
					window.history.back();
					alert('修改成功');
				}else{
					alert('發生錯誤，請重新整理！');
				}
			},
			error:function(){
				alert('連線發生錯誤，請重新整理！');
			},
			complete:function(){
				$('#member-data input[type=submit]').prop('disabled',false);
			}
		});
	});

	
	$('.delete').click(function(e){
		if(!window.confirm("確定刪除此會員？")){
			e.preventDefault();
		}else{
			$.ajax({
				url: (window.location.href.indexOf("/admin") > -1 ? '../':'')+'include/ajax/account.php?delete&'+auth+id,
				type: 'POST',
				dataType: 'json',
				beforeSend:function(){
					$('.delete').prop('disabled',true);
				},
				success:function(data){
					if(data.status){
						window.history.back();
						alert('刪除成功');
					}else{
						alert('發生錯誤，請重新整理！');
					}
				},
				error:function(){
					alert('連線發生錯誤，請重新整理！');
				},
				complete:function(){
					$('.delete').prop('disabled',false);
				}
			});
		}
	});
});
</script>
<?php if(isset($_GET['ok'])){?>
<div class="alert alert-success">修改成功！</div>
<?php } ?>
<h2 class="page-header">編輯帳號</h2>
	<form id="member-data">
	<div class="row">
		<div class="col-sm-3 text-center">
			<img id="avatar" class="avatar">
		</div>
		<div class="col-sm-9">
			<div class="form-label-group">
				<input name="username" id="username" type="text" class="form-control-plaintext" maxlength="20" required="required" placeholder="帳號" readonly >
				<label for="username">帳號</label>
			</div>
			<div class="form-label-group">
				<input name="nickname" id="nickname" type="text" class="form-control" maxlength="20" required="required" placeholder="暱稱">
				<label for="nickname">姓名</label>
			</div>
			<div class="form-label-group">
			<input class="form-control" id="password" name="password" type="password" maxlength="30" placeholder="密碼">
				<label for="password">密碼</label>
			</div>
			<div class="form-label-group">
			<input class="form-control" id="authpassword" name="authpassword" type="password" maxlength="30" placeholder="確認密碼">
				<label for="authpassword">確認密碼</label>
			</div>
			<div class="form-label-group">
			<input class="form-control" id="email" name="email" type="email" maxlength="255" required="required" placeholder="電子信箱">
				<label for="email">電子信箱</label>
			</div>
			<div class="form-label-group">
				<select name="level" id="level" class="form-control" required>
					<?php foreach (sc_member_level_array() as $k=>$v){ ?>
					<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
					<?php } ?> 
				</select>
			</div>
			<div class="form-label-group">
				<input type="text" id="remark" name="remark" class="form-control" placeholder="個人網站">
				<label for="remark">備註</label>
			</div>
			<div class="form-group row">
				<div class="col-sm-offset-3 col-9 col-md-10">
					<input class="btn btn-success btn-lg" type="submit" value="確認修改">
					<?php if($_SESSION['center']['id']!=$_GET['id']){ ?><input class="btn btn-danger btn-sm delete" type="button" value="刪除此會員"><?php } ?>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
	$view->render();
?>