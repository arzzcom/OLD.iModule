<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mEmail = new ModuleEmail();
$idx = Request('idx');

if ($idx != null) {
	$data = $mDB->DBfetch($mEmail->table['file'],array('repto','filepath','filetype'),"where `idx`='$idx'");
	if ($data['filetype'] == 'IMG' && file_exists($_ENV['userfilePath'].$mEmail->userfile.$data['filepath']) == true) {
		$check = getimagesize($_ENV['userfilePath'].$mEmail->userfile.$data['filepath']);

		Header("Content-type: $check[mime]");

		readfile($_ENV['userfilePath'].$mEmail->userfile.$data['filepath']);
	}
}
?>