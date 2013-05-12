<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$idx = Request('idx');
$mDB = &DB::instance();
$mBanner = new ModuleBanner();

$data = $mDB->DBfetch($mBanner->table['item'],'*',"where `idx`='$idx'");

if (isset($data['idx']) == true) {
	$mBanner->ItemClick($data['idx']);
	header("location:{$data['url']}");
} else {
	header("HTTP/1.0 404 Not Found");
}
?>