<?php
set_include_path('../include/');
$includepath = true;

require_once('../config.php');
require_once('view.php');

sc_level_auth(9,'../index.php');

if(isset($_GET['logout'])){
	sc_loginout();
	header("Location: ../index.php?out");
	exit;
}

$view = new View('theme/admin_default.html',$center['site_name'],'會員管理','admin/nav.php');
$view->addScript('https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js');
$view->addScript('https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js');
$view->addCSS('https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css');
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
	.table-responsive  div.dataTables_wrapper{
		min-width:180vw !important;
	}
}
</style>
<h2 class="page-header">會員管理</h2>
<script>
$(function(){
	auth='<?php echo sc_csrf(); ?>';
	$.ajax({
		url: '../include/ajax/member.php?'+auth,
		type: 'GET',
		cache:false,
		dataType: 'json',
		success:function(data){
			
			$('.loading').remove();
			if(data!=''){
				$('table tbody').html('');
				if(data.member){
					$.each(data.member,function(i,v){
						var tr=$('<tr>',{'data-id':v['id']});
						$('<td>',{'html':'<a href="account.php?id='+v['id']+'">'+v['username']+'<!--small class="badge badge-secondary ml-2">'+v['level']+'</small--></a>'}).appendTo(tr);
						$('<td>',{'text':v['nickname']}).appendTo(tr);
						$('<td>',{'html':'<small>'+v['email']+'</small>'}).appendTo(tr);
						$('<td>',{'html':'<small>'+v['last_login']+'</small>'}).appendTo(tr);
						$('<td>',{'text':v['level']}).appendTo(tr);
						$('<td>',{'html':'<small>'+v['remark']+'</small>'}).appendTo(tr);
						tr.appendTo('table tbody');
					});
				}else{
					$('table').after('<div class="alert alert-danger">沒有帳號！</div>');
					$('table').remove();
				}
			}
		}
	}).done(function(){
		$('table').DataTable({ 
			searching: true,
			pageLength:100,
			paging:false,
			stateSave: true,
			language: {
				url: '../include/js/datatable/datatable.lang.tw.json'
			},
			order: [[ 1, "asc" ]]
		});
	});
});
</script>
<div class="loading text-center">
	<div class="spinner-border"></div><br><p>讀取中</p>
</div>
<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th>帳號</th>
				<th>暱稱</th>
				<th>E-mail</th>
				<th>最後登入</th>
				<th>權限</th>
				<th>備註</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<?php
$view->render();
?>