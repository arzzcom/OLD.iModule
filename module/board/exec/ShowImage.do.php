<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mBoard = new ModuleBoard();
$idx = Request('idx');
if ($idx != null) {
	$data = $mDB->DBfetch($mBoard->table['file'],array('repto','filepath','filetype'),"where `idx`='$idx'");
	$post = $mDB->DBfetch($mBoard->table['post'],array('is_delete'),"where `idx`='{$data['repto']}'");

	if ($mBoard->IsAdmin() == true || $data['repto'] == '0' || (isset($post['is_delete']) == true && $post['is_delete'] == 'FALSE')) {
		if ($data['filetype'] == 'IMG' && file_exists($_ENV['userfilePath'].$mBoard->userfile.$data['filepath']) == true) {
			$check = getimagesize($_ENV['userfilePath'].$mBoard->userfile.$data['filepath']);
			$mDB->DBupdate($mBoard->table['file'],'',array('hit'=>'`hit`+1'),"where `idx`='$idx'");
	
			Header("Content-type: $check[mime]");
	
			readfile($_ENV['userfilePath'].$mBoard->userfile.$data['filepath']);
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	} else {
		header("HTTP/1.0 404 Not Found");
	}
}
?>