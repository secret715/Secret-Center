/*
<Secret Center, open source member management system>
Copyright (C) 2012-2017 Secret Center開發團隊 <http://center.gdsecret.net/#team>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, version 3.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Also add information on how to contact you by electronic and paper mail.

  If your software can interact with users remotely through a computer
network, you should also make sure that it provides a way for users to
get its source.  For example, if your program is a web application, its
interface could display a "Source" link that leads users to an archive
of the code.  There are many ways you could offer source, and different
solutions will be better for different programs; see section 13 for the
specific requirements.

  You should also get your employer (if you work as a programmer) or school,
if any, to sign a "copyright disclaimer" for the program, if necessary.
For more information on this, and how to apply and follow the GNU AGPL, see
<http://www.gnu.org/licenses/>.
*/

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