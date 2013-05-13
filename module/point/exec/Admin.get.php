<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mPoint = new ModulePoint();

$action = Request('action');
$get = Request('get');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$lists = array();
$return = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	$return['message'] = '관리권한이 없습니다.';
	$return['totalCount'] = 0;
	$return['lists'] = $lists;
	exit(json_encode($return));
}

if ($action == 'payment') {
	if ($get == 'list') {
		$find = '';
		
		$lists = $mDB->DBfetchs($mPoint->table['payment'],'*',$find,$orderer);
	}
	
	if ($get == 'info') {
		$idx = Request('idx');
		
		$data = $mDB->DBfetch($mPoint->table['payment'],'*',"where `idx`='$idx'");
		
		switch ($data['type']) {
			case 'BANKING' :
				if (preg_match('/(.*?) (.*?) \(([^\)]+)\)/',$data['value'],$match) == true) {
					$data['banking1'] = $match[1];
					$data['banking2'] = $match[2];
					$data['banking3'] = $match[3];
				}
				break;
		}
		
		$data['is_use'] = $data['is_use'] == 'TRUE' ? 'on' : 'off';
		$return['success'] = true;
		$return['data'] = $data;
		
		exit(json_encode($return));
	}
}

$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>