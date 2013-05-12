{$formStart}
<div id="SigninNdoor">
	<div style="padding:25px 0px 50px 0px;"><img src="{$skinDir}/images/title_step1.gif" /></div>
	
	<div style="padding:0px 120px 0px 120px;">
		<div><img src="{$skinDir}/images/intro_step1.gif" /></div>
		
		<div style="height:50px;"></div>
		
		{foreach name=form from=$form item=data}
			{if $data.type == 'agreement'}
			<div class="height10" /></div>
			<div class="innerimg"><img src="{$skinDir}/images/text_agreement.gif" /></div>
			<div class="height10"></div>
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
			<div class="height10" /></div>
			<div class="innerimg"><img src="{$skinDir}/images/text_privacy.gif" /></div>
			<div class="height10"></div>
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
	</div>
	
	<div class="innerimg center">
		<input type="image" src="{$skinDir}/images/btn_agree.gif" />
		<img src="{$skinDir}/images/btn_not_agree.gif" class="pointer" onclick="history.go(-1);" />
	</div>
</div>
{$formEnd}