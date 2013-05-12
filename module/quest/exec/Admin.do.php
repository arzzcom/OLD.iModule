<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mBoard = new ModuleBoard();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

if ($action == 'board') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add' || $do == 'modify') {
		$bid = Request('bid');
		$Error = array();

		if ($do == 'add') {
			$do = 'add';
			$mBoard = new ModuleBoard();
		} elseif ($do == 'modify') {
			$mBoard = new ModuleBoard($bid);
		}

		if ($mBoard->GetPermission('setup') == false) $Error['title'] = '관리권한이 없습니다.';

		$insert = array();
		$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '게시판 제목을 입력하여 주십시오.';
		$insert['skin'] = Request('skin') ? Request('skin') : $Error['skin'] = '게시판 스킨을 입력하여 주십시오.';
		$insert['width'] = Request('width') && ereg("^[^0]+[0-9]+(%)?$",Request('width')) == true ? Request('width') : $Error['width'] = '게시판가로크기를 정확하게 입력하여 주십시오.';
		$view_list = array();
		if (Request('list_loopnum') == 'on') $view_list[] = 'loopnum';
		if (Request('list_name') == 'on') $view_list[] = 'name';
		if (Request('list_reg_date') == 'on') $view_list[] = 'reg_date';
		if (Request('list_hit') == 'on') $view_list[] = 'hit';
		if (Request('list_vote') == 'on') $view_list[] = 'vote';
		if (Request('list_avghit') == 'on') $view_list[] = 'avgvote';
		$insert['view_list'] = implode(',',$view_list);
		$insert['listnum'] = Request('listnum') && ereg("[0-9]+",Request('listnum')) == true ? Request('listnum') : $Error['listnum'] = '목록수를 정확하게 입력하여 주십시오.';
		$insert['pagenum'] = Request('pagenum') && ereg("[0-9]+",Request('pagenum')) == true ? Request('pagenum') : $Error['pagenum'] = '페이지수를 정확하게 입력하여 주십시오.';
		$insert['view_notice_page'] = array_shift(explode(',',Request('view_notice')));
		$insert['view_notice_count'] = array_pop(explode(',',Request('view_notice')));
		$insert['use_ment'] = Request('use_ment') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_trackback'] = Request('use_trackback') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_category'] = Request('use_category') == 'on' ? 'TRUE' : 'FALSE';
		if ($insert['use_category'] == 'TRUE') $insert['use_category'] = Request('use_category_option') == 'on' ? 'TRUE' : 'OPTION';
		$insert['use_uploader'] = Request('use_uploader') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_charge'] = Request('use_charge') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_select'] = Request('use_select') == 'on' ? 'TRUE' : 'FALSE';
		$insert['use_rss'] = Request('use_rss') == 'on' ? 'TRUE' : 'FALSE';
		$insert['rss_config'] = GetSerialize(array('rss_limit'=>Request('rss_limit'),'rss_post_limit'=>Request('rss_post_limit'),'rss_link'=>Request('rss_link'),'rss_description'=>Request('rss_description'),'rss_language'=>Request('rss_language')));
		$insert['permission'] = GetSerialize(array('list'=>Request('permission_list'),'view'=>Request('permission_view'),'post'=>Request('permission_post'),'ment'=>Request('permission_ment'),'modify'=>Request('permission_modify'),'delete'=>Request('permission_delete'),'select'=>Request('permission_select'),'secret'=>Request('permission_secret'),'notice'=>Request('permission_notice')));

		if ($do == 'add') {
			$insert['apikey'] = md5(time());
			$insert['bid'] = Request('bid') && eregi("^[a-z0-9_]+$",Request('bid')) == true ? Request('bid') : $Error['bid'] = '게시판ID를 영문,숫자,_(언더바)를 이용하여 입력하여 주십시오.';

			if ($mDB->DBcount($mBoard->table['setup'],"where `bid`='{$insert['bid']}'") > 0) $Error['bid'] = '이미 사용중인 게시판 ID입니다.';

			if (sizeof($Error) == 0) $mDB->DBinsert($mBoard->table['setup'],$insert);
			SaveAdminLog('board','['.$insert['bid'].'] 게시판을 추가하였습니다.');
		} else {
			$bid = split(',',Request('bid'));
			for ($i=0, $loop=sizeof($bid);$i<$loop;$i++) {
				$bid[$i] = "'".$bid[$i]."'";
			}
			$bid = implode(',',$bid);

			if (sizeof($Error) == 0) $mDB->DBupdate($mBoard->table['setup'],$insert,'',"where `bid` IN ($bid)");
			SaveAdminLog('board','['.$insert['bid'].'] 게시판을 수정하였습니다.');
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}

	if ($do == 'delete') {
		$bid = explode(',',Request('bid'));

		for ($i=0, $loop=sizeof($bid);$i<$loop;$i++) {
			$mDB->DBdelete($mBoard->table['setup'],"where `bid`='{$bid[$i]}'");
			$post = $mDB->DBfetchs($mBoard->table['post'],array('idx'),"where `bid`='{$bid[$i]}'");
			for ($j=0, $loopj=sizeof($post);$j<$loopj;$j++) {
				$mDB->DBdelete($mBoard->table['post'],"where `idx`={$post[$j]['idx']}");

				$file = $mDB->DBfetchs($mBoard->table['file'],array('idx','filepath'),"where `type`='POST' and `repto`={$post[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) {
					$mDB->DBdelete($mBoard->table['file'],"where `idx`={$file[$k]['idx']}");
					@unlink($_ENV['path'].$file[$k]['filepath']);
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

			SaveAdminLog('board','['.$bid[$i].'] 게시판을 삭제하였습니다.');
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

if ($action == 'category') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add') {
		$Error = array();
		$bid = Request('bid');
		$title = Request('title') ? Request('title') : $Error['title'] = '카테고리명을 입력하여 주십시오.';
		$permission = Request('permission');

		if (sizeof($Error) == 0) {
			if ($do == 'add') {
				$order = $mDB->DBfetch($mBoard->table['category'],array('MAX(order)'),"where `bid`='$bid'");
				$order = isset($order[0]) == true ? $order[0]+1 : 1;
				$mDB->DBinsert($mBoard->table['category'],array('bid'=>$bid,'category'=>$title,'permission'=>$permission,'order'=>$order));
			}
		}

		SaveAdminLog('board','['.$bid.'] 게시판에 ['.$title.'] 카테고리를 추가하였습니다.');

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}

	if ($do == 'modify') {
		$bid = Request('bid');
		$data = GetExtData('data');

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mBoard->table['category'],array('category'=>$data[$i]['title'],'permission'=>$data[$i]['permission'],'order'=>$i),'',"where `idx`={$data[$i]['category']}");
		}

		SaveAdminLog('board','['.$bid.'] 게시판의 카테고리를 수정하였습니다.');

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'delete') {
		$post = Request('post');
		$idx = Request('idx');
		$bid = Request('bid');

		$mDB->DBdelete($mBoard->table['category'],"where `bid`='$bid' and `idx` IN ($idx)");

		if ($post == 'reset') {
			$mDB->DBupdate($mBoard->table['post'],array('category'=>'0'),"where `bid`='$bid' and `category` IN ($idx)");
		} elseif ($post == 'delete') {
			$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE'),"where `bid`='$bid' and `category` IN ($idx)");
		}

		SaveAdminLog('board','['.$bid.'] 게시판의 카테고리를 삭제하였습니다.');

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '<errors>';
		echo '<field><id>'.$idx.'</id></field>';
		echo '</errors>';
		echo '</message>';
	}
}

if ($action == 'post') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

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
	}

	if ($do == 'spam') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mBoard->table['post'],array('idx','title','ip'),"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($mDB->DBcount($_ENV['table']['ipban'],"where `ip`='{$data[$i]['ip']}'") == 0) {
				$mDB->DBinsert($_ENV['table']['ipban'],array('ip'=>$data[$i]['ip'],'memo'=>'스팸게시물 등록으로 인한 아이피차단','reg_date'=>GetGMT()));
			}

			SaveAdminLog('board','['.$data[$i]['title'].'] 게시물을 스팸차단하였습니다.','/module/board/board.php?bid='.$data[$i]['bid'].'&mode=view&idx='.$data[$i]['idx']);
		}

		$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE'),'',"where `idx` IN ($idx)");
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
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}

if ($action == 'ment') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

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
	}

	if ($do == 'spam') {
		$idx = Request('idx');
		$data = $mDB->DBfetchs($mBoard->table['ment'],array('idx','bid','mno','password','parent','repto','search','ip'),"where `idx` IN ($idx)");
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
	}


	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}

if ($action == 'file') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'retrench') {
		$data = $mDB->DBfetchs($mBoard->table['file'],array('idx','type','repto'),"where `repto`!=0");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['type'] == 'POST') {
				if ($mDB->DBcount($mBoard->table['post'],"where `idx`={$data[$i]['repto']}") == 0) $mBoard->FileDelete($data[$i]['idx']);
			} else {
				if ($mDB->DBcount($mBoard->table['ment'],"where `idx`={$data[$i]['repto']}") == 0) $mBoard->FileDelete($data[$i]['idx']);
			}
		}

		$fileDirPath = @opendir($_ENV['path'].'/userfile/board');
		while ($fileDir = @readdir($fileDirPath)) {
			if ($fileDir != 'thumbneil' && $fileDir != '.' && $fileDir != '..' && is_dir($_ENV['path'].'/userfile/board/'.$fileDir) == true) {
				$filePath = @opendir($_ENV['path'].'/userfile/board/'.$fileDir);
				while ($file = @readdir($filePath)) {
					if ($file != '.' && $file != '..') {
						if ($mDB->DBcount($mBoard->table['file'],"where `filepath`='/userfile/board/{$fileDir}/{$file}'") == 0) {
							@unlink($_ENV['path'].'/userfile/board/'.$fileDir.'/'.$file);
						}
					}
				}
				@closedir($filePath);
			}
		}
		@closedir($fileDirPath);
	}

	if ($do == 'removetemp') {
		$data = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `repto`=0");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mBoard->FileDelete($data[$i]['idx']);
		}
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mBoard->FileDelete($idx[$i]);
		}
	}

	if ($do == 'totalsize') {
		$data = $mDB->DBfetch($mBoard->table['file'],array('SUM(filesize)'));
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true"><totalsize>'.$data[0].'</totalsize>';
		echo '</message>';
		exit;
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}

if ($action == 'trash') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'recover') {
		$idx = Request('idx');
		$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'FALSE'),'',"where `idx` IN ($idx)");
	}

	if ($do == 'empty') {
		$post = $mDB->DBfetchs($mBoard->table['post'],array('idx'),"where `is_delete`='TRUE'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$ment = $mDB->DBfetchs($mBoard->table['ment'],array('idx'),"where `repto`={$post[$i]['idx']}");
			for ($j=0, $loopj=sizeof($ment);$j<$loopj;$j++) {
				$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='MENT' and `repto`={$ment[$j]['idx']}");
				for ($k=0, $loopk=sizeof($file);$k<$loopk;$k++) $mBoard->FileDelete($file[$k]['idx']);
			}
			$mDB->DBdelete($mBoard->table['ment'],"where `repto`={$post[$i]['idx']}");
			$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='POST' and `repto`={$data[$i]['idx']}");
			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) $mBoard->FileDelete($file[$j]['idx']);
		}
		$mDB->DBdelete($mBoard->table['post'],"where `is_delete`='TRUE'");
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}
?>