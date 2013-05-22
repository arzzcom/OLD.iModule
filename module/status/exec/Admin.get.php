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
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$lists = array();
$mStatus = new ModuleStatus();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'daylog') {
	$date = Request('date');
	for ($i=0;$i<=23;$i++) {
		$lists[$i] = array('hour'=>$i,'visit'=>0,'pageview'=>0);
	}
	
	$find = "where `date`='$date'";
	$data = $mDB->DBfetchs($mStatus->table['hour'],'*',$find);
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$lists[$data[$i]['hour']]['visit']+= $data[$i]['visit'];
		$lists[$data[$i]['hour']]['pageview']+= $data[$i]['pageview'];
	}
}

if ($action == 'monthlog') {
	$date = Request('date');
	
	for ($i=0, $loop=date('t',strtotime($date.'-01'));$i<$loop;$i++) {
		$lists[$i] = array('day'=>$i+1,'visit'=>0,'pageview'=>0);
	}
	
	$find = "where `date` like '$date%'";
	$data = $mDB->DBfetchs($mStatus->table['day'],'*',$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$day = date('j',strtotime($data[$i]['date']))-1;
		$lists[$day]['visit']+= $data[$i]['visit'];
		$lists[$day]['pageview']+= $data[$i]['pageview'];
	}
}

if ($action == 'log_visit') {
	$date = Request('date');
	$find = "where `date`='$date'";
	$type = Request('type');
	if ($type == 'MEMBER') $find.= " and `mno`!=0";
	
	$ip = Request('ip');
	$mno = Request('mno');
	
	if ($ip) $find = "where `ip`='$ip'";
	if ($mno) $find = "where `mno`='$mno'";

	$total = $mDB->DBcount($mStatus->table['log_visit'],$find);
	$lists = $mDB->DBfetchs($mStatus->table['log_visit'],'*',$find,$orderer,$limiter);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if ($lists[$i]['mno'] != 0) {
			$user = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $user['name'];
			$lists[$i]['user_id'] = $user['user_id'];
		} else {
			$lists[$i]['name'] = '';
			$lists[$i]['user_id'] = '';
		}
		
		$lists[$i]['visit_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['visit_time']);
	}
}

if ($action == 'log_bot') {
	if ($get == 'list') {
		$date = Request('date');
		$find = "where `date`='$date'";
	
		$lists = $mDB->DBfetchs($mStatus->table['log_bot'],'*',$find,$orderer);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['botcode'] = $lists[$i]['botname'];
			$lists[$i]['botname'] = $mStatus->GetBotName($lists[$i]['botname']);
			$lists[$i]['avgrevisit'] = sprintf('%0.2f',($lists[$i]['last_time']-$lists[$i]['first_time'])/$lists[$i]['visit']);
			$lists[$i]['first_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['first_time']);
			$lists[$i]['last_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['last_time']);
		}
	}
	
	if ($get == 'pie') {
		$start_date = Request('start_date');
		$end_date = Request('end_date');
		$find = "where `date`>='$start_date' and `date`<='$end_date'";
		$data = $mDB->DBfetchs($mStatus->table['log_bot'],'*',$find,$orderer);
		$visit = array();
		$totalVisit = 0;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$visit[$data[$i]['botname']] = isset($visit[$data[$i]['botname']]) == false ? 0 : $visit[$data[$i]['botname']];
			$visit[$data[$i]['botname']]+= $data[$i]['visit'];
			$totalVisit+= $data[$i]['visit'];
		}
		foreach ($visit as $key=>$value) {
			if (ceil($value/$totalVisit*100) < 5) $display = '';
			else $display = $mStatus->GetBotName($key);
			$lists[] = array('botname'=>$mStatus->GetBotName($key),'display'=>$display,'visit'=>$value);
		}
	}
	
	if ($get == 'chart') {
		$start_date = Request('start_date');
		$end_date = Request('end_date');
		$find = "where `date`>='$start_date' and `date`<='$end_date'";
		
		$data = $mDB->DBfetchs($mStatus->table['log_bot'],'*',$find);
		$visit = array();
		
		$bot = $mStatus->GetAllBotCode();
		$botList = array();
		for ($i=0, $loop=sizeof($bot);$i<$loop;$i++) {
			$botList[$bot[$i]] = 0;
		}
		$botList['others'] = 0;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$visit[$data[$i]['date']] = isset($visit[$data[$i]['date']]) == true ? $visit[$data[$i]['date']] : $botList;
			if (in_array($data[$i]['botname'],$bot) == true) {
				$visit[$data[$i]['date']][$data[$i]['botname']]+= $data[$i]['visit'];
			} else {
				$visit[$data[$i]['date']]['others']+= $data[$i]['visit'];
			}
		}
		
		$thisMonth = 0;
		$loop = 0;
		for ($i=strtotime($start_date);$i<=strtotime($end_date);$i=$i+60*60*24) {
			$list = isset($visit[date('Y-m-d',$i)]) == true ? $visit[date('Y-m-d',$i)] : $botList;
			$list['date'] = date('m-d',$i);
			$list['display'] = $thisMonth != date('m',$i) ? date('m-d',$i) : date('d',$i);
			$lists[] = $list;
			$loop++;
			if ($loop > 30) break;
			$thisMonth = date('m',$i);
		}
	}
}

if ($action == 'referer') {
	$date = Request('date');
	$find = "where `date`='$date'";
	$keyword = Request('keyword');
	$type = Request('type');
	if ($keyword) $find.= " and `keyword`='$keyword'";
	elseif ($type == 'KEYWORD') $find.= " and `keyword`!=''";

	$total = $mDB->DBcount($mStatus->table['referer'],$find);
	$lists = $mDB->DBfetchs($mStatus->table['referer'],'*',$find,$orderer,$limiter);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['visit_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['visit_time']);
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>