{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<tr class="splitBar">
		<td style="width:120px;" class="hidden-xs"></td>
		<td style="width:1px;" class="hidden-xs"></td>
		<td style="width:100%;"></td>
	</tr>
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">회원아이디</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">회원아이디</div></div>
			<input type="text" name="user_id" class="input" style="width:100%;" value="{$member.user_id}" disabled="disabled" />
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">패스워드</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">패스워드</div></div>
			<input type="password" name="password" class="input" style="width:100%;" />
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">탈퇴사유</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">탈퇴사유</div></div>
			<textarea name="msg" class="textarea" style="height:100px;"></textarea>
			<div class="help-block">사이트의 발전을 위해 탈퇴사유를 간단하게나마 입력하여 주십시오.</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	<tr class="sectionEnd">
		<td class="hidden-xs"><div></div></td>
		<td class="hidden-xs"><div></div></td>
		<td><div></div></td>
	</tr>
	</table>
	
	<div class="height10"></div>

	<div class="center">
		<input type="submit" value="회원탈퇴하기" class="btn btn-danger" />
	</div>
	
	<div class="height10"></div>
</div>
{$formEnd}