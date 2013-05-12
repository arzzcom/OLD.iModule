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
			<td colspan="2">
				<div class="subinfo">
					쪽지 : <span class="point">0</span> | 포인트 : <span class="point">{$member.point|number_format}</span>
				</div>
				<div class="subbox">
					<div class="height5"></div>
					<iframe src="/inc/loginInfo.php?mno={$member.idx}&ver={php}echo $_GET['ver'];{/php}" style="width:100%; height:35px;" frameborder="0"></iframe>
				</div>
			</td>
		</tr>
		</table>
	</div>
</div>
{$formEnd}