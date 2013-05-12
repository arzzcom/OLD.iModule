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

$mBoard = new ModuleBoard();

if ($action == 'list') {
	$find = '';
	$is_all = Request('is_all');
	$keyword = Request('keyword');

	if ($keyword != null) {
		$find = "where `bid` like '%$keyword%' or `title` like '%$keyword%'";
	}
	$data = $mDB->DBfetchs($mBoard->table['setup'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mBoard->table['setup'],$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$postnum = $mDB->DBcount($mBoard->table['post'],"where `bid`='{$data[$i]['bid']}'");
		$option = ($data[$i]['use_category'] == 'FALSE' ? 'FALSE' : 'TRUE').','.$data[$i]['use_uploader'].','.$data[$i]['use_ment'].','.$data[$i]['use_trackback'];

		$lastPost = $mDB->DBfetch($mBoard->table['post'],array('reg_date'),"where `bid`='{$data[$i]['bid']}'",'loop,asc','0,1');
		if (isset($lastPost['reg_date']) == true) {
			$last_date = GetTime('Y.m.d H:i:s',$lastPost['reg_date']);
		} else {
			$last_date = '';
		}

		$list[$i] = '{';
		$list[$i].= '"bid":"'.$data[$i]['bid'].'",';
		$list[$i].= '"group":"",';
		$list[$i].= '"width":"'.$data[$i]['width'].'",';
		$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
		$list[$i].= '"skin":"'.$data[$i]['skin'].'",';
		$list[$i].= '"option":"'.$option.'",';
		$list[$i].= '"postnum":"'.$postnum.'",';
		$list[$i].= '"last_date":"'.$last_date.'"';
		$list[$i].= '}';
	}

	if ($is_all == 'true') {
		$list[] = '{"bid":"","title":"전체게시판"}';
	}
}

if ($action == 'category') {
	$bid = Request('bid');
	$find = "where `bid`='$bid'";
	$is_all = Request('is_all');
	$is_none = Request('is_none');
	$data = $mDB->DBfetch($mBoard->table['setup'],array('use_category'),$find);

	if ($data['use_category'] == 'TRUE') {
		$categoryList = $mDB->DBfetchs($mBoard->table['category'],'*',"where `bid`='$bid'",'order,asc');

		for ($i=0, $loop=sizeof($categoryList);$i<$loop;$i++) {
			$list[$i] = '{"category":"'.$categoryList[$i]['idx'].'","title":"'.GetString($categoryList[$i]['category'],'ext').'","order":"'.$categoryList[$i]['order'].'","permission":"'.GetString($categoryList[$i]['permission'],'ext').'"}';
		}
	}

	if ($is_all == 'true') {
		$list[] = '{"category":"","title":"전체","order":"-2"}';
	}

	if ($is_none == 'true') {
		$list[] = '{"category":"0","title":"카테고리없음","order":"-1"}';
	}
}

if ($action == 'board') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$bid = Request('bid');

	$data = $mDB->DBfetch($mBoard->table['setup'],'*',"where `bid`='$bid'");

	$view_list = split(',',$data['view_list']);
	unset($data['view_list']);

	$data['list_loopnum'] = in_array('loopnum',$view_list) == true ? 'on' : 'off';
	$data['list_name'] = in_array('name',$view_list) == true ? 'on' : 'off';
	$data['list_reg_date'] = in_array('reg_date',$view_list) == true ? 'on' : 'off';
	$data['list_hit'] = in_array('hit',$view_list) == true ? 'on' : 'off';
	$data['list_vote'] = in_array('vote',$view_list) == true ? 'on' : 'off';
	$data['list_avgvote'] = in_array('avgvote',$view_list) == true ? 'on' : 'off';

	$data['alllist'] = $data['view_alllist'] == 'TRUE' ? 'on' : 'off';
	$data['prevnext'] = $data['view_prevnext'] == 'TRUE' ? 'on' : 'off';
	$data['use_ment'] = $data['use_ment'] == 'TRUE' ? 'on' : 'off';
	$data['use_trackback'] = $data['use_trackback'] == 'TRUE' ? 'on' : 'off';
	$data['use_category_option'] = $data['use_category'] == 'OPTION' ? 'off' : 'on';
	$data['use_category'] = $data['use_category'] == 'FALSE' ? 'off' : 'on';
	$data['use_uploader'] = $data['use_uploader'] == 'TRUE' ? 'on' : 'off';
	$data['use_charge'] = $data['use_charge'] == 'TRUE' ? 'on' : 'off';
	$data['use_select'] = $data['use_select'] == 'TRUE' ? 'on' : 'off';
	$data['view_notice'] = $data['view_notice_page'].','.$data['view_notice_count'];

	$data['use_rss'] = $data['use_rss'] == 'TRUE' ? 'on' : 'off';
	if ($data['rss_config'] && is_array(GetUnserialize($data['rss_config'])) == true) {
		$rss = unserialize($data['rss_config']);
	} else {
		$rss = array('rss_limit'=>'30','rss_post_limit'=>'0','rss_link'=>'{$HTTP_HOST}'.$mBoard->moduleDir.'/board.php?bid='.$bid,'rss_description'=>'','rss_language'=>'ko');
	}

	$data = array_merge($data,$rss);

	if ($data['permission'] && is_array(GetUnserialize($data['permission'])) == true) {
		$temp = GetUnserialize($data['permission']);
		foreach($temp as $key=>$value) {
			$permission['permission_'.$key] = $value;
		}
	} else {
		$permission = array('permission_list'=>'true','permission_post'=>'true','permission_view'=>'true','permission_ment'=>'true','permission_select'=>'{$member.type} == \'ADMINISTRATOR\'','permission_secret'=>'{$member.type} == \'ADMINISTRATOR\'','permission_modify'=>'{$member.type} == \'ADMINISTRATOR\'','permission_delete'=>'{$member.type} == \'ADMINISTRATOR\'','permission_notice'=>'{$member.type} == \'ADMINISTRATOR\'');
	}

	$data = array_merge($data,$permission);

	echo GetArrayToExtXML($data);
	exit;
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
			$find.= " `ip` like '%$keyword%'";
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
	$data = $mDB->DBfetchs($mBoard->table['post'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mBoard->table['post'],$find);

	$boardInfo = array();
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		if (isset($boardInfo[$data[$i]['bid']]) == false) {
			$boardInfo[$data[$i]['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$data[$i]['bid']}'");
		}

		if ($data[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($data[$i]['mno']);
			$data[$i]['name'] = $mData['name'].'('.$mData['nickname'].')';
		}

		$file = array();
		$files = $mDB->DBfetchs($mBoard->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$data[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$file = implode(',',$file);

		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"bid":"'.$data[$i]['bid'].'",';
		$list[$i].= '"category":"'.GetString($mBoard->GetCategoryName($data[$i]['category']),'ext').'",';
		$list[$i].= '"boardtitle":"'.GetString($boardInfo[$data[$i]['bid']]['title'],'ext').'",';
		$list[$i].= '"width":"'.$boardInfo[$data[$i]['bid']]['width'].'",';
		$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
		$list[$i].= '"name":"'.GetString($data[$i]['name'],ext).'",';
		$list[$i].= '"ment":"'.$data[$i]['ment'].'",';
		$list[$i].= '"newment":"'.($data[$i]['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE').'",';
		$list[$i].= '"trackback":"'.$data[$i]['trackback'].'",';
		$list[$i].= '"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'",';
		$list[$i].= '"hit":"'.$data[$i]['hit'].'",';
		$list[$i].= '"vote":"'.$data[$i]['vote'].'",';
		$list[$i].= '"avgvote":"'.($data[$i]['voter'] > 0 ? sprintf('%0.2f',$data[$i]['vote']/$data[$i]['voter']) : '0.001').'",';
		$list[$i].= '"file":"'.$file.'"';
		$list[$i].= '}';
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
			$find.= " `ip` like '%$keyword%'";
		}
	}

	if ($sort == 'idx' && $dir == 'desc') {
		$sort = 'loop';
		$dir = 'asc';
	}
	$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';
	$data = $mDB->DBfetchs($mBoard->table['ment'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mBoard->table['ment'],$find);

	$boardInfo = array();
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$post = $mDB->DBfetch($mBoard->table['post'],array('is_delete'),"where `idx`={$data[$i]['repto']}");

		if (isset($boardInfo[$data[$i]['bid']]) == false) {
			$boardInfo[$data[$i]['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$data[$i]['bid']}'");
		}

		if ($data[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($data[$i]['mno']);
			$data[$i]['name'] = $mData['name'].'('.$mData['nickname'].')';
		}

		$file = array();
		$files = $mDB->DBfetchs($mBoard->table['file'],array('idx','filename','filesize','hit'),"where `type`='MENT' and `repto`={$data[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$file = implode(',',$file);

		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
		$list[$i].= '"postdelete":"'.$post['is_delete'].'",';
		$list[$i].= '"bid":"'.$data[$i]['bid'].'",';
		$list[$i].= '"boardtitle":"'.GetString($boardInfo[$data[$i]['bid']]['title'],'ext').'",';
		$list[$i].= '"width":"'.$boardInfo[$data[$i]['bid']]['width'].'",';
		$list[$i].= '"content":"'.GetString($data[$i]['search'],'ext').'",';
		$list[$i].= '"name":"'.$data[$i]['name'].'",';
		$list[$i].= '"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'",';
		$list[$i].= '"file":"'.$file.'"';
		$list[$i].= '}';
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
			$find.= " `ip` like '%$keyword%'";
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
	$data = $mDB->DBfetchs($mBoard->table['post'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mBoard->table['post'],$find);

	$boardInfo = array();
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		if (isset($boardInfo[$data[$i]['bid']]) == false) {
			$boardInfo[$data[$i]['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$data[$i]['bid']}'");
		}

		if ($data[$i]['mno'] != 0) {
			$mData = $mMember->GetMemberInfo($data[$i]['mno']);
			$data[$i]['name'] = $mData['name'].'('.$mData['nickname'].')';
		}

		$file = array();
		$files = $mDB->DBfetchs($mBoard->table['file'],array('idx','filename','filesize','hit'),"where `type`='POST' and `repto`={$data[$i]['idx']}");
		for ($f=0, $loopf=sizeof($files);$f<$loopf;$f++) {
			$file[] = $files[$f]['idx'].'|'.$files[$f]['filename'].'|'.$files[$f]['filesize'].'|'.$files[$f]['hit'];
		}
		$file = implode(',',$file);

		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"bid":"'.$data[$i]['bid'].'",';
		$list[$i].= '"category":"'.GetString($mBoard->GetCategoryName($data[$i]['category']),'ext').'",';
		$list[$i].= '"boardtitle":"'.GetString($boardInfo[$data[$i]['bid']]['title'],'ext').'",';
		$list[$i].= '"width":"'.$boardInfo[$data[$i]['bid']]['width'].'",';
		$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
		$list[$i].= '"name":"'.GetString($data[$i]['name'],ext).'",';
		$list[$i].= '"ment":"'.$data[$i]['ment'].'",';
		$list[$i].= '"newment":"'.($data[$i]['last_ment'] > GetGMT()-60*60*24 ? 'TRUE' : 'FALSE').'",';
		$list[$i].= '"trackback":"'.$data[$i]['trackback'].'",';
		$list[$i].= '"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'",';
		$list[$i].= '"hit":"'.$data[$i]['hit'].'",';
		$list[$i].= '"vote":"'.$data[$i]['vote'].'",';
		$list[$i].= '"avgvote":"'.($data[$i]['voter'] > 0 ? sprintf('%0.2f',$data[$i]['vote']/$data[$i]['voter']) : '0.001').'",';
		$list[$i].= '"file":"'.$file.'"';
		$list[$i].= '}';
	}
}

if ($action == 'file') {
	$get = Request('get');
	$keyword = Request('keyword');
	if ($get == 'register') $find = "where `repto`!=0";
	else $find = "where `repto`=0";
	$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

	if ($keyword) $find.= " and `filename` like '%$keyword%'";
	$boardInfo = array();
	$data = $mDB->DBfetchs($mBoard->table['file'],'*',$find,$orderer,$limiter);
	$total = $mDB->DBcount($mBoard->table['file'],$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		unset($post);
		if ($data[$i]['repto'] != 0) {
			if ($data[$i]['type'] == 'POST') {
				$post = $mDB->DBfetch($mBoard->table['post'],array('idx','bid','title','mno','name','reg_date'),"where `idx`={$data[$i]['repto']}");
				$title = $post['title'];
				if ($post['mno'] != 0) {
					$mData = $mMember->GetMemberInfo($post['mno']);
					$name = $mData['name'].'('.$mData['nickname'].')';
				} else {
					$name = $post['name'];
				}

				if ($data[$i]['reg_date'] == 0) {
					$mDB->DBupdate($mBoard->table['file'],array('reg_date'=>$post['reg_date']),'',"where `idx`={$data[$i]['idx']}");
					$data[$i]['reg_date'] = $post['reg_date'];
				}
			} else {
				$ment = $mDB->DBfetch($mBoard->table['ment'],array('repto','mno','name','reg_date'),"where `idx`={$data[$i]['repto']}");

				if (isset($ment['repto']) == true) {
					$post = $mDB->DBfetch($mBoard->table['post'],array('idx','bid','title'),"where `idx`={$ment['repto']}");
					$title = $post['title'];
					if ($ment['mno'] != 0) {
						$mData = $mMember->GetMemberInfo($ment['mno']);
						$name = $mData['name'].'('.$mData['nickname'].')';
					} else {
						$name = $ment['name'];
					}

					if ($data[$i]['reg_date'] == 0) {
						$mDB->DBupdate($mBoard->table['file'],array('reg_date'=>$ment['reg_date']),'',"where `idx`={$data[$i]['idx']}");
						$data[$i]['reg_date'] = $ment['reg_date'];
					}
				}
			}

			if (isset($boardInfo[$post['bid']]) == false) {
				$boardInfo[$post['bid']] = $mDB->DBfetch($mBoard->table['setup'],array('title','width'),"where `bid`='{$post['bid']}'");
			}

			$bid = $post['bid'];
			$boardtitle = $boardInfo[$bid]['title'];
			$boardwidth = $boardInfo[$bid]['width'];
			$pid = $post['idx'];
		}


		if (isset($post['idx']) == false) {
			$bid = $boardtitle = $title = $name = $pid = '';
		}


		$list[$i] = '{';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"bid":"'.$bid.'",';
		$list[$i].= '"pid":"'.$pid.'",';
		$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
		$list[$i].= '"width":"'.$boardwidth.'",';
		$list[$i].= '"boardtitle":"'.GetString($boardtitle,'ext').'",';
		$list[$i].= '"filename":"'.GetString($data[$i]['filename'],'ext').'",';
		$list[$i].= '"filesize":"'.$data[$i]['filesize'].'",';
		$list[$i].= '"filepath":"'.$_ENV['path'].$data[$i]['filepath'].'",';
		$list[$i].= '"type":"'.$data[$i]['type'].'",';
		$list[$i].= '"title":"'.GetString($title,'ext').'",';
		$list[$i].= '"name":"'.GetString($name,'ext').'",';
		$list[$i].= '"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'",';
		$list[$i].= '"hit":"'.$data[$i]['hit'].'"';
		$list[$i].= '}';
	}

}

if ($action == 'skin') {
	$skinPath = @opendir($_ENV['path'].'/module/board/templet/board');
	$i = 0;
	while ($skin = @readdir($skinPath)) {
		if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/board/templet/board/'.$skin) == true) {
			$list[$i] = '{"skin":"'.$skin.'"}';
			$i++;
		}
	}
	@closedir($skinPath);
}


$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>