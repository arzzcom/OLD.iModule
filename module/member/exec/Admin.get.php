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
$return = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'member') {
	if ($get == 'list') {
		$grouplist = $mDB->DBfetchs($_ENV['table']['group'],'*');
		$groupname = array();
		for ($i=0, $loop=sizeof($grouplist);$i<$loop;$i++) {
			$groupname[$grouplist[$i]['group']] = $grouplist[$i]['title'];
		}
		
		$find = "where `is_leave`='FALSE'";
		$group = Request('group');
		$keyword = Request('keyword');
		$active = Request('active');
		
		$find.= $group ? " and `group`='$group'" : '';
		$find.= $active != 'all' ? " and `is_active`='$active'" : '';
		$find.= $keyword ? " and (`user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%')" : '';
		$lists = $mDB->DBfetchs($_ENV['table']['member'],array('idx','type','is_active','group','user_id','name','nickname','email','jumin','exp','point','telephone','cellphone','reg_date','last_login'),$find,$orderer,$limiter);
		$total = $mDB->DBcount($_ENV['table']['member'],$find);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$level = $mMember->GetLevel($lists[$i]['exp']);
	
			$lv = $level['lv'];
			$exp = $level['exp'];
			$next = $level['next'];
	
			$lists[$i]['groupname'] = $groupname[$lists[$i]['group']];
			$lists[$i]['phone'] = $lists[$i]['cellphone'] ? $lists[$i]['cellphone'] : $lists[$i]['telephone'];
			$lists[$i]['exp'] = $lv.','.$exp.','.$next;
			$lists[$i]['reg_date'] = GetTime('Y.m.d H:i',$lists[$i]['reg_date']);
			$lists[$i]['last_week'] = $lists[$i]['last_login'] > GetGMT()-60*60*24*7 ? 'TRUE' : 'FALSE';
			$lists[$i]['last_login'] = GetTime('Y.m.d H:i',$lists[$i]['last_login']);
		}
	}
	
	if ($get == 'data') {
		$idx = Request('idx');
		$data = $mMember->GetMemberInfo($idx);
		
		$data['reg_date'] = GetTime('Y.m.d H:i:s',$data['reg_date']);
		$data['last_login'] = GetTime('Y.m.d H:i:s',$data['last_login']);
		$data['provider'] = $data['cellphone']['provider'];
		$data['cellphone1'] = $data['cellphone']['cellphone1'];
		$data['cellphone2'] = $data['cellphone']['cellphone2'];
		$data['cellphone3'] = $data['cellphone']['cellphone3'];
		$data['birthday1'] = intval($data['birthday']['year']);
		$data['birthday2'] = intval($data['birthday']['month']);
		$data['birthday3'] = intval($data['birthday']['day']);
	
		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
}

if ($action == 'group') {
	$is_all = Request('is_all');
	$is_member = Request('is_member');
	
	if ($is_all == 'true') {
		$lists[] = '{"group":"","title":"전체","sort":"-1"}';
	}
	
	$find = '';
	$keyword = Request('keyword');
	$find = "where `group`='$keyword' or `title` like '%$keyword%'";
	$lists = array_merge($lists,$mDB->DBfetchs($_ENV['table']['group'],'*',$find,'sort,asc'));
	
	if ($is_member == 'true') {
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['sort'] = $i;
			$mDB->DBupdate($_ENV['table']['group'],array('sort'=>$i),'',"where `group`='{$lists[$i]['group']}'");
			$lists[$i]['membernum'] = $mDB->DBcount($_ENV['table']['member'],"where `group`='{$lists[$i]['group']}' and `is_leave`='FALSE'");
		}
	}
}

if ($action == 'signin') {
	if ($get == 'field') {
		$group = Request('group');
		
		$lists = $mDB->DBfetchs($_ENV['table']['signin'],'*',"where `group`='$group' and `type` NOT IN ('agreement','privacy','youngpolicy')",'sort,asc');
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if (preg_match('/^extra_/',$lists[$i]['name']) == true) {
				$lists[$i]['is_default'] = 'FALSE';
			} else {
				$lists[$i]['is_default'] = 'TRUE';
			}
			
			$lists[$i]['sort'] = $i;
			$mDB->DBupdate($_ENV['table']['signin'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
		}
	}
	
	if ($get == 'info') {
		$idx = Request('idx');
		
		$data = $mDB->DBfetch($_ENV['table']['signin'],'*',"where `idx`='$idx'");
		
		if (preg_match('/^extra_/',$data['name']) == true) {
			$data['name'] = preg_replace('/^extra_/','',$data['name']);

			if ($data['type'] == 'input') {
				$data['valid'] = $data['value'] ? unserialize($data['value']) : '';
			}
			
			if ($data['type'] == 'textarea') {
				$data['height'] = $data['value'] ? unserialize($data['value']) : '';
			}
			
			if (in_array($data['type'],array('checkbox','radio','select')) == true) {
				$data['list'] = $data['value'] ? implode("\n",unserialize($data['value'])) : '';
			}
			
			$data['allowblank'] = $data['allowblank'] == 'FALSE' ? 'on' : 'off';
		} else {
			if ($data['type'] == 'cellphone') {
				$value = $data['value'] ? unserialize($data['value']) : array('provider'=>'off','realphone'=>'off');
				$data['provider'] = $value['provider'];
				$data['realphone'] = $value['realphone'];
			}
			
			if ($data['type'] == 'voter') {
				$value = $data['value'] ? unserialize($data['value']) : array('vote'=>'0','voter'=>'0');
				$data['vote'] = $value['vote'];
				$data['voter'] = $value['voter'];
			}
		}
		
		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
	
	if ($get == 'agreement') {
		$group = Request('group');
		
		$data = $mDB->DBfetch($_ENV['table']['signin'],array('value','msg'),"where `group`='$group' and `type`='agreement'");
		
		$return['success'] = true;
		$return['data'] = array('value'=>$data['value'],'msg'=>$data['msg']);
		
		exit(json_encode($return));
	}
	
	if ($get == 'privacy') {
		$group = Request('group');
		
		$data = $mDB->DBfetch($_ENV['table']['signin'],array('value','msg'),"where `group`='$group' and `type`='privacy'");
		
		$return['success'] = true;
		if (isset($data['value']) == true) {
			$return['data'] = array('value'=>$data['value'],'msg'=>$data['msg'],'disable'=>'off');
		} else {
			$return['data'] = array('value'=>'','msg'=>'위의 개인정보보호정책에 동의합니다.','disable'=>'on');
		}
		
		exit(json_encode($return));
	}
	
	if ($get == 'youngpolicy') {
		$group = Request('group');
		
		$data = $mDB->DBfetch($_ENV['table']['signin'],array('value','msg'),"where `group`='$group' and `type`='youngpolicy'");
		
		$return['success'] = true;
		if (isset($data['value']) == true) {
			$return['data'] = array('value'=>$data['value'],'msg'=>$data['msg'],'disable'=>'off');
		} else {
			$return['data'] = array('value'=>'','msg'=>'위의 청소년보호정책에 동의합니다.','disable'=>'on');
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'leave') {
	if ($get == 'list') {
		$find = "where `is_leave`='TRUE'";
		
		$total = $mDB->DBcount($_ENV['table']['member'],$find);
		$lists = $mDB->DBfetchs($_ENV['table']['member'],array('idx','group','user_id','name','nickname','email','jumin','point','telephone','cellphone','reg_date','leave_date','last_login'),$find,$orderer,$limiter);
		
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$leave = $mDB->DBfetch($_ENV['table']['leave'],'*',"where `mno`='{$lists[$i]['idx']}'");
			
			$lists[$i]['msg'] = isset($leave['idx']) == true ? GetRemoveEnterTab($leave['msg'],' ') : 'SYSTEM';
			$lists[$i]['phone'] = $lists[$i]['cellphone'] ? $lists[$i]['cellphone'] : $lists[$i]['telephone'];
			$lists[$i]['reg_date'] = GetTime('Y.m.d H:i',$lists[$i]['reg_date']);
			$lists[$i]['leave_date'] = GetTime('Y.m.d H:i',$lists[$i]['leave_date']);
			$lists[$i]['last_login'] = GetTime('Y.m.d H:i',$lists[$i]['last_login']);
		}
	}
}

if ($action == 'point') {
	$idx = Request('idx');
	$find = $idx == null ? '' : "where `mno`='$idx'";
	
	$total = $mDB->DBcount($_ENV['table']['point'],$find);
	$lists = $mDB->DBfetchs($_ENV['table']['point'],'*',$find,$orderer,$limiter);
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
	}
}

if ($action == 'phone') {
	$keyword = Request('keyword');
	$find = "where `is_leave`='FALSE' and `cellphone`!='||' and `cellphone`!=''";
	$find.= $keyword ? " and (`user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%')" : '';
	$lists = $mDB->DBfetchs($_ENV['table']['member'],array('idx','user_id','name','nickname','cellphone'),$find,$orderer,$limiter);
	$total = $mDB->DBcount($_ENV['table']['member'],$find);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$level = $mDB->DBfetch($_ENV['table']['level'],array('lv','exp','next'),"where `exp`<={$lists[$i]['exp']}",'lv,desc','0,1');

		$lv = $level['lv'];
		$exp = $lists[$i]['exp']-$level['exp'];
		$next = $level['next'];

		$lists[$i] = '{';
		$lists[$i].= '"idx":"'.$lists[$i]['idx'].'",';
		$lists[$i].= '"user_id":"'.$lists[$i]['user_id'].'",';
		$lists[$i].= '"name":"'.GetString($lists[$i]['name'],'ext').'",';
		$lists[$i].= '"nickname":"'.GetString($lists[$i]['nickname'],'ext').'",';
		$lists[$i].= '"phone":"'.$lists[$i]['cellphone'].'",';
		$lists[$i].= '"cellphone":"'.array_shift(explode('||',$lists[$i]['cellphone'])).'"';
		$lists[$i].= '}';
	}
}

if ($action == 'email') {
	$keyword = Request('keyword');
	$find = "where `is_leave`='FALSE' and `email`!=''";
	$find.= $keyword ? " and (`user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%')" : '';
	$lists = $mDB->DBfetchs($_ENV['table']['member'],array('idx','user_id','name','nickname','email'),$find,$orderer,$limiter);
	$total = $mDB->DBcount($_ENV['table']['member'],$find);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$level = $mDB->DBfetch($_ENV['table']['level'],array('lv','exp','next'),"where `exp`<={$lists[$i]['exp']}",'lv,desc','0,1');

		$lv = $level['lv'];
		$exp = $lists[$i]['exp']-$level['exp'];
		$next = $level['next'];

		$lists[$i] = '{';
		$lists[$i].= '"idx":"'.$lists[$i]['idx'].'",';
		$lists[$i].= '"user_id":"'.$lists[$i]['user_id'].'",';
		$lists[$i].= '"name":"'.GetString($lists[$i]['name'],'ext').'",';
		$lists[$i].= '"nickname":"'.GetString($lists[$i]['nickname'],'ext').'",';
		$lists[$i].= '"email":"'.GetString($lists[$i]['email'],'ext').'"';
		$lists[$i].= '}';
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>