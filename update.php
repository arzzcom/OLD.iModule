<?php
REQUIRE_ONCE './config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();


if ($member['type'] == 'ADMINISTRATOR') {
	GetDefaultHeader('아이모듈 업데이트','',array(
		array('type'=>'css','css'=>$_ENV['dir'].'/css/install.css')
	));
?>
<script type="text/javascript">
function Update() {
	if (confirm("정말 업데이트를 진행하시겠습니까?\n이 작업은 취소될 수 없습니다.") == true) {
		document.getElementById("installButton").innerHTML = "업데이트를 진행중입니다. 잠시만 기다려주시기 바랍니다.";
		updateFrame.location.href = "<?php echo $_ENV['dir']; ?>/exec/install.do.php?action=update";
	}
}
</script>
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="height100">
	<td></td>
</tr>
<tr class="updateTitle">
	<td><div><span class="version">Ver.2.0.0</span></div></td>
</tr>
<tr>
	<td>
		<div class="installBox">
			아이모듈을 업데이트 합니다.<br /><br />
			새로운 아이모듈버전을 다운받아 서버에 업로드를 한 경우나, DB및 폴더구조의 유실로 아이모듈이 정상동작하지 않을 때 업데이트를 하면 정상적으로 사용하실 수 있습니다.<br /><br />
			만약을 대비하여 업데이트 진행하기전에 중요데이터 백업을 권장하며, 완료될때까지 페이지이동 등을 삼가해주시기 바랍니다.
		</div>

		<div id="installButton"><img src="<?php echo $_ENV['dir']; ?>/images/install/btn_update.gif" class="pointer" onclick="Update();" /></div>
	</td>
</tr>
</table>
<iframe name="updateFrame" style="display:none;"></iframe>
<?php
	GetDefaultFooter();
} else {
	GetDefaultHeader('사이트관리 로그인');
	$mMember = new ModuleMember();
	$mMember->PrintLoginForm('admin');
	GetDefaultFooter();
}
?>