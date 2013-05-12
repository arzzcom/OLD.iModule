<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mOneroom = new ModuleOneroom();
$idx = Request('idx');

if ($idx != null) {
	$data = $mDB->DBfetch($mOneroom->table['file'],array('repto','filepath','filetype'),"where `idx`='$idx'");
	if ($data['filetype'] == 'IMG' && file_exists($_ENV['userfilePath'].$mOneroom->userfile.$data['filepath']) == true) {
		$check = getimagesize($_ENV['userfilePath'].$mOneroom->userfile.$data['filepath']);

		Header("Content-type: $check[mime]");

		readfile($_ENV['userfilePath'].$mOneroom->userfile.$data['filepath']);
	}
}
?>