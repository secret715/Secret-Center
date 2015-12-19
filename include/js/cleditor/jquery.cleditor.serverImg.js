(function($){
	$.cleditor.buttons.serverImg = {
		name: "serverImg",
		title: "從文件夾選擇圖片",
		image: 'serverImgs.png',
		command: "insertimage",
		popupName: "從文件夾選擇圖片",
		popupHover: false,
		popupContent: '',
		buttonClick: function(e, data) {
			$(data.popup).width();
			$(data.popup).css('background-color', '#fff');
		},
		popupClick: function(e, data){
			data.value = $(e.target).attr('data-img');
		}
	};
	$.cleditor.defaultOptions.controls = $.cleditor.defaultOptions.controls.replace("image", "serverImg image");
	
	var ul = $('<ul class="file">');
	$.ajax({
		url: 'include/ajax/findImg.php',
		type: 'GET',
		dataType: 'json',
		async: false, //CLEditor bug - 不能動態改變Popup內容
		success: function(data){
			if(data.length > 0){
				$.each(data, function(i){
					$('<li>', {'data-img': data[i]}).append($('<img>', {src: data[i], 'data-img': data[i]})).appendTo(ul);
				});
				$.cleditor.buttons.serverImg.popupContent = ul;
			}
			else {
				$.cleditor.buttons.serverImg.popupContent = $('<div class="prompt">文件夾上沒有圖片！</div>');
			}
		},
		error: function(){
			$.cleditor.buttons.serverImg.popupContent = $('<div class="prompt error">伺服器錯誤！</div>');
		}
	});
})(jQuery);