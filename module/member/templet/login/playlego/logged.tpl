{$formStart}
<div class="LoginBox">
	<div class="LoginUser">
		<span class="bold">{$member.name}</span>님 환영합니다.
	</div>
	<div class="LoginBar"></div>
	<div class="LoginInfo">
	나의포인트 : <span class="tahoma"><span class="bold">{$member.point|number_format}</span>POINT</span><br />
	나의활동지수 : <span class="tahoma">LV.<span class="bold" style="color:#EF5600;">{$member.level.lv}</span> / <span class="bold">{$member.level.exp|number_format}</span>EXP</span><br />
	새쪽지 : <b></b> / 친구요청 : <b></b>
	</div>

	<div class="LoginBottom">
		<span class="selectedtext pointer" onclick="OpenPopup('/mypage.php?idx=<?php echo $member['idx']; ?>',980,640)">마이플레</span>&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;<a href="{$link.logout}" target="{$execTarget}" onclick="return confirm('정말 로그아웃하시겠습니까?');"><span class="pointedtext">로그아웃</span></a>
	</div>
</div>
{$formEnd}