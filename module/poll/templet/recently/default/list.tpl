<div class="PollRecentlyDefault">
{foreach name=list from=$data item=data}
	<div class="box">
		{$data.thumbnail}
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="35" /><col width="100%">
		<tr>
			<td class="vTop"><img src="{$skinDir}/images/icon.gif" /></td>
			<td>
				<div class="title">{$data.title}</div>
				<div class="reg_date">{$data.reg_date} ~ {$data.end_date}</div>
			</td>
		</tr>
		</table>
		
		{if $data.thumbnail}<div class="thumbnail" style="background-image:url({$data.thumbnail});"></div>{/if}
		
		{if $data.is_end == true}
			끝남.
		{else}
			{$data.formStart}
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			{foreach name=item from=$data.item item=item}
			<tr>
				<td class="item">
					{if $data.vote_type == 'MULTI'}
					<input id="poll-{$data.idx}-{$item.idx}" type="checkbox" name="vote[]" value="{$item.idx}" />
					{else}
					<input id="poll-{$data.idx}-{$item.idx}" type="radio" name="vote" value="{$item.idx}" />
					{/if}
				</td>
				<td class="item"><label for="poll-{$data.idx}-{$item.idx}">{$item.title}</label></td>
			</tr>
			<tr class="height5"><td colspan="2"></td></tr>
			{/foreach}
			</table>
			
			<div class="button">
				<input type="image" src="{$skinDir}/images/btn_vote.gif" /><a href="{$data.result}"><img src="{$skinDir}/images/btn_result.gif" /></a>
			</div>
			{$data.formEnd}
		{/if}
	</div>
	
	<div class="height10"></div>
	
	
{/foreach}
</div>