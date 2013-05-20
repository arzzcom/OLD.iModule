<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$mStatus = new ModuleStatus();

$return = array();
$errors = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'log_visit_delete') {
	$mDB->DBtruncate($mStatus->table['log_visit']);

	$return['success'] = true;
	exit(json_encode($return));
}

if ($action == 'log_bot_delete') {
	$bot = Request('bot');
	$date = Request('date');
	
	if ($bot == null && $date == null) {
		$mDB->DBtruncate($mStatus->table['log_bot']);
	} else {
		$mDB->DBdelete($mStatus->table['log_bot'],"where `date`='$date' and `botname`='$bot'");
	}

	$return['success'] = true;
	exit(json_encode($return));
}
?>