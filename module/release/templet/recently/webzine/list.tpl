<div class="RecentlyWebzine">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="65" /><col width="100%" /><col width="45" />
	{if $title}
	<tr>
		<td colspan="2" class="board_title">{$title}</td>
		<td class="right"><a href="{$page}"><img src="{$skinDir}/images/btn_more.gif" style="margin-top:3px;" /></a></td>
	</tr>
	{/if}
	{foreach name=list from=$data item=data}
	<tr>
		<td class="title" colspan="3"><a href="{$data.postlink}">{$data.title|cutstring:20:true:$data.category}</a> <span class="comment">({$data.ment}{if $data.is_newment == true}+{/if})</span></td>
	</tr>
	{if $data.image}
	<tr>
		<td class="image"><img src="{$data.image}" style="width:60px; height:45px;" /></td>
		<td class="body" colspan="2">{$data.search|cutstring:"60"}</td>
	</tr>
	<tr class="height5"><td colspan="2"></td></tr>
	{else}
	<tr>
		<td colspan="3" class="body">{$data.search|cutstring:"80"}</td>
	</tr>
	{/if}
	{/foreach}
	</table>
</div>