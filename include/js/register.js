function auth_password(){
	if($('#password').val()!=$('#authpassword').val()){
		$('#authpassword').addClass('is-invalid');
		//$('input[type="submit"]').prop('disabled',true);
		return false;
	}else{
		//$('input[type="submit"]').prop('disabled',false);
		$('#authpassword').removeClass('is-invalid'); 
		return true;
	}
 }
 function auth_username(){
	 $.ajax({
		 url: (window.location.href.indexOf("/admin") > -1 ? '../include/ajax/account.php':'include/ajax/account.php')+'?'+auth,
		 type: 'POST',
		 data: {auth:true,'auth_username':$('#username').val()},
		 dataType: 'json',
		 success:function(data){
			 if(data!=''&&data.user_exist){
				 $('#username').addClass('is-invalid');
				 alert('此帳號已經被使用，換一個吧！');
				 return false;
			 }else{
				 $('#username').removeClass('is-invalid');
				 return true;
			 }
		 },
		 error:function(){
			 alert('發生錯誤，請重新整理！');
		 }
	 });
 }
$(function(){
	$('#username:not([readonly])').on('blur',function(){
		auth_username();
	}); 
	$('#authpassword').on('blur',function(){
		auth_password();
	}); 
	
	$('.captcha').on('click', function(e){
		e.preventDefault();
		$(this).attr('src', 'include/captcha.php?_=' + (new Date).getTime());
	});

	
	$('#register').submit(function(e){
		if(!auth_password()){
		    e.preventDefault();
		    alert('密碼不一致，請再次確認'); 
			$('#authpassword').val('');
		}
		if($(this).find('input.is-invalid').length>0){
		    e.preventDefault();
			alert('請再次確認您的資料是否正確');
		}
   });


});