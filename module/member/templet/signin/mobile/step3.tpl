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
					<td><div>약관동의</div></td>
					{if $is_realname == true}<td><div>실명인증</div></td>{/if}
					<td><div class="select">이력조회</div></td>
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
		<table cellpadding="0" cellspacing="0" class="memberTable">
		<col width="100" /><col width="1" /><col width="100%" />
		<tr>
			<td colspan="3" class="sectionTitle">회원가입여부 조회하기</td>
		</tr>
		<tr class="sectionBar">
			<td colspan="3"></td>
		</tr>
		<tr>
			<td colspan="3" class="sectionInfo">
				회원님의 실명과 이메일주소로 가입여부를 확인합니다.<br />
				가입이력이 존재하지 않으면, 다음단계로 진행됩니다. 가입이력이 있을 경우, 가입절차가 중단됩니다.
			</td>
		</tr>
		<tr class="splitBar">
			<td colspan="3"></td>
		</tr>
		<tr>
			<td class="headerCell">실명</td>
			<td class="splitBar"></td>
			<td class="bodyCell"><input type="text" name="checkname" class="input" style="width:100%;" /></td>
		</tr>
		<tr class="splitBar">
			<td colspan="3"></td>
		</tr>
		<tr>
			<td class="headerCell">이메일주소</td>
			<td class="splitBar"></td>
			<td class="bodyCell"><input type="email" name="email" class="input" style="width:100%;" /></td>
		</tr>
		<tr class="splitBar">
			<td colspan="3"></td>
		</tr>
		<tr class="sectionEnd">
			<td colspan="3"><div></div></td>
		</tr>
		</table>
	</div>

	<div class="center">
		<input type="submit" class="btn btn-primary" value="회원가입여부 조회하기" />
	</div>
	
	<div class="height10"></div>
</div>
{$formEnd}