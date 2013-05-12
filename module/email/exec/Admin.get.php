<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
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

$mEmail = new ModuleEmail();

if ($action == 'list') {
	$find = '';
	$data = $mDB->DBfetchs($mEmail->table['send'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mEmail->table['send'],$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"sender":"'.GetString(GetString($data[$i]['from'],'replace'),'ext').'",';
		$list[$i].= '"receiver":"'.implode(', ',explode("\n",GetString($data[$i]['to'],'replace'))).'",';
		$list[$i].= '"subject":"'.GetString($data[$i]['subject'],'ext').'",';
		$list[$i].= '"send_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['send_date']).'",';
		$list[$i].= '"result":"'.$data[$i]['result'].'",';
		$list[$i].= '"read_time":"'.($data[$i]['read_time'] != '0' ? GetTime('Y.m.d H:i:s',$data[$i]['read_time']) : '').'"';
		$list[$i].= '}';
	}
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>