<div class="innerimg"><img src="{$skinDir}/images/title.gif" alt="중개업소/중개담당자등록" /></div>

<div class="height10"></div>

<div id="sForm">

<div class="box">
{$register_info}
</div>

<div class="height10"></div>

<div class="titlebar"><img src="{$skinDir}/images/title_selectinfo.gif" /></div>
<div class="innerimg"><img src="{$skinDir}/images/text_select.gif" /></div>

<div class="height10"></div>

<div class="titlebar"><img src="{$skinDir}/images/title_select.gif" /></div>
<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
{if $use_private == true}
<col width="32%" /><col width="1%" /><col width="34%" /><col width="1%" /><col width="32%" />
{else}
<col width="50%" /><col width="10" /><col width="50%" />
{/if}
<tr>
	<td>
		<div class="btnSelect" onclick="location.href='{$link.agent}';">
			<div class="bold">중개업소등록</div>
			<div class="height10"></div>
			<div class="gray">
				새로운 중개업소로 등록합니다.<br />
				등록비용은 <span class="blue bold">{if $point.agent == 0}무료{else}{$point.agent|number_format}포인트{/if}</span>이며,<br />
				{if $is_auto.agent == true}<span class="blue">신청즉시 승인되어 매물등록이 가능합니다.</span>{else}<span class="red">신청후 사이트관리자의 승인이 필요합니다.</span>{/if}
			</div>
		</div>
	</td>
	<td></td>
	<td>
		<div class="btnSelect" onclick="location.href='{$link.dealer}';">
			<div class="bold">중개담당자등록</div>
			<div class="height10"></div>
			<div class="gray">
				특정 중개업소에 중개담당자로 등록합니다.<br />
				등록비용은 <span class="blue bold">{if $point.dealer == 0}무료{else}{$point.dealer|number_format}포인트{/if}</span>이며,<br />
				{if $is_auto.dealer == true}<span class="blue">신청즉시 승인되어 매물등록이 가능합니다.</span>{else}<span class="red">신청후 중개업소관리자의 승인이 필요합니다.</span>{/if}
			</div>
		</div>
	</td>
	{if $use_private == true}
	<td></td>
	<td>
		<div class="btnSelect" onclick="location.href='{$link.private}';">
			<div class="bold">개인등록</div>
			<div class="height10"></div>
			<div class="gray">
				개인으로 등록합니다.<br />
				등록비용은 <span class="blue bold">무료</span>이며,<br />
				<span class="blue">신청즉시 승인되어 매물등록이 가능합니다.</span>
			</div>
		</div>
	</td>
	{/if}
</tr>
</table>

</div>