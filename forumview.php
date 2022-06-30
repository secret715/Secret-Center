<?php
require_once('config.php');
require_once('include/view.php');

sc_level_auth(-1,'index.php?n');

if((!isset($_GET['id']))or($_GET['id']=='')){
    header("Location: forum.php");
	exit;
}else{
	$_GET['id']=intval($_GET['id']);
}

$_post = sc_get_result("SELECT `forum`.`id`,`forum`.`title`,`forum`.`content`,`forum`.`block`,`forum`.`level`,`forum`.`mktime`,`forum`.`author`,`member`.`nickname`   FROM `forum`,`member` WHERE `forum`.`id` = '%d' and `forum`.`author`=`member`.`id`",array($_GET['id']));

if($_post['num_rows']<=0){
	header("Location: forum.php");
	exit;
}

if(!sc_level_auth($_post['row'][0]['level'])){
	header("Location: forum.php?level&fid=".$_post['row'][0]['block']);
	exit;
}

if(isset($_GET['reply'])){
	if($_SESSION['center']['level']==0){
		header("Location: forumview.php?banned&id=".$_GET['id']);
		exit;
	}
}
if((isset($_GET['reply']))&& isset($_POST['content']) && trim($_POST['content'],"&nbsp;") != ''&&sc_csrf_auth()){
	$SQL=sc_db_conn();
	$SQL->query("INSERT INTO `forum_reply` ( `post_id`,`content`, `mktime`, `author`) VALUES ('%s','%s',now(),'%d')",array(
		$_post['row'][0]['id'],
		sc_xss_filter($_POST['content']),
		$_SESSION['center']['id']
	));
	
	if($_SESSION['center']['id']!=$_post['row'][0]['author']){
		 sc_add_notice(
			sc_get_headurl().'forumview.php?id='.$_post['row'][0]['id'],
			$_SESSION['center']['username'].'在您的文章中發表回覆',
			$_SESSION['center']['id'],
			$_post['row'][0]['author']
		);
	}
	sc_tag_member(
		sc_xss_filter($_POST['content']),
		sc_get_headurl().'forumview.php?id='.$_post['row'][0]['id'],
		$_SESSION['center']['username'].'在論壇提到你',
		$_SESSION['center']['id']
	);
	header("Location: forumview.php?success&id=".$_GET['id']);
	exit;
}


$_block = sc_get_result("SELECT * FROM `forum_block` WHERE `id`='%d'",array($_post['row'][0]['block']));

$view = new View('include/theme/default.html',$center['site_name'],$_post['row'][0]['title'],'include/nav.php');
$view->addScript("include/js/notice.js");
$view->addCSS("include/js/summernote/summernote-bs4.css");
$view->addScript("include/js/summernote/summernote-bs4.min.js");
$view->addScript("include/js/summernote/lang/summernote-zh-TW.min.js");
?>
<?php if(isset($_GET['success'])){?>
	<div class="alert alert-success">回覆成功！</div>
<?php }elseif(isset($_GET['editok'])){ ?>
	<div class="alert alert-success">編輯成功！</div>
<?php }elseif(isset($_GET['banned'])){ ?>
	<div class="alert alert-danger">您被禁言無法發文！</div>
<?php } ?>
<script>
$(function(){
	function pagination_item(i,active){
		var active = active || '';
		active = active ? ' active': '';
		return '<div class="page-item'+active+'"><a class="page-link" href="?id=<?php echo $_GET['id'];?>&page='+i+'">'+i+'</a></div>';
	}
	var url = new URL(location.href);
	var now_page=(url.searchParams.get('page') == null) ? 1 : url.searchParams.get('page');

	$.getJSON('include/ajax/forum.php?pid=<?php echo $_GET['id'];?>&page='+now_page,function(data){
		$('.loading').remove();
		if(data!=''&&data['reply'].length>0){
			if(data['limit']<data['reply_num']){
				$('#pagination').removeClass('d-none');
				page_total=Math.ceil(data['reply_num']/data['limit']);
				if(page_total<12){
					for(i = 1 ;i<=page_total;i++){
						$('#pagination > ul').append(pagination_item(i,(i==data['page'])));
					}
				}else{
					center=data['page'];
					if(data['page']===page_total)center=Math.ceil(data['page']/2);
					arr=[1,2,3,center-1,center,parseInt(center)+1,page_total-2,page_total-1,page_total];
					
					last_val=0;
					$.each(Array.from(new Set(arr)), function( i, v ) {
						if(v>page_total||v<=0)return;
						if(last_val!=v-1)$('#pagination > ul').append('<li class="page-item disabled"><a class="page-link" href="#">...</a></li>');
						$('#pagination > ul').append(pagination_item(v,(v==data['page'])));
						last_val=v;
					});
				}
			}
			$.each(data['reply'],function(i,v){
				content=$('#reply-simple').html();
				content=content.replace(/\$id/gi,v['id']);
				content=content.replace(/\$avatar/gi,v['avatar']);
				content=content.replace(/\$content/gi,v['content']);
				content=content.replace(/\$mktime/gi,v['mktime']);
				content=content.replace(/\$nickname/gi,v['nickname']);
				content=content.replace(/\$floor/gi,v['floor']);

				$('.content > .post:last').after(content);
				if(!v['own']){
					$('.content > .post:last .own').remove();
				}

			});
			
		}else{
			$('.post:eq(0)').after('<div class="alert alert-danger">沒有回覆！</div>');
		}
	
	});



	$("#summernote").summernote({width:'99%', height:300, focus: true, lang: 'zh-TW',toolbar: [
	  ['misc',['undo','redo']],
  ['style', ['style']],
  ['fontname', ['fontname','fontsize']],
  ['font', ['bold', 'underline', 'clear']],
  ['color', ['color']],
  ['para', ['ul', 'ol', 'paragraph']],
  ['table', ['table']],
  ['insert', ['link', 'picture', 'video']],
  ['view', ['fullscreen', 'codeview', 'help']],
]});
	
	$(document.body).on('click','.info a[data-reply-id]',function(e){
		e.preventDefault();
		self=$(this);
		rid=$(this).attr('data-reply-id');

		$('#summernote').summernote('reset');
		$('#summernote').summernote('code', $(this).parents('.info').siblings('.content').find('div').html());
		
		$('#reply-form').modal('show');
		$('#reply-form .modal-title').text('修改回覆');
		$('#reply-form form').off('submit');
		
		$('#reply-form form').one('submit',function(e){
			e.preventDefault();
			$.ajax({
				url:  (window.location.href.indexOf("/admin") > -1 ? '../':'') +'include/ajax/forumedit.php?<?php echo sc_csrf(); ?>&rid='+rid+'&edit',
				type: 'POST',
				data:$('#reply-form form').serialize(),
				dataType: 'json',
				cache: false,
				beforeSend: function(){
					$('#reply-form .btn-primary').prop('disabled',true).prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
				},
				success: function(data) {
					if(data.status){
						alert('編輯成功！');
						self.parents('.info').siblings('.content').find('div').html($('#summernote').summernote('code'));
						$('#reply-form').modal('hide');
					}else{
						alert('編輯異常。');
						self.trigger('click');
					}
				},error:function(){
					alert('編輯異常，請洽詢問處。');
					self.trigger('click');
				},
				complete:function(){
					$('#reply-form .loading').remove();
					$('#reply-form .btn-primary').prop('disabled',false).find('.spinner-border').remove();
				}
			});

			$('#reply-form .btn-secondary').click(function(){
				$('#reply-form form').off('submit');
			});
		});
	});


	$('.add-reply').click(function(e){
		e.preventDefault();
		$('#reply-form form').off('submit');
		$('#summernote').summernote('reset');
		$('#reply-form .modal-title').text('新增回覆');
		$('#reply-form').modal('show');
	});

	$('#reply-form').on('bs.modal.hide',function(){
		$('#reply-form form').off('submit');
	});
});
</script>
<ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="forum.php">論壇</a></li>
    <li class="breadcrumb-item"><a href="forum.php?fid=<?php echo $_block['row'][0]['id']; ?>"><?php echo $_block['row'][0]['blockname']; ?></a></li>
    <li class="breadcrumb-item active"><?php echo sc_removal_escape_string($_post['row'][0]['title']); ?></li>
</ol>
<ul class="list-inline">
	<li class="list-inline-item">
		<h2><?php echo $_post['row'][0]['title']; ?></h2>
	</li>
	<?php if($_post['row'][0]['level']>1){ ?>
	<li class="list-inline-item">
		<span class="badge badge-secondary"><?php echo sc_member_level_array($_post['row'][0]['level']); ?></span>
	</li>
	<?php } ?>
	<li class="list-inline-item">
		<a href="#" class="add-reply btn btn-sm btn-primary">發表新回覆</a>
	</li>
</ul>
<div class="post d-flex flex-column flex-sm-row align-items-stretch">
	<div class="info d-flex flex-sm-column justify-content-between">
		<div class="d-flex flex-sm-column align-items-center">
			<div class="mr-3 mr-sm-0">
				<img src="<?php echo sc_avatar_url($_post['row'][0]['author']); ?>" class="avatar">
			</div>
			<div class="mr-3 mr-sm-0">
				<?php echo $_post['row'][0]['nickname']; ?>
			</div>
			<div class="mr-3 mr-sm-0">
				<small class="text-muted"><?php echo $_post['row'][0]['mktime']; ?></small>
			</div>
		</div>
		<div class="align-self-center">
			<?php if($_post['row'][0]['author'] == $_SESSION['center']['id']){ ?>
			<a href="forumedit.php?id=<?php echo $_post['row'][0]['id']; ?>" class="btn btn-info btn-sm">
				編輯
			</a>
			<a href="javascript:if(confirm('確定刪除？'))location='mypost.php?del=<?php echo $_post['row'][0]['id'].'&'.sc_csrf(); ?>'" class="btn btn-danger btn-sm">
				刪除
			</a>
			<?php } ?>
		</div>
	</div>
   	<div class="content d-flex align-items-center">
		<div>
			<?php echo sc_removal_escape_string($_post['row'][0]['content']); ?>
		</div>
	</div>
</div>

<div class="loading text-center">
	<div class="spinner-border"></div><br><p>讀取中</p>
</div>

<div id="reply-simple" class="d-none">
	<div id="reply-$floor" class="post d-flex flex-column flex-sm-row align-items-stretch">
		<div class="info d-flex flex-sm-column justify-content-between">
			<div class="d-flex flex-sm-column align-items-center">
				<div class="mr-3 mr-sm-0">
					<img src="$avatar" class="avatar">
				</div>
				<div class="mr-3 mr-sm-0">
					$nickname
				</div>
				<div class="mr-3 mr-sm-0">
					<small class="text-muted">$mktime</small>
				</div>
			</div>
			<div class="own align-self-center">
				
				<a href="#" data-reply-id="$id" class="btn btn-info btn-sm">
					編輯
				</a>
				<a href="javascript:if(confirm('確定刪除？'))location='mypost.php?del=$id&<?php echo sc_csrf(); ?>'" class="btn btn-danger btn-sm">
					刪除
				</a>
			</div>
		</div>
		<div class="content d-flex align-items-center">
			<div>
				$content
			</div>
		</div>
	</div>
</div>
<nav id="pagination" class="d-none">
	<ul class="pagination"></ul>
</nav>
<div id="reply-form" class="modal fade" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <form action="forumview.php?id=<?php echo $_GET['id'].'&'.sc_csrf(); ?>&reply" class="modal-content" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">新增回覆</h5>
      </div>
      <div class="modal-body">
	    <label for="content">回覆內容：</label>
		<textarea id="summernote" class="form-control" name="content" cols="65" rows="10" required="required"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="submit" class="btn btn-primary">送出</button>
      </div>
    </form>
  </div>
</div>
<?php
$view->render();
?>