{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<col width="100%" /><col width="200" />
	<tr>
		<td class="sectionTitle">회원탈퇴</td>
		<td class="right"><div class="essential">필수입력항목</div></td>
	</tr>
	<tr class="sectionBar">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td colspan="2" class="sectionInfo">
			회원탈퇴시 본인확인을 위하여 가입당시의 회원님의 정보가 필요합니다.<br />
			아래의 양식에 맞게 정보를 입력하신 후 탈퇴하기 버튼을 클릭하여 주시기 바랍니다.
		</td>
	</tr>
	</table>

	<table cellpadding="0" cellspacing="0" class="memberTable">
	<col width="120" /><col width="1" /><col width="100%" />
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	<tr>
		<td class="headerCell"><div class="essential">회원아이디</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="text" name="user_id" class="input" style="width:200px;" value="{$member.user_id}" disabled="disabled" />
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	<tr>
		<td class="headerCell"><div class="essential">패스워드</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="password" name="password" class="input" style="width:200px;" />
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	<tr>
		<td class="headerCell"><div class="essential">탈퇴사유</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<textarea name="msg" class="textarea" style="height:100px;"></textarea>
			<div class="help-block">사이트의 발전을 위해 탈퇴사유를 간단하게나마 입력하여 주십시오.</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	<tr class="sectionEnd">
		<td colspan="3"><div></div></td>
	</tr>
	</table>
	
	<div class="height10"></div>

	<div class="center">
		<input type="submit" value="회원탈퇴하기" class="btn btn-sm btn-danger" />
	</div>
</div>
{$formEnd}