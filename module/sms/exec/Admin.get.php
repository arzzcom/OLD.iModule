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

$mSMS = new ModuleSMS();

if ($action == 'list') {
	$find = '';
	$data = $mDB->DBfetchs($mSMS->table['send'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mSMS->table['send'],$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		if ($data[$i]['mno'] != '0') {
			$sender = &Member::instance()->GetMemberInfo($data[$i]['mno']);
			$sender = $sender['name'].'('.$sender['user_id'].')';
		} else {
			$sender = $data[$i]['sender'];
		}

		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"sender":"'.GetString($sender,'ext').'",';
		$list[$i].= '"receiver":"'.$data[$i]['receiver'].'",';
		$list[$i].= '"content":"'.GetString($data[$i]['content'],'ext').'",';
		$list[$i].= '"send_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['send_date']).'",';
		$list[$i].= '"result":"'.$data[$i]['result'].'"';
		$list[$i].= '}';
	}
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>