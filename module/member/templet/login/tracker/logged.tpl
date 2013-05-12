{$formStart}
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr>
	<td>
		<div class="errorBox">
			트래커 유저가 아닌 일반유저 아이디로 로그인되어 있습니다.
		</div>

		<div class="errorButton"><a href="{$link.logout}" target="{$execTarget}" onclick="return confirm('정말 로그아웃하시겠습니까?');"><img src="{$skinDir}/images/btn_logout.gif" /></a></div>
	</td>
</tr>
</table>
{$formEnd}