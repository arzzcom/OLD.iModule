<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mMember = new ModuleMember();
$mOneroom = new ModuleOneroom();

GetDefaultHeader('매물관리시스템 로그인');

$mMember->PrintLoginForm('full');

if (preg_match('/AIR/',$_SERVER['HTTP_USER_AGENT']) == false) {
?>
<div style="width:450px; margin:0 auto; position:relative;">
	<div style="position:absolute; top:-154px; left:455px; z-index:1; font-family:NanumGothicWeb; color:#CCCCCC; width:160px; margin:0 auto; border:2px solid #4F4F4F; background:#000000; padding:8px; font-size:11px; line-height:1.6;">
		프로그램을 별도로 고객님의 컴퓨터에 설치하여, 웹사이트에 접속하지 않고도 원룸매니져의 모든 기능을 사용해보세요.
	</div>
	
	<div style="position:absolute; top:-54px; left:455px; z-index:1;">
		<?php $mOneroom->PrintManagerProgram($_ENV['dir'].'/module/oneroom/images/common/btn_manager_air.gif',180,43); ?>
	</div>
</div>
<?php
}
GetDefaultFooter();
?>