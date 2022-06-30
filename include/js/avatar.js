$(function(){
	allow_type=['png','jpg','jpeg','gif','PNG','JPG','JPEG','GIF'];
	max_file_size=2000000;
	if(typeof(id)!="undefined"){
		id = '&id='+id;
	}else{
		id='';
	}
	$('#upload').fileupload({
		url: (window.location.href.indexOf("/admin") > -1 ? '../include/ajax/avatar.php':'include/ajax/avatar.php')+'?step=1&'+auth+id,
		dataType: 'json',
		add: function (e, data) {
			
				try {
					var upload_file = $('<div id="avatar-file" class="text-info">'+data.files[0].name+'</div><div class="upload-progress text-success"></div>').insertAfter('#upload');
					
					var extend=data.files[0].name.split('.').pop();
					
					var in_array = false;
					for(var i=0;i<allow_type.length;i++){
						if(allow_type[i]==extend){
							in_array = true;
							break;
						}
					}
					
					if(in_array==false){
						throw data.files[0].name+' 不允許此格式';
					}
					if(data.files[0].size>max_file_size){
						throw data.files[0].name+' 的大小過大';
					}
					
					data.submit();
					
				}catch (e) {
					upload_file.remove();
					alert(e) ;
				}
			},

			//單一檔案進度
			progress: function(e, data){
				var progress = parseInt(data.loaded / data.total * 100, 10);
				$('.upload-progress').text(progress+'%');
				if(progress == 100){
					$('#upload').addClass('d-none');
					$('.upload-progress').text('完成');
				}
			},

			//上傳失敗
			fail:function(e, data){
				$('.upload-progress').text('失敗');
			},

			//單一檔案上傳完成
			done: function (e, data) {
				if(data.result.status=='error'){
					$('.upload-progress').text(data.result.msg);
				}else{
					location.href='?step=2'+id;
				}
			}
	});

	
	
	$('#jcrop').Jcrop({aspectRatio: 1, minSize: [100,100],boxWidth: 360, onSelect: updateCoords});
	range=[];
	function updateCoords(c){
		range['x']=c.x;
		range['y']=c.y;
		range['w']=c.w;
		range['h']=c.h;
	}
	
	$('#crop').one('click',function(){
		$.ajax({
			url: (window.location.href.indexOf("/admin") > -1 ? '../include/ajax/avatar.php':'include/ajax/avatar.php')+'?step=3&'+auth+id,
			type: 'POST',
			data: {x:range['x'],y:range['y'],w:range['w'],h:range['h']},
			dataType: 'json',
			success:function(data){
				if(data.status){
					alert('更換成功');
					location.href="?a"+id;
				}
			},
			error:function(){
				alert('發生錯誤，請洽詢問處！');
			}
		});
	});
});