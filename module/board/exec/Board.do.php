<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$bid = Request('bid');
$action = Request('action');
$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mBoard = new ModuleBoard($bid);

$checkIP = $mBoard->mIPBan->CheckIP();
if ($checkIP['result'] == true) {
	Alertbox('해당 아이피 '.$checkIP['ip'].'는 차단된 아이피입니다.\n\n차단일시 : '.GetTime('Y년 m월 d일').'\n차단사유 : '.$checkIP['memo']);
}

// 글 관련
if ($action == 'post') {
	$mode = Request('mode');

	if ($mMember->IsLogged() == false) {
		$mAntiSpam = new AntiSpam();
		if ($mAntiSpam->CheckAntiSpam(Request('antispam')) == false) {
			Alertbox('스팸방지코드를 바르게 입력하여 주십시오.');
		}
	}

	$insert = array();
	$insert['bid'] = Request('bid');
	$insert['image'] = Request('image');
	$insert['category'] = Request('category') ? Request('category') : 0;
	$insert['title'] = Request('title') ? Request('title') : Alertbox('제목을 입력하여 주십시오.');
	$insert['content'] = Request('content') ? $mBoard->SetContent(Request('content')) : Alertbox('내용을 입력하여 주십시오.');
	$insert['search'] = GetIndexingText(Request('content'));
	$insert['is_notice'] = $mBoard->GetPermission('notice') == true && Request('is_notice') ? 'TRUE' : 'FALSE';
	$insert['is_html_title'] = $mBoard->GetPermission('notice') == true && Request('is_html_title') ? 'TRUE' : 'FALSE';
	$insert['is_secret'] = Request('is_secret') ? 'TRUE' : 'FALSE';
	$insert['is_ment'] = Request('is_ment') ? 'TRUE' : 'FALSE';
	$insert['is_trackback'] = Request('is_trackback') ? 'TRUE' : 'FALSE';
	$insert['is_msg'] = $mMember->IsLogged() == true && Request('is_msg') ? 'TRUE' : 'FALSE';
	$insert['is_email'] = $insert['email'] && Request('is_email') ? 'TRUE' : 'FALSE';
	$insert['field1'] = Request('field1');
	$insert['field2'] = Request('field2');
	$insert['field3'] = Request('field3');
	
	if (Request('is_mobile') == 'TRUE') $insert['is_mobile'] = 'TRUE';

	if ($mBoard->setup['use_category'] == 'TRUE' && $insert['category'] == 0) Alertbox('이 게시판은 카테고리를 반드시 선택하도록 설정되어 있습니다.');

	$extraValue = array();
	foreach ($_REQUEST as $extra=>$value) {
		if (preg_match('/^extra_/',$extra) == true) {
			$extraValue[preg_replace('/^extra_/','',$extra)] = Request($extra);
		}
	}
	$insert['extra_content'] = sizeof($extraValue) > 0 ? serialize($extraValue) : '';

	if ($mode == 'post') {
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('암호를 입력하여 주십시오.');
			$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : '';
			$insert['homepage'] = Request('homepage') ? (preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage')) : '';
			setcookie('iModuleBoardName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardEmail',$insert['email'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardHomepage',$insert['homepage'],time()+60*60*24*365,'/');
		} else {
			$insert['name'] = $insert['email'] = $insert['homepage'] = '';
			$insert['mno'] = $member['idx'];
		}
		$insert['reg_date'] = GetGMT();
		$idx = $mDB->DBinsert($mBoard->table['post'],$insert);
		$mDB->DBupdate($mBoard->table['post'],array('loop'=>$idx*-1),'',"where `idx`='$idx'");
		$mDB->DBupdate($mBoard->table['setup'],array('post_time'=>GetGMT()),array('post'=>'`post`+1'),"where `bid`='{$insert['bid']}'");
		if ($insert['category'] != 0) {
			$mDB->DBupdate($mBoard->table['category'],array('post_time'=>GetGMT()),array('post'=>'`post`+1'),"where `idx`='{$insert['category']}'");
		}

		$resultMsg = '게시물을 성공적으로 등록하였습니다.';

		$autosaveFind = "where `bid`='{$bid}'";
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
	} else {
		$idx = Request('idx');
		$post = $mDB->DBfetch($mBoard->table['post'],array('mno','category','password','last_modify_hit'),"where `idx`='$idx'");

		if ($mBoard->GetPermission('modify') == false) {
			if ($post['mno'] == '0') {
				$check_password = ArzzDecoder(Request('check_password'));
				if ($check_password == null) {
					Alertbox('게시물의 패스워드를 입력하여 주십시오.');
				} else {
					if (md5($check_password) != $post['password']) {
						Alertbox('패스워드가 일치하지 않습니다.');
					}
				}
			} elseif ($post['mno'] != $member['idx']) {
				Alertbox('글을 수정할 권한이 없습니다.');
			}
		}

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : $post['password'];
			$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : '';
			$insert['homepage'] = Request('homepage') ? (preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage')) : '';
			$insert['last_modify_mno'] = 0;
			setcookie('iModuleBoardName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardEmail',$insert['email'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardHomepage',$insert['homepage'],time()+60*60*24*365,'/');
		} else {
			$insert['last_modify_mno'] = $member['idx'];
		}
		$insert['last_modify_date'] = GetGMT();
		$insert['last_modify_hit'] = $post['last_modify_hit']+1;

		$mDB->DBupdate($mBoard->table['post'],$insert,'',"where `idx`='$idx'");
		if ($post['category'] != $insert['category']) {
			if ($post['category'] != 0) $mDB->DBupdate($mBoard->table['category'],'',array('post'=>'`post`-1'),"where `idx`='{$post['category']}'");
			if ($insert['category'] != 0) $mDB->DBupdate($mBoard->table['category'],array('post_time'=>GetGMT()),array('post'=>'`post`+1'),"where `idx`='{$insert['category']}'");
		}

		$resultMsg = '게시물을 성공적으로 수정하였습니다.';

		$autosaveFind = "where `bid`='{$bid}'";
		$autosaveFind.= " and `repto`=$idx";
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
	}

	$file = Request('file');
	if ($file != null) {
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$temp = explode('|',$file[$i]);
			$fidx = $temp[0];

			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mBoard->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['userfilePath'].$mBoard->userfile.$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mBoard->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mBoard->table['file'],"where `idx`='$fidx'");
			} else {
				$mDB->DBupdate($mBoard->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
			}
		}
		
		
		if ($insert['image'] == '') {
			$image = $mDB->DBfetch($mBoard->table['file'],array('idx'),"where `repto`=$idx and `filetype`='IMG'");
			$image = isset($image['idx']) == true ? $image['idx'] : 0;
			$mDB->DBupdate($mBoard->table['post'],array('image'=>$image),'',"where `idx`=$idx");
		}
	}

	$mDB->DBdelete($mBoard->table['autosave'],$autosaveFind);
	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mBoard->GetQueryString(array('mode'=>'view','idx'=>$idx),$path[1],false);
	$returnURL = $url.$query;

	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mBoard->setup['post_point'] > 0) {
			$mMember->SendPoint($member['idx'],$mBoard->setup['post_point'],'게시물 작성 ('.GetCutString($insert['title'],20).')','/module/board/board.php?bid='.$bid.'&mode=view&idx='.$idx,'board');
			$mMember->SendExp($member['idx'],15);
		}
	}

	Alertbox($resultMsg,3,$returnURL,'parent');
}

// 댓글 관련
if ($action == 'ment') {
	if ($mMember->IsLogged() == false) {
		$mAntiSpam = new AntiSpam();
		if ($mAntiSpam->CheckAntiSpam(Request('antispam')) == false) {
			Alertbox('스팸방지코드를 바르게 입력하여 주십시오.');
		}
	}

	$insert = array();
	$mode = Request('mode');
	$repto = Request('repto');
	$insert['bid'] = Request('bid');
	$insert['repto'] = $repto;
	$insert['parent'] = Request('parent') ? Request('parent') : '0';

	$insert['content'] = Request('content') ? $mBoard->SetContent(Request('content')) : Alertbox('내용을 입력하여 주십시오.');
	$insert['search'] = GetIndexingText(Request('content'));
	$insert['is_msg'] = $mMember->IsLogged() == true && Request('is_msg') ? 'TRUE' : 'FALSE';
	$insert['is_email'] = CheckEmail(Request('email')) == true && Request('is_email') ? 'TRUE' : 'FALSE';
	if (Request('is_mobile') == 'TRUE') $insert['is_mobile'] = 'TRUE';

	$extraValue = array();
	foreach ($_REQUEST as $extra=>$value) {
		if (preg_match('/^extra_/',$extra) == true) {
			$extraValue[$extra] = Request($extra);
		}
	}
	$insert['extra_content'] = sizeof($extraValue) > 0 ? serialize($extraValue) : '';

	$post = $mDB->DBfetch($mBoard->table['post'],'*',"where `idx`='$repto'");
	if (isset($post['idx']) == false) Alertbox('게시물을 찾을 수 없습니다.');

	$insert['postmno'] = $post['mno'];

	if ($mode == 'post') {
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('암호를 입력하여 주십시오.');
			$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : '';
			$insert['homepage'] = Request('homepage') ? (preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage')) : '';
			setcookie('iModuleBoardName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardEmail',$insert['email'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardHomepage',$insert['homepage'],time()+60*60*24*365,'/');
		} else {
			$insert['name'] = $insert['email'] = $insert['homepage'] = '';
			$insert['mno'] = $member['idx'];
		}

		if ($insert['parent'] == '0' && $post['is_msg'] == 'TRUE' && $post['mno'] != '0') {
			$message = array('module'=>'board','mno'=>$member['idx'],'name'=>$insert['name'],'type'=>'post','parent'=>$post['title'],'message'=>$insert['content']);
			$mMember->SendMessage($post['mno'],$message,'/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$post['idx'],-1);
		}

		if ($insert['parent'] != '0') {
			$parent = $mDB->DBfetch($mBoard->table['ment'],array('is_msg','mno','content'),"where `idx`='{$insert['parent']}'");
			if ($parent['is_msg'] == 'TRUE' && $parent['mno'] != '0') {
				$message = array('module'=>'board','mno'=>$member['idx'],'name'=>$insert['name'],'type'=>'ment','parent'=>$parent['content'],'message'=>$insert['content']);
				$mMember->SendMessage($parent['mno'],$message,'/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$post['idx'],-1);
			}
		}

		$insert['reg_date'] = GetGMT();
		$idx = $mDB->DBinsert($mBoard->table['ment'],$insert);
		$mDB->DBupdate($mBoard->table['post'],array('last_ment'=>GetGMT()),array('ment'=>'`ment`+1'),"where `idx`='{$insert['repto']}'");

		$resultMsg = '댓글을 성공적으로 등록하였습니다.';
	} else {
		$idx = Request('idx');
		$ment = $mDB->DBfetch($mBoard->table['ment'],array('mno','password','last_modify_hit'),"where `idx`='$idx'");

		if ($mBoard->GetPermission('modify') == false) {
			if ($ment['mno'] == '0') {
				$check_password = ArzzDecoder(Request('check_password'));
				if ($check_password == null) {
					Alertbox('댓글의 패스워드를 입력하여 주십시오.');
				} else {
					if (md5($check_password) != $ment['password']) {
						Alertbox('패스워드가 일치하지 않습니다.');
					}
				}
			} elseif ($ment['mno'] != $member['idx']) {
				Alertbox('댓글을 수정할 권한이 없습니다.');
			}
		}

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : $ment['password'];
			$insert['last_modify_mno'] = 0;
			setcookie('iModuleBoardName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardEmail',$insert['email'],time()+60*60*24*365,'/');
			setcookie('iModuleBoardHomepage',$insert['homepage'],time()+60*60*24*365,'/');
		} else {
			$insert['last_modify_mno'] = $member['idx'];
		}
		$insert['last_modify_date'] = GetGMT();
		$insert['last_modify_hit'] = $ment['last_modify_hit']+1;

		$mDB->DBupdate($mBoard->table['ment'],$insert,'',"where `idx`='$idx'");

		$resultMsg = '댓글을 성공적으로 수정하였습니다.';
	}

	$file = Request('file');

	if ($file != null) {
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$temp = explode('|',$file[$i]);
			$fidx = $temp[0];

			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mBoard->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['userfilePath'].$mBoard->userfile.$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mBoard->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mBoard->table['file'],"where `idx`='$fidx'");
			} else {
				$mDB->DBupdate($mBoard->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
			}
		}
	}

	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mBoard->GetQueryString(array('mode'=>'view','idx'=>$repto,'repto'=>''),$path[1],false);
	$returnURL = $url.$query;

	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mBoard->setup['ment_point'] > 0) {
			$mMember->SendPoint($member['idx'],$mBoard->setup['ment_point'],'댓글 작성 ('.GetCutString($insert['search'],20).')','/module/board/board.php?bid='.$bid.'&mode=view&idx='.$repto,'board');
			$mMember->SendExp($member['idx'],5);
		}
	}
	Alertbox($resultMsg,3,$returnURL,'parent');
}

// 삭제관련
if ($action == 'delete') {
	$idx = Request('idx');
	$mode = Request('mode');

	if ($mode == 'post') {
		$data = $mDB->DBfetch($mBoard->table['post'],array('idx','bid','category','mno','password','title'),"where `idx`='$idx'");
		$mBoard = new ModuleBoard($data['bid']);

		if ($mBoard->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				if (md5(Request('password')) != $data['password']) Alertbox('패스워드가 일치하지 않습니다.');
			} elseif ($data['mno'] != $member['idx']) {
				Alertbox('게시물을 삭제할 권한이 없습니다.');
			}
		}

		$mDB->DBupdate($mBoard->table['post'],array('is_delete'=>'TRUE'),'',"where `idx`='$idx'");
		$mDB->DBupdate($mBoard->table['setup'],'',array('post'=>'`post`-1'),"where `bid`='{$data['bid']}'");
		if ($data['category'] != 0) $mDB->DBupdate($mBoard->table['category'],'',array('post'=>'`post`-1'),"where `idx`='{$data['category']}'");
		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mBoard->GetQueryString(array('mode'=>'list','idx'=>''),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mBoard->setup['post_point']*-1,'게시물 삭제 ('.GetCutString($data['title'],20).')','/module/board/board.php?bid='.$data['bid'].'&mode=view&idx='.$data['idx'],'board',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}

	if ($mode == 'ment') {
		$data = $mDB->DBfetch($mBoard->table['ment'],array('mno','password','parent','repto','search','is_select'),"where `idx`='$idx'");
		$post = $mDB->DBfetch($mBoard->table['post'],array('bid'),"where `idx`='{$data['repto']}'");
		$mBoard = new ModuleBoard($post['bid']);

		if ($mBoard->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				if (md5(Request('password')) != $data['password']) Alertbox('패스워드가 일치하지 않습니다.');
			} elseif ($data['mno'] != $member['idx']) {
				Alertbox('댓글을 삭제할 권한이 없습니다.');
			}
		}

		if ($mBoard->GetChildMent($idx) == true) {
			$mDB->DBupdate($mBoard->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`='$idx'");
		} else {
			$mDB->DBdelete($mBoard->table['ment'],"where `idx`='$idx'");
		}

		$mBoard->CheckParentMent($data['parent']);

		$last_ment = $mDB->DBfetch($mBoard->table['ment'],array('reg_date'),"where `repto`={$data['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
		$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

		$mDB->DBupdate($mBoard->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data['repto']}'");

		$file = $mDB->DBfetchs($mBoard->table['file'],array('idx'),"where `type`='MENT' and `repto`=$idx");
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$mBoard->FileDelete($file['idx']);
		}
		
		if ($data['is_select'] == 'TRUE') $mDB->DBupdate($mBoard->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data['repto']}'");

		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mBoard->GetQueryString(array('mode'=>'view','idx'=>$data['repto']),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mBoard->setup['ment_point']*-1,'댓글 삭제 ('.GetCutString($data['search'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$data['repto'],'board',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}
}

// 자동저장
if ($action == 'autosave') {
	$insert = array();
	$tid = md5($_SERVER['REMOTE_ADDR'].Request('bid').Request('repto'));
	$insert['bid'] = Request('bid');
	$insert['repto'] = Request('repto');
	$data = array();

	$file = array();
	$files = explode(',',Request('file'));
	for ($i=0, $loop=sizeof($files);$i<$loop;$i++) {
		$temp = explode('|',$files[$i]);
		$fidx = $temp[0];

		if ($fidx) {
			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mBoard->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['userfilePath'].$mBoard->userfile.$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mBoard->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mBoard->table['file'],"where `idx`='$fidx'");
			} else {
				$file[] = $files[$i];
			}
		}
	}
	if (sizeof($file) > 0) $data['file'] = implode(',',$file);

	foreach ($_REQUEST as $key=>$value) {
		if (in_array($key,array('action','bid','repto','file','PHPSESSID')) == false) $data[$key] = Request($key);
	}
	$insert['data'] = serialize($data);
	$insert['ip'] = $_SERVER['REMOTE_ADDR'];
	$insert['reg_date'] = GetGMT();

	if ($mDB->DBcount($mBoard->table['autosave'],"where `tid`='$tid'") == 0) {
		$insert['tid'] = $tid;
		$mDB->DBinsert($mBoard->table['autosave'],$insert);
	} else {
		$mDB->DBupdate($mBoard->table['autosave'],$insert,'',"where `tid`='$tid'");
	}

	echo GetTime('Y년 m월 d일 H시 m분 s초',$insert['reg_date']);
}

// 답변선택
if ($action == 'select') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('idx');

	$data = $mDB->DBfetch($mBoard->table['ment'],array('repto','is_select','mno','name','content'),"where `idx`='$idx'");
	$post = $mDB->DBfetch($mBoard->table['post'],array('bid','mno','title'),"where `idx`='{$data['repto']}'");
	$board = $mDB->DBfetch($mBoard->table['setup'],array('use_select','select_point'),"where `bid`='{$post['bid']}'");

	echo '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';
	if ($board['use_select'] == 'TRUE') {
		if ($data['is_select'] == 'FALSE' && ($mBoard->GetPermission('select') == true || ($data['mno'] != $member['idx'] && $post['mno'] != '0' && $post['mno'] == $member['idx']))) {
			if ($mDB->DBcount($mBoard->table['ment'],"where `repto`={$data['repto']} and `is_select`='TRUE' and `is_delete`='FALSE'") == 0) {
				if ($data['mno'] != 0) {
					$mMember->SendPoint($data['mno'],$board['select_point'],'답변채택 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$data['repto'],'board');
					$mMember->SendExp($data['mno'],20);
				}
				if ($post['mno'] != 0) {
					$mMember->SendPoint($post['mno'],(int)($board['select_point']/2),'답변선택 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$data['repto'],'board');
					$mMember->SendExp($data['mno'],10);
				}

				$mDB->DBupdate($mBoard->table['ment'],array('is_select'=>'TRUE'),'',"where `idx`='$idx'");
				$mDB->DBupdate($mBoard->table['post'],array('is_select'=>'TRUE'),'',"where `idx`='{$data['repto']}'");

				echo '<result success="TRUE" msg="답변을 채택하였습니다."></result>';

				if ($data['mno'] != 0) {
					$message = array('module'=>'board','mno'=>$member['idx'],'name'=>$data['name'],'type'=>'select','parent'=>$data['content'],'message'=>'답변채택에 따라  '.number_format($board['select_point']).'포인트가 적립되었습니다.');
					$mMember->SendMessage($data['mno'],$message,'/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$data['repto'],-1);
				}
			} else {
				echo '<result success="FALSE" msg="이미 채택된 답변이 있습니다."></result>';
			}
		} else {
			echo '<result success="FALSE" msg="답변을 채탤할 수 있는 권한이 없습니다."></result>';
		}
	} else {
		echo '<result success="FALSE" msg="답변을 채택할 수 있는 게시판이 아닙니다."></result>';
	}
	echo '</Ajax>';
}

if ($action == 'complete') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('idx');

	$post = $mDB->DBfetch($mBoard->table['post'],array('bid','mno','title','is_select'),"where `idx`='$idx'");
	$board = $mDB->DBfetch($mBoard->table['setup'],array('use_select'),"where `bid`='{$post['bid']}'");

	echo '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';
	if ($board['use_select'] == 'TRUE') {
		if ($post['is_select'] == 'FALSE' && ($mBoard->GetPermission('select') == true || ($post['mno'] != '0' && $post['mno'] == $member['idx']))) {
			if ($mDB->DBcount($mBoard->table['ment'],"where `repto`='$idx' and `is_select`='TRUE' and `is_delete`='FALSE'") == 0) {
				$mDB->DBupdate($mBoard->table['post'],array('is_select'=>'TRUE'),'',"where `idx`='$idx'");

				echo '<result success="TRUE" msg="미해결완료로 처리하였습니다."></result>';
			} else {
				echo '<result success="FALSE" msg="이미 채택된 답변이 있습니다."></result>';
			}
		} else {
			echo '<result success="FALSE" msg="미해결완료처리할 수 있는 권한이 없습니다."></result>';
		}
	} else {
		echo '<result success="FALSE" msg="답변을 채택할 수 있는 게시판이 아닙니다."></result>';
	}
	echo '</Ajax>';
}

// 게시물추천
if ($action == 'vote') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('idx');
	$post = $mDB->DBfetch($mBoard->table['post'],array('bid','mno','title'),"where `idx`='$idx'");

	if ($mMember->IsLogged() == false) {
		$msg = '회원만 추천이 가능합니다.';
	} elseif ($post['mno'] == $member['idx']) {
		$msg = '자신의 게시물은 추천할 수 없습니다.';
	} elseif ($mDB->DBcount($mBoard->table['log'],"where `mno`={$member['idx']} and `repto`='$idx' and `type`='VOTE'") > 0) {
		$msg = '이미 추천한 게시물입니다.';
	} else {
		$voted = $mDB->DBcount($mBoard->table['log'],"where `mno`={$member['idx']} and `type`='VOTE' and `reg_date`>".(GetGMT()-60*60*24));

		if ($voted >= $member['level']['lv']+4) {
			$msg = '24시간동안 추천가능한 횟수를 초과하였습니다. (총 '.($member['level']['lv']+4).'회)';
		} else {
			$mMember->SendExp($member['idx'],5);
			$mMember->SendPoint($member['idx'],10,'게시물 추천 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$idx,'board');

			if ($post['mno'] != '0') {
				$mMember->SendExp($post['mno'],10);
				$mMember->SendPoint($post['mno'],20,'게시물 추천획득 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$idx,'board');
			}

			$mDB->DBinsert($mBoard->table['log'],array('bid'=>$post['bid'],'repto'=>$idx,'type'=>'VOTE','mno'=>$member['idx'],'ip'=>$_SERVER['REMOTE_ADDR'],'reg_date'=>GetGMT()));
			$mDB->DBupdate($mBoard->table['post'],'',array('vote'=>'`vote`+1'),"where `idx`='$idx'");
			$msg = '성공적으로 추천하였습니다. (잔여추천횟수 :  '.($member['level']['lv']+3-$voted).'회)';
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';
	echo '<result msg="'.$msg.'"></result>';
	echo '</Ajax>';
}
?>