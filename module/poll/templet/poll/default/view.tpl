<div class="PollDefault">
	<div class="box">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="35" /><col width="100%" /><col width="60" />
		<tr>
			<td class="vTop"><img src="{$skinDir}/images/icon.gif" /></td>
			<td>
				<div class="title"><a href="{$data.titlelink}">{$data.title}</a></div>
			</td>
			<td rowspan="2"><div class="photo"><img src="{$data.photo}" /></div></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<div class="reg_date">설문기간 : {$data.reg_date|date_format:"%Y년 %m월 %d일"} ~ {$data.end_date|date_format:"%Y년 %m월 %d일"}</div>
				<div class="numbers">총 참여인원 : {$data.voter|number_format}명 / 댓글 : {$data.ment|number_format}개</div>
			</td>
		</tr>
		</table>
		
		<div class="height10"></div>
		
		<div class="content">{$data.content}</div>
		
		<div class="height10"></div>
		
		{if $viewmode == 'POLL'}{$formStart}{/if}
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="35" />{if $data.image}<col width="300" /><col width="10" />{/if}<col width="100%" />
		<tr>
			<td></td>
			{if $data.image}<td class="image" style="background-image:url({$data.image});"></td><td></td>{/if}
			<td>
				{if $viewmode == 'RESULT'}
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="100%" /><col width="50" /><col width="50" />
					{foreach name=item from=$data.item item=item}
					<tr>
						<td class="item">{$item.title}</td>
					</tr>
					<tr>
						<td class="graph">
							<div class="bg">
								<div class="vote color{$smarty.foreach.item.index%4+1}" style="width:{$item.percent}%;"></div>
							</div>
						</td>
						<td class="percent">{$item.percent}%</td>
						<td class="voter">({$item.voter|number_format})</td>
					</tr>
					{/foreach}
					</table>
				{else}
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
				{/if}
			</td>
		</tr>
		</table>
		
		{if $viewmode == 'RESULT'}
			<div class="button">
				<a href="{$link.poll}"><img src="{$skinDir}/images/btn_poll.gif" /></a>
			</div>
		{else}
			<div class="button">
				<input type="image" src="{$skinDir}/images/btn_vote.gif" />
				<a href="{$link.result}"><img src="{$skinDir}/images/btn_result.gif" /></a>
			</div>
			{$formEnd}
		{/if}
		
	</div>
	
	<div class="height5"></div>
	
	<div class="right">
		<a href="{$link.list}"><img src="{$skinDir}/images/btn_list.gif" /></a>
	</div>
	
	<div class="height5"></div>
	{$ment}
</div>