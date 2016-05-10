(function($,window){
	var sc_notice = function(){
		var self = this;
		$('.content').prepend('<span id="notifs-button"><a href="#">通知</a><span id="num">0</span></span><div id="notifs"></div>');
		$('#notifs').html('<h4>通知</h4><div class="alert alert-success text-center">載入中...</div>');
		this.count();
		$("#notifs-button").click(function(e){
			e.preventDefault();
			$("#notifs").fadeToggle(300);
			$("#notifs").css({
				top: $('#notifs-button').offset().top + $('#notifs-button').outerHeight(),
				left: $('#notifs-button').offset().left + $('#notifs-button').outerWidth() - $('#notifs').outerWidth()
			});
			if($('#notifs').is(':visible')){
				var last = window.last || 0 ;
				self.load(last);
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
				}else{
					$('#num').removeClass('new').text(0);
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