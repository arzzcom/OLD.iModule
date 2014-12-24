{$formStart}
	<div class="height5"></div>
	<div class="titlebox">회원로그인</div>
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="80" /><col width="100%" />
		<tr>
			<td class="header">아이디</td>
			<td class="input"><input type="text" name="user_id" msg="회원아이디를 입력하세요." /></td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="header">패스워드</td>
			<td class="input"><input type="password" name="password" msg="패스워드를 입력하세요." /></td>
		</tr>
		</table>
	</div>
	
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="30" /><col width="100%" />
		<tr>
			<td class="check"><input type="checkbox" name="autologin" id="autologin" value="1" onclick="MemberLoginAutoLogin(this.id);" msg="현재 모바일기기에서 항상 자동으로 로그인을 하도록 설정하시겠습니까?" /></td>
			<td class="input">항상 로그인상태를 유지합니다.</td>
		</tr>
		</table>
	</div>
	
	<div class="submitbox">
		<input type="submit" value="회원로그인" style="height:30px; line-height:30px;" />
	</div>
{$formEnd}