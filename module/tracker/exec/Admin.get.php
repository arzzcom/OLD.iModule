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

$mTracker = new ModuleTracker();

if ($action == 'layout') {
	$type = strtoupper(Request('type'));
	$data = $mDB->DBfetchs($mTracker->table['layout'],'*',"where `type`='$type'");

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"title":"'.$data[$i]['title'].'"';
		$list[$i].= '}';
	}
}

if ($action == 'category') {
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$data = $mDB->DBfetchs($mTracker->table['category'],'*',$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"title":"'.$data[$i]['title'].'",';
		$list[$i].= '"form_layout":"'.$data[$i]['form_layout'].'",';
		$list[$i].= '"view_layout":"'.$data[$i]['view_layout'].'",';
		$list[$i].= '"search_layout":"'.$data[$i]['search_layout'].'",';
		$list[$i].= '"artist_layout":"'.$data[$i]['artist_layout'].'",';
		$list[$i].= '"sort":"'.$data[$i]['sort'].'",';
		$list[$i].= '"item":"0"';
		$list[$i].= '}';
	}

	if ($is_all == 'true') {
		$list[] = '{"idx":"0","title":"전체","sort":"-1"}';
	}

	if ($is_none == 'true') {
		$list[] = '{"idx":"0","title":"선택안함(없음)","sort":"-1"}';
	}
}

if ($action == 'tag') {
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$category = Request('category');
	$find = "where `category1`='$category'";
	$data = $mDB->DBfetchs($mTracker->table['tag'],'*',$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"title":"'.$data[$i]['title'].'",';
		$list[$i].= '"sort":"'.$data[$i]['sort'].'",';
		$list[$i].= '"item":"0"';
		$list[$i].= '}';
	}

	if ($is_all == 'true') {
		$list[] = '{"idx":"0","title":"전체","sort":"-1"}';
	}

	if ($is_none == 'true') {
		$list[] = '{"idx":"0","title":"선택안함(없음)","sort":"-1"}';
	}
}

if ($action == 'torrent') {
	$category = Request('category');
	$key = Request('key');
	$keyword = Request('keyword');
	
	$data = $mDB->DBfetchs($mTracker->table['torrent'],'*',$find,$orderer,$limiter);
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		
		$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
		$list[$i].= '"file":"'.$data[$i]['file'].'",';
		$list[$i].= '"filesize":"'.$data[$i]['filesize'].'",';
		$list[$i].= '"snatch":"'.$data[$i]['snatch'].'",';
		$list[$i].= '"seeder":"'.$data[$i]['seeder'].'",';
		$list[$i].= '"leecher":"'.$data[$i]['leecher'].'",';
		$list[$i].= '"downloadsize":"'.$data[$i]['downloadsize'].'",';
		$list[$i].= '"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'"';
		$list[$i].= '}';
	}
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>