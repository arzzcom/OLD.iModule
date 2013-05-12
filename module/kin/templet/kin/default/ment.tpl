<div class="mentbox">
	{$formStart}
	<div class="mentwritebox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="42" /><col width="100%" /><col width="73" />
		{if $member.idx == 0}
		<tr>
			<td></td>
			<td>
				<div class="mentUserbox">
					<div>
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="110" /><col width="50" /><col width="110" /><col width="60" /><col width="100%" />
					<tr>
						<td><input type="text" name="name" class="mentInputbox" /></td>
						<td class="text">| 작성자</td>
						<td><input type="password" name="password" class="mentInputbox" /></td>
						<td class="text">| 패스워드</td>
					</tr>
					</table>
					</div>
				</div>
			</td>
			<td></td>
		</tr>
		{/if}
		<tr>
			<td class="vTop"><img src="{$member.photo}" class="mentPhoto" /></td>
			<td class="center"><textarea name="content" class="mentTextbox"></textarea></td>
			<td><input type="image" src="{$skinDir}/images/btn_ment_submit.gif" /></td>
		</tr>
		</table>
	</div>
	{$formEnd}
	
	<div class="height10"></div>
	
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="50" /><col width="100%" />
	{foreach name=ment from=$data item=data}
	<tr class="listBar">
		<td colspan="2"></td>
	</tr>
	<tr class="height5">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td class="vTop"><img src="{$data.photo}" class="mentPhoto" /></td>
		<td class="vTop">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100%" /><col width="150" />
			<tr>
				<td class="mentName">
					<div class="innerLayer">{$data.nickname}</div>
					<div class="innerLayer mentInfo">({if $data.mno == 0}비회원{else}질문 <span class="pointText">{$data.user.question|number_format}</span>건&nbsp;|&nbsp;질문마감률 <span class="pointText">{$data.user.complete}</span>, 답변 <span class="pointText">{$data.user.answer|number_format}</span>건&nbsp;|&nbsp;답변채택률 <span class="pointText">{$data.user.selected}</span>{/if})</div>
				</td>
				<td class="mentInfo">{$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</td>
			</tr>
			<tr>
				<td colspan="2" class="mentContent">{$data.content}</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="height5">
		<td colspan="2"></td>
	</tr>
	{/foreach}
	</table>
</div>