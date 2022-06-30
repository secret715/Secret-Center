<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(9,'../index.php');

$view = new View('theme/admin_default.html',$center['site_name'],'論壇管理','admin/nav.php');
?>
<?php if(isset($_GET['del'])){ ?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<?php if(isset($_GET['fid'])){ ?>
<ul class="breadcrumb">
	<li><a href="forum.php">論壇</a></li>
	<li class="active"><a href="forum.php?fid=<?php echo $_block['row']['id']; ?>"><?php echo $_block['row']['blockname']; ?></a></li>
</ul>
<h2 class="page-header"><?php echo $_block['row']['blockname']; ?></h2>
<script>
$(function(){
	$('input.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除所選取的文章？\n\n提醒您，該文章的回覆也會一並刪除！")){
			e.preventDefault();
		}
	});
});
</script>

<h2 class="page-header">論壇</h2>
<form class="form-inline" method="POST" action="forum.php?newblock&<?php echo $_SESSION['Center_Auth']; ?>">
	<div class="input-group">
		<input class="form-control" name="blockname" type="text" placeholder="區塊名稱" required="required">
		<span class="input-group-btn">
			<input type="submit" class="btn btn-success" value="新增區塊">
		</span>
	</div>
</form>
<script>
$(function(){
	$('a.btn.btn-danger').click(function(e){
		if(!window.confirm("確定刪除所選取的區域？\n\n提醒您，該區塊的文章也會一並刪除！")){
			e.preventDefault();
		}
	});
});
$(function(){
	 $.getJSON('../include/ajax/forum.php',function(data){
		$('.loading').remove();
		if(data!=''&&data.length>0){
			$('table tbody').html('');
			$.each(data,function(i,v){
				var tr=$('<tr>');
				$('<td>',{'html':'<a href="forum.php?fid='+v['id']+'">'+v['blockname']+'</a>'}).appendTo(tr);
				$('<td>',{'text':v['post_num']}).appendTo(tr);
				$('<td>',{'text':v['last_post']}).appendTo(tr);
				tr.appendTo('table tbody');
			});
		}else{
			$('table').remove().after('<div class="alert alert-danger">沒有區塊！</div>');
		}
	}).done(function(){
		$('table').DataTable({
			pageLength:30,
			paging:false,
			stateSave: true,
			language: {
				url: '../include/js/datatable/datatable.lang.tw.json'
			},
			order: [[ 2, "desc" ]]
		});
	});
});
</script>
<h2 class="page-header">論壇</h2>
<div class="loading text-center">
	<div class="spinner-border"></div><br><p>讀取中</p>
</div>
<div class="table-responsive">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>區塊</th>
				<th>文章數</th>
				<th>最後發文</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
    </table>
</div>
<?php
}
$view->render();
?>