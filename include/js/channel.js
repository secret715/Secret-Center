(function($,window){
	// AJAX Channel implantation
	// For real-time updates
    var Channel = function(endpoint){
		this.counter = 0;
		this.payload = null;
		this.endpoint = endpoint;
		this.handler = function(){};
		this.xhr = null;
	};
	
	Channel.prototype._start = function(){
		var self = this;
		this.counter++;
		var data = {
			e: this.counter
		};
		
		if(this.payload !== null){
			data.payload = this.payload;
		}
		
		this.xhr = $.ajax({
			url: this.endpoint,
			type: "GET",
			dataType: "json",
			data: data,
			cache: false,
			success:function(channel){
				if(channel.status == "DATA"){
					self.payload = channel.payload;
					self.handler(channel.data);
				}
				
				self._start();
			},
			error:function(){
				setTimeout(function(){
					self._start();
				}, 3000);
			}
		});
	};
	
	Channel.prototype.start = function(){
		this._start();
	};
	
	Channel.prototype.stop = function(){
		this.xhr.abort();
	};
	
	// Chat system
	var Chat = function(target,priv,interval){
		var self = this;
		this.target = $(target);
		this.sent = false;
		this.priv = priv || false;
		this.interval = interval || 3000;
		this.channel = new Channel((window.location.href.indexOf("/admin") > -1 ? "../" : "") + "include/ajax/chat.php?type=public");
		this.setup();
		
		this.channel.handler = function(data){
			self.update(data);
		};
		
		this.channel.start();
	};
	function scrolltoBtm(){
		$(".msg-pane").scrollTop(9999);
	}
	Chat.prototype.setup = function(){
		var self = this;
		this.msgPane = $('<div class="msg-pane"><div class="msg alert"><span class="c">沒有訊息！</span></div></div>');
		this.chat_form = $('<form><div class="controls-inline"><input name="data" type="text" id="data" maxlength="150" title="提示：按Enter鍵送出信息" class="input-xxlarge" />&nbsp;&nbsp;&nbsp;<em>提示：按Enter鍵送出信息</em></div></form>');
		
		this.target.append(this.msgPane);
		this.target.append(this.chat_form);
		
		this.chat_form.find('#data').on('keypress',function(e){
			if(e.which === 13){
				e.preventDefault();
				if(self.chat_form.find('#data').val()){
					$.ajax({
						url: window.location.href.indexOf("/admin") > -1 ? '../include/ajax/chat_receive.php' : 'include/ajax/chat_receive.php',
						type: 'POST',
						data: self.chat_form.serialize(),
						dataType: 'json',
						success:function(status){
							if(!status.success){
								window.alert("無法傳送訊息，請稍後再試。");
							}
							else {
								self.chat_form.find('#data').val('');
								if(!self.priv && !self.sent){
									self.sent = true;
									self.chat_form.find('#data').attr('disabled',true);
									self.chat_form.find('#data + em').text('提示：'+self.interval / 1000 + '秒後才可以繼續發言。');
									setTimeout(function(){
										self.sent = false;
										self.chat_form.find('#data').attr('disabled',false);
										self.chat_form.find('#data + em').text('提示：按Enter鍵送出信息');
									}, self.interval);
								}
								
								
								//self.channel.stop();
								//self.chnnel.start();
							}
						},
						error:function(){
							window.alert("無法傳送訊息，請稍後再試。");
						}
					});
					scrolltoBtm();
				}
				else {
					window.alert("您必需填寫所有欄位。");
				}
			}
		});
	};
	
	Chat.prototype.update = function(data){
		var self = this;
		this.msgPane.find('.msg.alert').remove();
		$.each(data,function(i,ele){
			var template = $("<div>").addClass("msg");
			template.append($('<span>',{text:ele.name}).addClass("n"));
			template.append($('<span>').html(ele.data).addClass("c"));
			template.append($('<span>',{text:ele.ptime}).addClass("t"));
			self.msgPane.append(template);
		});
		scrolltoBtm();
	};
	
	//Expose as globals
	window.Channel = Channel;
	window.Chat = Chat;
})(jQuery,window);