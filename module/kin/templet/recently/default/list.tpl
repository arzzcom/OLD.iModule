<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100%" /><col width="60" />
<thead>
<tr style="height:25px;">
	<td>{$title}</td>
	<td class="right innerimg"><a href="{$page}"><img src="{$skinDir}/images/btn_more.gif" /></a></td>
</tr>
</thead>
<tbody>
{foreach name=list from=$data item=data}
<tr style="height:20px;">
	<td style="font-family:돋움; font-size:12px; padding-left:8px; background:url({$skinDir}/images/icon_dotted.gif) no-repeat 2px 8px;"><a href="{$data.postlink}" style="text-decoration:none; color:#717888;">{$data.title}</a></td>
	<td class="right" style="font-family:tahoma; font-size:10px; padding-right:5px;">{$data.reg_date|date_format:"%Y.%m.%d"}</td>
</tr>
{/foreach}
</tbody>
</table>