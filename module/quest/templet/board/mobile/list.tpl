<div id="content" class="whiteBg">
	<ul id="main" title="Tamrin's Link" selected="true">
		{foreach name=list from=$data item=data}
		<li><a href="{$data.postlink}"><span class="listTitle">{$data.title}</span></a></li>
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

	<div class="height5"></div>
</div>
