{$formStart}
<div class="ModuleMemberSignInMobile">
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
					<td><div>약관동의</div></td>
					{if $is_realname == true}<td><div>실명인증</div></td>{/if}
					<td><div>이력조회</div></td>
					<td><div>정보입력</div></td>
					<td><div class="select" style="border-right:1px solid #cccccc;">가입완료</div></td>
				</tr>
				</table>
			</div>
		</td>
	</tr>
	</table>

	<div class="padding5">
		<div class="finishbox">
			<span class="bold pointText">{$name}</span>님의 회원가입을 환영합니다.<br />
			<span class="bold pointText">{$name}</span>님의 회원아이디는 <span class="bold pointText">{$user_id}</span>입니다.<br /><br />
			{if $inactive == true}
			관리자의 승인후 회원님의 아이디로 로그인이 가능합니다.<br />
			{/if}
			즐거운 하루 보내시기 바랍니다.
		</div>
	</div>
	
	<div class="center">
		<a href="/" class="btn btn-primary" />메인페이지로 이동하기</a>
	</div>
	
	<div class="height10"></div>
</div>
{$formEnd}