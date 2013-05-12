<?php

REQUIRE_ONCE '../../../config/default.conf.php';

$idx = Request('idx');

$mDB = &DB::instance();
$mTracker = new ModuleTracker();

$data = $mDB->DBfetch('test','*',"where `idx`='2'");

echo base64_decode($data['content']);
exit;

$hash = $mTracker->GetInfoHashToSHA1(Request('info_hash'));

//if ($mTracker->CheckIsRegisterTorrent($hash) == true) {
//	bencode(array('failure reason' => $reason)));
//}
?>