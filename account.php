<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

$view = new View('include/theme/default.html',$center['site_name'],'我的帳號','include/nav.php');
$view->addCSS("include/css/form.css");
$view->addScript("include/js/notice.js");
$view->addScript('include/js/register.js');
$view->addScript("include/js/qrcode.min.js");
?>
<style>
.qrcode{
	max-width:100% !important;
	-webkit-user-select: none;
	user-select: none; 
}
.qrcode img{
	width:120px;
}
</style>
<script>
auth="<?php echo sc_csrf() ?>";
function new_qrcode(el,size,text){
	el=$(el);
	var qrcode = new QRCode(el.find('.qrcode')[0], {
		text: text,
		width: size,
		height: size,
		colorDark : "#000000",
		colorLight : "#ffffff",
		correctLevel : QRCode.CorrectLevel.Q
	});
	var box_pos=el.position();
	var box_size=el.outerWidth();
	el.append('<div class="cover"></div>');

	$('.cover').css({'-webkit-user-select':'none','user-select':'none','position': 'absolute','top':box_pos.top,'left':box_pos.left,'height':box_size,'width':box_size});
}
$(function(){
	if(typeof(id)!="undefined"){
		id = '&id='+id;
	}else{
		id='';
	}
	$.ajax({
		url: (window.location.href.indexOf("/admin") > -1 ? '../include/ajax/account.php?':'include/ajax/account.php?')+auth+id,
		type: 'GET',
		data:{data:true},
		dataType: 'json',
		success:function(data){
			for(i=0;i<data.length;i++){
				switch(data[i][0]){
					case 'avatar':
						$('#'+data[i][0]).attr({'src':data[i][1].replace(/<br.*>/, '')});
						continue;
						break;
					case 'qrcode':
						key=data[i][1];
						break;
					case 'id':
						auth=data[i][1];
						break;
				}
				
				if(data[i][1]!=null)$('#'+data[i][0]).val(data[i][1].replace(/<br.*>/, ''));
			}
			new_qrcode("#qrcode",128,'{"key":"'+key+'","auth":"'+auth+'"}');
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
			url: (window.location.href.indexOf("/admin") > -1 ? '../':'')+'include/ajax/account.php?update&'+auth,
			type: 'POST',
			data:$('#member-data').serialize(),
			dataType: 'json',
			beforeSend:function(){
				$('#member-data input[type=submit]').prop('disabled',true);
			},
			success:function(data){
				if(data.status){
					alert('修改成功');
				}else{
					alert('發生錯誤，請重新整理！');
				}
			},
			error:function(){
				alert('發生錯誤，請重新整理！');
			},
			complete:function(){
				$('#member-data input[type=submit]').prop('disabled',false);
			}
		});
	});
});
</script>
<?php if(isset($_GET['ok'])){?>
<div class="alert alert-success">修改成功！</div>
<?php } ?>
<h2 class="page-header">我的帳號</h2>
	<form id="member-data">
	<div class="row">
		<div class="col-sm-3 text-center">
			<img id="avatar" class="avatar-lg img-thumbnail">
			<p><a class="btn btn-secondary btn-rounded mt-3" href="avatar.php">修改頭貼</a></p>
			<hr>
			<div id="qrcode" class="p-2 bg-white d-inline-block">
				<div class="qrcode"></div>
			</div>
			<p>會員條碼</p>
		</div>
		<div class="col-sm-9">
			<div class="form-label-group">
				<input name="username" id="username" type="text" class="form-control-plaintext" maxlength="20" required="required" placeholder="帳號" readonly >
				<label for="username">帳號</label>
			</div>
			<div class="form-label-group">
				<input name="nickname" id="nickname" type="text" class="form-control-plaintext" maxlength="20" required="required" placeholder="暱稱" readonly >
				<label for="nickname">暱稱</label>
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
				<input name="level" id="level" type="text" class="form-control-plaintext" required="required" placeholder="權限" readonly >
				<label for="level">權限</label>
			</div>
			<div class="form-label-group">
				<input name="joined" id="joined" type="text" class="form-control-plaintext" required="required" placeholder="註冊日期" readonly >
				<label for="joined">註冊日期</label>
			</div>
			<div class="form-group row">
				<div class="col-sm-offset-3 col-9 col-md-10">
					<input class="btn btn-success" type="submit" value="確認修改">
				</div>
			</div>
		</div>
	</div>
</form>
<?php
	$view->render();
?>