<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$bid = Request('bid');
$action = Request('action');
$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mKin = new ModuleKin();

$checkIP = $mKin->mIPBan->CheckIP();
if ($checkIP['result'] == true) {
	Alertbox('해당 아이피 '.$checkIP['ip'].'는 차단된 아이피입니다.\n\n차단일시 : '.GetTime('Y년 m월 d일').'\n차단사유 : '.$checkIP['memo']);
}

// 글 관련
if ($action == 'question') {
	$mode = Request('mode');

	if ($mMember->IsLogged() == false) {
		$mAntiSpam = new AntiSpam();
		if ($mAntiSpam->CheckAntiSpam(Request('antispam')) == false) {
			Alertbox('스팸방지코드를 바르게 입력하여 주십시오.');
		}
	}

	if (Request('is_agree') == null) Alertbox('지식인 이용약관에 동의하여야 등록이 가능합니다.');
	$insert = array();
	$insert['image'] = Request('image');
	$insert['category1'] = Request('category1') ? Request('category1') : Alertbox('1차 카테고리를 선택해주세요.');
	$insert['category2'] = Request('category2') ? Request('category2') : Alertbox('2차 카테고리를 선택해주세요.');
	$insert['category3'] = Request('category3');
	$insert['title'] = Request('title') ? Request('title') : Alertbox('제목을 입력하여 주십시오.');
	$insert['content'] = Request('content') ? $mKin->SetContent(Request('content')) : Alertbox('내용을 입력하여 주십시오.');
	$insert['search'] = GetIndexingText(Request('content'));
	$insert['point'] = Request('point');

	$insert['is_secret'] = Request('is_secret') ? 'TRUE' : 'FALSE';
	$insert['is_hidename'] = Request('is_hidename') ? 'TRUE' : 'FALSE';
	$insert['is_msg'] = $mMember->IsLogged() == true && Request('is_msg') ? 'TRUE' : 'FALSE';
	$insert['is_email'] = $insert['email'] && Request('is_email') ? 'TRUE' : 'FALSE';

	if ($mode == 'post') {
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('암호를 입력하여 주십시오.');
			$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : '';

			setcookie('iModuleKinName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleKinEmail',$insert['email'],time()+60*60*24*365,'/');
		} else {
			$insert['name'] = $insert['email'] = '';
			$insert['mno'] = $member['idx'];
		}
		$insert['reg_date'] = GetGMT();
		$loop = $mDB->DBfetch($mKin->table['question'],array('MIN(loop)'));
		$insert['loop'] = $loop[0]-1;
		
		$idx = $mDB->DBinsert($mKin->table['question'],$insert);

		$resultMsg = '질문을 성공적으로 등록하였습니다.';

		$autosaveFind = "where `type`='QUESTION'";
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
	} else {
		$idx = Request('idx');
		$post = $mDB->DBfetch($mKin->table['question'],array('mno','password','answer'),"where `idx`='$idx'");

		if ($post['answer'] > 0) Alertbox('답변이 등록되어 있는 질문은 수정할 수 없습니다.');
		
		if ($mKin->GetPermission('modify') == false) {
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
			setcookie('iModuleKinName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleKinEmail',$insert['email'],time()+60*60*24*365,'/');
		}

		$mDB->DBupdate($mKin->table['question'],$insert,'',"where `idx`='$idx'");

		$resultMsg = '질문을 성공적으로 수정하였습니다.';

		$autosaveFind = "where `type`='QUESTION' and `repto`=$idx";
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
	}

	$file = Request('file');
	if ($file != null) {
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$temp = explode('|',$file[$i]);
			$fidx = $temp[0];

			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mKin->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['path'].$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mKin->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mKin->table['file'],"where `idx`='$fidx'");
			} else {
				$mDB->DBupdate($mKin->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
			}
		}
		
		
		if ($insert['image'] == '') {
			$image = $mDB->DBfetch($mKin->table['file'],array('idx'),"where `type`='QUESTION' and `repto`=$idx and `filetype`='IMG'");
			$image = isset($image['idx']) == true ? $image['idx'] : 0;
			$mDB->DBupdate($mKin->table['question'],array('image'=>$image),'',"where `idx`=$idx");
		}
	}

	$mDB->DBdelete($mKin->table['autosave'],$autosaveFind);
	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mKin->GetQueryString(array('mode'=>'view','idx'=>$idx),$path[1],false);
	$returnURL = $url.$query;


	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mKin->GetConfig('point_question') > 0) {
			$mMember->SendPoint($member['idx'],$mKin->GetConfig('point_question'),'게시물 작성 ('.GetCutString($insert['title'],20).')','/module/kin/kin.php?mode=view&idx='.$idx,'kin');
			$mMember->SendExp($member['idx'],15);
		}
	}

	Alertbox($resultMsg,3,$returnURL,'parent');
}

// 답변 관련
if ($action == 'answer') {
	if ($mMember->IsLogged() == false) {
		$mAntiSpam = new AntiSpam();
		if ($mAntiSpam->CheckAntiSpam(Request('antispam')) == false) {
			Alertbox('스팸방지코드를 바르게 입력하여 주십시오.');
		}
	}

	if (Request('is_agree') == null) Alertbox('지식인 이용약관에 동의하여야 등록이 가능합니다.');

	$insert = array();
	$mode = Request('mode');
	$repto = Request('repto');
	$insert['repto'] = $repto;
	$insert['title'] = Request('title') ? Request('title') : Alertbox('답변 제목을 입력하여 주십시오.');
	$insert['content'] = Request('content') ? $mKin->SetContent(Request('content')) : Alertbox('내용을 입력하여 주십시오.');
	$insert['search'] = GetIndexingText(Request('content'));
	$insert['resource'] = Request('resource');

	$post = $mDB->DBfetch($mKin->table['question'],'*',"where `idx`='$repto'");
	if (isset($post['idx']) == false) Alertbox('질문을 찾을 수 없습니다.');

	if ($mode == 'post') {
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];

		if ($mMember->IsLogged() == false) {
			$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
			$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('암호를 입력하여 주십시오.');
			$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : '';
			setcookie('iModuleKinName',$insert['name'],time()+60*60*24*365,'/');
			setcookie('iModuleKinEmail',$insert['email'],time()+60*60*24*365,'/');
		} else {
			$insert['name'] = $insert['email'] = '';
			$insert['mno'] = $member['idx'];
		}

		if ($post['is_msg'] == 'TRUE' && $post['mno'] != '0') {
			$message = array('module'=>'board','mno'=>$member['idx'],'name'=>$insert['name'],'type'=>'post','parent'=>$post['title'],'message'=>$insert['content']);
			$mMember->SendMessage($post['mno'],$message,'/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$post['idx'],-1);
		}

		$insert['reg_date'] = GetGMT();
		$idx = $mDB->DBinsert($mKin->table['answer'],$insert);
		$mDB->DBupdate($mKin->table['question'],array('last_answer'=>GetGMT()),array('answer'=>'`answer`+1'),"where `idx`='{$insert['repto']}'");

		$resultMsg = '답변을 성공적으로 등록하였습니다.';

		$autosaveFind = "where `type`='ANSWER'";
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
	} else {
		$idx = Request('idx');
		$ment = $mDB->DBfetch($mKin->table['ment'],array('mno','password','last_modify_hit'),"where `idx`='$idx'");

		if ($mKin->GetPermission('modify') == false) {
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
		}
		$mDB->DBupdate($mKin->table['ment'],$insert,'',"where `idx`='$idx'");

		$resultMsg = '댓글을 성공적으로 수정하였습니다.';

		$autosaveFind = "where `type`='ANSWER' and `repto`=$idx";
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
	}

	$file = Request('file');

	if ($file != null) {
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$temp = explode('|',$file[$i]);
			$fidx = $temp[0];

			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mKin->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['path'].$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mKin->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mKin->table['file'],"where `idx`='$fidx'");
			} else {
				$mDB->DBupdate($mKin->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
			}
		}

		if ($insert['image'] == '') {
			$image = $mDB->DBfetch($mKin->table['file'],array('idx'),"where `type`='ANSWER' and `repto`=$idx and `filetype`='IMG'");
			$image = isset($image['idx']) == true ? $image['idx'] : 0;
			$mDB->DBupdate($mKin->table['answer'],array('image'=>$image),'',"where `idx`=$idx");
		}
	}

	$mDB->DBdelete($mKin->table['autosave'],$autosaveFind);
	$path = explode('?',$_SERVER['HTTP_REFERER']);
	$url = $path[0];
	$query = $mKin->GetQueryString(array('mode'=>'view','idx'=>$repto,'repto'=>''),$path[1],false);
	$returnURL = $url.$query;

	if ($mode == 'post') {
		if ($mMember->IsLogged() == true && $mKin->setup['ment_point'] > 0) {
			$mMember->SendPoint($member['idx'],$mKin->setup['ment_point'],'댓글 작성 ('.GetCutString($insert['search'],20).')','/module/board/board.php?bid='.$bid.'&mode=view&idx='.$repto,'board');
			$mMember->SendExp($member['idx'],5);
		}
	}
	Alertbox($resultMsg,3,$returnURL,'parent');
}

// 의견관련
if ($action == 'ment') {
	$insert = array();
	$type = Request('type');
	$repto = Request('repto');
	
	$insert['type'] = strtoupper($type);
	if (in_array($insert['type'],array('QUESTION','ANSWER')) == false) Alertbox('의견을 등록하지 못하였습니다.');
	$insert['repto'] = $repto;
	if ($member['idx'] == 0) {
		$insert['name'] = Request('name') ? Request('name') : Alertbox('작성자를 입력하여 주십시오.');
		$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('패스워드를 입력하여 주십시오.');
	} else {
		$insert['mno'] = $member['idx'];
	}
	
	$insert['content'] = Request('content') ? Request('content') : Alertbox('내용을 입력하여 주십시오.');
	$insert['ip'] = $_SERVER['REMOTE_ADDR'];
	$insert['reg_date'] = GetGMT();
	
	$mDB->DBinsert($mKin->table['ment'],$insert);
	$ment = $mDB->DBcount($mKin->table['ment'],"where `type`='{$insert['type']}' and `repto`='$repto'");
	$mDB->DBupdate($mKin->table[$type],array('ment'=>$ment),'',"where `idx`='$repto'");
	
	Alertbox('의견을 등록하였습니다.',3,'reload','parent');
}

// 삭제관련
if ($action == 'delete') {
	$idx = Request('idx');
	$mode = Request('mode');

	if ($mode == 'question') {
		$data = $mDB->DBfetch($mKin->table['question'],array('idx','mno','password','title','answer','point'),"where `idx`='$idx'");
		if ($data['answer'] > 0) Alertbox('답변이 등록된 질문은 삭제할 수 없습니다.');
		
		if ($mKin->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				if (md5(Request('password')) != $data['password']) Alertbox('패스워드가 일치하지 않습니다.');
			} elseif ($data['mno'] != $member['idx']) {
				Alertbox('게시물을 삭제할 권한이 없습니다.');
			}
		}

		$mDB->DBupdate($mKin->table['question'],array('is_delete'=>'TRUE'),'',"where `idx`='$idx'");
		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mKin->GetQueryString(array('mode'=>'list','idx'=>''),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) {
			$mMember->SendPoint($data['mno'],$mKin->GetConfig('point_question')*-1,'지식인 질문삭제 ('.GetCutString($data['title'],20).')','/module/kin/kin.php?mode=view&idx='.$data['idx'],'kin',true);
			if ($data['point'] > 0) $mMember->SendPoint($data['mno'],$data['point'],'지식인 질문삭제 추가포인트 환급 ('.GetCutString($data['title'],20).')','/module/kin/kin.php?mode=view&idx='.$data['idx'],'kin',true);
		}
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}

	if ($mode == 'ment') {
		$data = $mDB->DBfetch($mKin->table['ment'],array('mno','password','parent','repto','search','is_select'),"where `idx`='$idx'");
		$post = $mDB->DBfetch($mKin->table['post'],array('bid'),"where `idx`='{$data['repto']}'");
		$mKin = new ModuleBoard($post['bid']);

		if ($mKin->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				if (md5(Request('password')) != $data['password']) Alertbox('패스워드가 일치하지 않습니다.');
			} elseif ($data['mno'] != $member['idx']) {
				Alertbox('댓글을 삭제할 권한이 없습니다.');
			}
		}

		if ($mKin->GetChildMent($idx) == true) {
			$mDB->DBupdate($mKin->table['ment'],array('is_delete'=>'TRUE'),'',"where `idx`='$idx'");
		} else {
			$mDB->DBdelete($mKin->table['ment'],"where `idx`='$idx'");
		}

		$mKin->CheckParentMent($data['parent']);

		$last_ment = $mDB->DBfetch($mKin->table['ment'],array('reg_date'),"where `repto`={$data['repto']} and `is_delete`='FALSE'",'reg_date,desc','0,1');
		$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

		$mDB->DBupdate($mKin->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data['repto']}'");

		$file = $mDB->DBfetchs($mKin->table['file'],array('idx'),"where `type`='MENT' and `repto`=$idx");
		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$mKin->FileDelete($file['idx']);
		}
		
		if ($data['is_select'] == 'TRUE') $mDB->DBupdate($mKin->table['post'],array('is_select'=>'FALSE'),'',"where `idx`='{$data['repto']}'");

		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mKin->GetQueryString(array('mode'=>'view','idx'=>$data['repto']),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mKin->setup['post_point']*-1,'댓글 삭제 ('.GetCutString($data['search'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$data['repto'],'board',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}
}

// 자동저장
if ($action == 'autosave') {
	$insert = array();
	$tid = md5($_SERVER['REMOTE_ADDR'].Request('type').Request('repto'));
	$insert['type'] = strtoupper(Request('type'));
	$insert['repto'] = Request('repto');
	$data = array();

	$mDB->DBinsert($mKin->table['autosave'],array('data'=>serialize($_REQUEST)));

	$file = array();
	$files = explode(',',Request('file'));
	for ($i=0, $loop=sizeof($files);$i<$loop;$i++) {
		$temp = explode('|',$files[$i]);
		$fidx = $temp[0];

		if ($fidx) {
			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mKin->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
				@unlink($_ENV['path'].$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mKin->thumbnail.'/'.$fidx.'.thm');
				$mDB->DBdelete($mKin->table['file'],"where `idx`='$fidx'");
			} else {
				$file[] = $files[$i];
			}
		}
	}
	if (sizeof($file) > 0) $data['file'] = implode(',',$file);

	foreach ($_REQUEST as $key=>$value) {
		if (in_array($key,array('action','repto','file','PHPSESSID')) == false) $data[$key] = Request($key);
	}
	$insert['data'] = GetSerialize($data);
	$insert['ip'] = $_SERVER['REMOTE_ADDR'];
	$insert['reg_date'] = GetGMT();

	if ($mDB->DBcount($mKin->table['autosave'],"where `tid`='$tid'") == 0) {
		$insert['tid'] = $tid;
		$mDB->DBinsert($mKin->table['autosave'],$insert);
	} else {
		$mDB->DBupdate($mKin->table['autosave'],$insert,'',"where `tid`='$tid'");
	}

	echo GetTime('Y년 m월 d일 H시 m분 s초',$insert['reg_date']);
}

if ($action == 'select') {
	$idx = Request('idx');
	
	$answer = $mDB->DBfetch($mKin->table['answer'],array('mno','repto'),"where `idx`='$idx'");
	$question = $mDB->DBfetch($mKin->table['question'],array('mno','name','point','title','password','is_complete','is_select'),"where `idx`='{$answer['repto']}'");
	
	if ($question['is_select'] == 'TRUE') Alertbox('이미 답변을 선택한 질문입니다.',2,'','parent');
	if ($question['is_complete'] == 'TRUE') Alertbox('이미 마감된 질문입니다.',2,'','parent');
	
	if ($mKin->GetPermission('select') == false) {
		if ($question['mno'] == 0) {
			if (md5(Request('password')) != $question['password']) Alertbox('패스워드가 일치하지 않습니다');
		} else {
			if ($question['mno'] != $member['idx']) Alertbox('답변을 채택할 권한이 없습니다.',2,'','parent');
		}
	}
	
	$message = Request('message') ? Request('message') : Alertbox('감사인사를 입력하여 주십시오.');
	
	$mDB->DBupdate($mKin->table['answer'],array('is_select'=>'TRUE'),'',"where `idx`='$idx'");
	$mDB->DBupdate($mKin->table['question'],array('is_complete'=>'TRUE','is_select'=>'TRUE'),'',"where `idx`='{$answer['repto']}'");
	$mDB->DBinsert($mKin->table['thanks'],array('qno'=>$answer['repto'],'ano'=>$idx,'message'=>$message));
	
	if ($question['mno'] != '0') {
		$mMember->SendPoint($question['mno'],$mKin->point['select']+round($question['point']/2),'지식인 질문마감 감사포인트','/module/kin/kin.php?mode=view&idx='.$answer['repto'],'kin');
	}
	
	if ($answer['mno'] != '0') {
		$mMember->SendPoint($answer['mno'],$mKin->point['select']+$question['point'],'지식인 질문채택 포인트(기본 '.$mKin->point['select'].'포인트, 감사포인트 '.$question['point'].'포인트)','/module/kin/kin.php?mode=view&idx='.$answer['idx'],'kin');
		
		$message = array('module'=>'kin','mno'=>$answer['mno'],'name'=>$question['name'],'type'=>'select','parent'=>$question['title'],'message'=>'답변채택에 따라 '.number_format($board['select_point']).'포인트가 적립되었습니다.<br /><br /><span class="bold">질문자 감사인사 :</span><br />'.nl2br($message));
		$mMember->SendMessage($answer['mno'],$message,'/module/kin/kin.php?mode=view&idx='.$answer['repto'],-1);
	}
	
	Alertbox('답변을 성공적으로 채택하였습니다.\\n질문을 마감하여 주셔서 감사드립니다.',6,'','parent');
}

if ($action == 'complete') {
	$idx = Request('idx');
	$mode = Request('mode');
	
	$question = $mDB->DBfetch($mKin->table['question'],array('mno','name','point','title','password','is_complete','is_select','is_rewrite','reg_date'),"where `idx`='$idx'");
	
	if ($question['is_select'] == 'TRUE') Alertbox('이미 답변을 선택한 질문입니다.',2,'','parent');
	if ($question['is_complete'] == 'TRUE') Alertbox('이미 마감된 질문입니다.',2,'','parent');
	
	if ($mKin->GetPermission('select') == false) {
		if ($question['mno'] == 0) {
			if (md5(Request('password')) != $question['password']) Alertbox('패스워드가 일치하지 않습니다');
		} else {
			if ($question['mno'] != $member['idx']) Alertbox('질문을 마감할 권한이 없습니다.',2,'','parent');
		}
	}
	
	if ($mode == 'rewrite') {
		if ($question['reg_date'] > GetGMT()-60*60*24*15) Alertbox('질문한지 15일이 경과되었을 경우 재등록이 가능합니다.');
		if ($question['is_rewrite'] == 'TRUE') Alertbox('이미 재등록된 질문입니다.');
		
		$loop = $mDB->DBfetch($mKin->table['question'],array('MIN(loop)'));
		$loop = $loop[0]-1;
		$mDB->DBupdate($mKin->table['question'],array('loop'=>$loop,'is_rewrite'=>'TRUE'),'',"where `idx`='$idx'");
		
		Alertbox('질문을 성공적으로 재등록하였습니다.\\n새로운 답변을 더 받으실 수 있습니다.',6,'','parent');
	} else {
		$mDB->DBupdate($mKin->table['question'],array('is_complete'=>'TRUE'),'',"where `idx`='$idx'");
		
		if ($question['mno'] != '0') {
			$mMember->SendPoint($question['mno'],$mKin->point['complete']+round($question['point']/10),'지식인 질문마감 감사포인트','/module/kin/kin.php?mode=view&idx='.$answer['idx'],'kin');
		}
		
		Alertbox('질문을 성공적으로 마감하였습니다.\\n질문을 마감하여 주셔서 감사드립니다.',6,'','parent');
	}
}

// 게시물추천
if ($action == 'vote') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('idx');
	$post = $mDB->DBfetch($mKin->table['post'],array('bid','mno','title'),"where `idx`='$idx'");

	if ($mMember->IsLogged() == false) {
		$msg = '회원만 추천이 가능합니다.';
	} elseif ($post['mno'] == $member['idx']) {
		$msg = '자신의 게시물은 추천할 수 없습니다.';
	} elseif ($mDB->DBcount($mKin->table['log'],"where `mno`={$member['idx']} and `repto`='$idx' and `type`='VOTE'") > 0) {
		$msg = '이미 추천한 게시물입니다.';
	} else {
		$voted = $mDB->DBcount($mKin->table['log'],"where `mno`={$member['idx']} and `type`='VOTE' and `reg_date`>".(GetGMT()-60*60*24));

		if ($voted >= $member['level']['lv']+4) {
			$msg = '24시간동안 추천가능한 횟수를 초과하였습니다. (총 '.($member['level']['lv']+4).'회)';
		} else {
			$mMember->SendExp($member['idx'],5);
			$mMember->SendPoint($member['idx'],10,'게시물 추천 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$idx,'board');

			if ($post['mno'] != '0') {
				$mMember->SendExp($post['mno'],10);
				$mMember->SendPoint($post['mno'],20,'게시물 추천획득 ('.GetCutString($post['title'],20).')','/module/board/board.php?bid='.$post['bid'].'&mode=view&idx='.$idx,'board');
			}

			$mDB->DBinsert($mKin->table['log'],array('bid'=>$post['bid'],'repto'=>$idx,'type'=>'VOTE','mno'=>$member['idx'],'ip'=>$_SERVER['REMOTE_ADDR'],'reg_date'=>GetGMT()));
			$mDB->DBupdate($mKin->table['post'],'',array('vote'=>'`vote`+1'),"where `idx`='$idx'");
			$msg = '성공적으로 추천하였습니다. (잔여추천횟수 :  '.($member['level']['lv']+3-$voted).'회)';
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';
	echo '<result msg="'.$msg.'"></result>';
	echo '</Ajax>';
}
?>