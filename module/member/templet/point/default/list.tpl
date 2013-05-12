{if $categoryList}
<div id="iBoardCategory" class="selectbox" style="width:100px;">
	<div onclick="InputSelectBox('iBoardCategory')" clicker="iBoardCategory">{if $categoryName}{$categoryName}{else}전체보기{/if}</div>

	<ul style="display:none;" clicker="iBoardCategory">
		<li onclick="InputSelectBoxSelect('iBoardCategory','전체보기','',ListSelectCategory)">전체보기</li>
		{foreach from=$categoryList item=categoryList}
		<li onclick="InputSelectBoxSelect('iBoardCategory','{$categoryList.category|replace:"'":"\'"}','{$categoryList.idx}',ListSelectCategory)">{$categoryList.category}</li>
		{/foreach}
	</ul>
</div>
<div class="height5"></div>
{/if}
<div id="tList">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="5" /><col width="120" /><col width="100%" /><col width="100" /><col width="100" /><col width="5" />
	<thead>
	<tr class="listbar">
		<td class="left"></td>
		<td class="text">날짜</td>
		<td class="text">내역</td>
		<td class="text">적립포인트</td>
		<td class="text">사용포인트</td>
		<td class="right"></td>
	</tr>
	</thead>
	<tbody>
	{foreach name=list from=$data item=data}
	<tr class="listrow">
		<td></td>
		<td class="number center">{$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</td>
		<td>{if $data.url}<img src="{$skinDir}/images/icon_go.png" style="vertical-align:middle" /> <a href="{$data.url}" onclick="return OpenBoard(this);">{/if}{$data.msg}{if $data.url}</a>{/if}</td>
		<td class="number right"><span class="save">{$data.save|number_format}</span> Point</td>
		<td class="number right"><span class="use">{$data.use|number_format}</span> Point</td>
		<td></td>
	</tr>
	<tr class="listrowbar">
		<td colspan="6"></td>
	</tr>
	{/foreach}
	</tbody>
	</table>

	<div class="height10"></div>

	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="150" /><col width="100%" /><col width="150" />
	<tr>
		<td class="innerimg">
			{if $prevlist != false}
				<a href="{$link.page}{$prevlist}"><img src="{$skinDir}/images/btn_prev.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_prev_off.gif" />
			{/if}
		</td>
		<td class="pageinfo"><span class="bold">{$total}</span> records / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
		<td class="innerimg right">
			{if $setup.use_mode == 'FALSE'}<a href="{$link.post}"><img src="{$skinDir}/images/btn_newpost.gif" style="margin-right:3px;" /></a>{/if}
			{if $nextlist != false}
				<a href="{$link.page}{$nextlist}"><img src="{$skinDir}/images/btn_next.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_next_off.gif" />
			{/if}
		</td>
	</tr>
	</table>

	<div class="height10"></div>

	<div class="pagenav">
	{if $prevpage != false}
		<a href="{$link.page}{$prevpage}">이전{$pagenum}페이지</a>
	{else}
		<span>이전{$pagenum}페이지</span>
	{/if}
	{foreach name=page from=$page item=page}
		{if $page == $p}
		<strong>{$page}</strong>
		{else}
		<a href="{$link.page}{$page}">{$page}</a>
		{/if}
	{/foreach}
	{if $nextpage != false}
		<a href="{$link.page}{$nextpage}">다음{$pagenum}페이지</a>
	{else}
		<span>다음{$pagenum}페이지</span>
	{/if}
	</div>

	<div class="height10"></div>
</div>