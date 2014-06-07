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
				<td class="center"><img src="{$skinDir}/images/step1_off.png" /></td>
				{if $is_realname == true}<td class="center"><img src="{$skinDir}/images/step2_off.png" /></td>{/if}
				<td class="center"><img src="{$skinDir}/images/step3_off.png" /></td>
				<td class="center"><img src="{$skinDir}/images/step4_off.png" /></td>
				<td class="center"><img src="{$skinDir}/images/step5_on.png" /></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td class="sectionInfo">
			회원가입이 완료되었습니다.
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

	<div class="finishbox">
		<span class="bold pointText">{$name}</span>님의 회원가입을 환영합니다.<br />
		<span class="bold pointText">{$name}</span>님의 회원아이디는 <span class="bold pointText">{$user_id}</span>입니다.<br /><br />
		{if $inactive == true}
		관리자의 승인후 회원님의 아이디로 로그인이 가능합니다.<br />
		{/if}
		즐거운 하루 보내시기 바랍니다.
	</div>
	
	<div class="height10"></div>

	<div class="center">
		<a href="/" class="btn btn-sm btn-primary" />메인페이지로 이동하기</a>
	</div>
</div>
{$formEnd}