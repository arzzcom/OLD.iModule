{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td>
			<div class="stepbar">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				{if $is_realname == true}
				<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
				{else}
				<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
				{/if}
				<tr>
					<td><div class="select">약관동의</div></td>
					{if $is_realname == true}<td><div>실명인증</div></td>{/if}
					<td><div>이력조회</div></td>
					<td><div>정보입력</div></td>
					<td><div style="border-right:1px solid #cccccc;">가입완료</div></td>
				</tr>
				</table>
			</div>
		</td>
	</tr>
	<tr class="sectionEnd">
		<td><div></div></td>
	</tr>
	</table>

	<div class="padding5">
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
			<td class="f14"><label for="check_{$data.type}">{$data.msg}</label></td>
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
			<td class="f14"><label for="check_{$data.type}">{$data.msg}</label></td>
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
			<td class="f14"><label for="check_{$data.type}">{$data.msg}</label></td>
		</tr>
		</table>

		<div class="height10" /></div>
		{/if}
	{/foreach}
	</div>

	<div class="center">
		<input type="submit" class="btn btn-primary" value="동의합니다." />
		<div class="btn btn-default" onclick="history.go(-1);">취소</div>
	</div>
	
	<div class="height10" /></div>
</div>
{$formEnd}