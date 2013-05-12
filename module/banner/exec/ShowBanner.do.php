<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$idx = Request('idx');
$mDB = &DB::instance();
$mBanner = new ModuleBanner();

$data = $mDB->DBfetch($mBanner->table['item'],'*',"where `idx`='$idx'");

if (isset($data['idx']) == true) {
	if (file_exists($_ENV['userfilePath'].$mBanner->userfile.$data['bannerpath']) == true) {
		$mBanner->ItemView($data['idx']);
		$check = getimagesize($_ENV['userfilePath'].$mBanner->userfile.$data['bannerpath']);
		header("Content-type:$check[mime]");
		readfile($_ENV['userfilePath'].$mBanner->userfile.$data['bannerpath']);
	} else {
		header("HTTP/1.0 404 Not Found");
	}
} else {
	header("HTTP/1.0 404 Not Found");
}
?>