<?php
require_once('config.php');
require_once('include/view.php');

if(isset($_POST['username'])&&sc_csrf_auth()){
	if(sc_login($_POST['username'],$_POST['password'])==1){
		header("Location: member.php?login");
	}else{
		header("Location: index.php?login");
	}
	die();
}
else if(isset($_GET['logout'])) {
	sc_loginout();
}

$view = new View('include/theme/login.html',$center['site_name'],'會員登入','include/nav.php');
?>
<script>  //初始化
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '334094547467968',
      cookie     : true,
      xfbml      : true,
      version    : 'v5.0'
    });
    //記錄用戶行為資料 可在後台查看用戶資訊
    FB.AppEvents.logPageView();   
  };//嵌入臉書sdk
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
 
  $(function() {
    //點擊登入按鈕
    $("#fb-login").click(function() {
      //檢查臉書登入狀態
      FB.getLoginStatus(function(response) {
        //如果已經有授權過應用程式
        if (response.authResponse) {
          //呼叫FB.api()取得使用者資料
          FB.api('/me',{fields: 'id,name,email'}, function (response) {
			  //這邊就可以判斷取得資料跟網站使用者資料是否一致
			  console.log(response);
          });
        //沒授權過應用程式
        } else {
          //呼叫FB.login()請求使用者授權
          FB.login(function (response) {
            if (response.authResponse) {
              FB.api('/me',{fields: 'id,name,email'}, function (response) {
                console.log(response);
                console.log("a"); //這邊就可以判斷取得資料跟網站使用者資料是否一致
              });
            }
          //FB.login()預設只會回傳基本的授權資料
          //如果想取得額外的授權資料需要另外設定在scope參數裡面
          //可以設定的授權資料可以參考官方文件          
          }, { scope: 'email,user_likes' });
        }
      });
    });
  });
</script>
<form class="form-signin" action="index.php?<?php echo sc_csrf(); ?>" method="POST">
<?php 

switch(true){
	case isset($_GET['reg']):
?>
	<div class="alert alert-success">註冊成功！</div>
<?php
	break;
	case isset($_GET['out']):
?>
	<div class="alert alert-success">您已經登出！</div>
<?php
	break;
	case isset($_GET['getpassword']):
?>
	<div class="alert alert-success">密碼重設成功！</div>
<?php
	break;
	case isset($_GET['login']):
?>
	<div class="alert alert-danger">登入失敗</div>
<?php
	break;
} ?>
<h1>登入 <?php echo $center['site_name']; ?></h1>
	<div class="form-label-group">
	    <input type="text" id="username" name="username" class="form-control" placeholder="帳號" pattern="[a-zA-Z0-9]{1,30}" required>
  		<label for="username">帳號</label>
	</div>
	<div class="form-label-group">
	<input class="form-control" id="password" name="password" type="password" maxlength="30" required="required" placeholder="密碼">
		<label for="password">密碼</label>
	</div>
	<div class="form-group">
			<input class="btn btn-primary" type="submit" value="登入">
			<a href="register.php" class="btn btn-info">註冊</a>
			<a href="#" id="fb-login" class="btn btn-info">FB登入</a>
			<a href="getpassword.php">忘了您的密碼？</a>
	</div>
</form>
<?php
	$view->render();
?>