<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mBoard = new ModuleBoard();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($action == 'board') {
	if ($do == 'add' || $do == 'modify') {
		if ($do == 'add') {
			$mBoard = new ModuleBoard();
		} elseif ($do == 'modify') {
			$bid = Request('bid');
			$mBoard = new ModuleBoard($bid);
		}

		if ($mBoard->GetPermission('setup') == false) $errors['message'] = '관리권한이 없습니다.';

		$insert = array();
		$insert['title'] = Request('title');
		$insert['skin'] = Request('skin');
		$insert['width'] = Request('width') && preg_match('/^[^0]+[0-9]+(%)?$/',Request('width')) == true ? Request('width') : $errors['width'] = '게시판가로크기를 정확하게 입력하여 주십시오.';
		
		$view_list = array();
		if (Request('list_loopnum') == 'on') $view_list[] = 'loopnum';
		if (Request('list_name') == 'on') $view_list[] = 'name';
		if (Request('list_reg_date') == 'on') $view_list[] = 'reg_date';
		if (Request('list_hit') == 'on') $view_list[] = 'hit';
		if (Request('list_vote') == 'on') $view_list[] = 'vote';
		if (Request('list_avghit') == 'on') $view_list[] = 'avgvote';
		$insert['view_list'] = implode(',',$view_list);
		$insert['listnum'] = Request('listnum');
		$insert['pagenum'] = Request('pagenum');
		$insert['view_notice_page'] = array_shift(explode(',',Request('view_notice')));
		$insert['view_notice_count'] = array_pop(explode(',',Request('view_notice')));
		$insert['view_alllist'] = Request('view_alllist') == 'on' ? 'TRUE' : 'FALSE';
		$insert['view_prevnext'] = Request('view_prevnext') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_ment'] = Request('use_ment') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_trackback'] = Request('use_trackback') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_category'] = Request('use_category') == 'on' ? 'TRUE' : 'FALSE';
		if ($insert['use_category'] == 'TRUE') $insert['use_category'] = Request('use_category_option') == 'on' ? 'TRUE' : 'OPTION';
		$insert['use_uploader'] = Request('use_uploader') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_charge'] = Request('use_charge') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_select'] = Request('use_select') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_rss'] = Request('use_rss') == 'on' ? 'TRUE' : 'FALSE';
		$insert['rss_config'] = serialize(array('rss_limit'=>Request('rss_limit'),'rss_post_limit'=>Request('rss_post_limit'),'rss_link'=>Request('rss_link'),'rss_description'=>Request('rss_description'),'rss_language'=>Request('rss_language')));
		$insert['post_point'] = Request('post_point');
		$insert['ment_point'] = Request('ment_point');
		$insert['select_point'] = Request('select_point');
		$insert['permission'] = serialize(array('list'=>Request('permission_list'),'view'=>Request('permission_view'),'post'=>Request('permission_post'),'ment'=>Request('permission_ment'),'modify'=>Request('permission_modify'),'delete'=>Request('permission_delete'),'select'=>Request('permission_select'),'secret'=>Request('permission_secret'),'notice'=>Request('permission_notice')));

		if ($do == 'add') {
			$insert['apikey'] = md5(time());
			$insert['bid'] = Request('bid') && preg_match('/^[a-z0-9_]+$/i',Request('bid')) == true ? Request('bid') : $errors['bid'] = '게시판ID를 영문,숫자,_(언더바)를 이용하여 입력하여 주십시오.';

			if ($mDB->DBcount($mBoard->table['setup'],"where `bid`='{$insert['bid']}'") > 0) $errors['bid'] = '이미 사용중인 게시판 ID입니다.';

			if (sizeof($errors) == 0) {
				$mDB->DBinsert($mBoard->table['setup'],$insert);
				$return['success'] = true;
				SaveAdminLog('board','['.$insert['bid'].'] 게시판을 추가하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		} else {
			$bid = Request('bid');

			if (sizeof($errors) == 0) {
				$mDB->DBupdate($mBoard->table['setup'],$insert,'',"where `bid`='$bid'");
				$return['success'] = true;
				SaveAdminLog('board','['.$bid.'] 게시판을 수정하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		}

		exit(json_encode($return));
	}
	
	if ($do == 'modify_all') {
		$bid = explode(',',Request('bid'));

		$insert = array();
		if (Request('is_skin') == 'on') $insert['skin'] = Request('skin');
		if (Request('is_width') == 'on') $insert['width'] = Request('width') && preg_match('/^[^0]+[0-9]+(%)?$/',Request('width')) == true ? Request('width') : $errors['width'] = '게시판가로크기를 정확하게 입력하여 주십시오.';
		
		if (Request('is_view_list') == 'on') {
			$view_list = array();
			if (Request('list_loopnum') == 'on') $view_list[] = 'loopnum';
			if (Request('list_name') == 'on') $view_list[] = 'name';
			if (Request('list_reg_date') == 'on') $view_list[] = 'reg_date';
			if (Request('list_hit') == 'on') $view_list[] = 'hit';
			if (Request('list_vote') == 'on') $view_list[] = 'vote';
			if (Request('list_avghit') == 'on') $view_list[] = 'avgvote';
			$insert['view_list'] = implode(',',$view_list);
		}
		if (Request('is_listnum') == 'on') $insert['listnum'] = Request('listnum');
		if (Request('is_pagenum') == 'on') $insert['pagenum'] = Request('pagenum');
		if (Request('is_view_notice') == 'on') {
			$insert['view_notice_page'] = array_shift(explode(',',Request('view_notice')));
			$insert['view_notice_count'] = array_pop(explode(',',Request('view_notice')));
		}
		if (Request('is_use_ment') == 'on') $insert['use_ment'] = Request('use_ment') == 'on' ? 'TRUE' : 'FALSE';
		if (Request('is_use_trackback') == 'on') $insert['use_trackback'] = Request('use_trackback') == 'on' ? 'TRUE' : 'FALSE';
		if (Request('is_use_category') == 'on') $insert['use_category'] = Request('use_category') == 'on' ? 'TRUE' : 'FALSE';
		if (Request('is_use_category_option') == 'on') $insert['use_category'] = Request('use_category_option') == 'on' ? 'TRUE' : 'OPTION';
		if (Request('is_use_uploader') == 'on') $insert['use_uploader'] = Request('use_uploader') == 'on' ? 'TRUE' : 'FALSE';
		if (Request('is_use_charge') == 'on') $insert['use_charge'] = Request('use_charge') == 'on' ? 'TRUE' : 'FALSE';
		if (Request('is_use_select') == 'on') $insert['use_select'] = Request('use_select') == 'on' ? 'TRUE' : 'FALSE';
		
		if (Request('is_post_point') == 'on') $insert['post_point'] = Request('post_point');
		if (Request('is_ment_point') == 'on') $insert['ment_point'] = Request('ment_point');
		if (Request('is_select_point') == 'on') $insert['select_point'] = Request('select_point');

		if (sizeof($errors) == 0) {
			for ($i=0, $loop=sizeof($bid);$i<$loop;$i++) {
				$board = $mDB->DBfetch($mBoard->table['setup'],array('permission'),"where `bid`='{$bid[$i]}'");
				
				if ($board['permission'] && is_array(unserialize($board['permission'])) == true) {
					$insert['permission'] = unserialize($board['permission']);
				} else {
					$insert['permission'] = array('list'=>'true','post'=>'true','view'=>'true','ment'=>'true','select'=>'{$member.type} == \'ADMINISTRATOR\'','secret'=>'{$member.type} == \'ADMINISTRATOR\'','modify'=>'{$member.type} == \'ADMINISTRATOR\'','delete'=>'{$member.type} == \'ADMINISTRATOR\'','notice'=>'{$member.type} == \'ADMINISTRATOR\'');
				}
				foreach ($insert['permission'] as $key=>$value) {
					if (Request('is_permission_'.$key) == 'on') $insert['permission'][$key] = Request('permission_'.$key);
				}
				$insert['permission'] = serialize($insert['permission']);
				
				$mDB->DBupdate($mBoard->table['setup'],$insert,'',"where `bid`='{$bid[$i]}'");
			}
			
			$return['success'] = true;
			SaveAdminLog('board','['.$bid.'] 게시판을 수정하였습니다.');
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$bid = explode(',',Request('bid'));

		for ($i=0, $loop=sizeof($bid);$i<$loop;$i++) {
			$mDB->DBdelete($mBoard->table['setup'],"where `bid`='{$bid[$i]}'");
			$post = $mDB->DBfetchs($mBoard->table['post'],array('idx'),"where `bid`='{$bid[$i]}'");
			for ($j=0, $loopj=sizeof($post);$j<$loopj;$j++) {
				$mDB->DBdelete($mBoard->table['post'],"where `idx`={$post[$j]['idx']}");

				$file = $mDB->DBfetchs($mBoard->table['file'],array('idx','filetype','filepath'),"where `type`='POST' and `repto`={$post[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) {
					$mDB->DBdelete($mBoard->table['file'],"where `idx`={$file[$k]['idx']}");
					@unlink($_ENV['userfilePath'].$mBoard->userfile.$file[$k]['filepath']);
					if ($file[$k]['filetype'] == 'IMG') {
						@unlink($_ENV['userfilePath'].$mBoard->thumbnail.'/'.$file[$k]['idx'].'.thm');
					}
				}
				$mDB->DBdelete($mBoard->table['log'],"where `repto`={$post[$j]['idx']}");
			}

			$ment = $mDB->DBfetchs($mBoard->table['ment'],array('idx'),"where `bid`='{$bid[$i]}'");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$mDB->DBdelete($mBoard->table['ment'],"where `idx`={$ment[$j]['idx']}");

				$file = $mDB->DBfetchs($mBoard->table['file'],array('idx','filepath'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) {
					$mDB->DBdelete($mBoard->table['file'],"where `idx`={$file[$k]['idx']}");
					@unlink($_ENV['path'].$file[$k]['filepath']);
				}
			}

			$mDB->DBdelete($mBoard->table['category'],"where `bid`='{$bid[$i]}'");
			$mDB->DBdelete($mBoard->table['log'],"where `bid`='{$bid[$i]}'");
			
			RemoveDirectory($_ENV['userfilePath'].$mBoard->skinThumbnail.'/'.$bid[$i]);

			SaveAdminLog('board','['.$bid[$i].'] 게시판을 삭제하였습니다.');
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'category') {
	if ($do == 'add') {
		$errors = array();
		$bid = Request('bid');
		$category = Request('category');
		$permission = Request('permission');

		if ($mDB->DBcount($mBoard->table['category'],"where `bid`='$bid' and `category`='$category'") > 0) {
			$errors['category'] = '이미 등록된 카테고리명입니다.';
		}

		if (sizeof($errors) == 0) {
			$sort = $mDB->DBcount($mBoard->table['category'],"where `bid`='$bid'");
			$idx = $mDB->DBinsert($mBoard->table['category'],array('bid'=>$bid,'category'=>$category,'permission'=>$permission,'sort'=>$sort));
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['category'] = $category;
			$return['permission'] = $permission;
			$return['sort'] = $sort;
			
			SaveAdminLog('board','['.$bid.'] 게시판에 ['.$category.'] 카테고리를 추가하였습니다.');
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}

		exit(json_encode($return));
	}

	if ($do == 'modify') {
		$data = json_decode(Request('data'),true);
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mBoard->table['category'],array('category'=>$data[$i]['category'],'permission'=>$data[$i]['permission']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'sort') {
		$data = json_decode(Request('data'),true);
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mBoard->table['category'],array('sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = Request('idx');
		$bid = Request('bid');
		$post = Request('post');
		
		if ($post == 'move') {
			$move = Request('move');
			if (in_array($move,explode(',',$idx)) == true) {
				$errors['message'] = '삭제할 카테고리에 게시물이 옮겨질 카테고리도 포함되어 있습니다.';
			}
		}
		
		if (sizeof($errors) == 0) {
			$mDB->DBdelete($mBoard->table['category'],"where `idx` IN ($idx) and `bid`='$bid'");
		
			if ($post == 'reset') {
				$mDB->DBupdate($mBoard->table['post'],array('category'=>'0'),'',"where `category` IN ($idx)");
			} elseif ($post == 'move') {
				$mDB->DBupdate($mBoard->table['post'],array('category'=>$move),'',"where `category` IN ($idx)");
			} elseif ($post == 'delete') {
				$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE','category'=>'0'),'',"where `category` IN ($idx)");
			}
			
			$data = $mDB->DBfetchs($mBoard->table['category'],array('idx'),"where `bid`='$bid'",'sort,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBupdate($mBoard->table['category'],array('sort'=>$i),'',"where `idx`='{$data[$i]['idx']}'");
			}
			SaveAdminLog('board','['.$bid.'] 게시판의 카테고리를 삭제하였습니다.');
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'post') {
	if ($do == 'delete') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mBoard->table['post'],array('idx','bid','is_delete','mno','title'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['is_delete'] == 'FALSE' && $data[$i]['mno'] != '0') {
				$check = $mDB->DBfetch($mBoard->table['setup'],array('post_point'),"where `bid`='{$data[$i]['bid']}'");
				$mMember->SendPoint($data[$i]['mno'],$check['post_point']*-1,$msg='관리자에 의한 게시물 삭제 ('.GetCutString($data[$i]['title'],20).')','/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['idx'],'board',true);

				$message = array('module'=>'board','mno'=>$member['idx'],'type'=>'delete','parent'=>$data[$i]['title'],'message'=>'관리자에 의해 게시물이 삭제됨에 따라  '.number_format($check['post_point']).'포인트가 차감되었습니다.');
				$mMember->SendMessage($data[$i]['mno'],$message,'/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['idx'],-1);
			}

			$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE'),'',"where `idx`={$data[$i]['idx']}");

			SaveAdminLog('board','['.$data[$i]['title'].'] 게시물을 삭제하였습니다.','/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['idx']);
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'spam') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mBoard->table['post'],array('idx','bid','title','ip'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($mDB->DBcount($_ENV['table']['ipban'],"where `ip`='{$data[$i]['ip']}'") == 0) {
				$mDB->DBinsert($_ENV['table']['ipban'],array('ip'=>$data[$i]['ip'],'memo'=>'스팸게시물 등록으로 인한 아이피차단','reg_date'=>GetGMT()));
			}

			SaveAdminLog('board','['.$data[$i]['title'].'] 게시물을 스팸차단하였습니다.','/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['idx']);
		}

		$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE'),'',"where `idx` IN ($idx)");
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'move') {
		$bid = Request('bid');
		$category = Request('category');
		$idx = Request('idx');

		$data = $mDB->DBfetchs($mBoard->table['post'],array('idx','is_delete','mno','title'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['is_delete'] == 'FALSE' && $data[$i]['mno'] != '0') {
				$check = $mDB->DBfetch($mBoard->table['setup'],array('title'),"where `bid`='$bid'");
				$message = array('module'=>'board','mno'=>$member['idx'],'type'=>'move','parent'=>$data[$i]['title'],'message'=>'관리자에 의해 게시물이 '.$check['title'].'게시판으로 이동되었습니다.');
				$mMember->SendMessage($data[$i]['mno'],$message,'/module/board/board.php?bid='.$bid.'&mode=view&idx='.$data[$i]['idx'],-1);

				SaveAdminLog('board','['.$data[$i]['title'].'] 게시물을 '.$bid.'게시판으로 이동하였습니다.','/module/board/board.php?bid='.$bid.'&mode=view&idx='.$data[$i]['idx']);
			}

			$mDB->DBupdate($mBoard->table['post'],array('bid'=>$bid,'category'=>$category),'',"where `idx`={$data[$i]['idx']}");
			$mDB->DBupdate($mBoard->table['ment'],array('bid'=>$bid),'',"where `repto`={$data[$i]['idx']}");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'ment') {
	if ($do == 'delete') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mBoard->table['ment'],array('idx','bid','mno','password','parent','repto','search','is_select'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$post = $mDB->DBfetch($mBoard->table['post'],array('bid'),"where `idx`='{$data[$i]['repto']}'");
			$mBoard = new ModuleBoard($post['bid']);

			if ($mBoard->GetChildMent($data[$i]['idx']) == true) {
				$mDB->DBupdate($mBoard->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`={$data[$i]['idx']}");
			} else {
				$mDB->DBdelete($mBoard->table['ment'],"where `idx`={$data[$i]['idx']}");
			}

			$mBoard->CheckParentMent($data[$i]['parent']);

			$last_ment = $mDB->DBfetch($mBoard->table['ment'],array('reg_date'),"where `repto`={$data[$i]['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
			$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

			$mDB->DBupdate($mBoard->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data[$i]['repto']}'");

			$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='MENT' and `repto`={$data[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
				$mBoard->FileDelete($file[$j]['idx']);
			}

			if ($data[$i]['mno'] != '0') {
				$check = $mDB->DBfetch($mBoard->table['setup'],array('ment_point'),"where `bid`='{$data[$i]['bid']}'");
				$mMember->SendPoint($data[$i]['mno'],$check['ment_point']*-1,$msg='관리자에 의한 댓글 삭제 ('.GetCutString($data[$i]['search'],20).')','/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['repto'],'board',true);

				$message = array('module'=>'board','mno'=>$member['idx'],'type'=>'delete','parent'=>$data[$i]['search'],'message'=>'관리자에 의해 댓글이 삭제됨에 따라  '.number_format($check['ment_point']).'포인트가 차감되었습니다.');
				$mMember->SendMessage($data[$i]['mno'],$message,'/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['repto'],-1);
			}
			
			if ($data[$i]['is_select'] == 'TRUE') $mDB->DBupdate($mBoard->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data[$i]['repto']}'");

			SaveAdminLog('board','['.GetCutString($data[$i]['search'],20).'] 댓글을 삭제하였습니다.');
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'spam') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mBoard->table['ment'],array('idx','bid','mno','password','parent','repto','search','is_select','ip'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$post = $mDB->DBfetch($mBoard->table['post'],array('bid'),"where `idx`='{$data[$i]['repto']}'");
			$mBoard = new ModuleBoard($post['bid']);

			if ($mBoard->GetChildMent($data[$i]['idx']) == true) {
				$mDB->DBupdate($mBoard->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`={$data[$i]['idx']}");
			} else {
				$mDB->DBdelete($mBoard->table['ment'],"where `idx`={$data[$i]['idx']}");
			}

			$mBoard->CheckParentMent($data[$i]['parent']);

			$last_ment = $mDB->DBfetch($mBoard->table['ment'],array('reg_date'),"where `repto`={$data[$i]['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
			$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

			$mDB->DBupdate($mBoard->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data[$i]['repto']}'");

			if ($mDB->DBcount($_ENV['table']['ipban'],"where `ip`='{$data[$i]['ip']}'") == 0) {
				$mDB->DBinsert($_ENV['table']['ipban'],array('ip'=>$data[$i]['ip'],'memo'=>'스팸게시물 등록으로 인한 아이피차단','reg_date'=>GetGMT()));
			}
			
			if ($data[$i]['is_select'] == 'TRUE') $mDB->DBupdate($mBoard->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data[$i]['repto']}'");

			SaveAdminLog('board','['.GetCutString($data[$i]['search'],20).'] 댓글을 스팸차단하였습니다.');
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'file') {
	if ($do == 'retrench') {
		$mFlush = new Flush();
		$dirs = scandir($_ENV['userfilePath'].$mBoard->userfile.'/attach',0);
		
		for ($i=0, $loop=sizeof($dirs);$i<$loop;$i++) {
			$dirname = $dirs[$i];

			if ($dirname != '.' && $dirname != '..' && is_dir($_ENV['userfilePath'].$mBoard->userfile.'/attach/'.$dirname) == true) {
				$files = scandir($_ENV['userfilePath'].$mBoard->userfile.'/attach/'.$dirname,0);
			
				$totalFile = sizeof($files);
				$deleteFile = 0;
			
				for ($j=0;$j<$totalFile;$j++) {
					if (is_dir($_ENV['userfilePath'].$mBoard->userfile.'/attach/'.$dirname.'/'.$files[$j]) == false) {
						if ($mDB->DBcount($mBoard->table['file'],"where `filepath`='/attach/{$dirname}/{$files[$j]}'") == 0) {
							$deleteFile++;
							//@unlink($_ENV['userfilePath'].$mBoard->userfile.'/attach/'.$dirname.'/'.$files[$j]) or $deleteFile--;
							
							echo '<script type="text/javascript">top.RetrenchProgressControl("'.$dirname.'",'.($i+1).','.$loop.','.$j.','.$totalFile.','.$deleteFile.');</script>';
							$mFlush->flush();
						}
					}
					
					if ($j%10 == 0) {
						echo '<script type="text/javascript">top.RetrenchProgressControl("'.$dirname.'",'.($i+1).','.$loop.','.$j.','.$totalFile.','.$deleteFile.');</script>';
						$mFlush->flush();
					}
				}
			}
			
			echo '<script type="text/javascript">top.RetrenchProgressControl("'.$dirname.'",'.($i+1).','.$loop.','.$loop.','.$totalFile.','.$deleteFile.');</script>';
			$mFlush->flush();
			sleep(1);
		}
		
		echo '<script type="text/javascript">top.RetrenchProgressControl("",'.$loop.','.$loop.',0,0,0);</script>';
		$mFlush->flush();
	}
	
	if ($do == 'norepto') {
		$mFlush = new Flush();
		
		$totalFile = $mDB->DBcount($mBoard->table['file'],"where `repto`!=0");
		$data = $mDB->DBfetchs($mBoard->table['file'],array('idx','type','repto'),"where `repto`>0");
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['type'] == 'POST') {
				if ($mDB->DBcount($mBoard->table['post'],"where `idx`={$data[$i]['repto']}") == 0) {
					//$mBoard->FileDelete($data[$i]['idx']);
					$deleteFile++;
				}
			} else {
				if ($mDB->DBcount($mBoard->table['ment'],"where `idx`={$data[$i]['repto']}") == 0) {
					//$mBoard->FileDelete($data[$i]['idx']);
					$deleteFile++;
				}
			}
			
			if ($i%50 == 0) {
				echo '<script type="text/javascript">top.NoReptoProgressControl('.$i.','.$totalFile.','.$deleteFile.');</script>';
				$mFlush->flush();
			}
		}
		
		echo '<script type="text/javascript">top.NoReptoProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		$mFlush->flush();
	}

	if ($do == 'removetemp') {
		$mFlush = new Flush();
		
		$totalFile = $mDB->DBcount($mBoard->table['file'],"where `repto`=0");
		$data = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `repto`=0");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$deleteFile++;
			//$mBoard->FileDelete($data[$i]['idx']);
			
			if ($i%50 == 0) {
				echo '<script type="text/javascript">top.TempProgressControl('.$i.','.$totalFile.','.$deleteFile.');</script>';
				$mFlush->flush();
			}
		}

		echo '<script type="text/javascript">top.TempProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		$mFlush->flush();
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mBoard->FileDelete($idx[$i]);
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'reptodelete') {
		$idx = Request('idx');
		
		$data = $mDB->DBfetchs($mBoard->table['file'],'*',"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['type'] == 'POST') {
				$post = $mDB->DBfetch($mBoard->table['post'],'*',"where `idx`='{$data[$i]['repto']}'");
				
				if (isset($post['idx']) == true && $post['is_delete'] == 'FALSE') {
					$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE'),'',"where `idx`='{$data[$i]['repto']}'");
				
					if ($post['mno'] != '0') {
						$check = $mDB->DBfetch($mBoard->table['setup'],array('post_point'),"where `bid`='{$post['bid']}'");
						$mMember->SendPoint($post['mno'],$check['post_point']*-1,$msg='관리자에 의한 게시물 삭제 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$post['idx'],'board',true);
	
						$message = array('module'=>'board','mno'=>$member['idx'],'type'=>'delete','parent'=>$post['title'],'message'=>'관리자에 의해 댓글이 삭제됨에 따라  '.number_format($check['post_point']).'포인트가 차감되었습니다.');
						$mMember->SendMessage($ment['mno'],$message,'/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$post['idx'],-1);
					}
				}
			} else {
				$ment = $mDB->DBfetch($mBoard->table['ment'],'*',"where `idx`='{$data[$i]['repto']}'");
				
				if (isset($ment['idx']) == true && $ment['is_delete'] == 'FALSE') {
					if ($mBoard->GetChildMent($ment['idx']) == true) {
						$mDB->DBupdate($mBoard->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`={$ment['idx']}");
					} else {
						$mDB->DBdelete($mBoard->table['ment'],"where `idx`={$ment['idx']}");
					}
		
					$mBoard->CheckParentMent($ment['parent']);
		
					$last_ment = $mDB->DBfetch($mBoard->table['ment'],array('reg_date'),"where `repto`={$ment['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
					$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;
		
					$mDB->DBupdate($mBoard->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$ment['repto']}'");
		
					$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment['idx']}");
					for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
						$mBoard->FileDelete($file[$j]['idx']);
					}
	
					if ($ment['mno'] != '0') {
						$check = $mDB->DBfetch($mBoard->table['setup'],array('ment_point'),"where `bid`='{$ment['bid']}'");
						$mMember->SendPoint($ment['mno'],$check['ment_point']*-1,$msg='관리자에 의한 댓글 삭제 ('.GetCutString($ment['search'],20).')','/module/board/board.php?bid='.$ment['bid'].'&mode=view&idx='.$ment['repto'],'board',true);
		
						$message = array('module'=>'board','mno'=>$member['idx'],'type'=>'delete','parent'=>$ment['search'],'message'=>'관리자에 의해 댓글이 삭제됨에 따라  '.number_format($check['ment_point']).'포인트가 차감되었습니다.');
						$mMember->SendMessage($ment['mno'],$message,'/module/board/board.php?bid='.$ment['bid'].'&mode=view&idx='.$ment['repto'],-1);
					}
					
					if ($ment['is_select'] == 'TRUE') $mDB->DBupdate($mBoard->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$ment['repto']}'");
				}
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'trash') {
	if ($do == 'recover') {
		$idx = Request('idx');
		$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'FALSE'),'',"where `idx` IN ($idx)");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = Request('idx');
		
		$post = $mDB->DBfetchs($mBoard->table['post'],array('idx'),"where `idx` IN ($idx) and `is_delete`='TRUE'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$ment = $mDB->DBfetchs($mBoard->table['ment'],array('idx'),"where `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) $mBoard->FileDelete($file[$k]['idx']);
			}
			$mDB->DBdelete($mBoard->table['ment'],"where `repto`={$post[$i]['idx']}");
			$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='POST' and `repto`={$data[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) $mBoard->FileDelete($file[$j]['idx']);
			$mDB->DBdelete($mBoard->table['post'],"where `idx`='{$post[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'empty') {
		$post = $mDB->DBfetchs($mBoard->table['post'],array('idx'),"where `is_delete`='TRUE'");
		for ($i=0, $loop=sizeof($post);$i<$loop;$i++) {
			$ment = $mDB->DBfetchs($mBoard->table['ment'],array('idx'),"where `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) $mBoard->FileDelete($file[$k]['idx']);
			}
			$mDB->DBdelete($mBoard->table['ment'],"where `repto`={$post[$i]['idx']}");
			$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='POST' and `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) $mBoard->FileDelete($file[$j]['idx']);
		}
		$mDB->DBdelete($mBoard->table['post'],"where `is_delete`='TRUE'");
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'log') {
	if ($do == 'delete') {
		$time = GetGMT() - 60*60*24*31;
		$mDB->DBdelete($mBoard->table['log'],"where `reg_date`<$time");
		$min = $mDB->DBfetch($mBoard->table['log'],array('MIN(idx)'));
		$min = $min[0];
		
		$mDB->DBupdate($mBoard->table['log'],'',array('idx'=>'`idx`-'.($min+1)));
		$mDB->DBreset($mBoard->table['log']);
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}
?>