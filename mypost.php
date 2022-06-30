<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

if(isset($_GET['del'])&& abs($_GET['del'])!='' && isset($_GET[sc_csrf()])){
	$_del[] = sprintf("DELETE FROM `forum` WHERE `id` = '%d' AND `author`='%d'",abs($_GET['del']),$_SESSION['center']['id']);
    $_del[] = sprintf("DELETE FROM `forum_reply` WHERE post_id = '%d'",abs($_GET['del']));
    foreach($_del as $val){
		$SQL->query($val);
	}
	$_GET['delok']=true;
}


$view = new View('include/theme/default.html',$center['site_name'],'我的文章','include/nav.php');
$view->addScript('https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js');
$view->addScript('https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js');
$view->addCSS('https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css');
$view->addScript("include/js/notice.js");
?>
<script>
$(function(){ 
	var auth='<?php echo sc_csrf(); ?>';
	$.ajax({
		url:  (window.location.href.indexOf("/admin") > -1 ? '../':'')+ 'include/ajax/forum.php?member&'+auth,
		type: 'POST',
		dataType: 'json',
		cache: false,
		success: function(data) {
	
			$('.loading').remove();
			if(data!=''&&data.length>0){
				$('table tbody').html('');
				$('.blockname').text(data.blockname);
				$.each(data,function(i,v){
					var tr=$('<tr>');
					$('<td>',{'html':'<a href="forumview.php?id='+v['id']+'">'+v['title']+'<small class="badge badge-secondary ml-2">'+v['level']+'</small></a>'}).appendTo(tr);
					//$('<td>',{'text':v['nickname']}).appendTo(tr);
					$('<td>',{'html':'<small>'+v['mktime']+'</small>'}).appendTo(tr);
					$('<td>',{'text':v['reply_num']}).appendTo(tr);
					var last_replay='無';
					if(v['reply_num']>0){
						last_replay='<span style="font-size:0.8rem">'+v['last_reply']['author_nickname']+'<br>'+v['last_reply']['mktime']+'';
					}
					$('<td>',{'html':last_replay,'style':'line-height:0.8rem;'}).appendTo(tr);
					$('<td>',{'html':'<a href="forum.php?fid='+v['block']+'">'+v['blockname']+'</a>'}).appendTo(tr);
					$('<td>',{'html':'<a class="btn btn-info btn-sm" href="forumedit.php?post&id='+v['id']+'&mypost">編輯</a><a class="btn btn-danger btn-sm" href="?del='+v['id']+'&'+auth+'">刪除</a>'}).appendTo(tr);
					tr.appendTo('table tbody');
				});
			}else{
				$('table').after('<div class="alert alert-success">沒有文章！趕快去<a href="forum.php?newpost">發表文章</a>吧。</div>');
				$('table').remove();
			}
		}
	}).done(function(){
		$('table').DataTable({ 
			pageLength:30,
			paging:false,
			language: {
				url: 'include/js/datatable/datatable.lang.tw.json'
			},
			order: [[ 1, "desc" ]]
		});
	});
});
</script>
<?php if(isset($_GET['delok'])){?>
	<div class="alert alert-success">刪除成功！</div>
<?php } ?>
<h2 class="page-header">我的文章</h2>
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>文章名稱</th>
			<th>發表時間</th>
			<th>回覆</th>
			<th>最後回覆</th>
			<th>區塊</th>
			<th>管理</th>
		</tr>
	</thead>
	<tbody>
    </tbody>
</table>
<?php
$view->render();
?>