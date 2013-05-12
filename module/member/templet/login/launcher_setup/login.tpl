<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="loginTitle">
	<td><div></div></td>
</tr>
<tr>
	<td>
		<div class="loginBox">
			{$formStart}
			<table cellspacing="0" cellpadding="0" class="layoutfixed">
			<col width="105" /><col width="100%" />
			<tr>
				<td><img src="{$skinDir}/images/text_user_id.gif" alt="아이디" /></td>
				<td><input type="text" name="user_id" class="inputbox" msg="아이디를 입력하여 주십시오." /></td>
			</tr>
			<tr class="height10">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td><img src="{$skinDir}/images/text_password.gif" alt="패스워드" /></td>
				<td><input type="password" name="password" class="inputbox" msg="패스워드를 입력하여 주십시오." /></td>
			</tr>
			</table>

			<div class="loginButton">
				<table cellspacing="0" cellpadding="0" class="layoutfixed">
				<col width="100%" /><col width="205" /><col width="65" />
				<tr>
					<td></td>
					<td></td>
					<td><input type="image" src="{$skinDir}/images/btn_login.gif" /></td>
				</tr>
				</table>
			</div>
			{$formEnd}
		</div>
	</td>
</tr>
</table>