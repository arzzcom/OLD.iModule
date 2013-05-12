{$formStart}
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="50%" /><col width="10" /><col width="50%" />
<tr>
	<td class="f12 vTop" style="line-height:1.6;">
		이미 커뮤니티 사이트에 가입하였다면, <span class="pointText">회원아이디와 패스워드를 입력하여 가입</span>할 수 있습니다.<br />
		If you join the community site already, <span class="pointText">you can sign up by entering your userid and password.</span>
		
		<div class="height20"></div>
		
		<table cellpadding="0" cellspacing="1" class="layoutfixed trackerFormTable">
		<col width="80" /><col width="100%" />
		<tr>
			<td class="header">회원아이디<br /><span class="gray tahoma f10">UserID</span></td>
			<td class="content"><input type="text" name="user_id" class="inputbox" style="width:235px;" /></td>
		</tr>
		<tr>
			<td class="header">패스워드<br /><span class="gray tahoma f10">Password</span></td>
			<td class="content"><input type="password" name="password" class="inputbox" style="width:235px;" /></td>
		</tr>
		</table>
		
		<div class="height10"></div>
		<div class="innerimg right">
			<input type="image" src="{$skinDir}/images/btn_signin.png" />
		</div>
	</td>
	<td></td>
	<td class="f12 vTop" style="line-height:1.6;">
		신규로 가입하고자하는 경우 아래의 버튼을 눌러 가입절차를 계속 진행할 수 있습니다.<br />
		Press the button below if you wish to join a new sign-up process can continue.

		<div style="height:116px;"></div>
		<div class="innerimg right">
			<a href="{$link.next}"><img src="{$skinDir}/images/btn_next.png" /></a>
		</div>
	</td>
</tr>
</table>
{$formEnd}