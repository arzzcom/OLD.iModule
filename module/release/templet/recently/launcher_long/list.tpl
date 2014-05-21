<div class="blackTitle">
	<div class="height5"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="50%" /><col width="3" /><col width="50%" />
	<tr>
		<td style="padding-right:10px;" class="vTop">
			<div style="{$title|replace:"content":""}; overflow-y:scroll;">
				<div class="bold listTitle" style="height:14px; line-height:14px; padding:5px !important; font-size:14px;"><a href="javascript:ShowContent('게시글보기','{$data.postlink}')" class="listTitle"><span style="{$title}">{$data.0.title|cutstring:30:true:$data.0.category}</span></a></div>
				<div style="font-size:12px; line-height:1.6; padding:5px 5px 5px 8px;">{$data.0.search}</div>
			</div>
		</td>
		<td><div style="width:1px; {$title|replace:"area":""};"></div></td>
		<td class="vTop" style="padding-left:10px;">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100%" /><col width="60" />
			<tbody>
			{foreach name=list from=$data item=data}
			<tr class="listRow">
				<td class="listTitle">{if $data.category}<span class="category" style="{$title}">[{$data.category}]</span>{/if}<a href="javascript:ShowContent('게시글보기','{$data.postlink}')" class="listTitle"><span style="{$title}">{$data.title|cutstring:30:true:$data.category}</span></a> <span class="comment" style="{$title}">({$data.ment}{if $data.is_newment == true}+{/if})</span></td>
				<td class="listDate" style="{$title}">{$data.reg_date|date_format:"%Y.%m.%d"}</td>
			</tr>
			{/foreach}
			</tbody>
			</table>
		</td>
	</tr>
	</table>
</div>