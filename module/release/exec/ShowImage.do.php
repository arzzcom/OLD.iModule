<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mRelease = new ModuleRelease();
$idx = Request('idx');
if ($idx != null) {
	$data = $mDB->DBfetch($mRelease->table['file'],array('repto','filepath','filetype'),"where `idx`='$idx'");
	$post = $mDB->DBfetch($mRelease->table['post'],array('is_delete'),"where `idx`='{$data['repto']}'");

	if ($mRelease->IsAdmin() == true || $data['repto'] == '0' || (isset($post['is_delete']) == true && $post['is_delete'] == 'FALSE')) {
		if ($data['filetype'] == 'IMG' && file_exists($_ENV['userfilePath'].$mRelease->userfile.$data['filepath']) == true) {
			$check = getimagesize($_ENV['userfilePath'].$mRelease->userfile.$data['filepath']);
			$mDB->DBupdate($mRelease->table['file'],'',array('hit'=>'`hit`+1'),"where `idx`='$idx'");
	
			Header("Content-type: $check[mime]");
	
			readfile($_ENV['userfilePath'].$mRelease->userfile.$data['filepath']);
		} else {
			header("HTTP/1.0 404 Not Found");
		}
	} else {
		header("HTTP/1.0 404 Not Found");
	}
}
?>