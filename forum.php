<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

$view = new View('include/theme/default.html',$center['site_name'],'論壇','include/nav.php');
$view->addScript('https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js');
$view->addScript('https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js');
$view->addCSS('https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css');
$view->addScript("include/js/notice.js");
?>
<style>

@media screen and (max-width: 767px) {
 div.dataTables_wrapper div.dataTables_length,
 div.dataTables_wrapper div.dataTables_filter,
 div.dataTables_wrapper div.dataTables_info,
 div.dataTables_wrapper div.dataTables_paginate {
		text-align:left;
	}
	.dataTables_filter{
		text-align:right !important;
	}
}
</style>
<?php if(isset($_GET['success'])){?>
	<div class="alert alert-success">發佈成功！</div>
<?php }elseif(isset($_GET['captcha'])){ ?>
	<div class="alert alert-danger">請檢查驗證碼！</div>
<?php }elseif(isset($_GET['banned'])){ ?>
	<div class="alert alert-danger">您被禁言無法發文！</div>
<?php }elseif(isset($_GET['level'])){ ?>
	<div class="alert alert-danger">權限不足！</div>
<?php }
if(isset($_GET['fid'])){
	$_GET['fid']=intval($_GET['fid']);
?>
<script>
$(function(){
	 $.getJSON('include/ajax/forum.php?fid=<?php echo $_GET['fid'];?>',function(data){
		$('.loading').remove();
		$('.table-responsive').removeClass('d-none');
		if(data!=''){
			$('table tbody').html('');
			$('.blockname').text(data.blockname);
			if(data.post){
				$.each(data.post,function(i,v){
					var tr=$('<tr>');
					$('<td>',{'html':'<a href="forumview.php?id='+v['id']+'">'+v['title']+'<small class="badge badge-secondary ml-2">'+v['level']+'</small></a>'}).appendTo(tr);
					$('<td>',{'text':v['nickname']}).appendTo(tr);
					$('<td>',{'html':'<small>'+v['mktime']+'</small>'}).appendTo(tr);
					$('<td>',{'text':v['reply_num']}).appendTo(tr);
					var last_replay='無';
					if(v['reply_num']>0){
						last_replay='<span style="font-size:0.8rem">'+v['last_reply']['author_nickname']+'<br>'+v['last_reply']['mktime']+'';
					}
					$('<td>',{'html':last_replay,'style':'line-height:0.8rem;'}).appendTo(tr);
					tr.appendTo('table tbody');
				});
			}else{
				$('table').after('<div class="alert alert-danger">沒有文章！</div>');
				$('table').remove();
			}
		}
	}).done(function(){
		$('table').DataTable({ 
			searching: false,
			pageLength:30,
			paging:false,
			stateSave: true,
			language: {
				url: 'include/js/datatable/datatable.lang.tw.json'
			},
			order: [[ 2, "desc" ]]
		});
	});
});
</script>
<ol class="breadcrumb">
	<li class="breadcrumb-item"><a href="forum.php">論壇</a></li>
	<li class="breadcrumb-item active"><a class="blockname" href="forum.php?fid=<?php echo $_GET['fid']; ?>"></a></li>
</ol>
<div class="row">
	<div class="col-md-9 col-sm-8">
		<h2 class="page-header">
			<span class="blockname"></span>
			<a href="forumedit.php?block=<?php echo $_GET['fid']; ?>&new" class="btn btn-sm btn-primary">發表文章</a>
		</h2>
	</div>
	<div class="col-md-3 col-sm-4 text-right">
		<form id="search" class="form-inline" method="GET" action="forumsearch.php">
			<div class="input-group">
				<input id="q" class="form-control" name="q" type="text" class="search-query" required="required">
				<div class="input-group-append">
					<button class="btn btn-outline-secondary" onclick="if(document.getElementById('q').value!=''){document.getElementById('search').submit();}"><span class="material-icons" style="font-size:1rem;">search</span></button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="loading text-center">
	<div class="spinner-border"></div><br><p>讀取中</p>
</div>
<div class="table-responsive d-none">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>標題</th>
				<th>作者</th>
				<th>發表時間</th>
				<th>回覆</th>
				<th>最後回覆</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<?php
}else{ ?>

<script>
$(function(){
	 $.getJSON('include/ajax/forum.php',function(data){
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
				url: 'include/js/datatable/datatable.lang.tw.json'
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
<?php } ?>
<?php
$view->render();
?>