<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mKin = new ModuleKin();
$idx = Request('idx');

if ($idx != null) {
	$data = $mDB->DBfetch($mKin->table['file'],array('type','repto','filepath','filetype'),"where `idx`=$idx");
	if ($data['repto'] == '0') $post['is_delete'] = 'FALSE';
	else $post = $mDB->DBfetch($mKin->table[strtolower($data['type'])],array('is_delete'),"where `idx`='{$data['repto']}'");

	if (isset($post['is_delete']) == true && $post['is_delete'] == 'FALSE' && $data['filetype'] == 'IMG' && file_exists($_ENV['path'].$data['filepath']) == true) {
		$check = getimagesize($_ENV['path'].$data['filepath']);

		Header("Content-type: $check[mime]");

		readfile($_ENV['path'].$data['filepath']);
	} else {
		header("HTTP/1.0 404 Not Found");
	}
}
?>