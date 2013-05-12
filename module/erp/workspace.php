<?php
REQUIRE_ONCE '../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();

$mErp = new ModuleErp();
$mode = Request('mode');

if ($mode == null) {
	if ($mMember->IsLogged() == true && $mErp->CheckWorkspaceMaster() == true) {
		$mErp->PrintWorkspace('manager');
	} else {
		$mErp->PrintWorkspace('default');
	}
} else {
	$mErp->PrintWorkspace($mode);
}
?>