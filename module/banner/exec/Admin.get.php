<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mBanner = new ModuleBanner();

$action = Request('action');
$get = Request('get');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$lists = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'section') {
	if ($get == 'list') {
		$keyword = Request('keyword');
		$find = '';
		if ($keyword) $find.= "where `code` like '%$keyword%' or `title` like '%$keyword%'";
		$lists = $mDB->DBfetchs($mBanner->table['section'],'*',$find,$orderer,$limiter);
	}
	
	if ($get == 'data') {
		$code = Request('code');
		$data = $mDB->DBfetch($mBanner->table['section'],'*',"where `code`='$code'");
		
		$fileType = explode(',',$data['filetype']);
		if (in_array('IMG',$fileType) == true) $data['IMG'] = 'on';
		if (in_array('SWF',$fileType) == true) $data['SWF'] = 'on';
		if (in_array('TEXT',$fileType) == true) $data['TEXT'] = 'on';
		
		$data['allow_user'] = $data['allow_user'] == 'TRUE' ? 'on' : 'off';
		$data['auto_active'] = $data['auto_active'] == 'TRUE' ? 'on' : 'off';
		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
}

if ($action == 'item') {
	if ($get == 'list') {
		$find = 'where 1';
		
		$lists = $mDB->DBfetchs($mBanner->table['item'],'*',$find,$orderer,$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$itemCount = $mBanner->GetItemCount($lists[$i]['code']);
			$section = $mDB->DBfetch($mBanner->table['section'],array('title'),"where `code`='{$lists[$i]['code']}'");
			$lists[$i]['code'] = $section['title'].'('.$lists[$i]['code'].')';
			
			if ($lists[$i]['mno'] != '0') {
				$master = $mDB->DBfetch($_ENV['table']['member'],array('name','user_id'),"where `idx`='{$lists[$i]['mno']}'");
				$lists[$i]['master'] = $master['name'].'('.$master['user_id'].')';
			}
			
			if ($lists[$i]['is_active'] == 'TRUE' && $lists[$i]['type'] == 'CPM' && $lists[$i]['start_date'] <= date('Y-m-d') && $lists[$i]['end_date'] >= date('Y-m-d')) {
				$lists[$i]['percent'] = 1/$itemCount['totalItem']*100;
			} else {
				$lists[$i]['percent'] = 0;
			}
			
			if ($lists[$i]['type'] == 'CPC' && $lists[$i]['paid_point'] > 0) {
				$lists[$i]['percent'] = $lists[$i]['point']/$itemCount['totalCPCPoint']*($itemCount['totalCPC']/$itemCount['totalItem']*100);
			}
		}
	}
	
	if ($get == 'data') {
		$idx = Request('idx');
		$data = $mDB->DBfetch($mBanner->table['item'],'*',"where `idx`='$idx'");
		$section = $mDB->DBfetch($mBanner->table['section'],'*',"where `code`='{$data['code']}'");
		
		$data['section'] = $section;
		
		$data['start_date'] = $data['start_date'] == '0000-00-00' ? date('Y-m-d') : $data['start_date'];
		$data['end_date'] = $data['end_date'] == '0000-00-00' ? date('Y-m-d',mktime(0,0,0,date('m'),date('d')+30,date('Y'))) : $data['end_date'];

		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
}

if ($action == 'status') {
	if ($get == 'day') {
		$date = Request('date');
		$bno = Request('bno');
		
		for ($i=0;$i<=23;$i++) {
			$lists[$i] = array('hour'=>$i,'view'=>0,'hit'=>0,'cview'=>0,'chit'=>0);
		}
		
		$find = "where `date`='$date'";
		if ($bno) $find.= " and `bno`='$bno'";
		$data = $mDB->DBfetchs($mBanner->table['log_count'],'*',$find);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$lists[$data[$i]['hour']]['view']+= $data[$i]['view'];
			$lists[$data[$i]['hour']]['hit']+= $data[$i]['hit'];
		}
	}
	
	if ($get == 'month') {
		$date = Request('date');
		$bno = Request('bno');
		
		for ($i=0, $loop=date('t',strtotime($date.'-01'));$i<$loop;$i++) {
			$lists[$i] = array('day'=>$i+1,'view'=>0,'hit'=>0,'cview'=>0,'chit'=>0);
		}
		
		$find = "where `date` like '$date%'";
		if ($bno) $find.= " and `bno`='$bno'";
		$data = $mDB->DBfetchs($mBanner->table['log_count'],'*',$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$day = date('j',strtotime($data[$i]['date']))-1;
			$lists[$day]['view']+= $data[$i]['view'];
			$lists[$day]['hit']+= $data[$i]['hit'];
		}
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>