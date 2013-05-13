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

$return = array();
$lists = array();

$mOneroom = new ModuleOneroom();

if ($action == 'mypoint') {
	$return['success'] = true;
	$return['point'] = $member['point'];
	exit(json_encode($return));
}

if ($action == 'region') {
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$lists = $mDB->DBfetchs($mOneroom->table['region'],'*',$find);

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'0','title'=>'전체','sort'=>'-1');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'0','title'=>'선택안함(없음)','sort'=>'-1');
	}
}

if ($action == 'category') {
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$parent = Request('parent');
	$find = "where `parent`='$parent'";

	$lists = $mDB->DBfetchs($mOneroom->table['category'],'*',$find);

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'0','title'=>'전체','sort'=>'-1');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'0','title'=>'선택안함(없음)','sort'=>'-1');
	}
}

if ($action == 'option') {
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	
	$lists = $mDB->DBfetchs($mOneroom->table['option'],'*',$find);
}

if ($action == 'database') {
	$subaction = Request('subaction');
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	
	if ($subaction == 'university') {
		$lists = $mDB->DBfetchs($mOneroom->table['university'],'*',$find);
	}
	
	if ($subaction == 'subway') {
		$lists = $mDB->DBfetchs($mOneroom->table['subway'],'*',$find);
	}

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'0','title'=>'전체','sort'=>'-1');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'0','title'=>'선택안함(없음)','sort'=>'-1');
	}
}

if ($action == 'item') {
	if ($get == 'register_point') {
		$return['success'] = true;
		$return['point'] = $mOneroom->GetConfig('register_point');
		$return['mypoint'] = $member['point'];
		exit(json_encode($return));
	}
	
	if ($get == 'list') {
		$idx = Request('idx');
		$find = "where `mno`='{$member['idx']}'";
		
		if (is_numeric($idx) == true && $idx > 0) {
			$find.= " and `idx` like '$idx%'";
		} else {
			$is_open = Request('is_open');
			$type = Request('type');
			$category1 = $mOneroom->GetZeroValue('category1');
			$category2 = $mOneroom->GetZeroValue('category2');
			$category3 = $mOneroom->GetZeroValue('category3');
			
			$region1 = $mOneroom->GetZeroValue('region1');
			$region2 = $mOneroom->GetZeroValue('region2');
			$region3 = $mOneroom->GetZeroValue('region3');
			
			$keyword = Request('keyword');
			
			if ($is_open != null) $find.= " and `is_open`='$is_open'";
			
			if ($type != null && $type != 'all') {
				if ($type == 'premium') $find.= " and `is_premium`='TRUE'";
				if ($type == 'regionitem') $find.= " and `is_regionitem`='TRUE'";
				if ($type == 'default') $find.= " and `is_open`='TRUE' and `is_premium`='FALSE' and `is_regionitem`='FALSE'";
				if ($type == 'wait') $find.= " and `is_open`='FALSE'";
			}
			
			if ($category1 != null) $find.= " and `category1`='$category1'";
			if ($category2 != null) $find.= " and `category1`='$category2'";
			if ($category3 != null) $find.= " and `category1`='$category3'";
			
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
			
			if ($keyword != null) $find.= " and `title` like '%$keyword%'";
		}
		$lists = $mDB->DBfetchs($mOneroom->table['item'],array('idx','category1','category2','category3','region1','region2','region3','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','title','reg_date','end_date','is_open','is_premium','is_regionitem','hit'),$find,$orderer,$limiter);
		$total = $mDB->DBcount($mOneroom->table['item'],$find);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['region'] = $mOneroom->GetRegion($lists[$i]['region1'],$lists[$i]['region2'],$lists[$i]['region3']);
			$lists[$i]['category'] = $mOneroom->GetCategory($lists[$i]['category1'],$lists[$i]['category2'],$lists[$i]['category3']);
			$lists[$i]['reg_date'] = GetTime('Y.m.d',$lists[$i]['reg_date']);
			$lists[$i]['end_date'] = $lists[$i]['end_date'] == 0 ? '0' : GetTime('Y.m.d',$lists[$i]['end_date']);
		}
	}
	
	if ($get == 'data') {
		$idx = Request('idx');
		$data = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
		$data['is_buy'] = $data['is_buy'] == 'TRUE' ? 'on' : '';
		$data['is_rent_all'] = $data['is_rent_all'] == 'TRUE' ? 'on' : '';
		$data['is_rent_month'] = $data['is_rent_month'] == 'TRUE' ? 'on' : '';
		$data['is_rent_short'] = $data['is_rent_short'] == 'TRUE' ? 'on' : '';
		$data['floor1'] = array_shift(explode('-',$data['floor']));
		$data['floor2'] = array_pop(explode('-',$data['floor']));
		$data['floor1'] = $data['floor1'] ? $data['floor1'] : '0';
		$data['floor2'] = $data['floor2'] ? $data['floor2'] : '0';
		$data['is_double'] = $data['is_double'] == 'TRUE' ? 'on' : '';
		$data['is_under'] = $data['is_under'] == 'TRUE' ? 'on' : '';
		$data['movein_date_now'] = $data['movein_date'] == '0000-00-00' ? 'on' : '';
		
		if ($data['subway'] != '0') {
			$subway = $mDB->DBfetch($mOneroom->table['subway'],array('parent'),"where `idx`='{$data['subway']}'");
			$data['subway1'] = $subway['parent'];
			$data['subway2'] = $data['subway'];
		}
		
		if ($data['university'] != '0') {
			$university = $mDB->DBfetch($mOneroom->table['university'],array('parent'),"where `idx`='{$data['university']}'");
			$data['university1'] = $university['parent'];
			$data['university2'] = $data['university'];
		}
		
		$options = explode(',',$data['options']);
		for ($i=0, $loop=sizeof($options);$i<$loop;$i++) $data['options_'.$options[$i]] = 'on';
		
		$data['default_image'] = $data['image'];
		$data['detail'] = str_replace('{$moduleDir}',$mOneroom->moduleDir,$data['detail']);
		
		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
	
	if ($get == 'opencount') {
		$return['success'] = true;
		$return['count'] = $mDB->DBcount($mOneroom->table['item'],"where `mno`='{$member['idx']}' and `is_open`='TRUE'");
		exit(json_encode($return));
	}
	
	if ($get == 'closecount') {
		$return['success'] = true;
		$return['count'] = $mDB->DBcount($mOneroom->table['item'],"where `mno`='{$member['idx']}' and `is_open`='FALSE'");
		exit(json_encode($return));
	}
	
	if ($get == 'remaincount') {
		$return['success'] = true;
		$return['limit'] = $mOneroom->GetConfig('open_limit');
		$return['count'] = $mDB->DBcount($mOneroom->table['item'],"where `mno`='{$member['idx']}' and `is_open`='TRUE'");
		exit(json_encode($return));
	}
}

if ($action == 'premium') {
	if ($get == 'myslot') {
		$find = "where `mno`='{$member['idx']}' and `type`='PREMIUM'";
		$total = $mDB->DBcount($mOneroom->table['user_slot'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['user_slot'],'*',$find,'idx,desc',$limiter);
		
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['ino'] != '0') {
				$item = $mDB->DBfetch($mOneroom->table['item'],array('title'),"where `idx`='{$lists[$i]['ino']}'");
				$lists[$i]['title'] = $item['title'];
			} else {
				$lists[$i]['title'] = '';
			}
			$lists[$i]['status'] = $lists[$i]['start_time'] < GetGMT() && $lists[$i]['end_time'] > GetGMT() ? 'ACTIVE' : 'INACTIVE';
			$lists[$i]['start_time'] = GetTime('Y-m-d H:i:s',$lists[$i]['start_time']);
			$lists[$i]['end_time'] = GetTime('Y-m-d H:i:s',$lists[$i]['end_time']);
		}
	}
	
	if ($get == 'slot') {
		$type = Request('type');
		$lists = $mDB->DBfetchs($mOneroom->table['slot'],'*',"where `type`='$type'");
	}
	
	if ($get == 'auction') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		
		$find = "where `type`='AUCTION' and `month`='$month'";
		$total = $mDB->DBcount($mOneroom->table['premium_item'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['premium_item'],'*',$find,array('point,desc','last_bidding,asc'),$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$user = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['premium_item'],"where `month`='$month' and `point`>{$lists[$i]['point']}") < $mOneroom->GetConfig('prodealer_limit') ? (mktime(0,0,10,date('m'),$mOneroom->GetConfig('auction_end'),date('Y')) > time() ? 'INFLUENTIAL' : 'SUCCESS') : 'FAIL';
			$lists[$i]['user_id'] = substr($user['user_id'],0,strlen($user['user_id'])-3).'***'.($lists[$i]['mno'] == $member['idx'] ? '(나)' : '');
			$lists[$i]['last_bidding'] = GetTime('Y.m.d H:i:s',$lists[$i]['last_bidding']);
		}
	}
	
	if ($get == 'myauction') {
		$find = "where `type`='AUCTION' and `mno`='{$member['idx']}'";
		$total = $mDB->DBcount($mOneroom->table['premium_item'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['premium_item'],'*',$find,'month,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['status'] == 'NEW') {
				if (mktime(0,0,10,intval(array_pop(explode('-',$lists[$i]['month'])))-1,$mOneroom->GetConfig('auction_end'),array_shift(explode('-',$lists[$i]['month']))) < time()) {
					$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['premium_item'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('premium_limit') ? 'SUCCESS' : 'FAIL';
					$mDB->DBupdate($mOneroom->table['premium_item'],array('status'=>$lists[$i]['status']),'',"where `idx`='{$lists[$i]['idx']}'");
				} else {
					$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['premium_item'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('premium_limit') ? 'INFLUENTIAL' : 'FAIL';
				}
			}
			$lists[$i]['title'] = date('Y년 m월 프리미엄매물 경매',strtotime($lists[$i]['month'].'-01'));
			$lists[$i]['last_bidding'] = GetTime('Y-m-d H:i:s',$lists[$i]['last_bidding']);
		}
	}
	
	if ($get == 'myauction_item') {
		$find = "where `type`='AUCTION' and `mno`='{$member['idx']}' and (";
		$find.= "`month`='".date('Y-m')."'";
		if ($mOneroom->GetConfig('auction_end') < date('d')) $find.= " or `month`='".date('Y-m',mktime(0,0,0,date('m'),1,date('Y')))."'";
		$find.= ")";
		
		$lists = $mDB->DBfetchs($mOneroom->table['premium_item'],'*',$find,'month,asc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','title','hit','end_date'),"where `idx`='{$lists[$i]['ino']}'");
			$lists[$i]['month'] = date('Y.m.d',strtotime($lists[$i]['month'].'-01')).'~'.date('m.t',strtotime($lists[$i]['month'].'-01'));
			if (isset($item['idx']) == true) {
				$lists[$i]['ino'] = $item['idx'];
				$lists[$i]['title'] = $item['title'];
				$lists[$i]['hit'] = $item['hit'];
				$lists[$i]['end_date'] = $item['end_date'];
			} else {
				$lists[$i]['ino'] = '0';
				$lists[$i]['title'] = '';
				$lists[$i]['hit'] = '0';
				$lists[$i]['end_date'] = '0';
			}
		}
	}
	
	if ($get == 'auction_type') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		$find = "where `type`='AUCTION' and `month`='$month' and `mno`='{$member['idx']}'";
		$data = $mDB->DBfetchs($mOneroom->table['premium_item'],array('idx','point','bidding'),$find,'point,asc');
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$bidding_count = $mOneroom->GetConfig('auction_limit') == 0 ? '무제한' : ($mOneroom->GetConfig('auction_limit') - $data[$i]['bidding']).'회';
			$lists[$i] = array('idx'=>$data[$i]['idx'],'title'=>'경매번호 '.$data[$i]['idx'].' (현재가 : '.number_format($data[$i]['point']).'포인트 / 남은입찰횟수 : '.$bidding_count.')','sort'=>$i);
		}
		
		$lists[] = array('idx'=>'0','title'=>'신규입찰','sort'=>'-1');
	}
	
	if ($get == 'bidding') {
		$idx = Request('idx');
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		
		$return['success'] = true;
		$return['data'] = array();
		
		if ($idx) {
			$bidding = $mDB->DBfetch($mOneroom->table['premium_item'],array('idx','point','bidding'),"where `idx`='$idx' and `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
		}
		
		if (isset($bidding) == true && isset($bidding['idx']) == true) {
			$return['data']['mybidding'] = $bidding['point'];
			$return['data']['limit_count'] = $mOneroom->GetConfig('auction_limit') == '0' ? '-1' : $mOneroom->GetConfig('auction_limit') - $bidding['bidding'];
		} else {
			$return['data']['mybidding'] = '0';
			$return['data']['limit_count'] = $mOneroom->GetConfig('auction_limit') == '0' ? '-1' : $mOneroom->GetConfig('auction_limit') - $mDB->DBcount($mOneroom->table['premium_item'],"where `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
		}

		$return['data']['point'] = $return['data']['mybidding'] < $mOneroom->GetConfig('auction_min') ? $mOneroom->GetConfig('auction_min') : $return['data']['mybidding'] + 1000;
		$return['data']['bidding_point'] = $mOneroom->GetConfig('auction_point');
		$return['data']['mypoint'] = $member['point'];
		
		if (time() < mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_start'),date('Y'))) {
			$return['data']['limit_time'] = 2678400000;
		} else {
			$return['data']['limit_time'] = (mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_end'),date('Y'))-time())*1000;
		}
		
		exit(json_encode($return));
	}
	
	if ($get == 'pointlist') {
		$lists = $mDB->DBfetchs($mOneroom->table['premium_item'],'*',"where `type`='POINT' and `mno`='{$member['idx']}'",'end_time,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','title','hit'),"where `idx`='{$lists[$i]['ino']}'");
			
			$lists[$i]['status'] = $lists[$i]['end_time'] < GetGMT() ? 'FALSE' : 'ACTIVE';
			$lists[$i]['start_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['start_time']);
			$lists[$i]['end_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['end_time']);
			$lists[$i]['title'] = $item['title'];
			$lists[$i]['hit'] = $item['hit'];
		}
	}
	
	if ($get == 'pointinfo') {
		$item = $mDB->DBcount($mOneroom->table['premium_item'],"where `type`='POINT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
		$return['success'] = true;
		$return['price'] = $mOneroom->GetConfig('premium_point');
		$return['point'] = $member['point'];
		$return['limit'] = $mOneroom->GetConfig('premium_limit') == '0' ? '-1' : $mOneroom->GetConfig('premium_limit')-$item;
		exit(json_encode($return));
	}
}

if ($action == 'regionitem') {
	if ($get == 'myslot') {
		$find = "where `mno`='{$member['idx']}' and `type`='REGIONITEM'";
		$total = $mDB->DBcount($mOneroom->table['user_slot'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['user_slot'],'*',$find,'idx,desc',$limiter);
		
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['ino'] != '0') {
				$item = $mDB->DBfetch($mOneroom->table['item'],array('title'),"where `idx`='{$lists[$i]['ino']}'");
				$lists[$i]['title'] = $item['title'];
			} else {
				$lists[$i]['title'] = '';
			}
			$lists[$i]['status'] = $lists[$i]['start_time'] < GetGMT() && $lists[$i]['end_time'] > GetGMT() ? 'ACTIVE' : 'INACTIVE';
			$lists[$i]['start_time'] = GetTime('Y-m-d H:i:s',$lists[$i]['start_time']);
			$lists[$i]['end_time'] = GetTime('Y-m-d H:i:s',$lists[$i]['end_time']);
		}
	}
	
	if ($get == 'slot') {
		$type = Request('type');
		$lists = $mDB->DBfetchs($mOneroom->table['slot'],'*',"where `type`='$type'");
	}
	
	if ($get == 'auction') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		
		$find = "where `type`='AUCTION' and `month`='$month'";
		$total = $mDB->DBcount($mOneroom->table['region_item'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['region_item'],'*',$find,array('point,desc','last_bidding,asc'),$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$user = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['region_item'],"where `month`='$month' and `point`>{$lists[$i]['point']}") < $mOneroom->GetConfig('prodealer_limit') ? (mktime(0,0,10,date('m'),$mOneroom->GetConfig('auction_end'),date('Y')) > time() ? 'INFLUENTIAL' : 'SUCCESS') : 'FAIL';
			$lists[$i]['user_id'] = substr($user['user_id'],0,strlen($user['user_id'])-3).'***'.($lists[$i]['mno'] == $member['idx'] ? '(나)' : '');
			$lists[$i]['last_bidding'] = GetTime('Y.m.d H:i:s',$lists[$i]['last_bidding']);
		}
	}
	
	if ($get == 'myauction') {
		$find = "where `type`='AUCTION' and `mno`='{$member['idx']}'";
		$total = $mDB->DBcount($mOneroom->table['region_item'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['region_item'],'*',$find,'month,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['status'] == 'NEW') {
				if (mktime(0,0,10,intval(array_pop(explode('-',$lists[$i]['month'])))-1,$mOneroom->GetConfig('auction_end'),array_shift(explode('-',$lists[$i]['month']))) < time()) {
					$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['region_item'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('regionitem_limit') ? 'SUCCESS' : 'FAIL';
					$mDB->DBupdate($mOneroom->table['region_item'],array('status'=>$lists[$i]['status']),'',"where `idx`='{$lists[$i]['idx']}'");
				} else {
					$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['region_item'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('regionitem_limit') ? 'INFLUENTIAL' : 'FAIL';
				}
			}
			$lists[$i]['title'] = date('Y년 m월 지역추천매물 경매',strtotime($lists[$i]['month'].'-01'));
			$lists[$i]['last_bidding'] = GetTime('Y-m-d H:i:s',$lists[$i]['last_bidding']);
		}
	}
	
	if ($get == 'myauction_item') {
		$find = "where `type`='AUCTION' and `mno`='{$member['idx']}' and (";
		$find.= "`month`='".date('Y-m')."'";
		if ($mOneroom->GetConfig('auction_end') < date('d')) $find.= " or `month`='".date('Y-m',mktime(0,0,0,date('m'),1,date('Y')))."'";
		$find.= ")";
		
		$lists = $mDB->DBfetchs($mOneroom->table['region_item'],'*',$find,'month,asc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','title','hit','end_date'),"where `idx`='{$lists[$i]['ino']}'");
			$lists[$i]['month'] = date('Y.m.d',strtotime($lists[$i]['month'].'-01')).'~'.date('m.t',strtotime($lists[$i]['month'].'-01'));
			if (isset($item['idx']) == true) {
				$lists[$i]['ino'] = $item['idx'];
				$lists[$i]['title'] = $item['title'];
				$lists[$i]['hit'] = $item['hit'];
				$lists[$i]['end_date'] = $item['end_date'];
			} else {
				$lists[$i]['ino'] = '0';
				$lists[$i]['title'] = '';
				$lists[$i]['hit'] = '0';
				$lists[$i]['end_date'] = '0';
			}
		}
	}
	
	if ($get == 'auction_type') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		$find = "where `type`='AUCTION' and `month`='$month' and `mno`='{$member['idx']}'";
		$data = $mDB->DBfetchs($mOneroom->table['region_item'],array('idx','point','bidding'),$find,'point,asc');
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$bidding_count = $mOneroom->GetConfig('auction_limit') == 0 ? '무제한' : ($mOneroom->GetConfig('auction_limit') - $data[$i]['bidding']).'회';
			$lists[$i] = array('idx'=>$data[$i]['idx'],'title'=>'경매번호 '.$data[$i]['idx'].' (현재가 : '.number_format($data[$i]['point']).'포인트 / 남은입찰횟수 : '.$bidding_count.')','sort'=>$i);
		}
		
		$lists[] = array('idx'=>'0','title'=>'신규입찰','sort'=>'-1');
	}
	
	if ($get == 'bidding') {
		$idx = Request('idx');
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		
		$return['success'] = true;
		$return['data'] = array();
		
		if ($idx) {
			$bidding = $mDB->DBfetch($mOneroom->table['region_item'],array('idx','point','bidding'),"where `idx`='$idx' and `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
		}
		
		if (isset($bidding) == true && isset($bidding['idx']) == true) {
			$return['data']['mybidding'] = $bidding['point'];
			$return['data']['limit_count'] = $mOneroom->GetConfig('auction_limit') == '0' ? '-1' : $mOneroom->GetConfig('auction_limit') - $bidding['bidding'];
		} else {
			$return['data']['mybidding'] = '0';
			$return['data']['limit_count'] = $mOneroom->GetConfig('auction_limit') == '0' ? '-1' : $mOneroom->GetConfig('auction_limit') - $mDB->DBcount($mOneroom->table['region_item'],"where `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
		}

		$return['data']['point'] = $return['data']['mybidding'] < $mOneroom->GetConfig('auction_min') ? $mOneroom->GetConfig('auction_min') : $return['data']['mybidding'] + 1000;
		$return['data']['bidding_point'] = $mOneroom->GetConfig('auction_point');
		$return['data']['mypoint'] = $member['point'];
		
		if (time() < mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_start'),date('Y'))) {
			$return['data']['limit_time'] = 2678400000;
		} else {
			$return['data']['limit_time'] = (mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_end'),date('Y'))-time())*1000;
		}
		
		exit(json_encode($return));
	}
	
	if ($get == 'pointlist') {
		$lists = $mDB->DBfetchs($mOneroom->table['region_item'],'*',"where `type`='POINT' and `mno`='{$member['idx']}'",'end_time,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','title','hit'),"where `idx`='{$lists[$i]['ino']}'");
			
			$lists[$i]['status'] = $lists[$i]['end_time'] < GetGMT() ? 'FALSE' : 'ACTIVE';
			$lists[$i]['start_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['start_time']);
			$lists[$i]['end_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['end_time']);
			$lists[$i]['title'] = $item['title'];
			$lists[$i]['hit'] = $item['hit'];
		}
	}
	
	if ($get == 'pointinfo') {
		$item = $mDB->DBcount($mOneroom->table['region_item'],"where `type`='POINT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
		$return['success'] = true;
		$return['price'] = $mOneroom->GetConfig('regionitem_point');
		$return['point'] = $member['point'];
		$return['limit'] = $mOneroom->GetConfig('regionitem_limit') == '0' ? '-1' : $mOneroom->GetConfig('regionitem_limit')-$item;
		exit(json_encode($return));
	}
}

if ($action == 'prodealer') {
	if ($get == 'auction') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		
		$find = "where `type`='AUCTION' and `month`='$month'";
		$total = $mDB->DBcount($mOneroom->table['prodealer'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['prodealer'],'*',$find,array('point,desc','last_bidding,asc'),$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$user = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['prodealer'],"where `month`='$month' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('prodealer_limit') ? (mktime(0,0,10,date('m'),25,date('Y')) > time() ? 'INFLUENTIAL' : 'SUCCESS') : 'FAIL';
			$lists[$i]['user_id'] = substr($user['user_id'],0,strlen($user['user_id'])-3).'***'.($lists[$i]['mno'] == $member['idx'] ? '(나)' : '');
			$lists[$i]['region'] = $mOneroom->GetRegion($lists[$i]['region1'],$lists[$i]['region2'],$lists[$i]['region3']);
			$lists[$i]['last_bidding'] = GetTime('Y.m.d H:i:s',$lists[$i]['last_bidding']);
		}
	}

	if ($get == 'myauction') {
		$find = "where `type`='AUCTION' and `mno`='{$member['idx']}'";
		$total = $mDB->DBcount($mOneroom->table['prodealer'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['prodealer'],'*',$find,'month,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['status'] == 'NEW') {
				if (mktime(0,0,10,intval(array_pop(explode('-',$lists[$i]['month'])))-1,$mOneroom->GetConfig('auction_end'),array_shift(explode('-',$lists[$i]['month']))) < time()) {
					$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['prodealer'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('prodealer_limit') ? 'SUCCESS' : 'FAIL';
					$mDB->DBupdate($mOneroom->table['prodealer'],array('status'=>$lists[$i]['status']),'',"where `idx`='{$lists[$i]['idx']}'");
				} else {
					$lists[$i]['status'] = $mDB->DBcount($mOneroom->table['prodealer'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('prodealer_limit') ? 'INFLUENTIAL' : 'FAIL';
				}
			}
			$lists[$i]['title'] = date('Y년 m월 지역전문가 경매',strtotime($lists[$i]['month'].'-01'));
			$lists[$i]['last_bidding'] = GetTime('Y.m.d H:i:s',$lists[$i]['last_bidding']);
		}
	}
	
	if ($get == 'bidding') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		$data = $mDB->DBfetch($mOneroom->table['prodealer'],array('idx','region1','region2','region3'),"where `mno`='{$member['idx']}'",'month,desc','0,1');
		
		$return['success'] = true;
		$return['data'] = array();
		if (isset($data['idx']) == true) {
			$return['data']['region1'] = $data['region1'];
			$return['data']['region2'] = $data['region2'];
			$return['data']['region3'] = $data['region3'];
		}
		
		$bidding = $mDB->DBfetch($mOneroom->table['prodealer'],array('idx','point','bidding'),"where `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
		$return['data']['mybidding'] = isset($bidding['idx']) == true ? $bidding['point'] : '0';
		$return['data']['point'] = $return['data']['mybidding'] < $mOneroom->GetConfig('auction_min') ? $mOneroom->GetConfig('auction_min') : $return['data']['mybidding'] + 1000;
		$return['data']['bidding_point'] = $mOneroom->GetConfig('auction_point');
		$return['data']['mypoint'] = $member['point'];
		$return['data']['limit_count'] = $mOneroom->GetConfig('auction_limit') == '0' ? '-1' : $mOneroom->GetConfig('auction_limit') - (isset($bidding['bidding']) == true ? $bidding['bidding'] : 0);
		if (time() < mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_start'),date('Y'))) {
			$return['data']['limit_time'] = 2678400000;
		} else {
			$return['data']['limit_time'] = (mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_end'),date('Y'))-time())*1000;
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'point') {
	if ($get == 'list') {
		$find = "where `mno`='{$member['idx']}'";
		$total = $mDB->DBcount($_ENV['table']['point'],$find);
		$lists = $mDB->DBfetchs($_ENV['table']['point'],'*',$find,$orderer,$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['reg_date'] = GetTime('Y-m-d H:i:s',$lists[$i]['reg_date']);
		}
	}
	
	if ($get == 'paylist') {
		$mModule = new Module('point');
		if ($mModule->IsSetup() == true) {
			$mPoint = new ModulePoint();
			$find = "where `mno`='{$member['idx']}'";
			$total = $mDB->DBcount($mPoint->table['buy'],$find);
			$lists = $mDB->DBfetchs($mPoint->table['buy'],'*',$find,$orderer,$limiter);
			
			for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
				$payment = $mDB->DBfetch($mPoint->table['payment'],'*',"where `idx`='{$lists[$i]['payment']}'");
				if ($payment['type'] == 'BANKING') {
					$lists[$i]['payment'] = '[무통장입금] '.$payment['value'];
				}
				$lists[$i]['reg_date'] = GetTime('Y-m-d H:i:s',$lists[$i]['reg_date']);
			}
		}
	}
	
	if ($get == 'payment') {
		$mModule = new Module('point');
		if ($mModule->IsSetup() == true) {
			$mPoint = new ModulePoint();
			if ($mPoint->GetConfig('use_buy') == 'on') {
				$type = array('BANKING'=>'무통장입금','ACCOUNT'=>'계좌이체','CARD'=>'신용카드','CELLPHONE'=>'휴대폰결제');
				$lists = $mDB->DBfetchs($mPoint->table['payment'],'*',"where `is_use`='TRUE'");
				for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
					$lists[$i]['display'] = $type[$lists[$i]['type']];
					if ($lists[$i]['type'] == 'BANKING') {
						$lists[$i]['display'] = '['.$lists[$i]['display'].'] '.$lists[$i]['value'];
					}
				}
			}
		}
	}
	
	if ($get == 'buyinfo') {
		$mModule = new Module('point');
		if ($mModule->IsSetup() == true) {
			$mPoint = new ModulePoint();
			if ($mPoint->GetConfig('use_buy') == 'on') {
				$return['success'] = true;
				$return['ratio'] = $mPoint->GetConfig('ratio');
			} else {
				$return['success'] = false;
			}
		} else {
			$return['success'] = false;
		}
		
		exit(json_encode($return));
	}
}

$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>