<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mPoll = new ModulePoll();
$idx = Request('idx');

if ($idx != null) {
	if (file_exists($_ENV['userfilePath'].$mPoll->userfile.'/'.$idx.'.file') == true) {
		$check = getimagesize($_ENV['userfilePath'].$mPoll->userfile.'/'.$idx.'.file');
		header("Content-type: $check[mime]");
		readfile($_ENV['userfilePath'].$mPoll->userfile.'/'.$idx.'.file');
	} else {
		header("HTTP/1.0 404 Not Found");
	}
} else {
	header("HTTP/1.0 404 Not Found");
}
?>