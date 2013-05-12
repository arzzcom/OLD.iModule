<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mBoard = new ModuleBoard();
$idx = Request('idx');
if ($idx != null) {
	$data = $mDB->DBfetch($mBoard->table['file'],array('repto','filepath','filetype'),"where `idx`=$idx");
	$post = $mDB->DBfetch($mBoard->table['post'],array('is_delete'),"where `idx`='{$data['repto']}'");

	if (is_set($post['is_delete']) == true && $post['is_delete'] == 'FALSE' && $data['filetype'] == 'IMG' && file_exists($_ENV['path'].$data['filepath']) == true) {
		$check = getimagesize($_ENV['path'].$data['filepath']);

		Header("Content-type: $check[mime]");

		readfile($_ENV['path'].$data['filepath']);
	} else {
		header("HTTP/1.0 404 Not Found");
	}
}
?>