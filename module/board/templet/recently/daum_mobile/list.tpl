<div class="ModuleBoardRecentlyDaumMobile">
	<div class="boardTitle">
		{$title}
		<a href="{$page}" class="label label-primary pull-right">더보기</a>
	</div>
	
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	{foreach name=list from=$data item=data}
	<tr>
		<td class="item">
			<div>{if $data.category}<span class="category">[{$data.category}]</span>{/if}<a href="{$data.postlink}" class="listTitle"{if $mode == 'mypost' || $mode == 'myment'} onclick="return OpenBoard(this);"{/if}>{$data.title|cutstring:30:true:$data.category}</a> <span class="comment">({$data.ment}{if $data.is_newment == true}+{/if})</span></div>
		</td>
		<td class="date hidden-xs">{$data.reg_date|date_format:"%Y.%m.%d"}</td>
	</tr>
	
	<tr class="split">
		<td></td>
		<td class="hidden-xs"></td>
	</tr>
	{/foreach}
	</table>
</div>