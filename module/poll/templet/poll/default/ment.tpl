{$ment_write}
<div class="height10"></div>

<div class="mentbox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="50" /><col width="100%" />
	{foreach from=$data item=data}
		<tr>
			<td class="vTop"><div class="photo"><img src="{$data.photo}" /></div></td>
			<td class="vTop">
				<div class="name">{$data.name}</div>
				<div class="content">
					{$data.content}
				</div>
				<div class="info">{$data.reg_date} | <a href="{$data.link.delete}" class="delete">DELETE</a></div>
			</td>
		</tr>
		<tr>
			<td colspan="2"><div class="split"></div></td>
		</tr>
	{/foreach}
	</table>
</div>