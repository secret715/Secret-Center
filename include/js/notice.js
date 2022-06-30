(function($,window){
	var sc_notice = function(){
		var self = this;
		$('.avatar-menu .dropdown-item:eq(0)').after('<a id="notifs-button" href="#" class="dropdown-item"><i class="material-icons">notifications_none</i>通知<span id="num">0</span></a>');
		$('#main > .content').append('<div id="notifs"></div>');
		$('#notifs').html('<h4>通知</h4><div class="alert alert-success text-center">載入中...</div>');
		this.count();
		$("#notifs-button").click(function(e){
			e.preventDefault();
			$("#notifs").fadeToggle(300);
			$("#notifs").css({
				top: $('#main').offset().top + 5,
				left: $('#notifs-button').offset().left + $('#notifs-button').outerWidth() - $('#notifs').outerWidth()
			});
			if($('.navbar-toggler').is(':visible'))$('.navbar-toggler').click();
			if($('#notifs').is(':visible')){
				var last = window.last || 0 ;
				self.load(last);
				$('#main').one('click',function(){
					$("#notifs").fadeToggle(300);
				});
			}
		});
	}
	sc_notice.prototype.count = function(last_count){
		var self = this;
		var last_count = last_count || 0;
		$.ajax({
			url:  window.location.href.indexOf("/admin") > -1 ? '../include/ajax/notice.php' : 'include/ajax/notice.php',
			type: 'POST',
			data:{last_count: last_count},
			dataType: 'json',
			cache: false,
			timeout: 20000,
			error : function(){
				self.count(last_count);
			},
			success: function(data) {
				if(data.count>0){
					$('#num').addClass('new').text(data.count);
				}else if(data.count==0){
					$('#num').removeClass('new').text(0);
				}else if(data.count==-1){
					alert('登入超時');
					location.href='index.php';
				}
				self.count(data.count);
			}
		});
	}
	sc_notice.prototype.load = function(last){
		var self = this;
		var last = last || 0;
		$.ajax({
			url:  window.location.href.indexOf("/admin") > -1 ? '../include/ajax/notice.php' : 'include/ajax/notice.php',
			type: 'POST',
			data:{last: last},
			dataType: 'json',
			cache: false,
			success: function(data) {
				$('#notifs').find('.alert').remove();
				$('#notifs notifications').removeClass('new');
				$.each(data.data,function(i,e){
					var item = $('<div>').addClass('notifications');
					if(e.status==0){
						item.addClass('new');
					}
					item.append($('<img>',{'src':'include/avatar/'+e.send_from_avatar}).addClass('avatar avatar-sm'));
					item.append($('<span>',{text:e.mktime}).addClass('time'));
					item.append($('<p>').html($('<a>',{'href':e.url,text:e.content})));
					$('#notifs h4').after(item);
				});
				window.last=data.last;
			}
		});
	}
	window.sc_notice = sc_notice;
})(jQuery,window);

$(function(){
	new sc_notice();
});