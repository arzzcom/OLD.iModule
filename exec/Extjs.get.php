<?php
REQUIRE_ONCE '../config/default.conf.php';

header('Content-type: text/html; charset="UTF-8"', true);
header("Cache-Control: no-cache, must-revalidate"); 
header("Pragma: no-cache");

$mDB = &DB::instance();
$mMember = &Member::instance();
$action = Request('action');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$callbackStart = Request('callback') ? Request('callback').'(' : '';
$callbackEnd = Request('callback') ? ');' : '';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$list = array();

if ($action == 'address') {
	$keyword = Request('keyword');
	$data = $mDB->DBfetchs($_ENV['table']['zipcode'],array('zipcode','depth1','depth2','depth3','depth4'),"where `depth3` like '$keyword%'",'','0,50');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[] = '{"zipcode":"'.$data[$i]['zipcode'].'","address":"'.$data[$i]['depth1'].' '.$data[$i]['depth2'].' '.$data[$i]['depth3'].($data[$i]['depth4'] ? ' '.$data[$i]['depth4'] : '').'","value":"'.$data[$i]['depth1'].' '.$data[$i]['depth2'].' '.$data[$i]['depth3'].'"}';
	}
}

if ($action == 'member') {
	if ($mMember->IsLogged() == true) {
		$keyword = Request('keyword');
		$data = $mDB->DBfetchs($_ENV['table']['member'],array('idx','name','nickname','user_id'),"where `user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%'",$orderer,$limiter);
		$total = $mDB->DBcount($_ENV['table']['member'],"where `user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[] = '{"idx":"'.$data[$i]['idx'].'","user_id":"'.$data[$i]['user_id'].'",name:"'.$data[$i]['name'].'","nickname":"'.GetString($data[$i]['nickname'],'ext').'"}';
		}
	}
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>