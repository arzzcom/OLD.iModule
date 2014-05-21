<table cellpadding="0" cellspacing="0" class="pointTable">
<col width="130" /><col width="1" /><col width="100%" /><col width="1" /><col width="110" /><col width="1" /><col width="110" />
<tr class="sectionBar">
	<td colspan="7"></td>
</tr>
<tr>
	<td class="headerCell">날짜</td>
	<td class="splitBar"></td>
	<td class="headerCell">내역</td>
	<td class="splitBar"></td>
	<td class="headerCell">적립포인트</td>
	<td class="splitBar"></td>
	<td class="headerCell">사용포인트</td>
</tr>
<tr class="splitBar">
	<td colspan="7"></td>
</tr>
{foreach name=list from=$data item=data}
<tr>
	<td class="bodyCell number center">{$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><div{if $data.url} class="reference"{/if}>{if $data.url}<a href="{$data.url}" width="800" height="500" onclick="return OpenLinkToPopup(this);">{/if}{$data.msg}{if $data.url}</a>{/if}</div></td>
	<td class="splitBar"></td>
	<td class="bodyCell number right"><span class="save">{$data.save|number_format}</span> Point</td>
	<td class="splitBar"></td>
	<td class="bodyCell number right"><span class="use">{$data.use|number_format}</span> Point</td>
</tr>
<tr class="splitBar">
	<td colspan="7"></td>
</tr>
{/foreach}
<tr class="sectionEnd">
	<td colspan="7"><div></div></td>
</tr>
</table>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="150" /><col width="100%" /><col width="150" />
<tr>
	<td class="innerimg">
		<a href="{$link.page}{$prevlist}" class="btn btn-sm btn-primary"{if $prevlist == false} disabled="disabled"{/if}>이전페이지</a>
	</td>
	<td class="pageinfo"><span class="bold">{$total}</span> records / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
	<td class="innerimg right">
		<a href="{$link.page}{$nextlist}" class="btn btn-sm btn-primary"{if $nextlist == false} disabled="disabled"{/if}>다음페이지</a>
	</td>
</tr>
</table>

<div class="height10"></div>

<div class="center">
	<ul class="pagination pagination-sm">
		<li{if $prevpage == false} class="disabled"{/if}><a href="{if $prevpage != false}{$link.page}{$prevpage}{else}javascript:void(0);{/if}">이전{$pagenum}페이지</a></li>
		{foreach name=page from=$page item=page}
		<li{if $page == $p} class="active"{/if}><a href="{$link.page}{$page}">{$page}</a></li>
		{/foreach}
		<li{if $nextpage == false} class="disabled"{/if}><a href="{if $nextpage != false}{$link.page}{$nextpage}{else}javascript:void(0);{/if}">다음{$pagenum}페이지</a></li>
	</ul>
</div>