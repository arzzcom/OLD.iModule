{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<tr>
		<td class="sectionTitle">회원가입</td>
	</tr>
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td class="stepbar">
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
		</td>
	</tr>
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td class="sectionInfo">
			회원가입을 계속 진행하기 위해서는 아래의 회원약관 및 정책에 동의하셔야 합니다.
		</td>
	</tr>
	<tr class="splitBar">
		<td></td>
	</tr>
	<tr class="sectionEnd">
		<td><div></div></td>
	</tr>
	</table>
	
	<div class="height10"></div>

	{foreach name=form from=$form item=data}
		{if $data.type == 'agreement'}
		<table cellpadding="0" cellspacing="0" class="memberTable">
		<tr>
			<td class="sectionTitle">회원약관</td>
		</tr>
		<tr class="sectionBar">
			<td></td>
		</tr>
		<tr>
			<td class="sectionInfo">
				<div class="agreebox">
					{$data.value}
				</div>
			</td>
		</tr>
		<tr class="splitBar">
			<td></td>
		</tr>
		<tr class="sectionEnd">
			<td><div></div></td>
		</tr>
		</table>

		<div class="height10"></div>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="100%" />
		<tr>
			<td><input type="checkbox" id="check_{$data.type}" name="{$data.type}" /></td>
			<td class="f12"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}

		{if $data.type == 'privacy'}
		<table cellpadding="0" cellspacing="0" class="memberTable">
		<tr>
			<td class="sectionTitle">개인정보보호정책</td>
		</tr>
		<tr class="sectionBar">
			<td></td>
		</tr>
		<tr>
			<td class="sectionInfo">
				<div class="agreebox">
					{$data.value}
				</div>
			</td>
		</tr>
		<tr class="splitBar">
			<td></td>
		</tr>
		<tr class="sectionEnd">
			<td><div></div></td>
		</tr>
		</table>

		<div class="height10"></div>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="100%" />
		<tr>
			<td><input type="checkbox" id="check_{$data.type}" name="{$data.type}" /></td>
			<td class="f12"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}

		{if $data.type == 'youngpolicy'}
		<table cellpadding="0" cellspacing="0" class="memberTable">
		<tr>
			<td class="sectionTitle">청소년보호정책</td>
		</tr>
		<tr class="sectionBar">
			<td></td>
		</tr>
		<tr>
			<td class="sectionInfo">
				<div class="agreebox">
					{$data.value}
				</div>
			</td>
		</tr>
		<tr class="splitBar">
			<td></td>
		</tr>
		<tr class="sectionEnd">
			<td><div></div></td>
		</tr>
		</table>

		<div class="height10"></div>

		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="100%" />
		<tr>
			<td><input type="checkbox" id="check_{$data.type}" name="{$data.type}" /></td>
			<td class="f12"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}
	{/foreach}

	<div class="center">
		<input type="submit" class="btn btn-primary btn-sm" value="위의 약관 및 정책에 동의합니다." />
		<div class="btn btn-default btn-sm" onclick="history.go(-1);">동의하지 않습니다.</div>
	</div>
</div>
{$formEnd}