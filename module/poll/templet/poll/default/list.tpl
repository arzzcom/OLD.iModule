<div class="PollDefault">
{foreach name=list from=$data item=data}
	<div class="box">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="35" /><col width="100%" />{if $data.thumbnail}<col width="80" />{/if}
		<tr>
			<td class="vTop"><img src="{$skinDir}/images/icon.gif" /></td>
			<td>
				<div class="title"><a href="{$data.titlelink}">{$data.title}</a></div>
			</td>
			{if $data.thumbnail}
			<td rowspan="2" class="thumbnail">
				<div style="background-image:url({$data.thumbnail});"></div>
			</td>
			{/if}
		</tr>
		<tr>
			<td></td>
			<td>
				<div class="reg_date">설문기간 : {$data.reg_date|date_format:"%Y년 %m월 %d일"} ~ {$data.end_date|date_format:"%Y년 %m월 %d일"}</div>
				<div class="numbers">총 참여인원 : {$data.voter|number_format}명 / 댓글 : {$data.ment|number_format}개 / 등록 : {$data.name}</div>
			</td>
		</tr>
		</table>
	</div>
	
	<div class="height10"></div>
	
	
{/foreach}

	<div class="pagenav">
	{if $prevpage != false}
		<a href="{$link.page}{$prevpage}">이전{$pagenum}페이지</a>
	{else}
		<span>이전{$pagenum}페이지</span>
	{/if}
	{foreach name=page from=$page item=page}
		{if $page == $p}
		<strong>{$page}</strong>
		{else}
		<a href="{$link.page}{$page}">{$page}</a>
		{/if}
	{/foreach}
	{if $nextpage != false}
		<a href="{$link.page}{$nextpage}">다음{$pagenum}페이지</a>
	{else}
		<span>다음{$pagenum}페이지</span>
	{/if}
	</div>
</div>