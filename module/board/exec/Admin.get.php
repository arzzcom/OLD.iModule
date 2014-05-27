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

$mBoard = new ModuleBoard();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'list') {
	$find = '';
	$is_all = Request('is_all');
	$keyword = Request('keyword');

	if ($keyword != null) {
		$find = "where `bid` like '%$keyword%' or `title` like '%$keyword%'";
	}
	
	$total = $mDB->DBcount($mBoard->table['setup'],$find);
	$lists = $mDB->DBfetchs($mBoard->table['setup'],'*',$find,$orderer,$limiter);
	

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if ($is_all == null) {
			if ($lists[$i]['post_time'] > 0) {
				$lists[$i]['post_time'] = GetTime('Y.m.d H:i:s',$lists[$i]['post_time']);
			} else {
				$lists[$i]['post_time'] = '';
			}
		}
		$lists[$i]['option'] = ($lists[$i]['use_category'] == 'FALSE' ? 'FALSE' : 'TRUE').','.$lists[$i]['use_uploader'].','.$lists[$i]['use_ment'].','.$lists[$i]['use_trackback'].','.$lists[$i]['use_charge'].','.$lists[$i]['use_select'].','.$lists[$i]['use_rss'];
	}

	if ($is_all == 'true') {
		$lists[] = array('bid'=>'','title'=>'전체게시판');
	}
}

if ($action == 'category') {
	$bid = Request('bid');
	$find = "where `bid`='$bid'";
	$is_all = Request('is_all');
	$is_none = Request('is_none');

	$data = $mDB->DBfetch($mBoard->table['setup'],array('use_category'),$find);

	if ($data['use_category'] == 'TRUE') {
		$lists = $mDB->DBfetchs($mBoard->table['category'],'*',$find,'sort,asc');

		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['sort'] = $i;
			$mDB->DBupdate($mBoard->table['category'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
		}
	}

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'','category'=>'전체','sort'=>'-2');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'','category'=>'카테고리없음','sort'=>'-1');
	}
}

if ($action == 'board') {
	$bid = Request('bid');

	$data = $mDB->DBfetch($mBoard->table['setup'],'*',"where `bid`='$bid'");
	
	if (isset($data['bid']) == true) {
		$view_list = explode(',',$data['view_list']);
		unset($data['view_list']);
		
		$data['list_loopnum'] = in_array('loopnum',$view_list) == true ? 'on' : 'off';
		$data['list_name'] = in_array('name',$view_list) == true ? 'on' : 'off';
		$data['list_reg_date'] = in_array('reg_date',$view_list) == true ? 'on' : 'off';
		$data['list_hit'] = in_array('hit',$view_list) == true ? 'on' : 'off';
		$data['list_vote'] = in_array('vote',$view_list) == true ? 'on' : 'off';
		$data['list_avgvote'] = in_array('avgvote',$view_list) == true ? 'on' : 'off';

		$data['use_ment'] = $data['use_ment'] == 'TRUE' ? 'on' : 'off';
		$data['use_trackback'] = $data['use_trackback'] == 'TRUE' ? 'on' : 'off';
		$data['use_category_option'] = $data['use_category'] == 'OPTION' ? 'off' : 'on';
		$data['use_category'] = $data['use_category'] == 'FALSE' ? 'off' : 'on';
		$data['use_uploader'] = $data['use_uploader'] == 'TRUE' ? 'on' : 'off';
		$data['use_charge'] = $data['use_charge'] == 'TRUE' ? 'on' : 'off';
		$data['use_select'] = $data['use_select'] == 'TRUE' ? 'on' : 'off';

		$data['view_alllist'] = $data['view_alllist'] == 'TRUE' ? 'on' : 'off';
		$data['view_prevnext'] = $data['view_prevnext'] == 'TRUE' ? 'on' : 'off';
		$data['view_notice'] = $data['view_notice_page'].','.$data['view_notice_count'];
		
		$data['use_rss'] = $data['use_rss'] == 'TRUE' ? 'on' : 'off';
		if ($data['rss_config'] && is_array(unserialize($data['rss_config'])) == true) {
			$data = array_merge($data,unserialize($data['rss_config']));
		} else {
			$data = array_merge($data,array('rss_limit'=>'30','rss_post_limit'=>'0','rss_link'=>'{$HTTP_HOST}'.$mBoard->moduleDir.'/board.php?bid='.$bid,'rss_description'=>'','rss_language'=>'ko'));
		}
		
		if ($data['permission'] && is_array(unserialize($data['permission'])) == true) {
			$temp = unserialize($data['permission']);
			foreach($temp as $key=>$value) {
				$data['permission_'.$key] = $value;
			}
		} else {
			$data = array_merge($data,array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_secret'=>'{$member.type} == \'ADMINISTRATOR\'','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\'','permission_notice'=>'{$member.type} == \'ADMINISTRATOR\''));
		}
	} else {
		$data = array();
		
		$data['width'] = '100%';
		$data['list_loopnum'] = $data['list_name'] = $data['list_reg_date'] = $data['list_hit'] = 'on';
		$data['use_ment'] = $data['use_trackback'] = $data['use_uploader'] = 'on';
		$data['view_notice'] = 'ALL,INCLUDE';
		$data['view_alllist'] = 'on';
		
		$data = array_merge($data,array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_select'=>'{$member.type} == \'ADMINISTRATOR\'','permission_secret'=>'{$member.type} == \'ADMINISTRATOR\'','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\'','permission_notice'=>'{$member.type} == \'ADMINISTRATOR\''));
	}

	$return['success'] = true;
	$return['data'] = $data;
	exit(json_encode($return));
}

if ($action == 'board_all') {
	$bid = explode(',',Request('bid'));

	$data = array();
	
	$permission = array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_select'=>'{$member.type} == \'ADMINISTRATOR\'','permission_secret'=>'{$member.type} == \'ADMINISTRATOR\'','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\'','permission_notice'=>'{$member.type} == \'ADMINISTRATOR\'');
	
	for ($i=0, $loop=sizeof($bid);$i<$loop;$i++) {
		$board = $mDB->DBfetch($mBoard->table['setup'],'*',"where `bid`='{$bid[$i]}'");
		
		if (isset($data['skin']) == false) {
			$data['is_skin'] = 'on';
			$data['skin'] = $board['skin'];
		} elseif ($data['skin'] != $board['skin']) {
			$data['is_skin'] = 'off';
			$data['skin'] = '';
		}
		
		if (isset($data['width']) == false) {
			$data['is_width'] = 'on';
			$data['width'] = $board['width'];
		} elseif ($data['width'] != $board['width']) {
			$data['is_width'] = 'off';
			$data['width'] = '';
		}
		
		if (isset($data['use_ment']) == false) {
			$data['is_use_ment'] = 'on';
			$data['use_ment'] = $board['use_ment'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['use_ment'] != ($board['use_ment'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_ment'] = 'off';
			$data['use_ment'] = 'off';
		}
		
		if (isset($data['use_trackback']) == false) {
			$data['is_use_trackback'] = 'on';
			$data['use_trackback'] = $board['use_trackback'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['use_trackback'] != ($board['use_trackback'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_trackback'] = 'off';
			$data['use_trackback'] = 'off';
		}
		
		if (isset($data['use_category']) == false) {
			$data['is_use_category'] = 'on';
			$data['use_category'] = $board['use_category'] != 'FALSE' ? 'on' : 'off';
		} elseif ($data['use_category'] != ($board['use_category'] != 'FALSE' ? 'on' : 'off')) {
			$data['is_use_category'] = 'off';
			$data['use_category'] = 'off';
		}
		
		if (isset($data['use_category_option']) == false) {
			$data['is_use_category_option'] = 'on';
			$data['use_category_option'] = $board['use_category'] == 'OPTION' ? 'off' : 'on';
		} elseif ($data['use_category_option'] != ($board['use_category'] == 'OPTION' ? 'off' : 'on')) {
			$data['is_use_category_option'] = 'off';
			$data['use_category_option'] = 'off';
		}
		
		if (isset($data['use_uploader']) == false) {
			$data['is_use_uploader'] = 'on';
			$data['use_uploader'] = $board['use_uploader'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['use_uploader'] != ($board['use_uploader'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_uploader'] = 'off';
			$data['use_uploader'] = 'off';
		}
		
		if (isset($data['use_charge']) == false) {
			$data['is_use_charge'] = 'on';
			$data['use_charge'] = $board['use_charge'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['use_charge'] != ($board['use_charge'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_charge'] = 'off';
			$data['use_charge'] = 'off';
		}
		
		if (isset($data['use_select']) == false) {
			$data['is_use_select'] = 'on';
			$data['use_select'] = $board['use_select'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['use_select'] != ($board['use_select'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_select'] = 'off';
			$data['use_select'] = 'off';
		}
		
		if (isset($data['view_alllist']) == false) {
			$data['is_view_alllist'] = 'on';
			$data['view_alllist'] = $board['view_alllist'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['view_alllist'] != ($board['view_alllist'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_select'] = 'off';
			$data['use_select'] = 'off';
		}
		
		if (isset($data['view_prevnext']) == false) {
			$data['is_view_prevnext'] = 'on';
			$data['view_prevnext'] = $board['view_prevnext'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['view_prevnext'] != ($board['view_prevnext'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_view_prevnext'] = 'off';
			$data['view_prevnext'] = 'off';
		}
		
		if (isset($data['view_notice']) == false) {
			$data['is_view_notice'] = 'on';
			$data['view_notice'] = $board['view_notice_page'].','.$board['view_notice_count'];
		} elseif ($data['view_notice'] != $board['view_notice_page'].','.$board['view_notice_count']) {
			$data['is_view_notice'] = 'off';
			$data['view_notice'] = 'ALL,INCLUDE';
		}
		
		if (isset($data['listnum']) == false) {
			$data['listnum'] = $board['listnum'];
		} elseif ($data['listnum'] != $board['listnum']) {
			$data['listnum'] = '';
		}
		
		if (isset($data['pagenum']) == false) {
			$data['pagenum'] = $board['pagenum'];
		} elseif ($data['pagenum'] != $board['pagenum']) {
			$data['pagenum'] = '';
		}

		if (isset($data['post_point']) == false) {
			$data['post_point'] = $board['post_point'];
		} elseif ($data['post_point'] != $board['post_point']) {
			$data['post_point'] = '';
		}
		
		if (isset($data['ment_point']) == false) {
			$data['ment_point'] = $board['ment_point'];
		} elseif ($data['ment_point'] != $board['ment_point']) {
			$data['ment_point'] = '';
		}
		
		if (isset($data['select_point']) == false) {
			$data['select_point'] = $board['select_point'];
		} elseif ($data['select_point'] != $board['select_point']) {
			$data['select_point'] = '';
		}

		if (isset($data['view_list']) == false) {
			$data['view_list'] = $board['view_list'];
		} elseif ($data['view_list'] != $board['view_list']) {
			$data['view_list'] = '';
		}
		
		if ($board['permission'] && is_array(unserialize($board['permission'])) == true) {
			$temp = unserialize($board['permission']);
			foreach($temp as $key=>$value) {
				if (isset($data['permission_'.$key]) == false) {
					$data['permission_'.$key] = $value;
					$data['is_permission_'.$key] = 'on';
				} elseif ($data['permission_'.$key] != $value) {
					$data['permission_'.$key] = $permission['permission_'.$key];
					$data['is_permission_'.$key] = 'off';
				}
			}
		}
	}
	
	if ($data['use_category'] == 'off') {
		$data['is_use_category_option'] = 'off';
		$data['use_category_option'] = 'off';
	}
	
	if ($data['view_list'] != '') {
		$view_list = explode(',',$data['view_list']);
		
		$data['is_view_list'] = 'on';
		$data['list_loopnum'] = in_array('loopnum',$view_list) == true ? 'on' : 'off';
		$data['list_name'] = in_array('name',$view_list) == true ? 'on' : 'off';
		$data['list_reg_date'] = in_array('reg_date',$view_list) == true ? 'on' : 'off';
		$data['list_hit'] = in_array('hit',$view_list) == true ? 'on' : 'off';
		$data['list_vote'] = in_array('vote',$view_list) == true ? 'on' : 'off';
		$data['list_avgvote'] = in_array('avgvote',$view_list) == true ? 'on' : 'off';
	} else {
		$data['is_view_list'] = 'off';
	}
	
	if ($data['listnum'] == '') {
		$data['listnum'] = 30;
		$data['is_listnum'] = 'off';
	} else {
		$data['is_listnum'] = 'on';
	}
	
	if ($data['pagenum'] == '') {
		$data['pagenum'] = 30;
		$data['is_pagenum'] = 'off';
	} else {
		$data['is_pagenum'] = 'on';
	}
	
	if ($data['post_point'] == '') {
		$data['post_point'] = 30;
		$data['is_post_point'] = 'off';
	} else {
		$data['is_post_point'] = 'on';
	}
	
	if ($data['ment_point'] == '') {
		$data['ment_point'] = 30;
		$data['is_ment_point'] = 'off';
	} else {
		$data['is_ment_point'] = 'on';
	}
	
	if ($data['select_point'] == '') {
		$data['select_point'] = 30;
		$data['is_select_point'] = 'off';
	} else {
		$data['is_select_point'] = 'on';
	}

	$return['success'] = true;
	$return['data'] = $data;
	exit(json_encode($return));
}

if ($action == 'post') {
	$bid = Request('bid');
	$key = Request('key');
	$keyword = Request('keyword');
	$category = Request('category');

	$find = "where `is_delete`='FALSE'";
	if ($bid) $find.= " and `bid`='$bid'";

	if ($keyword != null) {
		$mKeyword = new Keyword($keyword);
		if ($key == 'content') {
			$keyQuery = $mKeyword->GetFullTextKeyword(array('title','search'));
			$find.= ' and '.$keyQuery;
		}

		if ($key == 'name') {
			$searchMember = $mDB->DBfetchs($_ENV['table']['member'],array('idx'),"where `name` like '%$keyword%' or `nickname` like '%$keyword%'");
			$mno = array();
			for ($i=0, $loop=sizeof($searchMember);$i<$loop;$i++) {
				$mno[] = $searchMember[$i]['idx'];
			}
			$keyQuery = $mKeyword->GetFullTextKeyword(array('name'));
			if (sizeof($mno) > 0) {
				$find.= ' and ('.$keyQuery.' or `mno` IN ('.implode(',',$mno).'))';
			} else {
				$find.= ' and '.$keyQuery;
			}
		}

		if ($key == 'ment') {
			$keyQuery = $mKeyword->GetFullTextKeyword(array('search'));
			$searchMent = $mDB->DBfetchs($mBoard->table['ment'],array('repto'),"where `is_delete`='FALSE' and ".$keyQuery);
			$ment = array();
			for ($i=0, $loop=sizeof($searchMent);$i<$loop;$i++) {
				if (in_array($searchMent[$i]['repto'],$ment) == false)$ment[] = $searchMent[$i]['repto'];
			}

			if (sizeof($ment) > 0) $find.= " and `idx` IN (".implode(',',$ment).")";
		}

		if ($key == 'ip') {
			$find.= " and `ip` like '%$keyword%'";
		}
	}

	if ($category != null) {
		$find.= " and `category`=$category";
	}

	if ($sort == 'idx' && $dir == 'desc') {
		$sort = 'loop';
		$dir = 'asc';
	}
	$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';
	$total = $mDB->DBcount($mBoard->table['post'],$find);
	$lists = $mDB->DBfetchs($mBoard->table['post'],'*',$find,$orderer,$limiter);

	$boardInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($boardInfo[$lists[$i]['bid']]) == false) {
			$boardInfo[$lists[$i]['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$lists[$i]['bid']}'");
		}
		$lists[$i]['boardtitle'] = $boardInfo[$lists[$i]['bid']]['title'];
		$lists[$i]['width'] = $boardInfo[$lists[$i]['bid']]['width'];
		
		$lists[$i]['category'] = $mBoard->GetCategoryName($lists[$i]['category']);
		
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$file = array();
		$files = $mDB->DBfetchs($mBoard->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$lists[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$lists[$i]['file'] = implode(',',$file);

		$lists[$i]['newment'] = $lists[$i]['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE';
		$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
		$lists[$i]['avgvote'] = $lists[$i]['voter'] > 0 ? sprintf('%0.2f',$lists[$i]['vote']/$lists[$i]['voter']) : '0.001';
	}
}

if ($action == 'ment') {
	$bid = Request('bid');
	$key = Request('key');
	$keyword = Request('keyword');

	$find = "where `is_delete`='FALSE'";
	if ($bid) $find.= " and `bid`='$bid'";

	if ($keyword != null) {
		$mKeyword = new Keyword($keyword);
		if ($key == 'content') {
			$keyQuery = $mKeyword->GetFullTextKeyword(array('search'));
			$find.= ' and '.$keyQuery;
		}

		if ($key == 'name') {
			$searchMember = $mDB->DBfetchs($_ENV['table']['member'],array('idx'),"where `name` like '%$keyword%' or `nickname` like '%$keyword%'");
			$mno = array();
			for ($i=0, $loop=sizeof($searchMember);$i<$loop;$i++) {
				$mno[] = $searchMember[$i]['idx'];
			}
			$keyQuery = $mKeyword->GetFullTextKeyword(array('name'));
			if (sizeof($mno) > 0) {
				$find.= ' and ('.$keyQuery.' or `mno` IN ('.implode(',',$mno).'))';
			} else {
				$find.= ' and '.$keyQuery;
			}
		}

		if ($key == 'ip') {
			$find.= " and `ip` like '%$keyword%'";
		}
	}

	if ($sort == 'idx' && $dir == 'desc') {
		$sort = 'loop';
		$dir = 'asc';
	}
	
	$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';
	$total = $mDB->DBcount($mBoard->table['ment'],$find);
	$lists = $mDB->DBfetchs($mBoard->table['ment'],'*',$find,$orderer,$limiter);

	$boardInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($boardInfo[$lists[$i]['bid']]) == false) {
			$boardInfo[$lists[$i]['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$lists[$i]['bid']}'");
		}
		$lists[$i]['boardtitle'] = $boardInfo[$lists[$i]['bid']]['title'];
		$lists[$i]['width'] = $boardInfo[$lists[$i]['bid']]['width'];
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$post = $mDB->DBfetch($mBoard->table['post'],array('is_delete','title'),"where `idx`={$lists[$i]['repto']}");
		$lists[$i]['postdelete'] = $post['is_delete'];
		$lists[$i]['posttitle'] = $post['title'];
		
		$file = array();
		$files = $mDB->DBfetchs($mBoard->table['file'],array('idx','filename','filesize','hit'),"where `type`='MENT' and `repto`={$lists[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$lists[$i]['file'] = implode(',',$file);
		$lists[$i]['content'] = $lists[$i]['search'];
		$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
	}
}

if ($action == 'file') {
	$get = Request('get');
	
	if ($get == 'totalsize') {
		$data = $mDB->DBfetch($mBoard->table['file'],array('SUM(filesize)'));
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
		$total = $mDB->DBcount($mBoard->table['file'],$find);
		$lists = $mDB->DBfetchs($mBoard->table['file'],'*',$find,$orderer,$limiter);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$post = $ment = array();
			if ($lists[$i]['repto'] != 0) {
				if ($lists[$i]['type'] == 'POST') {
					$post = $mDB->DBfetch($mBoard->table['post'],array('idx','is_delete','bid','title','mno','name','reg_date'),"where `idx`={$lists[$i]['repto']}");
				} elseif ($lists[$i]['type'] == 'MENT') {
					$ment = $mDB->DBfetch($mBoard->table['ment'],array('repto','mno','name','reg_date'),"where `idx`={$lists[$i]['repto']}");
					if (isset($ment['repto']) == true) {
						$post = $mDB->DBfetch($mBoard->table['post'],array('idx','is_delete','bid','title'),"where `idx`={$ment['repto']}");
						$post = array_merge($post,$ment);
					} else {
						$post = array();
					}
				} else {
					$post = array();
				}
			}
				
			if (isset($post['title']) == true) $lists[$i]['title'] = $post['title'];
			else $lists[$i]['title'] = '';
			
			if (isset($post['mno']) == true && $post['mno'] != 0) {
				$mData = $mMember->GetMemberInfo($post['mno']);
				$lists[$i]['name'] = $mData['name'];
				$lists[$i]['nickname'] = $mData['nickname'];
				$lists[$i]['mno'] = $post['mno'];
			} elseif (isset($post['name']) ==  true) {
				$lists[$i]['name'] = $post['name'];
				$lists[$i]['nickname'] = '';
				$lists[$i]['mno'] = 0;
			} else {
				$lists[$i]['name'] = $lists[$i]['nickname'] = '';
				$lists[$i]['mno'] = 0;
			}
			
			if (isset($post['is_delete']) == true) {
				$lists[$i]['postdelete'] = $post['is_delete'];
			} else {
				$lists[$i]['postdelete'] = 'TRUE';
			}
			
			if (isset($post['bid']) ==  true) {
				if (isset($boardInfo[$post['bid']]) == false) {
					$boardInfo[$post['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$post['bid']}'");
				}
		
				$lists[$i]['bid'] = $post['bid'];
				$lists[$i]['boardtitle'] = $boardInfo[$post['bid']]['title'];
				$lists[$i]['width'] = $boardInfo[$post['bid']]['width'];
				$lists[$i]['postidx'] = $post['idx'];
			} else {
				$lists[$i]['bid'] = '';
				$lists[$i]['boardtitle'] = '';
				$lists[$i]['width'] = '';
				$lists[$i]['postidx'] = '';
			}
			
			if (isset($post['reg_date']) == true) {
				$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$post['reg_date']);
			} else {
				$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
			}
			
			if ($lists[$i]['filetype'] == 'IMG' && $get == 'image') {
				if (file_exists($_ENV['userfilePath'].$mBoard->thumbnail.'/'.$lists[$i]['idx'].'.thm') == true) {
					$lists[$i]['image'] = $_ENV['userfileDir'].$mBoard->thumbnail.'/'.$lists[$i]['idx'].'.thm';
				} else {
					if (GetThumbnail($_ENV['userfilePath'].$mBoard->userfile.$lists[$i]['filepath'],$_ENV['userfilePath'].$mBoard->thumbnail.'/'.$lists[$i]['idx'].'.thm',150,120,false) == true) {
						$lists[$i]['image'] = $_ENV['userfileDir'].$mBoard->thumbnail.'/'.$lists[$i]['idx'].'.thm';
					} else {
						$lists[$i]['image'] = $_ENV['dir'].'/module/board/images/admin/noimage.gif';
					}
				}
				
			} else {
				$lists[$i]['image'] = '';
			}
			
			$lists[$i]['filepath'] = $_ENV['userfilePath'].$mBoard->userfile.$lists[$i]['filepath'];
		}
	}
}

if ($action == 'trash') {
	$bid = Request('bid');
	$key = Request('key');
	$keyword = Request('keyword');
	$category = Request('category');

	$find = "where `is_delete`='TRUE'";
	if ($bid) $find.= " and `bid`='$bid'";

	if ($keyword != null) {
		$mKeyword = new Keyword($keyword);
		if ($key == 'content') {
			$keyQuery = $mKeyword->GetFullTextKeyword(array('title','search'));
			$find.= ' and '.$keyQuery;
		}

		if ($key == 'name') {
			$searchMember = $mDB->DBfetchs($_ENV['table']['member'],array('idx'),"where `name` like '%$keyword%' or `nickname` like '%$keyword%'");
			$mno = array();
			for ($i=0, $loop=sizeof($searchMember);$i<$loop;$i++) {
				$mno[] = $searchMember[$i]['idx'];
			}
			$keyQuery = $mKeyword->GetFullTextKeyword(array('name'));
			if (sizeof($mno) > 0) {
				$find.= ' and ('.$keyQuery.' or `mno` IN ('.implode(',',$mno).'))';
			} else {
				$find.= ' and '.$keyQuery;
			}
		}

		if ($key == 'ment') {
			$keyQuery = $mKeyword->GetFullTextKeyword(array('search'));
			$searchMent = $mDB->DBfetchs($mBoard->table['ment'],array('repto'),"where `is_delete`='FALSE' and ".$keyQuery);
			$ment = array();
			for ($i=0, $loop=sizeof($searchMent);$i<$loop;$i++) {
				if (in_array($searchMent[$i]['repto'],$ment) == false)$ment[] = $searchMent[$i]['repto'];
			}

			if (sizeof($ment) > 0) $find.= " and `idx` IN (".implode(',',$ment).")";
		}

		if ($key == 'ip') {
			$find.= " and `ip` like '%$keyword%'";
		}
	}

	if ($category != null) {
		$find.= " and `category`=$category";
	}

	if ($sort == 'idx' && $dir == 'desc') {
		$sort = 'loop';
		$dir = 'asc';
	}
	$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';
	$total = $mDB->DBcount($mBoard->table['post'],$find);
	$lists = $mDB->DBfetchs($mBoard->table['post'],'*',$find,$orderer,$limiter);

	$boardInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($boardInfo[$lists[$i]['bid']]) == false) {
			$boardInfo[$lists[$i]['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$lists[$i]['bid']}'");
		}
		$lists[$i]['boardtitle'] = $boardInfo[$lists[$i]['bid']]['title'];
		$lists[$i]['width'] = $boardInfo[$lists[$i]['bid']]['width'];
		
		$lists[$i]['category'] = $mBoard->GetCategoryName($lists[$i]['category']);
		
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$file = array();
		$files = $mDB->DBfetchs($mBoard->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$lists[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$lists[$i]['file'] = implode(',',$file);

		$lists[$i]['newment'] = $lists[$i]['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE';
		$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
		$lists[$i]['avgvote'] = $lists[$i]['voter'] > 0 ? sprintf('%0.2f',$lists[$i]['vote']/$lists[$i]['voter']) : '0.001';
	}
}

if ($action == 'log') {
	$get = Request('get');
	
	if ($get == 'HIT') {
		$find = "where `type`='HIT'";
	} elseif ($get == 'VOTE') {
		$find = "where `type`='VOTE'";
	}

	$bid = Request('bid');
	$key = Request('key');
	$keyword = Request('keyword');
	
	if ($bid != null) $find.= " and `bid`='$bid'";
	
	if ($key == 'ip' && $keyword) {
		$find.= " and `ip`='$keyword'";
	}
	
	if ($key == 'name' && $keyword) {
		$searchMember = $mDB->DBfetchs($_ENV['table']['member'],array('idx'),"where `name`='$keyword'");
		$mno = array();
		for ($i=0, $loop=sizeof($searchMember);$i<$loop;$i++) {
			$mno[] = $searchMember[$i]['idx'];
		}
		if (sizeof($mno) > 0) {
			$find.= ' and `mno` IN ('.implode(',',$mno).')';
		}
	}
	
	$total = $mDB->DBcount($mBoard->table['log'],$find);
	$lists = $mDB->DBfetchs($mBoard->table['log'],'*',$find,$orderer,$limiter);
	
	$boardInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$post = $mDB->DBfetch($mBoard->table['post'],array('idx','bid','category','title','ment','last_ment'),"where `idx`='{$lists[$i]['repto']}'");
		if (isset($boardInfo[$post['bid']]) == false) {
			$boardInfo[$post['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$post['bid']}'");
		}
		$lists[$i]['boardtitle'] = $boardInfo[$lists[$i]['bid']]['title'];
		$lists[$i]['width'] = $boardInfo[$lists[$i]['bid']]['width'];
		
		$lists[$i]['postidx'] = $post['idx'];
		$lists[$i]['category'] = $mBoard->GetCategoryName($post['category']);
		$lists[$i]['title'] = $post['title'];
		
		if ($lists[$i]['mno'] != -1) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['name'] = '비회원';
			$lists[$i]['nickname'] = '';
			$lists[$i]['mno'] = 0;
		}
		
		$lists[$i]['newment'] = $post['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE';
		$lists[$i]['ment'] = $post['ment'];
		
		$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
	}
}

if ($action == 'status') {
	$date = Request('date') ? Request('date') : date('Y-m');
	$monthLast = date('t',strtotime($date.'-01'));
	
	for ($i=1;$i<=$monthLast;$i++) {
		$day = $date.'-'.sprintf('%02d',$i);
		$dayStart = GetGMT($day.' 00:00:00');
		$dayEnd = GetGMT($day.' 23:59:59');
		
		$lists[$i-1] = array();
		$lists[$i-1]['date'] = $i;
		if ($dayStart < GetGMT()) {
			$status = $mDB->DBfetch($mBoard->table['status'],'*',"where `date`='$day'");
			if (isset($status['date']) == false) {
				$lists[$i-1]['post'] = $mDB->DBcount($mBoard->table['post'],"where `reg_date`>$dayStart and `reg_date`<$dayEnd");
				$lists[$i-1]['ment'] = $mDB->DBcount($mBoard->table['ment'],"where `reg_date`>$dayStart and `reg_date`<$dayEnd");
				$lists[$i-1]['hit'] = $mDB->DBcount($mBoard->table['log'],"where `reg_date`>$dayStart and `reg_date`<$dayEnd and `type`='HIT'");
				
				if ($day != date('Y-m-d')) {
					$mDB->DBinsert($mBoard->table['status'],array('date'=>$day,'post'=>$lists[$i-1]['post'],'ment'=>$lists[$i-1]['ment'],'hit'=>$lists[$i-1]['hit']));
				}
			} else {
				$status['date'] = $i;
				$lists[$i-1] = $status;
			}
		} else {
			$lists[$i-1]['post'] = 0;
			$lists[$i-1]['ment'] = 0;
			$lists[$i-1]['hit'] = 0;
		}
	}
}

if ($action == 'skin') {
	$skinPath = @opendir($_ENV['path'].'/module/board/templet/board');
	$i = 0;
	while ($skin = @readdir($skinPath)) {
		if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/board/templet/board/'.$skin) == true) {
			$lists[$i] = array('skin'=>$skin);
			$i++;
		}
	}
	@closedir($skinPath);
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>