<div class="RecentlyNotice">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="100%" /><col width="50" />
	{foreach name=list from=$data item=data}
	{if $smarty.foreach.list.index == 0}
	<tr>
		<td class="board_title"><a href="{$data.postlink}">{$data.title|cutstring:25:true}</a> <span class="comment">({$data.ment}{if $data.is_newment == true}+{/if})</span></td>
		<td class="right"><a href="{$page}"><img src="{$skinDir}/images/btn_more.gif" style="margin-top:3px;" /></a></td>
	</tr>
	<tr>
		<td colspan="2" class="board_body">
			<div class="scroll">{$data.content}</div>
		</td>
	</tr>
	<tr class="board_line">
		<td colspan="2"><div></div></td>
	</tr>
	{else}
	<tr>
		<td class="title">
			<a href="{$data.postlink}">{$data.title}</a> <span class="comment">({$data.ment}{if $data.is_newment == true}+{/if})</span>
		</td>
		<td class="date">{$data.reg_date|date_format:"%y.%m.%d"}</td>
	</tr>
	{/if}
	{/foreach}
	</table>
</div>