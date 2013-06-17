<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mDatabase = new ModuleDatabase();
$idx = Request('idx');

if ($idx != null) {
	$data = $mDB->DBfetch($mDatabase->table['file'],array('repto','filepath','filetype'),"where `idx`=$idx");
	if ($data['filetype'] == 'IMG' && file_exists($_ENV['userfilePath'].$mDatabase->userfile.$data['filepath']) == true) {
		$check = getimagesize($_ENV['userfilePath'].$mDatabase->userfile.$data['filepath']);

		Header("Content-type: $check[mime]");

		readfile($_ENV['userfilePath'].$mDatabase->userfile.$data['filepath']);
	}
}
?>