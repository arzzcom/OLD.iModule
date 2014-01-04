<div id="toolbar">
	<h1>{$data.title}</h1>
	<a id="backButton" class="button" href="{$link.list}">이전</a>
	<a class="button" href="{$link.post}">글쓰기</a>
</div>
	
<div id="content" class="whiteBg">
	<div class="viewTitle">
		<div class="viewTitleText">{$data.title}</div>
		<div class="viewTitleInfo">{$data.nickname} At {$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</div>
	</div>
	
	<div class="viewContent">
	
	<div class="smartOutputMobile">
	{if $data.image}
	{foreach name=image from=$file item=image}
		{if $image.filetype == 'IMG'}<div style="padding:5px 0px 5px 0px;"><img src="{$moduleDir}/exec/ShowImage.do.php?idx={$image.idx}" class="showImage" /></div>{/if}
	{/foreach}
	{/if}
	</div>
	
	{$data.content}
	</div>
	
	{$ment}
	
	<ul>
		<li class="group">게시물 메뉴</li>
		{if $permission.ment == true}
		<li><a href="{$link.postment}">이 게시물에 댓글 작성하기</a></li>
		<li><div onclick="{$action.vote}">이 게시물을 추천하기</div></li>
		<li><a href="{$link.modify}">이 게시물을 수정하기</a></li>
		<li><a href="{$link.delete}">이 게시물을 삭제하기</a></li>
		{/if}
	</ul>
</div>

{literal}
<div style="background:#F4F4F4; text-align:center; border-top:1px solid #CCCCCC;">
	<script type="text/javascript">
	google_ad_client = "ca-pub-3210736654114323";
	google_ad_slot = "4241182321";
	google_ad_width = 320;
	google_ad_height = 50;
	</script>
	<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>
{/literal}