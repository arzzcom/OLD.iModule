<div class="RecentlyCalendarList">
	{foreach name=list from=$data item=data}
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="80" /><col width="100%" />
	<tr>
		<td class="vTop">
			<div class="dayIcon">
				<div class="month">{$data.reg_date|date_format:"%b"}</div>
				<div class="day">{$data.reg_date|date_format:"%e"}</div>
			</div>
		</td>
		<td>
			<div class="title">{$data.title|cutstring:25:true}</div>
			<div class="subinfo">{$data.reg_date|date_format:"%B %d, %Y (%Z)"}<br />Post By {$data.nickname}</div>
			
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<div class="height10"></div>
			{$data.content}
		</td>
	</tr>
	</table>
	
	<div class="height20" style="border-bottom:1px dashed #CCCCCC;"></div>
	<div class="height20"></div>
	
	<!-- 
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
	-->
	{/foreach}
</div>