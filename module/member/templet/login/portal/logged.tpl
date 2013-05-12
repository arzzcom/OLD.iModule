{$formStart}
<div class="MemberLoginPortal">
	<div class="innerLayer">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="70" /><col width="100%" /><col width="60" />
		<tr class="welcome">
			<td colspan="2" class="f12 vTop"><span class="bold">{$member.name}</span>님 환영합니다.</td>
			<td class="right innerimg vTop"><a href="{$link.logout}" target="{$execTarget}" onclick="return confirm('정말 로그아웃하시겠습니까?');"><img src="{$skinDir}/images/btn_logout.gif" /></a></td>
		</tr>
		<tr>
			<td><img src="{$member.photo}" class="photo" /></td>
			<td>
				<div class="nickname">{$member.nickname} <span>({$member.user_id})</span></div>
				<div class="expbar">
					<table cellpadding="0" cellspacing="0" class="layoutfixed">
					<col width="100%" /><col width="1" /><col width="{$member.level.exp/$member.level.next*75|ceil}" /><col width="{$member.level.exp/$member.level.next*-75+75|ceil}" /><col width="2" />
					<tr title="{$member.level.exp|number_format}/{$member.level.next|number_format}">
						<td class="text">LV.<span class="level">{$member.level.lv}</span></td>
						<td class="start"></td>
						<td class="on"></td>
						<td class="off"></td>
						<td class="end"></td>
					</tr>
					</table>
				</div>
				<div class="point"><span class="bold">Point : </span>{$member.point|number_format}</div>
				<div class="message" onclick="location.href='{$link.msgbox}';"><span class="bold">Message</span> : {$message.new} / {$message.all} {$message.checker}</div>
			</td>
			<td class="innerimg right">
				<a href="{$link.msgbox}"><img src="{$skinDir}/images/btn_msg.gif" /></a><br />
				<a href="{$link.myinfo}"><img src="{$skinDir}/images/btn_mypage.gif" /></a>
			</td>
		</tr>
		</table>
	</div>
</div>
{$formEnd}