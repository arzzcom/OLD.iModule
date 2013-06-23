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

$mPoll = new ModulePoll();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'list') {
	$find = '';
	$is_all = Request('is_all');
	$keyword = Request('keyword');

	if ($keyword != null) {
		$find = "where `pid` like '%$keyword%' or `title` like '%$keyword%'";
	}
	
	$total = $mDB->DBcount($mPoll->table['setup'],$find);
	$lists = $mDB->DBfetchs($mPoll->table['setup'],'*',$find,$orderer,$limiter);
	

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if ($is_all == null) {
			$lists[$i]['postnum'] = $mDB->DBcount($mPoll->table['post'],"where `pid`='{$lists[$i]['pid']}'");
			$lastPost = $mDB->DBfetch($mPoll->table['post'],array('reg_date'),"where `pid`='{$lists[$i]['pid']}'",'idx,desc','0,1');
			if (isset($lastPost['reg_date']) == true) {
				$lists[$i]['last_date'] = GetTime('Y.m.d H:i:s',$lastPost['reg_date']);
			} else {
				$lists[$i]['last_date'] = '';
			}
		}
	}

	if ($is_all == 'true') {
		$lists[] = array('pid'=>'','title'=>'전체설문조사');
	}
}

if ($action == 'poll') {
	$pid = Request('pid');

	$data = $mDB->DBfetch($mPoll->table['setup'],'*',"where `pid`='$pid'");
	
	if (isset($data['pid']) == true) {
		$data['use_ment'] = $data['use_ment'] == 'TRUE' ? 'on' : 'off';
		
		if ($data['permission'] && is_array(unserialize($data['permission'])) == true) {
			$temp = unserialize($data['permission']);
			foreach($temp as $key=>$value) {
				$data['permission_'.$key] = $value;
			}
		} else {
			$data = array_merge($data,array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_vote'=>'true','permission_result'=>'true','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\''));
		}
	} else {
		$data = array();
		
		$data['width'] = '100%';
		$data['use_ment'] = 'on';
		
		$data = array_merge($data,array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_vote'=>'true','permission_result'=>'true','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\''));
	}

	$return['success'] = true;
	$return['data'] = $data;
	exit(json_encode($return));
}

if ($action == 'poll_all') {
	$pid = explode(',',Request('pid'));

	$data = array();
	
	$permission = array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_vote'=>'true','permission_result'=>'true','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\'');
	
	for ($i=0, $loop=sizeof($pid);$i<$loop;$i++) {
		$poll = $mDB->DBfetch($mPoll->table['setup'],'*',"where `pid`='{$pid[$i]}'");
		
		if (isset($data['skin']) == false) {
			$data['is_skin'] = 'on';
			$data['skin'] = $poll['skin'];
		} elseif ($data['skin'] != $poll['skin']) {
			$data['is_skin'] = 'off';
			$data['skin'] = '';
		}
		
		if (isset($data['width']) == false) {
			$data['is_width'] = 'on';
			$data['width'] = $poll['width'];
		} elseif ($data['width'] != $poll['width']) {
			$data['is_width'] = 'off';
			$data['width'] = '';
		}
		
		if (isset($data['use_ment']) == false) {
			$data['is_use_ment'] = 'on';
			$data['use_ment'] = $poll['use_ment'] == 'TRUE' ? 'on' : 'off';
		} elseif ($data['use_ment'] != ($poll['use_ment'] == 'TRUE' ? 'on' : 'off')) {
			$data['is_use_ment'] = 'off';
			$data['use_ment'] = 'off';
		}
		
		if (isset($data['listnum']) == false) {
			$data['listnum'] = $poll['listnum'];
		} elseif ($data['listnum'] != $poll['listnum']) {
			$data['listnum'] = '';
		}
		
		if (isset($data['pagenum']) == false) {
			$data['pagenum'] = $poll['pagenum'];
		} elseif ($data['pagenum'] != $poll['pagenum']) {
			$data['pagenum'] = '';
		}

		if (isset($data['post_point']) == false) {
			$data['post_point'] = $poll['post_point'];
		} elseif ($data['post_point'] != $poll['post_point']) {
			$data['post_point'] = '';
		}
		
		if (isset($data['ment_point']) == false) {
			$data['ment_point'] = $poll['ment_point'];
		} elseif ($data['ment_point'] != $poll['ment_point']) {
			$data['ment_point'] = '';
		}
		
		if (isset($data['vote_point']) == false) {
			$data['vote_point'] = $poll['vote_point'];
		} elseif ($data['vote_point'] != $poll['vote_point']) {
			$data['vote_point'] = '';
		}
		
		if ($poll['permission'] && is_array(unserialize($poll['permission'])) == true) {
			$temp = unserialize($poll['permission']);
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
	
	if ($data['vote_point'] == '') {
		$data['vote_point'] = 30;
		$data['is_vote_point'] = 'off';
	} else {
		$data['is_vote_point'] = 'on';
	}

	$return['success'] = true;
	$return['data'] = $data;
	exit(json_encode($return));
}

if ($action == 'post') {
	if ($get == 'list') {
		$pid = Request('pid');
		$key = Request('key');
		$keyword = Request('keyword');
		$category = Request('category');
	
		$find = "where 1";
		if ($pid) $find.= " and `pid`='$pid'";
	
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
				$searchMent = $mDB->DBfetchs($mPoll->table['ment'],array('repto'),"where ".$keyQuery);
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
		$total = $mDB->DBcount($mPoll->table['post'],$find);
		$lists = $mDB->DBfetchs($mPoll->table['post'],'*',$find,$orderer,$limiter);
	
		$pollInfo = array();
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if (isset($pollInfo[$lists[$i]['pid']]) == false) {
				$pollInfo[$lists[$i]['pid']] = $mDB->DBfetch($mPoll->table['setup'],array('title','width'),"where `pid`='{$lists[$i]['pid']}'");
			}
			$lists[$i]['polltitle'] = $pollInfo[$lists[$i]['pid']]['title'];
			$lists[$i]['width'] = $pollInfo[$lists[$i]['pid']]['width'];
			
			if ($lists[$i]['mno'] != 0) {
				$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
				$lists[$i]['name'] = $mData['name'];
				$lists[$i]['nickname'] = $mData['nickname'];
			} else {
				$lists[$i]['nickname'] = '';
			}
			
			$lists[$i]['file'] = file_exists($_ENV['userfilePath'].$mPoll->userfile.'/'.$lists[$i]['idx'].'.file') == true ? 'TRUE' : 'FALSE';
	
			$lists[$i]['newment'] = $lists[$i]['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE';
			$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
			$lists[$i]['end_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['end_date']);
		}
	}
	
	if ($get == 'data') {
		$idx = Request('idx');
		
		$data = $mDB->DBfetch($mPoll->table['post'],'*',"where `idx`='$idx'");
		$data['end_date'] = $data['end_date'] == '0' ? '' : GetTime('Y-m-d',$data['end_date']);
		$data['unlimit'] = $data['end_date'] == '0' ? 'on' : 'off';
		$data['is_multi'] = $data['vote_type'] == 'MULTI' ? 'on' : 'off';
		$data['image'] = file_exists($_ENV['userfilePath'].$mPoll->userfile.'/'.$data['idx'].'.file') == true ? 'TRUE' : 'FALSE';
		
		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
	
	if ($get == 'item') {
		$idx = Request('idx');
		$lists = $mDB->DBfetchs($mPoll->table['item'],'*',"where `repto`='$idx'");
	}
}

if ($action == 'ment') {
	$pid = Request('pid');
	$key = Request('key');
	$keyword = Request('keyword');

	$find = "where `is_delete`='FALSE'";
	if ($pid) $find.= " and `pid`='$pid'";

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
	$total = $mDB->DBcount($mPoll->table['ment'],$find);
	$lists = $mDB->DBfetchs($mPoll->table['ment'],'*',$find,$orderer,$limiter);

	$pollInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($pollInfo[$lists[$i]['pid']]) == false) {
			$pollInfo[$lists[$i]['pid']] = $mDB->DBfetch($mPoll->table['setup'],array('title','width'),"where `pid`='{$lists[$i]['pid']}'");
		}
		$lists[$i]['polltitle'] = $pollInfo[$lists[$i]['pid']]['title'];
		$lists[$i]['width'] = $pollInfo[$lists[$i]['pid']]['width'];
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$post = $mDB->DBfetch($mPoll->table['post'],array('is_delete','title'),"where `idx`={$lists[$i]['repto']}");
		$lists[$i]['postdelete'] = $post['is_delete'];
		$lists[$i]['posttitle'] = $post['title'];
		
		$file = array();
		$files = $mDB->DBfetchs($mPoll->table['file'],array('idx','filename','filesize','hit'),"where `type`='MENT' and `repto`={$lists[$i]['idx']}");
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
		$data = $mDB->DBfetch($mPoll->table['file'],array('SUM(filesize)'));
		$return['success'] = true;
		$return['totalsize'] = isset($data[0]) == true ? $data[0] : 0;
		exit(json_encode($return));
	} else {
		$keyword = Request('keyword');
		if ($get == 'register') $find = "where `repto`!=0";
		elseif ($get == 'temp') $find = "where `repto`=0";
		elseif ($get == 'image') $find = "where `filetype`='IMG'";
	
		if ($keyword) $find.= " and `filename` like '%$keyword%'";
		$pollInfo = array();
		$total = $mDB->DBcount($mPoll->table['file'],$find);
		$lists = $mDB->DBfetchs($mPoll->table['file'],'*',$find,$orderer,$limiter);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$post = $ment = array();
			if ($lists[$i]['repto'] != 0) {
				if ($lists[$i]['type'] == 'POST') {
					$post = $mDB->DBfetch($mPoll->table['post'],array('idx','is_delete','pid','title','mno','name','reg_date'),"where `idx`={$lists[$i]['repto']}");
				} elseif ($lists[$i]['type'] == 'MENT') {
					$ment = $mDB->DBfetch($mPoll->table['ment'],array('repto','mno','name','reg_date'),"where `idx`={$lists[$i]['repto']}");
					if (isset($ment['repto']) == true) {
						$post = $mDB->DBfetch($mPoll->table['post'],array('idx','is_delete','pid','title'),"where `idx`={$ment['repto']}");
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
			
			if (isset($post['pid']) ==  true) {
				if (isset($pollInfo[$post['pid']]) == false) {
					$pollInfo[$post['pid']] = $mDB->DBfetch($mPoll->table['setup'],array('title','width'),"where `pid`='{$post['pid']}'");
				}
		
				$lists[$i]['pid'] = $post['pid'];
				$lists[$i]['polltitle'] = $pollInfo[$post['pid']]['title'];
				$lists[$i]['width'] = $pollInfo[$post['pid']]['width'];
				$lists[$i]['postidx'] = $post['idx'];
			} else {
				$lists[$i]['pid'] = '';
				$lists[$i]['polltitle'] = '';
				$lists[$i]['width'] = '';
				$lists[$i]['postidx'] = '';
			}
			
			if (isset($post['reg_date']) == true) {
				$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$post['reg_date']);
			} else {
				$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
			}
			
			if ($lists[$i]['filetype'] == 'IMG' && $get == 'image') {
				if (file_exists($_ENV['userfilePath'].$mPoll->thumbnail.'/'.$lists[$i]['idx'].'.thm') == true) {
					$lists[$i]['image'] = $_ENV['userfileDir'].$mPoll->thumbnail.'/'.$lists[$i]['idx'].'.thm';
				} else {
					if (GetThumbnail($_ENV['userfilePath'].$mPoll->userfile.$lists[$i]['filepath'],$_ENV['userfilePath'].$mPoll->thumbnail.'/'.$lists[$i]['idx'].'.thm',150,120,false) == true) {
						$lists[$i]['image'] = $_ENV['userfileDir'].$mPoll->thumbnail.'/'.$lists[$i]['idx'].'.thm';
					} else {
						$lists[$i]['image'] = $_ENV['dir'].'/module/poll/images/admin/noimage.gif';
					}
				}
				
			} else {
				$lists[$i]['image'] = '';
			}
			
			$lists[$i]['filepath'] = $_ENV['userfilePath'].$mPoll->userfile.$lists[$i]['filepath'];
		}
	}
}

if ($action == 'trash') {
	$pid = Request('pid');
	$key = Request('key');
	$keyword = Request('keyword');
	$category = Request('category');

	$find = "where `is_delete`='TRUE'";
	if ($pid) $find.= " and `pid`='$pid'";

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
			$searchMent = $mDB->DBfetchs($mPoll->table['ment'],array('repto'),"where `is_delete`='FALSE' and ".$keyQuery);
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
	$total = $mDB->DBcount($mPoll->table['post'],$find);
	$lists = $mDB->DBfetchs($mPoll->table['post'],'*',$find,$orderer,$limiter);

	$pollInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($pollInfo[$lists[$i]['pid']]) == false) {
			$pollInfo[$lists[$i]['pid']] = $mDB->DBfetch($mPoll->table['setup'],array('title','width'),"where `pid`='{$lists[$i]['pid']}'");
		}
		$lists[$i]['polltitle'] = $pollInfo[$lists[$i]['pid']]['title'];
		$lists[$i]['width'] = $pollInfo[$lists[$i]['pid']]['width'];
		
		$lists[$i]['category'] = $mPoll->GetCategoryName($lists[$i]['category']);
		
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$file = array();
		$files = $mDB->DBfetchs($mPoll->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$lists[$i]['idx']}");
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

	$pid = Request('pid');
	$key = Request('key');
	$keyword = Request('keyword');
	
	if ($pid != null) $find.= " and `pid`='$pid'";
	
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
	
	$total = $mDB->DBcount($mPoll->table['log'],$find);
	$lists = $mDB->DBfetchs($mPoll->table['log'],'*',$find,$orderer,$limiter);
	
	$pollInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$post = $mDB->DBfetch($mPoll->table['post'],array('idx','pid','category','title','ment','last_ment'),"where `idx`='{$lists[$i]['repto']}'");
		if (isset($pollInfo[$post['pid']]) == false) {
			$pollInfo[$post['pid']] = $mDB->DBfetch($mPoll->table['setup'],array('title','width'),"where `pid`='{$post['pid']}'");
		}
		$lists[$i]['polltitle'] = $pollInfo[$lists[$i]['pid']]['title'];
		$lists[$i]['width'] = $pollInfo[$lists[$i]['pid']]['width'];
		
		$lists[$i]['postidx'] = $post['idx'];
		$lists[$i]['category'] = $mPoll->GetCategoryName($post['category']);
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
			$status = $mDB->DBfetch($mPoll->table['status'],'*',"where `date`='$day'");
			if (isset($status['date']) == false) {
				$lists[$i-1]['post'] = $mDB->DBcount($mPoll->table['post'],"where `reg_date`>$dayStart and `reg_date`<$dayEnd");
				$lists[$i-1]['ment'] = $mDB->DBcount($mPoll->table['ment'],"where `reg_date`>$dayStart and `reg_date`<$dayEnd");
				$lists[$i-1]['hit'] = $mDB->DBcount($mPoll->table['log'],"where `reg_date`>$dayStart and `reg_date`<$dayEnd and `type`='HIT'");
				
				if ($day != date('Y-m-d')) {
					$mDB->DBinsert($mPoll->table['status'],array('date'=>$day,'post'=>$lists[$i-1]['post'],'ment'=>$lists[$i-1]['ment'],'hit'=>$lists[$i-1]['hit']));
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
	$skinPath = @opendir($_ENV['path'].'/module/poll/templet/poll');
	$i = 0;
	while ($skin = @readdir($skinPath)) {
		if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/poll/templet/poll/'.$skin) == true) {
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