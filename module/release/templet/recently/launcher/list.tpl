<div class="blackTitle">
	<div class="height5"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="100%" /><col width="60" />
	<tbody>
	{foreach name=list from=$data item=data}
	<tr class="listRow">
		<td class="listTitle">{if $data.category}<span class="category" style="{$title}">[{$data.category}]</span>{/if}<a href="javascript:ShowContent('게시물보기','{$data.postlink}')" class="listTitle"><span style="{$title}">{$data.title|cutstring:30:true:$data.category}</span></a> <span class="comment" style="{$title}">({$data.ment}{if $data.is_newment == true}+{/if})</span></td>
		<td class="listDate" style="{$title}">{$data.reg_date|date_format:"%Y.%m.%d"}</td>
	</tr>
	{/foreach}
	</tbody>
	</table>
</div>