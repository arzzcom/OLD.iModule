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

$mRelease = new ModuleRelease();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'list') {
	$find = '';
	$is_all = Request('is_all');
	$keyword = Request('keyword');

	if ($keyword != null) {
		$find = "where `rid` like '%$keyword%' or `title` like '%$keyword%'";
	}
	
	$total = $mDB->DBcount($mRelease->table['setup'],$find);
	$lists = $mDB->DBfetchs($mRelease->table['setup'],'*',$find,$orderer,$limiter);

	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if ($is_all == null) {
			$lists[$i]['postnum'] = $mDB->DBcount($mRelease->table['post'],"where `rid`='{$lists[$i]['rid']}' and `is_delete`='FALSE'");
			$lastPost = $mDB->DBfetch($mRelease->table['post'],array('reg_date'),"where `rid`='{$lists[$i]['rid']}' and `is_delete`='FALSE'",'loop,asc','0,1');
			if (isset($lastPost['reg_date']) == true) {
				$lists[$i]['last_date'] = GetTime('Y.m.d H:i:s',$lastPost['reg_date']);
			} else {
				$lists[$i]['last_date'] = '';
			}
		}
		$lists[$i]['option'] = ($lists[$i]['use_category'] == 'FALSE' ? 'FALSE' : 'TRUE').','.$lists[$i]['use_ment'].','.$lists[$i]['use_charge'];
	}

	if ($is_all == 'true') {
		$lists[] = array('rid'=>'','title'=>'전체게시판');
	}
}

if ($action == 'category') {
	$rid = Request('rid');
	$find = "where `rid`='$rid'";
	$is_all = Request('is_all');
	$is_none = Request('is_none');

	$data = $mDB->DBfetch($mRelease->table['setup'],array('use_category'),$find);

	if ($data['use_category'] == 'TRUE') {
		$lists = $mDB->DBfetchs($mRelease->table['category'],'*',$find,'sort,asc');

		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['sort'] = $i;
			$mDB->DBupdate($mRelease->table['category'],array('sort'=>$i),'',"where `idx`='{$lists[$i]['idx']}'");
		}
	}

	if ($is_all == 'true') {
		$lists[] = array('idx'=>'','category'=>'전체','sort'=>'-2');
	}

	if ($is_none == 'true') {
		$lists[] = array('idx'=>'','category'=>'카테고리없음','sort'=>'-1');
	}
}

if ($action == 'release') {
	$rid = Request('rid');

	$data = $mDB->DBfetch($mRelease->table['setup'],'*',"where `rid`='$rid'");
	
	if (isset($data['rid']) == true) {
		$data['use_ment'] = $data['use_ment'] == 'TRUE' ? 'on' : 'off';
		$data['use_category_option'] = $data['use_category'] == 'OPTION' ? 'off' : 'on';
		$data['use_category'] = $data['use_category'] == 'FALSE' ? 'off' : 'on';
		$data['use_charge'] = $data['use_charge'] == 'TRUE' ? 'on' : 'off';

		$data['view_alllist'] = $data['view_alllist'] == 'TRUE' ? 'on' : 'off';
		$data['view_prevnext'] = $data['view_prevnext'] == 'TRUE' ? 'on' : 'off';
		
		if ($data['permission'] && is_array(unserialize($data['permission'])) == true) {
			$temp = unserialize($data['permission']);
			foreach($temp as $key=>$value) {
				$data['permission_'.$key] = $value;
			}
		} else {
			$data = array_merge($data,array('permission_list'=>'true','permission_post'=>'{$member.type} != \'GUEST\'','permission_view'=>'true','permission_ment'=>'true','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\''));
		}
	} else {
		$data = array();
		
		$data['width'] = '100%';
		$data['use_ment'] = 'on';
		$data['view_alllist'] = 'on';
		
		$data = array_merge($data,array('permission_list'=>'true','permission_post'=>'{$member.type} != \'GUEST\'','permission_view'=>'true','permission_ment'=>'true','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\''));
	}

	$return['success'] = true;
	$return['data'] = $data;
	exit(json_encode($return));
}

if ($action == 'post') {
	$rid = Request('rid');
	$key = Request('key');
	$keyword = Request('keyword');
	$category = Request('category');

	$find = "where `is_delete`='FALSE'";
	if ($rid) $find.= " and `rid`='$rid'";

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
			$searchMent = $mDB->DBfetchs($mRelease->table['ment'],array('repto'),"where `is_delete`='FALSE' and ".$keyQuery);
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
	$total = $mDB->DBcount($mRelease->table['post'],$find);
	$lists = $mDB->DBfetchs($mRelease->table['post'],'*',$find,$orderer,$limiter);

	$releaseInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($releaseInfo[$lists[$i]['rid']]) == false) {
			$releaseInfo[$lists[$i]['rid']] = $mDB->DBfetch($mRelease->table['setup'],array('title','width'),"where `rid`='{$lists[$i]['rid']}'");
		}
		$lists[$i]['releasetitle'] = $releaseInfo[$lists[$i]['rid']]['title'];
		$lists[$i]['width'] = $releaseInfo[$lists[$i]['rid']]['width'];
		
		$lists[$i]['category'] = $mRelease->GetCategoryName($lists[$i]['category']);
		
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$file = array();
		$files = $mDB->DBfetchs($mRelease->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$lists[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$lists[$i]['file'] = implode(',',$file);

		$lists[$i]['newment'] = $lists[$i]['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE';
		$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
		$lists[$i]['avgvote'] = $lists[$i]['voter'] > 0 ? sprintf('%0.2f',$lists[$i]['vote']/$lists[$i]['voter']) : '0.001';
		
		$lists[$i]['version_count'] = $mDB->DBcount($mRelease->table['version'],"where `repto`='{$lists[$i]['idx']}'");
	}
}

if ($action == 'ment') {
	$rid = Request('rid');
	$key = Request('key');
	$keyword = Request('keyword');

	$find = "where `is_delete`='FALSE'";
	if ($rid) $find.= " and `rid`='$rid'";

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
	$total = $mDB->DBcount($mRelease->table['ment'],$find);
	$lists = $mDB->DBfetchs($mRelease->table['ment'],'*',$find,$orderer,$limiter);

	$releaseInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($releaseInfo[$lists[$i]['rid']]) == false) {
			$releaseInfo[$lists[$i]['rid']] = $mDB->DBfetch($mRelease->table['setup'],array('title','width'),"where `rid`='{$lists[$i]['rid']}'");
		}
		$lists[$i]['releasetitle'] = $releaseInfo[$lists[$i]['rid']]['title'];
		$lists[$i]['width'] = $releaseInfo[$lists[$i]['rid']]['width'];
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$post = $mDB->DBfetch($mRelease->table['post'],array('is_delete','title'),"where `idx`={$lists[$i]['repto']}");
		$lists[$i]['postdelete'] = $post['is_delete'];
		$lists[$i]['posttitle'] = $post['title'];
		
		$file = array();
		$files = $mDB->DBfetchs($mRelease->table['file'],array('idx','filename','filesize','hit'),"where `type`='MENT' and `repto`={$lists[$i]['idx']}");
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
		$data = $mDB->DBfetch($mRelease->table['file'],array('SUM(filesize)'));
		$return['success'] = true;
		$return['totalsize'] = isset($data[0]) == true ? $data[0] : 0;
		exit(json_encode($return));
	} else {
		$keyword = Request('keyword');
		if ($get == 'register') $find = "where `repto`!=0";
		elseif ($get == 'temp') $find = "where `repto`=0";
		elseif ($get == 'image') $find = "where `filetype`='IMG'";
	
		if ($keyword) $find.= " and `filename` like '%$keyword%'";
		$releaseInfo = array();
		$total = $mDB->DBcount($mRelease->table['file'],$find);
		$lists = $mDB->DBfetchs($mRelease->table['file'],'*',$find,$orderer,$limiter);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$post = $ment = array();
			if ($lists[$i]['repto'] != 0) {
				if ($lists[$i]['type'] == 'POST') {
					$post = $mDB->DBfetch($mRelease->table['post'],array('idx','is_delete','rid','title','mno','reg_date'),"where `idx`={$lists[$i]['repto']}");
				} elseif ($lists[$i]['type'] == 'MENT') {
					$ment = $mDB->DBfetch($mRelease->table['ment'],array('repto','mno','name','reg_date'),"where `idx`={$lists[$i]['repto']}");
					if (isset($ment['repto']) == true) {
						$post = $mDB->DBfetch($mRelease->table['post'],array('idx','is_delete','rid','title'),"where `idx`={$ment['repto']}");
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
			
			$mData = $mMember->GetMemberInfo($post['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
			$lists[$i]['mno'] = $post['mno'];
			
			if (isset($post['is_delete']) == true) {
				$lists[$i]['postdelete'] = $post['is_delete'];
			} else {
				$lists[$i]['postdelete'] = 'TRUE';
			}
			
			if (isset($post['rid']) ==  true) {
				if (isset($releaseInfo[$post['rid']]) == false) {
					$releaseInfo[$post['rid']] = $mDB->DBfetch($mRelease->table['setup'],array('title','width'),"where `rid`='{$post['rid']}'");
				}
		
				$lists[$i]['rid'] = $post['rid'];
				$lists[$i]['releasetitle'] = $releaseInfo[$post['rid']]['title'];
				$lists[$i]['width'] = $releaseInfo[$post['rid']]['width'];
				$lists[$i]['postidx'] = $post['idx'];
			} else {
				$lists[$i]['rid'] = '';
				$lists[$i]['releasetitle'] = '';
				$lists[$i]['width'] = '';
				$lists[$i]['postidx'] = '';
			}
			
			if (isset($post['reg_date']) == true) {
				$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$post['reg_date']);
			} else {
				$lists[$i]['reg_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['reg_date']);
			}
			
			if ($lists[$i]['filetype'] == 'IMG' && $get == 'image') {
				if (file_exists($_ENV['userfilePath'].$mRelease->thumbnail.'/'.$lists[$i]['idx'].'.thm') == true) {
					$lists[$i]['image'] = $_ENV['userfileDir'].$mRelease->thumbnail.'/'.$lists[$i]['idx'].'.thm';
				} else {
					if (GetThumbnail($_ENV['userfilePath'].$mRelease->userfile.$lists[$i]['filepath'],$_ENV['userfilePath'].$mRelease->thumbnail.'/'.$lists[$i]['idx'].'.thm',150,120,false) == true) {
						$lists[$i]['image'] = $_ENV['userfileDir'].$mRelease->thumbnail.'/'.$lists[$i]['idx'].'.thm';
					} else {
						$lists[$i]['image'] = $_ENV['dir'].'/module/release/images/admin/noimage.gif';
					}
				}
				
			} else {
				$lists[$i]['image'] = '';
			}
			
			$lists[$i]['filepath'] = $_ENV['userfilePath'].$mRelease->userfile.$lists[$i]['filepath'];
		}
	}
}

if ($action == 'trash') {
	$rid = Request('rid');
	$key = Request('key');
	$keyword = Request('keyword');
	$category = Request('category');

	$find = "where `is_delete`='TRUE'";
	if ($rid) $find.= " and `rid`='$rid'";

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
			$searchMent = $mDB->DBfetchs($mRelease->table['ment'],array('repto'),"where `is_delete`='FALSE' and ".$keyQuery);
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
	$total = $mDB->DBcount($mRelease->table['post'],$find);
	$lists = $mDB->DBfetchs($mRelease->table['post'],'*',$find,$orderer,$limiter);

	$releaseInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		if (isset($releaseInfo[$lists[$i]['rid']]) == false) {
			$releaseInfo[$lists[$i]['rid']] = $mDB->DBfetch($mRelease->table['setup'],array('title','width'),"where `rid`='{$lists[$i]['rid']}'");
		}
		$lists[$i]['releasetitle'] = $releaseInfo[$lists[$i]['rid']]['title'];
		$lists[$i]['width'] = $releaseInfo[$lists[$i]['rid']]['width'];
		
		$lists[$i]['category'] = $mRelease->GetCategoryName($lists[$i]['category']);
		
		if ($lists[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($lists[$i]['mno']);
			$lists[$i]['name'] = $mData['name'];
			$lists[$i]['nickname'] = $mData['nickname'];
		} else {
			$lists[$i]['nickname'] = '';
		}

		$file = array();
		$files = $mDB->DBfetchs($mRelease->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$lists[$i]['idx']}");
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

	$rid = Request('rid');
	$key = Request('key');
	$keyword = Request('keyword');
	
	if ($rid != null) $find.= " and `rid`='$rid'";
	
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
	
	$total = $mDB->DBcount($mRelease->table['log'],$find);
	$lists = $mDB->DBfetchs($mRelease->table['log'],'*',$find,$orderer,$limiter);
	
	$releaseInfo = array();
	for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
		$post = $mDB->DBfetch($mRelease->table['post'],array('idx','rid','category','title','ment','last_ment'),"where `idx`='{$lists[$i]['repto']}'");
		if (isset($releaseInfo[$post['rid']]) == false) {
			$releaseInfo[$post['rid']] = $mDB->DBfetch($mRelease->table['setup'],array('title','width'),"where `rid`='{$post['rid']}'");
		}
		$lists[$i]['releasetitle'] = $releaseInfo[$lists[$i]['rid']]['title'];
		$lists[$i]['width'] = $releaseInfo[$lists[$i]['rid']]['width'];
		
		$lists[$i]['postidx'] = $post['idx'];
		$lists[$i]['category'] = $mRelease->GetCategoryName($post['category']);
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

if ($action == 'skin') {
	$skinPath = @opendir($_ENV['path'].'/module/release/templet/release');
	$i = 0;
	while ($skin = @readdir($skinPath)) {
		if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/release/templet/release/'.$skin) == true) {
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