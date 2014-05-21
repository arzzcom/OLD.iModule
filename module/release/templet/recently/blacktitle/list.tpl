<div class="blackTitle">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="10" /><col width="100%" /><col width="45" /><col width="10" />
	<tr>
		<td class="titleStart"></td>
		<td class="titleText">{$title}</td>
		<td class="titleMore">{if $mode != 'mypost' || $page != ''}<a href="{$page}" title="더보기"><img src="{$skinDir}/images/title_more.gif" alt="더보기" /></a>{/if}</td>
		<td class="titleEnd"></td>
	</tr>
	</table>

	<div class="height5"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="100%" /><col width="60" />
	<tbody>
	{foreach name=list from=$data item=data}
	<tr class="listRow">
		<td class="listTitle">{if $data.category}<span class="category">[{$data.category}]</span>{/if}<a href="{$data.postlink}" class="listTitle"{if $mode == 'mypost' || $mode == 'myment'} onclick="return OpenBoard(this);"{/if}>{$data.title|cutstring:30:true:$data.category}</a> <span class="comment">({$data.ment}{if $data.is_newment == true}+{/if})</span></td>
		<td class="listDate">{$data.reg_date|date_format:"%Y.%m.%d"}</td>
	</tr>
	{if $mode == 'myment'}
	<tr class="listRow">
		<td colspan="2" class="listContent">{$data.content|cutstring:"35":true}</td>
	</tr>
	{/if}
	{/foreach}
	</tbody>
	</table>
</div>