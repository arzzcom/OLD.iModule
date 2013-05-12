<div class="CategoryArea">
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<tr class="top">
	<td></td>
</tr>
{foreach name=category from=$categorys item=data}
<tr>
	<td class="depth{$data.depth}"><a href="{$data.link}">{if $data.image}<img src="{$data.image}" alt="{$data.title|replace:'"':'\"'}" />{else}{$data.title}{/if}</a></td>
</tr>
{/foreach}
</table>
</div>