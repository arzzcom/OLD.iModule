<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

if ($member['type'] == 'ADMINISTRATOR') {
	if (isset($_SESSION['isAdminLog']) == false) {
		SaveAdminLog('admin','관리자 페이지에 접근하였습니다.');
		$_SESSION['isAdminLog'] = true;
	}
	$page = Request('page') ? Request('page') : 'main';
	$subpage = Request('subpage');
	
	$isExt3 = false;
	if ($page == 'module') {
		if ($subpage) {
			if (file_exists($_ENV['path'].'/module/'.$subpage.'/admin/category.inc.php') == true) {
				$isExt3 = false;
			} else {
				$isExt3 = true;
			}
		}
	} else {
		if (file_exists($_ENV['path'].'/admin/'.$page.'.category.inc.php') == true) {
			$isExt3 = false;
		} else {
			$isExt3 = true;
		}
	}
	
	if ($isExt3 == true) {
		REQUIRE_ONCE '../admin.extjs3/admin.php';
	} else {
		REQUIRE_ONCE './admin.php';
	}
} else {
	GetDefaultHeader('사이트관리 로그인');
	$mMember = new ModuleMember();
	$mMember->PrintLoginForm('admin');
	GetDefaultFooter();
}
?>
