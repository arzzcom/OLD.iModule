<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action == Request('action');
$get = Request('get');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$callbackStart = Request('callback') ? Request('callback').'(' : '';
$callbackEnd = Request('callback') ? ');' : '';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$list = array();

$mWebHard = new ModuleWebHard();

if ($action == 'list') {
	$dir = Request('dir') ? urldecode(Request('dir')) : '/';

	$data = $mDB->DBfetchs($mWebHard->table['file'],'*',"where `dir`='$dir'");

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i].= '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"filename":"'.$data[$i]['type'].'-'.GetString($data[$i]['filename'],'ext').'",';
		$list[$i].= '"dir":"'.$data[$i]['dir'].'",';
		$list[$i].= '"filesize":"'.$data[$i]['filesize'].'",';
		$list[$i].= '"reg_date":"'.GetTime('Y.m.d h:i:s a',$data[$i]['reg_date']).'",';
		$list[$i].= '"modify_date":"'.GetTime('Y.m.d h:i:s a',$data[$i]['modify_date']).'",';
		$list[$i].= '"download":"'.$data[$i]['download'].'"';
		$list[$i].= '}';
	}

	if ($dir != '/') $list[] = '{"filename":"DIRUP","filesize":"0"}';
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>