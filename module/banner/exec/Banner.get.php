<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();

$callback = Request('callback');
$section = Request('section');
$limit = Request('limit');

$mBanner = new ModuleBanner();
$item = $mBanner->GetItem($section,$limit);

$section = $mDB->DBfetch($mBanner->table['section'],'*',"where `code`='$section'");
$data = $mDB->DBfetchs($mBanner->table['item'],'*',"where `idx` IN (".implode(',',$item).")");
$banner = array();
for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
	$banner[$i] = array('type'=>$data[$i]['bannertype'],'file'=>'http://'.$_SERVER['HTTP_HOST'].$mBanner->moduleDir.'/exec/ShowBanner.do.php?idx='.$data[$i]['idx'],'url'=>'http://'.$_SERVER['HTTP_HOST'].$mBanner->moduleDir.'/exec/Click.do.php?idx='.$data[$i]['idx']);
}

$return = array();
$return['success'] = true;
$return['width'] = $section['width'];
$return['height'] = $section['height'];
$return['banner'] = $banner;

if ($callback) {
	echo $callback.'('.json_encode($return).');';
} else {
	exit(json_encode($return));
}
?>