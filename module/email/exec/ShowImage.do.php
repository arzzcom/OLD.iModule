<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mEmail = new ModuleEmail();
$idx = Request('idx');
if ($idx != null) {
	$data = $mDB->DBfetch($mEmail->table['file'],array('filepath','filetype'),"where `idx`=$idx");
	if ($data['filetype'] == 'IMG' && file_exists($_ENV['path'].$data['filepath']) == true) {
		$check = getimagesize($_ENV['path'].$data['filepath']);

		Header("Content-type: $check[mime]");

		readfile($_ENV['path'].$data['filepath']);
	}
}
?>