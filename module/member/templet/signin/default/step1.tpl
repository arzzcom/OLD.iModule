{$formStart}
<div id="sForm">
	<div class="innerimg"><img src="{$skinDir}/images/title.gif" alt="회원가입" /></div>
	<div class="stepbar">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		{if $is_realname == true}
		<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
		{else}
		<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
		{/if}
		<tr>
			<td class="center"><img src="{$skinDir}/images/step1_on.png" /></td>
			{if $is_realname == true}<td class="center"><img src="{$skinDir}/images/step2_off.png" /></td>{/if}
			<td class="center"><img src="{$skinDir}/images/step3_off.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step4_off.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step5_off.png" /></td>
		</tr>
		</table>
	</div>

	<div class="steptitlebar"><img src="{$skinDir}/images/title_step1.gif" /></div>
	<div class="innerimg"><img src="{$skinDir}/images/info_step1.gif" /></div>

	<div class="height10"></div>

	{foreach name=form from=$form item=data}
		{if $data.type == 'agreement'}
		<div class="innerimg"><img src="{$skinDir}/images/text_agreement.gif" /></div>
		<div class="box">
			{$data.value}
		</div>

		<div class="height5"></div>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="100%" />
		<tr>
			<td><input type="checkbox" id="check_{$data.type}" name="{$data.type}" /></td>
			<td class="dotum f12"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}

		{if $data.type == 'privacy'}
		<div class="innerimg"><img src="{$skinDir}/images/text_privacy.gif" /></div>
		<div class="box">
			{$data.value}
		</div>

		<div class="height5"></div>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="100%" />
		<tr>
			<td><input type="checkbox" id="check_{$data.type}" name="{$data.type}" /></td>
			<td class="dotum f12"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}

		{if $data.type == 'youngpolicy'}
		<div class="innerimg"><img src="{$skinDir}/images/text_young.gif" /></div>
		<div class="box">
			{$data.value}
		</div>

		<div class="height5"></div>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="100%" />
		<tr>
			<td><input type="checkbox" id="check_{$data.type}" name="{$data.type}" /></td>
			<td class="dotum f12"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}
	{/foreach}

	<div class="buttonbox">
		<input type="image" src="{$skinDir}/images/btn_agree.gif" />
		<img src="{$skinDir}/images/btn_notagree.gif" class="pointer" onclick="history.go(-1);" />
	</div>
</div>
{$formEnd}