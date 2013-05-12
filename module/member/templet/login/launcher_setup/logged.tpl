{$formStart}
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="nextTitle">
	<td><div></div></td>
</tr>
<tr>
	<td>
		<div class="nextBox">
			{php}
			$mDB = &DB::instance();
			$member = &Member::instance()->GetMemberInfo();
			$check = $mDB->DBfetch('launcher_config_table',array('title'),"where `mno`='{$member['idx']}'");
			if (isset($check['title']) == false) {
				echo '현재 회원님의 아이디('.$member['user_id'].')로 생성된 런쳐가 없습니다.<br />다음단계버튼을 클릭하시면 런쳐를 생성할 수 있습니다.';
			} else {
				echo '현재 회원님의 아이디('.$member['user_id'].')로 생성된 런쳐('.$check['title'].')가 존재합니다.<br />다음단계버튼을 클릭하면 현재 아이피('.$_SERVER['REMOTE_ADDR'].')를 추가할 수 있습니다.';
			}
			{/php}
			<br />
			로그아웃 버튼을 클릭하시면, 다른 아이디로 로그인할 수 있습니다.
		</div>

		<div class="nextButton">
			<a href="./setting/setup.php"><img src="{$skinDir}/images/btn_next.gif" style="margin-right:5px;" /></a>
			<a href="{$link.logout}" target="{$execTarget}" onclick="return confirm('정말 로그아웃하시겠습니까?');"><img src="{$skinDir}/images/btn_logout.gif" /></a>
		</div>
	</td>
</tr>
</table>
{$formEnd}