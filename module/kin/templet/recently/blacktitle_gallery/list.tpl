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
	<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
	<tbody>
	{foreach name=list from=$data item=data}
		{if $smarty.foreach.list.index % 5 == 0}<tr>{/if}
		<td>
			{if $data.category}<div class="category">[{$data.category}]</div>{/if}
			<div class="image"><a href="{$data.postlink}">{if $data.image}<img src="{$data.image}" style="width:140px; height:105px;" />{else}<img src="{$skinDir}/images/noimage.gif" style="width:140px; height:105px;" />{/if}</a></div>
			<div class="listTitle"><a href="{$data.postlink}" class="listTitle">{$data.title|cutstring:10:true:$data.category}</a> <span class="comment">({$data.ment}{if $data.is_newment == true}+{/if})</span></div>
		</td>
		{if $smarty.foreach.list.index % 5 == 4}</tr>{/if}
	{/foreach}
	
	{if $smarty.foreach.list.total%5 != 0}
	{section name=idx start=$smarty.foreach.list.total%4-4 step=1 loop=5}<td></td>{/section}
	</tr>
	{/if}
	</tbody>
	</table>
</div>