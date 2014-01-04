<div id="toolbar">
	<h1>{$setup.title}</h1>
	{if $link.prevURL}<a id="backButton" class="button" href="{$link.prevURL}">이전</a>{/if}
	<a class="button" href="{$link.post}">글쓰기</a>
</div>

{literal}
<div style="background:#F4F4F4; border-bottom:1px solid #CCCCCC; text-align:center;">
	<script type="text/javascript">
	google_ad_client = "ca-pub-3210736654114323";
	google_ad_slot = "4241182321";
	google_ad_width = 320;
	google_ad_height = 50;
	</script>
	<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>
{/literal}
<div id="content" class="whiteBg">
	<ul id="main" title="Tamrin's Link" selected="true">
		{foreach name=list from=$data item=data}
		<li style="background-image:url({mBoard->GetThumbnail post=$data width=176 height=0 min_height=110 error=$skinDir|cat:'/images/noimage.gif'});" class="list"><a href="{$data.postlink}"><span class="listTitle">{$data.title}</span><span class="listMent">[{if $data.is_newment == true}+{/if}{$data.ment}]</span></a></li>
		{/foreach}
	</ul>

	<div class="pagenav">
	{if $prevpage != false}
		<a href="{$link.page}{$prevpage}">이전</a>
	{else}
		<span>이전</span>
	{/if}
	{foreach name=page from=$page item=page}
		{if $page == $p}
		<strong>{$page}</strong>
		{else}
		<a href="{$link.page}{$page}">{$page}</a>
		{/if}
	{/foreach}
	{if $nextpage != false}
		<a href="{$link.page}{$nextpage}">다음</a>
	{else}
		<span>다음</span>
	{/if}
	</div>
	
	{$searchFormStart}
	<input type="hidden" name="key" value="tc" />
	<div class="searchbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100%" /><col width="34" />
		<tr>
			<td>
				<input type="search" name="keyword" value="{$keyword}" />
			</td>
			<td><input type="image" src="{$skinDir}/images/btn_search.png" /></td>
		</tr>
		</table>
	</div>
	{$searchFormEnd}
	<div class="height5"></div>
</div>
