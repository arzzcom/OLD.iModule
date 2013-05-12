{$formStart}
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="errorTitle">
	<td><div></div></td>
</tr>
<tr>
	<td>
		<div class="errorBox">
			관리자가 아닌 다른아이디로 로그인되어 있습니다.<br />
			로그아웃 하신 후 관리자 아이디로 로그인이 가능합니다.
		</div>

		<div class="errorButton"><a href="{$link.logout}" target="{$execTarget}" onclick="return confirm('정말 로그아웃하시겠습니까?');"><img src="{$skinDir}/images/btn_logout.gif" /></a></div>
	</td>
</tr>
</table>
{$formEnd}