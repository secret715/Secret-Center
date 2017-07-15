(function($,window){
	var sc_chat = function(target,interval,auth){
		var self = this;
		target = $(target);
		interval = interval || 3000;
		sent = false;
		msg_panel = $('<div class="msg-panel"><div class="alert alert-danger">沒有訊息！</div></div>');
		chat_form = $('<form><div class="form-group"><input class="form-control" name="content" type="text" maxlength="255" style="min-width:300px;width:calc(95% - 180px);display:inline-block;" title="提示：按Enter鍵送出" /><span class="hidden-xs text-muted">&nbsp;&nbsp;<em>提示：按Enter鍵送出</em></span></div></form>');
		target.append(msg_panel);
		target.append(chat_form);
		
		chat_form.find('.form-control').on('keypress',function(e){
			if(e.which === 13){
				e.preventDefault();
				if(chat_form.find('.form-control').val()){
					$.ajax({
						url: window.location.href.indexOf("/admin") > -1 ? '../include/ajax/chat.php?sent&'+auth : 'include/ajax/chat.php?sent&'+auth,
						type: 'POST',
						data: chat_form.serialize(),
						dataType: 'json',
						success:function(status){
							if(!status.success){
								window.alert("無法傳送訊息，請稍後再試。");
							} else {
								chat_form.find('.form-control').val('');
								if(!sent){
									sent = true;
									chat_form.find('.form-control').attr('disabled',true);
									chat_form.find('.form-control + span em').text('提示：'+interval / 1000 + '秒後才可以繼續發言。');
									setTimeout(function(){
										sent = false;
										chat_form.find('.form-control').attr('disabled',false);
										chat_form.find('.form-control + span em').text('提示：按Enter鍵送出');
									}, interval);
								}
							}
						},
						error:function(){
							window.alert("無法傳送訊息，請稍後再試。");
						}
					});
				} else {
					window.alert("您必需填寫所有欄位。");
				}
			}
		});	
		
		this.load();
		this.scroll_to_btm();
	}
	sc_chat.prototype.load = function(last){
		var self = this;
		var last = last || 0;
		$.ajax({
			url:  window.location.href.indexOf("/admin") > -1 ? '../include/ajax/chat.php' : 'include/ajax/chat.php',
			type: 'POST',
			data:{last: last},
			dataType: 'json',
			cache: false,
			timeout: 20000,
			error : function(){
				self.load(last);
			},
			success: function(data) {
				msg_panel.find('.alert').remove();
				$.each(data.data,function(i,e){
					var msg = $("<div>").addClass("msg");
					msg.append($('<span>',{text:e.author}).addClass("chat-author"));
					msg.append($('<span>').html(e.content).addClass("chat-content"));
					msg.append($('<span>',{text:e.mktime}).addClass("chat-mktime"));
					msg_panel.append(msg);
				});
				self.load(data.last);
				self.scroll_to_btm();
			}
		});
	}
	sc_chat.prototype.scroll_to_btm = function(){
		msg_panel.scrollTop(9999);
	}
	window.sc_chat = sc_chat;
})(jQuery,window);