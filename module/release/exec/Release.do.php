<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$rid = Request('rid');
$action = Request('action');
$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mRelease = new ModuleRelease($rid);


$checkIP = $mRelease->mIPBan->CheckIP();
if ($checkIP['result'] == true) {
	Alertbox('해당 아이피 '.$checkIP['ip'].'는 차단된 아이피입니다.\n\n차단일시 : '.GetTime('Y년 m월 d일').'\n차단사유 : '.$checkIP['memo']);
}

// 글 관련
if ($action == 'post') {
	$mode = Request('mode');

	if ($mMember->IsLogged() == false) Alertbox('먼저 로그인을 하여 주십시오.');

	$insert = array();
	$insert['rid'] = Request('rid');
	$insert['image'] = Request('image');
	$insert['category'] = Request('category') ? Request('category') : 0;
	$insert['title'] = Request('title') ? Request('title') : Alertbox('제목을 입력하여 주십시오.');
	$insert['content'] = Request('content') ? $mRelease->SetContent(Request('content')) : Alertbox('내용을 입력하여 주십시오.');
	$insert['search'] = GetIndexingText(Request('content'));
	$insert['is_ment'] = Request('is_ment') ? 'TRUE' : 'FALSE';
	$insert['is_msg'] = $mMember->IsLogged() == true && Request('is_msg') ? 'TRUE' : 'FALSE';
	$insert['is_email'] = Request('is_email') ? 'TRUE' : 'FALSE';
	$insert['license'] = Request('license') ? Request('license') : Alertbox('라이센스종류를 선택하여 주십시오.');
	$insert['field1'] = Request('field1');
	$insert['field2'] = Request('field2');
	$insert['field3'] = Request('field3');
	$insert['homepage'] = Request('homepage') ? (preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage')) : '';
	$insert['price'] = Request('price');

	if ($mRelease->setup['use_category'] == 'TRUE' && !Request('category')) Alertbox('이 릴리즈게시판은 카테고리를 반드시 선택하도록 설정되어 있습니다.');

	$extraValue = array();
	foreach ($_REQUEST as $extra=>$value) {
		if (preg_match('/^extra_/',$extra) == true) {
			$extraValue[preg_replace('/^extra_/','',$extra)] = Request($extra);
		}
	}
	$insert['extra_content'] = sizeof($extraValue) > 0 ? serialize($extraValue) : '';

	if (isset($_FILES['logo']['tmp_name']) == true && $_FILES['logo']['tmp_name']) {
		$check = getimagesize($_FILES['logo']['tmp_name']);
		if (in_array($check[2],array('1','2','3')) == false) {
			Alertbox('로고파일은 JPG, GIF, PNG파일만 가능합니다.');
		}
	}

	if ($mode == 'post') {
		$insert['mno'] = $member['idx'];
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		$insert['reg_date'] = GetGMT();

		$idx = $mDB->DBinsert($mRelease->table['post'],$insert);

		$mDB->DBupdate($mRelease->table['setup'],array('post_time'=>GetGMT()),array('post'=>'`post`+1'),"where `rid`='{$insert['rid']}'");
		if ($insert['category'] != 0) {
			$mDB->DBupdate($mRelease->table['category'],array('post_time'=>GetGMT()),array('post'=>'`post`+1'),"where `idx`='{$insert['category']}'");
		}

		$resultMsg = '프로그램을 성공적으로 등록하였습니다.';
	} else {
		$idx = Request('idx');
		$post = $mDB->DBfetch($mRelease->table['post'],array('category','mno','last_modify_hit'),"where `idx`='$idx'");

		if ($mRelease->GetPermission('modify') == false) {
			if ($post['mno'] != $member['idx']) {
				Alertbox('글을 수정할 권한이 없습니다.');
			}
		}

		$insert['last_modify_mno'] = $member['idx'];
		$insert['last_modify_date'] = GetGMT();
		$insert['last_modify_hit'] = $post['last_modify_hit']+1;

		$mDB->DBupdate($mRelease->table['post'],$insert,'',"where `idx`='$idx'");
		if ($post['category'] != $insert['category']) {
			if ($post['category'] != 0) $mDB->DBupdate($mRelease->table['category'],'',array('post'=>'`post`-1'),"where `idx`='{$post['category']}'");
			if ($insert['category'] != 0) $mDB->DBupdate($mRelease->table['category'],array('post_time'=>GetGMT()),array('post'=>'`post`+1'),"where `idx`='{$insert['category']}'");
		}
		
		$resultMsg = '프로그램을 성공적으로 수정하였습니다.';
	}
	
	if (isset($_FILES['logo']['tmp_name']) == true && $_FILES['logo']['tmp_name']) {
		GetThumbnail($_FILES['logo']['tmp_name'],$_ENV['userfilePath'].$mRelease->logo.'/'.$idx.'.thm',150,150,true);
		@chmod($_ENV['userfilePath'].$mRelease->logo.'/'.$idx.'.thm',0707);
	}

	$file = Request('file');
	if ($file != null) {
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			if (preg_match('/^@/',$file[$i]) == true) {
				$fidx = str_replace('@','',$file[$i]);
				$fileData = $mDB->DBfetch($mRelease->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['userfilePath'].$mRelease->userfile.$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mRelease->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mRelease->table['file'],"where `idx`='$fidx'");
			} else {
				$fidx = $file[$i];
				$mDB->DBupdate($mRelease->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
			}
		}
		
		
		if ($insert['image'] == '') {
			$image = $mDB->DBfetch($mRelease->table['file'],array('idx'),"where `repto`=$idx and `filetype`='IMG'");
			$image = isset($image['idx']) == true ? $image['idx'] : 0;
			$mDB->DBupdate($mRelease->table['post'],array('image'=>$image),'',"where `idx`=$idx");
		}
	}

	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mRelease->GetQueryString(array('mode'=>'view','idx'=>$idx),$path[1],false);
	$returnURL = $url.$query;

	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mRelease->setup['post_point'] > 0) {
			$mMember->SendPoint($member['idx'],$mRelease->setup['post_point'],'프로그램 등록 ('.GetCutString($insert['title'],20).')','/module/release/release.php?rid='.$rid.'&mode=view&idx='.$idx,'release');
			$mMember->SendExp($member['idx'],15);
		}
	}

	Alertbox($resultMsg,3,$returnURL,'parent');
}

if ($action == 'version') {
	$mode = Request('mode');

	if ($mMember->IsLogged() == false) Alertbox('먼저 로그인을 하여 주십시오.');

	$idx = Request('idx');
	$vidx = Request('vidx');
	$post = $mDB->DBfetch($mRelease->table['post'],'*',"where `idx`='$idx'");
	
	if (isset($post['idx']) == false) Alertbox('프로그램 게시글을 찾을 수 없습니다.');
	if ($post['mno'] != $member['idx'] && $mRelease->GetPermission('modify') == false) Alertbox('새버전을 등록할 권한이 없습니다.');
	
	$insert = array();
	$insert['repto'] = $idx;
	$insert['version'] = Request('version') ? Request('version') : Alertbox('버전을 입력하여 주십시오.');
	$insert['history'] = Request('history') ? $mRelease->SetContent(Request('history')) : Alertbox('버전히스토리를 입력하여 주십시오.');

	if ($mode == 'post') {
		if (isset($_FILES['file']['tmp_name']) == false || !$_FILES['file']['tmp_name']) {
			Alertbox('프로그램 파일을 선택하여 주십시오.');
		} else {
			$insert['filename'] = $_FILES['file']['name'];
			$insert['filesize'] = filesize($_FILES['file']['tmp_name']);
			$insert['hash'] = md5_file($_FILES['file']['tmp_name']);
		}
	
		$insert['reg_date'] = GetGMT();
		
		$insert['file'] = '/'.date('Ym').'/'.$insert['hash'].'.'.time().'.'.GetFileExec($_FILES['file']['name']);

		$vidx = $mDB->DBinsert($mRelease->table['version'],$insert);
		$mDB->DBupdate($mRelease->table['setup'],array('post_time'=>GetGMT()),'',"where `rid`='{$post['rid']}'");
		$resultMsg = '새로운버전을 성공적으로 등록하였습니다.';
	} else {
		$version = $mDB->DBfetch($mRelease->table['version'],'*',"where `idx`='$vidx'");
		if ($mRelease->GetPermission('modify') == false) {
			if ($post['mno'] != $member['idx']) {
				Alertbox('글을 수정할 권한이 없습니다.');
			}
		}
		
		if (isset($_FILES['file']['tmp_name']) == true && $_FILES['file']['tmp_name']) {
			@unlink($_ENV['userfilePath'].$mRelease->file.$version['file']);
			$insert['filename'] = $_FILES['file']['name'];
			$insert['filesize'] = filesize($_FILES['file']['tmp_name']);
			$insert['hash'] = md5_file($_FILES['file']['tmp_name']);
		}

		$mDB->DBupdate($mRelease->table['version'],$insert,'',"where `idx`='$vidx'");
		$resultMsg = '버전정보를 성공적으로 수정하였습니다.';
	}
	
	$checkVersion = $mDB->DBfetch($mRelease->table['version'],'*',"where `repto`='$idx'",'idx,desc','0,1');
	if (isset($checkVersion['idx']) == true) {
		$mDB->DBupdate($mRelease->table['post'],array('last_version'=>$checkVersion['version'],'loop'=>$checkVersion['idx']*-1),'',"where `idx`='$idx'");
	} else {
		$mDB->DBupdate($mRelease->table['post'],array('last_version'=>0,'loop'=>0),'',"where `idx`='$idx'");
	}
	
	if (isset($_FILES['file']['tmp_name']) == true && $_FILES['file']['tmp_name']) {
		if (CreateDirectory($_ENV['userfilePath'].$mRelease->file.'/'.date('Ym')) == true) {
			@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['userfilePath'].$mRelease->file.$insert['file']);
			@chmod($_ENV['userfilePath'].$mRelease->file.$insert['file'],0707);
		}
	}
	
	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mRelease->GetQueryString(array('mode'=>'view','idx'=>$idx,'vidx'=>''),$path[1],false);
	$returnURL = $url.$query;

	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mRelease->setup['post_point'] > 0) {
			$mMember->SendPoint($member['idx'],$mRelease->setup['post_point'],'프로그램 신규버전 등록 ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$rid.'&mode=view&idx='.$idx,'release');
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
	$insert['rid'] = Request('rid');
	$insert['repto'] = $repto;
	$insert['parent'] = Request('parent') ? Request('parent') : '0';

	$insert['content'] = Request('content') ? $mRelease->SetContent(Request('content')) : Alertbox('내용을 입력하여 주십시오.');
	$insert['search'] = GetIndexingText(Request('content'));
	$insert['is_msg'] = $mMember->IsLogged() == true && Request('is_msg') ? 'TRUE' : 'FALSE';
	$insert['is_email'] = CheckEmail(Request('email')) == true && Request('is_email') ? 'TRUE' : 'FALSE';

	$extraValue = array();
	foreach ($_REQUEST as $extra=>$value) {
		if (preg_match('/^extra_/',$extra) == true) {
			$extraValue[$extra] = Request($extra);
		}
	}
	$insert['extra_content'] = sizeof($extraValue) > 0 ? serialize($extraValue) : '';

	$post = $mDB->DBfetch($mRelease->table['post'],'*',"where `idx`='$repto'");
	if (isset($post['idx']) == false) Alertbox('게시물을 찾을 수 없습니다.');

	$insert['postmno'] = $post['mno'];

	if ($mode == 'post') {
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('암호를 입력하여 주십시오.');
			$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : '';
			$insert['homepage'] = Request('homepage') ? (preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage')) : '';
			setcookie('iModuleReleaseName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleReleaseEmail',$insert['email'],time()+60*60*24*365,'/');
			setcookie('iModuleReleaseHomepage',$insert['homepage'],time()+60*60*24*365,'/');
		} else {
			$insert['name'] = $insert['email'] = $insert['homepage'] = '';
			$insert['mno'] = $member['idx'];
		}

		if ($insert['parent'] == '0' && $post['is_msg'] == 'TRUE' && $post['mno'] != '0') {
			$message = array('module'=>'release','mno'=>$member['idx'],'name'=>$insert['name'],'type'=>'post','parent'=>$post['title'],'message'=>$insert['content']);
			$mMember->SendMessage($post['mno'],$message,'/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$post['idx'],-1);
		}

		if ($insert['parent'] != '0') {
			$parent = $mDB->DBfetch($mRelease->table['ment'],array('is_msg','mno','content'),"where `idx`='{$insert['parent']}'");
			if ($parent['is_msg'] == 'TRUE' && $parent['mno'] != '0') {
				$message = array('module'=>'release','mno'=>$member['idx'],'name'=>$insert['name'],'type'=>'ment','parent'=>$parent['content'],'message'=>$insert['content']);
				$mMember->SendMessage($parent['mno'],$message,'/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$post['idx'],-1);
			}
		}

		$insert['reg_date'] = GetGMT();
		$idx = $mDB->DBinsert($mRelease->table['ment'],$insert);
		$mDB->DBupdate($mRelease->table['post'],array('last_ment'=>GetGMT()),array('ment'=>'`ment`+1'),"where `idx`='{$insert['repto']}'");

		$resultMsg = '댓글을 성공적으로 등록하였습니다.';
	} else {
		$idx = Request('idx');
		$ment = $mDB->DBfetch($mRelease->table['ment'],array('mno','password','last_modify_hit'),"where `idx`='$idx'");

		if ($mRelease->GetPermission('modify') == false) {
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
			setcookie('iModuleReleaseName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleReleaseEmail',$insert['email'],time()+60*60*24*365,'/');
			setcookie('iModuleReleaseHomepage',$insert['homepage'],time()+60*60*24*365,'/');
		} else {
			$insert['last_modify_mno'] = $member['idx'];
		}
		$insert['last_modify_date'] = GetGMT();
		$insert['last_modify_hit'] = $ment['last_modify_hit']+1;

		$mDB->DBupdate($mRelease->table['ment'],$insert,'',"where `idx`='$idx'");

		$resultMsg = '댓글을 성공적으로 수정하였습니다.';
	}

	$file = Request('file');

	if ($file != null) {
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			if (preg_match('/^@/',$file[$i]) == true) {
				$fidx = str_replace('@','',$file[$i]);
				$fileData = $mDB->DBfetch($mRelease->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['userfilePath'].$mRelease->userfile.$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mRelease->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mRelease->table['file'],"where `idx`='$fidx'");
			} else {
				$fidx = $file[$i];
				$mDB->DBupdate($mRelease->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
			}
		}
	}

	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mRelease->GetQueryString(array('mode'=>'view','idx'=>$repto,'repto'=>''),$path[1],false);
	$returnURL = $url.$query;

	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mRelease->setup['ment_point'] > 0) {
			$mMember->SendPoint($member['idx'],$mRelease->setup['ment_point'],'댓글 작성 ('.GetCutString($insert['search'],20).')','/module/release/release.php?rid='.$rid.'&mode=view&idx='.$repto,'release');
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
		$data = $mDB->DBfetch($mRelease->table['post'],array('idx','rid','category','mno','password','title'),"where `idx`='$idx'");
		$mRelease = new ModuleRelease($data['rid']);

		if ($mRelease->GetPermission('delete') == false && $data['mno'] != $member['idx']) {
			Alertbox('게시물을 삭제할 권한이 없습니다.');
		}

		$mDB->DBupdate($mRelease->table['post'],array('is_delete'=>'TRUE'),'',"where `idx`='$idx'");
		$mDB->DBupdate($mRelease->table['setup'],'',array('post'=>'`post`-1'),"where `rid`='{$data['rid']}'");
		if ($data['category'] != 0) $mDB->DBupdate($mRelease->table['category'],'',array('post'=>'`post`-1'),"where `idx`='{$data['category']}'");
		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mRelease->GetQueryString(array('mode'=>'list','idx'=>''),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mRelease->setup['post_point']*-1,'게시물 삭제 ('.GetCutString($data['title'],20).')','/module/release/release.php?rid='.$data['rid'].'&mode=view&idx='.$data['idx'],'release',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}

	if ($mode == 'ment') {
		$data = $mDB->DBfetch($mRelease->table['ment'],array('mno','password','parent','repto','search','is_select'),"where `idx`='$idx'");
		$post = $mDB->DBfetch($mRelease->table['post'],array('rid'),"where `idx`='{$data['repto']}'");
		$mRelease = new ModuleRelease($post['rid']);

		if ($mRelease->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				if (md5(Request('password')) != $data['password']) Alertbox('패스워드가 일치하지 않습니다.');
			} elseif ($data['mno'] != $member['idx']) {
				Alertbox('댓글을 삭제할 권한이 없습니다.');
			}
		}

		if ($mRelease->GetChildMent($idx) == true) {
			$mDB->DBupdate($mRelease->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`='$idx'");
		} else {
			$mDB->DBdelete($mRelease->table['ment'],"where `idx`='$idx'");
		}

		$mRelease->CheckParentMent($data['parent']);

		$last_ment = $mDB->DBfetch($mRelease->table['ment'],array('reg_date'),"where `repto`={$data['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
		$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

		$mDB->DBupdate($mRelease->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data['repto']}'");

		$file = $mDB->DBfetchs($mRelease->table['file'],array('idx'),"where `type`='MENT' and `repto`=$idx");
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$mRelease->FileDelete($file['idx']);
		}
		
		if ($data['is_select'] == 'TRUE') $mDB->DBupdate($mRelease->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data['repto']}'");

		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mRelease->GetQueryString(array('mode'=>'view','idx'=>$data['repto']),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mRelease->setup['ment_point']*-1,'댓글 삭제 ('.GetCutString($data['search'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$data['repto'],'release',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}
	
	if ($mode == 'version') {
		$idx = Request('idx');
		$vidx = Request('vidx');
		
		$post = $mDB->DBfetch($mRelease->table['post'],'*',"where `idx`='$idx'");
		$version = $mDB->DBfetch($mRelease->table['version'],'*',"where `idx`='$vidx'");
		
		if (isset($version['idx']) == false) Alertbox('해당 버전을 찾을 수 없습니다.');
		if ($post['idx'] != $version['repto']) Alertbox('잘못된 접근입니다.');
		if ($mRelease->GetPermission('delete') == false && $member['idx'] != $post['mno']) Alertbox('권한이 없습니다.');
		
		@unlink($_ENV['userfilePath'].$mRelease->file.$version['file']);
		$mDB->DBdelete($mRelease->table['version'],"where `idx`='$vidx'");
		
		$checkVersion = $mDB->DBfetch($mRelease->table['version'],'*',"where `repto`='$idx'",'idx,desc','0,1');
		if (isset($checkVersion['idx']) == true) {
			$mDB->DBupdate($mRelease->table['post'],array('last_version'=>$checkVersion['version'],'loop'=>$checkVersion['idx']*-1),'',"where `idx`='$idx'");
		} else {
			$mDB->DBupdate($mRelease->table['post'],array('last_version'=>0,'loop'=>0),'',"where `idx`='$idx'");
		}
		
		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mRelease->GetQueryString(array('mode'=>'view','idx'=>$idx,'vidx'=>''),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mRelease->setup['post_point']*-1,'버전 삭제 ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$idx,'release',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}
}

// 게시물추천
if ($action == 'vote') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('idx');
	$post = $mDB->DBfetch($mRelease->table['post'],array('rid','mno','title'),"where `idx`='$idx'");

	if ($mMember->IsLogged() == false) {
		$msg = '회원만 추천이 가능합니다.';
	} elseif ($post['mno'] == $member['idx']) {
		$msg = '자신의 게시물은 추천할 수 없습니다.';
	} elseif ($mDB->DBcount($mRelease->table['log'],"where `mno`={$member['idx']} and `repto`='$idx' and `type`='VOTE'") > 0) {
		$msg = '이미 추천한 게시물입니다.';
	} else {
		$voted = $mDB->DBcount($mRelease->table['log'],"where `mno`={$member['idx']} and `type`='VOTE' and `reg_date`>".(GetGMT()-60*60*24));

		if ($voted >= $member['level']['lv']+4) {
			$msg = '24시간동안 추천가능한 횟수를 초과하였습니다. (총 '.($member['level']['lv']+4).'회)';
		} else {
			$mMember->SendExp($member['idx'],5);
			$mMember->SendPoint($member['idx'],10,'게시물 추천 ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$idx,'release');

			if ($post['mno'] != '0') {
				$mMember->SendExp($post['mno'],10);
				$mMember->SendPoint($post['mno'],20,'게시물 추천획득 ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$idx,'release');
			}

			$mDB->DBinsert($mRelease->table['log'],array('rid'=>$post['rid'],'repto'=>$idx,'type'=>'VOTE','mno'=>$member['idx'],'ip'=>$_SERVER['REMOTE_ADDR'],'reg_date'=>GetGMT()));
			$mDB->DBupdate($mRelease->table['post'],'',array('vote'=>'`vote`+1'),"where `idx`='$idx'");
			$msg = '성공적으로 추천하였습니다. (잔여추천횟수 :  '.($member['level']['lv']+3-$voted).'회)';
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';
	echo '<result msg="'.$msg.'"></result>';
	echo '</Ajax>';
}
?>