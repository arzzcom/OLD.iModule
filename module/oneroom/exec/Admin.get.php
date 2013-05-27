<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mOneroom = new ModuleOneroom();

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

if ($action == 'region') {
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$lists = $mDB->DBfetchs($mOneroom->table['region'],'*',$find,'sort,asc');

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['sort'] = $i;
		$mDB->DBupdate($mOneroom->table['region'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
	}

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'0','title'=>'전체','sort'=>'-1');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'0','title'=>'선택안함(없음)','sort'=>'-2');
	}
}

if ($action == 'category') {
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$lists = $mDB->DBfetchs($mOneroom->table['category'],'*',$find);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['sort'] = $i;
		$mDB->DBupdate($mOneroom->table['category'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
	}

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'0','title'=>'전체','sort'=>'-1');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'0','title'=>'선택안함(없음)','sort'=>'-2');
	}
}

if ($action == 'option') {
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$lists = $mDB->DBfetchs($mOneroom->table['option'],'*',$find);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['sort'] = $i;
		$mDB->DBupdate($mOneroom->table['option'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
	}
}

if ($action == 'university') {
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$lists = $mDB->DBfetchs($mOneroom->table['university'],'*',$find);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['sort'] = $i;
		$mDB->DBupdate($mOneroom->table['university'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
	}
}

if ($action == 'subway') {
	$parent = Request('parent');
	$find = "where `parent`='$parent'";
	$lists = $mDB->DBfetchs($mOneroom->table['subway'],'*',$find);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i]['sort'] = $i;
		$mDB->DBupdate($mOneroom->table['subway'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
	}
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
	
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$lists[$i] = '{';
		$lists[$i].= '"idx":"'.$lists[$i]['idx'].'",';
		$lists[$i].= '"title":"'.$lists[$i]['title'].'",';
		$lists[$i].= '"sort":"'.$lists[$i]['sort'].'",';
		$lists[$i].= '"item":"0"';
		$lists[$i].= '}';
	}

	if ($is_all == 'true') {
		$lists[] = '{"idx":"0","title":"전체","sort":"0"}';
	}

	if ($is_none == 'true') {
		$lists[] = '{"idx":"0","title":"선택안함(없음)","sort":"-1"}';
	}
}

if ($action == 'item') {
	if ($get == 'list') {
		$idx = Request('idx');
		$find = "where 1";
		
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
			
			$is_default_premium = Request('is_default_premium');
			$is_default_regionitem = Request('is_default_regionitem');
			
			if ($is_open != null) $find.= " and `is_open`='$is_open'";
			
			if ($type != null && $type != 'all') {
				if ($type == 'premium') $find.= " and `is_premium`='TRUE'";
				if ($type == 'regionitem') $find.= " and `is_regionitem`='TRUE'";
				if ($type == 'default') $find.= " and `is_open`='TRUE' and `is_premium`='FALSE' and `is_regionitem`='FALSE'";
				if ($type == 'wait') $find.= " and `is_open`='FALSE'";
			}
			
			if ($is_default_premium) $find.= " and `is_default_premium`='$is_default_premium'";
			if ($is_default_regionitem) $find.= " and `is_default_regionitem`='$is_default_regionitem'";
			
			
			if ($category1 != null) $find.= " and `category1`='$category1'";
			if ($category2 != null) $find.= " and `category1`='$category2'";
			if ($category3 != null) $find.= " and `category1`='$category3'";
			
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
			
			if ($keyword != null) $find.= " and `title` like '%$keyword%'";
		}
		$lists = $mDB->DBfetchs($mOneroom->table['item'],array('idx','mno','category1','category2','category3','region1','region2','region3','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','title','reg_date','end_date','is_open','is_premium','is_default_premium','is_default_regionitem','is_regionitem','hit'),$find,$orderer,$limiter);
		$total = $mDB->DBcount($mOneroom->table['item'],$find);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['region'] = $mOneroom->GetRegion($lists[$i]['region1'],$lists[$i]['region2'],$lists[$i]['region3']);
			$lists[$i]['category'] = $mOneroom->GetCategory($lists[$i]['category1'],$lists[$i]['category2'],$lists[$i]['category3']);
			$lists[$i]['reg_date'] = GetTime('Y.m.d',$lists[$i]['reg_date']);
			$lists[$i]['end_date'] = $lists[$i]['end_date'] == 0 ? '0' : GetTime('Y.m.d',$lists[$i]['end_date']);
			
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'].'('.$mData['user_id'].')';
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
}

if ($action == 'premium') {
	$subaction = Request('subaction');
	
	if ($get == 'item') {
		$find = "where 1";
		$premiumno = $mOneroom->GetZeroValue('premiumno');
	
		if ($premiumno != null) $find.= " and `premiumno`='$premiumno'";
	
		$lists = $mDB->DBfetchs($mOneroom->table['premium_item'],array('itemno','reg_date'),$find,$orderer,$limiter);
		$total = $mDB->DBcount($mOneroom->table['premium_item'],$find);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','agent','dealer','category1','category2','category3','region1','region2','region3','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','title','areasize1','real_areasize1','reg_date'),"where `idx`={$lists[$i]['itemno']}");
			
			$region = $mOneroom->GetRegion($item['region1'],$item['region2'],$item['region3']);
			$category = $mOneroom->GetCategory($item['category1'],$item['category2'],$item['category3']);
			
			$priceType = $item['is_buy'].','.$item['is_rent_all'].','.$item['is_rent_month'].','.$item['is_rent_short'];
			$price = $item['price_buy'].','.$item['price_rent_all'].','.$item['price_rent_deposit'].','.$item['price_rent_month'];
			
			$lists[$i] = '{';
			$lists[$i].= '"idx":"'.$item['idx'].'",';
			$lists[$i].= '"region":"'.GetString($region,'ext').'",';
			$lists[$i].= '"category":"'.GetString($category,'ext').'",';
			$lists[$i].= '"agent":"'.GetString($mOneroom->GetAgentName($item['agent']).'('.$mOneroom->GetDealerName($item['dealer']).')','ext').'",';
			$lists[$i].= '"title":"'.GetString($item['title'],'ext').'",';
			$lists[$i].= '"price_type":"'.$priceType.'",';
			$lists[$i].= '"price":"'.$price.'",';
			$lists[$i].= '"areasize":"'.$item['areasize1'].','.$item['real_areasize1'].'",';
			$lists[$i].= '"reg_date":"'.GetTime('Y.m.d H:i',$item['reg_date']).'"';
			$lists[$i].= '}';
		}
	}
}

if ($action == 'prodealer') {
	$type = Request('type');
	
	if ($type) {
		$defaults = $mDB->DBfetchs($mOneroom->table['prodealer_default'],array('mno'));
		for ($i=0, $loop=sizeof($defaults);$i<$loop;$i++) {
			$defaults[$i] = $defaults[$i]['mno'];
		}
	}
	if ($type == 'prodealer') {
		$find = 'where `idx` IN ('.implode(',',$defaults).')';
	} elseif ($type == 'default') {
		$find = 'where `idx` NOT IN ('.implode(',',$defaults).')';
	} else {
		$find = '';
	}
	
	$lists = $mDB->DBfetchs($_ENV['table']['member'],array('idx','user_id','name'),$find);
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$default = $mDB->DBfetch($mOneroom->table['prodealer_default'],'*',"where `mno`='{$lists[$i]['idx']}'");
		$lists[$i]['status'] = isset($default['mno']) == true > 0 ? 'TRUE' : 'FALSE';
		if (isset($default['mno']) == true) {
			$lists[$i]['region'] = $mOneroom->GetRegionName($default['region1']).' '.$mOneroom->GetRegionName($default['region2']).' '.$mOneroom->GetRegionName($default['region3']);
		} else {
			$lists[$i]['region'] = '';
		}
		$lists[$i]['itemcount'] = $mDB->DBcount($mOneroom->table['item'],"where `mno`='{$lists[$i]['idx']}' and `is_open`='TRUE'");
		
		$region1 = $mDB->DBfetch($mOneroom->table['item'],array('COUNT(*)','region1'),"where `mno`='{$lists[$i]['idx']}' and `is_open`='TRUE' group by `region1`",'`0`,desc','0,1');
		
		$lists[$i]['region1'] = isset($region1['region1']) == true ? $region1['region1'] : '0';
		$lists[$i]['region1_title'] = $lists[$i]['region1'] ? $mOneroom->GetRegionName($lists[$i]['region1']) : '';
		$lists[$i]['region1_count'] = isset($region1[0]) == true ? $region1[0] : '0';
		
		$region2 = $mDB->DBfetch($mOneroom->table['item'],array('COUNT(*)','region2'),"where `mno`='{$lists[$i]['idx']}' and `region1`='{$region1['region1']}' and `is_open`='TRUE' group by `region2`",'`0`,desc','0,1');
		
		$lists[$i]['region2'] = isset($region2['region2']) == true ? $region2['region2'] : '0';
		$lists[$i]['region2_title'] = $lists[$i]['region2'] != '0' ? $mOneroom->GetRegionName($lists[$i]['region2']) : '';
		$lists[$i]['region2_count'] = isset($region2[0]) == true ? $region2[0] : '0';
		
		$region3 = $mDB->DBfetch($mOneroom->table['item'],array('COUNT(*)','region3'),"where `mno`='{$lists[$i]['idx']}' and `region2`='{$region2['region2']}' and `is_open`='TRUE' group by `region3`",'`0`,desc','0,1');
		
		$lists[$i]['region3'] = isset($region3['region3']) == true ? $region3['region3'] : '0';
		$lists[$i]['region3_title'] = $lists[$i]['region3'] != '0' ? $mOneroom->GetRegionName($lists[$i]['region3']) : '';
		$lists[$i]['region3_count'] = isset($region3[0]) == true ? $region3[0] : '0';
	}
}

if ($action == 'slot') {
	$get = Request('get');
	
	$lists = $mDB->DBfetchs($mOneroom->table['slot'],'*',"where `type`='$get'");
}

if ($action == 'file') {
	$get = Request('get');
	
	if ($get == 'totalsize') {
		$data = $mDB->DBfetch($mOneroom->table['file'],array('SUM(filesize)'));
		$return['success'] = true;
		$return['totalsize'] = isset($data[0]) == true ? $data[0] : 0;
		exit(json_encode($return));
	} else {
		$keyword = Request('keyword');
		if ($get == 'register') $find = "where `repto`!=0";
		elseif ($get == 'temp') $find = "where `repto`=0";
		elseif ($get == 'image') $find = "where `filetype`='IMG'";
	
		if ($keyword) $find.= " and `filename` like '%$keyword%'";
		$boardInfo = array();
		$total = $mDB->DBcount($mOneroom->table['file'],$find);
		$lists = $mDB->DBfetchs($mOneroom->table['file'],'*',$find,$orderer,$limiter);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$post = $ment = array();
			if ($lists[$i]['repto'] != 0) {
				$item = $mDB->DBfetch($mOneroom->table['item'],array('title'),"where `idx`='{$lists[$i]['repto']}'");
			}
				
			if (isset($item['title']) == true) $lists[$i]['title'] = $item['title'];
			else $lists[$i]['title'] = '';
			
			if ($lists[$i]['filetype'] == 'IMG' && $get == 'image') {
				if (file_exists($_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$lists[$i]['idx'].'.thm') == true) {
					$lists[$i]['image'] = $_ENV['userfileDir'].$mOneroom->thumbnail.'/'.$lists[$i]['idx'].'.thm';
				} else {
					if (GetThumbnail($_ENV['userfilePath'].$mOneroom->userfile.$lists[$i]['filepath'],$_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$lists[$i]['idx'].'.thm',100,75,false) == true) {
						$lists[$i]['image'] = $_ENV['userfileDir'].$mOneroom->thumbnail.'/'.$lists[$i]['idx'].'.thm';
					} else {
						$lists[$i]['image'] = $_ENV['dir'].'/module/board/images/admin/noimage.gif';
					}
				}
				
			} else {
				$lists[$i]['image'] = '';
			}
			
			$lists[$i]['filepath'] = $_ENV['userfilePath'].$mOneroom->userfile.$lists[$i]['filepath'];
		}
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>