$(function(){
	function notice(){
		$('#notifs').html('<h4>通知</h4><div id="loading" class="alert alert-success text-center">載入中...</div>');
		$.ajax({
			url:window.location.href.indexOf("/admin") > -1 ? "../include/ajax/notice.php?"+ Math.random() : "include/ajax/notice.php?"+ Math.random(),
			dataType: 'json',
			success: function(data){
				if(data['error']!=1){
					$('#loading').hide();
					var notice_length=data.length;
					for (var i=0;i<notice_length; i++) {
						if(data[i]['status']!=1){
							var notice_new=' new';
						}else{
							var notice_new='';
						}
						$('#notifs').append('<div class="notifications'+notice_new+'"><img src="include/avatar.php?id='+data[i]['send_from']+'" class="avatar avatar-mini"><a href="'+data[i]['url']+'"><span class="time">'+data[i]['ptime']+'</span><p>'+data[i]['content']+'</p></a><br class="clearfix"></div>');
					}
				}else{
					$('#loading').removeClass('alert-success').addClass('alert-info').text('沒有訊息');
				}
			}
		});
	}
	function unread(){
		$.ajax({
			url:window.location.href.indexOf("/admin") > -1 ? "../include/ajax/notice.php?unread&"+ Math.random() : "include/ajax/notice.php?unread&"+ Math.random(),
			dataType: 'text',
			success: function(data){
				if(data>0){
					if($('#num').text()!=data){
						$('#num').addClass('new').text(data);
						if($('#notifs').is(":visible")){
							notice();
						}
					}
				}else{
					if($('#num').text()!=0){
						$('#notifications').removeClass('new');
					}
				}
			}
		});
	}
	function read(){
		if($('#num').text()>0){
			$.ajax({
				url:window.location.href.indexOf("/admin") > -1 ? "../include/ajax/notice.php?read&"+ Math.random() : "include/ajax/notice.php?read&"+ Math.random()
			});
			$('#num').removeClass('new').text(0);
			$('#notifications').removeClass('new');
		}
	}

	$("#notifs-button").click(function(e){
		e.preventDefault();
		if(!$('#notifs').is(":visible")){
			if($('#num').text()>0 || $('#notifs').text().length==0){
				notice();
			}
		}else{
			read();
		}
		$("#notifs").fadeToggle(300);
		$("#notifs").css({
			top: $('#notifs-button').offset().top + $('#notifs-button').outerHeight(),
			left: $('#notifs-button').offset().left + $('#notifs-button').outerWidth() - $('#notifs').outerWidth()
		});
	});

	setInterval(function(){
		unread();
	},3000);
	unread();
	$('.content').before('<div id="notifs"></div>');
	$('#notifs-button').html('<a href="#">通知</a><span id="num">0</span>').show();
});