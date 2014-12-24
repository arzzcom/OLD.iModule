<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$return = array();

if ($action == 'membercheck') {
	$name = Request('name');
	$email = Request('email');
	$companyno = Request('companyno');

	if ($companyno != null) {
		$check = $mDB->DBfetch($_ENV['table']['member'],array('user_id','reg_date'),"where `companyno`='$companyno' and `is_leave`='FALSE'");
		
		if (isset($check['user_id']) == true) {
			$user_id = '';
			for ($i=0, $loop=strlen($check['user_id'])-3;$i<$loop;$i++) {
				$user_id.= substr($check['user_id'],$i,1);
			}
			$user_id.= '***';
			
			$return['success'] = false;
			$return['find'] = true;
			$return['reg_date'] = GetTime('Y년 m월 d일 H시 i분',$check['reg_date']);
			$return['user_id'] = $user_id;
		} else {
			$return['success'] = true;
		}
	} else {
		if (CheckEmail($email) == true) {
			$check = $mDB->DBfetch($_ENV['table']['member'],array('user_id','reg_date'),"where `email`='$email' and `is_leave`='FALSE'");

			if (isset($check['user_id']) == true) {
				$user_id = '';
				for ($i=0, $loop=strlen($check['user_id'])-3;$i<$loop;$i++) {
					$user_id.= substr($check['user_id'],$i,1);
				}
				$user_id.= '***';
				
				$return['success'] = false;
				$return['find'] = true;
				$return['reg_date'] = GetTime('Y년 m월 d일 H시 i분',$check['reg_date']);
				$return['user_id'] = $user_id;
			} else {
				$return['success'] = true;
			}
		} else {
			$return['success'] = false;
			$return['find'] = false;
		}
	}
}

if ($action == 'duplication') {
	$check = Request('check');
	$value = Request('value');

	$mMember = &Member::instance();
	$member = $mMember->GetMemberInfo();
	$loggedFind = $mMember->IsLogged() == true ? " and `idx`!={$member['idx']}" : '';

	$return['success'] = true;
	$return['check'] = $check;
	if ($check == 'user_id') {
		if (CheckUserID($value) == true) {
			if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='$value' and ((`is_leave`='TRUE' and `leave_date`>".(GetGMT()-60*60*24*180).") or `is_leave`='FALSE')") == 0) {
				$return['message'] = $value.'는 사용가능한 아이디입니다.';
			} else {
				$return['success'] = false;
				$return['message'] = '아이디가 중복됩니다. 다른아이디를 입력하여 주십시오.';
			}
		} else {
			$return['success'] = false;
			$return['message'] = '아이디는 영문자로 시작하여 영문,숫자,_(언더바)조합의 6자~20자 이내만 가능합니다.';
		}
	}

	if ($check == 'email') {
		if (CheckEmail($value) == true) {
			if ($mDB->DBcount($_ENV['table']['member'],"where `email`='$value' and `is_leave`='FALSE'".$loggedFind) == 0) {
				$return['message'] = $value.'는 사용가능한 이메일주소입니다.';
			} else {
				$return['success'] = false;
				$return['message'] = '이메일주소가 중복됩니다. 이메일주소를 확인하여 주십시오.';
			}
		} else {
			$return['success'] = false;
			$return['message'] = '올바른 이메일주소가 아닙니다. 이메일주소를 확인하여 주십시오.';
		}
	}

	if ($check == 'nickname') {
		if (CheckNickname($value) == true) {
			if ($mDB->DBcount($_ENV['table']['member'],"where `nickname`='$value' and ((`is_leave`='TRUE' and `leave_date`>".(GetGMT()-60*60*24*180).") or `is_leave`='FALSE')".$loggedFind) == 0) {
				$return['message'] = $value.'는 사용가능한 닉네임입니다.';
			} else {
				$return['success'] = false;
				$return['message'] = '닉네임이 중복됩니다. 다른닉네임을 입력하여 주십시오.';
			}
		} else {
			$return['success'] = false;
			$return['message'] = '닉네임은 1자이상 20자 이하만 가능합니다.';
		}
	}

	if ($check == 'voter') {
		if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='$value' and `is_leave`='FALSE'") == 1) {
			$return['message'] = '추천인 아이디를 찾았습니다.';
		} else {
			$return['success'] = false;
			$return['message'] = '추천인 아이디를 찾지 못하였습니다.';
		}
	}
}

if ($action == 'phonecheck') {
	$phone = Request('phone');
	$pcode = rand(10000,99999);

	$check = $mDB->DBfetch($_ENV['table']['phone'],array('reg_date'),"where `phone`='$phone'");
	$return['success'] = true;
	if (isset($check['reg_date']) == true && $check['reg_date'] > GetGMT()-60*3) {
		$return['success'] = false;
		$return['message'] = '인증번호를 발송한지 3분이 경과되지 않았습니다. 3분 후에도 인증번호가 도착하지 않는다면, 그 때 시도하여 주십시오.';
	} else {
		$isCheck = true;
		$mMember = &Member::instance();
		if ($mMember->IsLogged() == true) {
			$member = $mMember->GetMemberInfo();
			if ($phone == $member['cellphone']['cellphone']) {
				$return['success'] = false;
				$return['message'] = '이미 인증을 받은 휴대전화번호입니다. 재인증을 받지 않아도 됩니다.';
				$isCheck = false;
			}
		}

		if ($isCheck == true) {
			$mSMS = new ModuleSMS();
			if ($mSMS->SendSMS($phone,'인증번호는['.$pcode.']입니다.') == true) {
				$mDB->DBdelete($_ENV['table']['phone'],"where `phone`='$phone'");
				$mDB->DBinsert($_ENV['table']['phone'],array('phone'=>$phone,'pcode'=>$pcode,'reg_date'=>GetGMT()));

				$return['message'] = '인증번호가 성공적으로 발송되었습니다. 인증코드 입력란에 인증코드를 입력하여 주십시오.';
			} else {
				$return['success'] = false;
				$return['message'] = '인증번호발송이 실패하였습니다. 휴대전화번호를 정확하게 확인하신 후 다시 시도하시거나, 관리자에게 문의바랍니다.';
			}
		}
	}
}

if ($action == 'message') {
	$mMember = &Member::instance();
	$member = $mMember->GetMemberInfo();
	if ($mMember->IsLogged() == true) {
		$mno = Request('mno');
		$list = Request('list');
		$prev = Request('prev');
		$next = Request('next');
		$message = GetAjaxParam('message');

		if ($message) {
			$mMember->SendMessage($mno,$message);
		}

		$find = "where `mno`='{$member['idx']}' and ((`frommno`='$mno' and `tomno`='{$member['idx']}') or (`frommno`='{$member['idx']}' and `tomno`='$mno'))";
		if ($list == 'next') {
			$limiter = $next == '0' ? '0,10' : '';
			$find.= " and `reg_date`>'$next'";
		} else {
			$limiter = '0,10';
			$find.= " and `reg_date`<'$prev'";
		}

		$returnDatas = array();
		$data = $mDB->DBfetchs($_ENV['table']['message'],'*',$find,'idx,desc',$limiter);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['frommno'] == $member['idx']) {
				$type = 'send';
				$photo = $member['photo'];
			} else {
				$memberData = $mMember->GetMemberInfo($data[$i]['frommno']);
				$type = 'receive';
				$photo = $memberData['photo'];
				if ($data[$i]['is_read'] == 'FALSE') $mDB->DBupdate($_ENV['table']['message'],array('is_read'=>'TRUE'),'',"where `idx`='{$data[$i]['idx']}'");
			}
			$data[$i]['message'] = '<div class="smartOutput">'.$data[$i]['message'].'</div>';

			$returnXML.= '<item type="'.$type.'" fromPhoto="'.$photo.'" message="'.GetString($data[$i]['message'],'xml').'" time="'.$data[$i]['reg_date'].'" reg_date="'.GetTime('Y.m.d h:i:s a',$data[$i]['reg_date']).'" url="'.GetString($data[$i]['url'],'xml').'" />';
		}
	}
}

if ($action == 'message_count') {
	if ($mMember->IsLogged() == false) {
		$return['success'] = false;
	} else {
		$mno = $member['idx'];
		$return['success'] = true;
		$return['new'] = intval($mDB->DBcount($_ENV['table']['message'],"where `mno`='$mno' and `tomno`='$mno' and `is_read`='FALSE'"));
		$return['total'] = intval($mDB->DBcount($_ENV['table']['message'],"where `mno`='$mno' and `tomno`='$mno'"));
	}
}

if ($action == 'deleteMessage') {
	$mMember = &Member::instance();
	$member = $mMember->GetMemberInfo();
	$idx = Request('idx');
	$mDB->DBdelete($_ENV['table']['message'],"where `mno`='{$member['idx']}' and `idx` IN ($idx)");

	$returnXML.= '<item result="TRUE" />';
}

if ($action == 'find') {
	$get = Request('get');

	if ($get == 'user_id') {
		$name = GetAjaxParam('name');
		$email = GetAjaxParam('email');
		$jumin = GetAjaxParam('jumin');

		if (!$name) {
			$returnXML.= '<item result="FALSE" msg="실명을 입력하여 주십시오." />';
		} elseif (!$email && !$jumin) {
			$returnXML.= '<item result="FALSE" msg="회원가입당시 입력하셨던 정보(이메일 또는 주민등록번호)를 입력하여 주십시오." />';
		} else {
			if ($jumin) {
				$check = $mDB->DBfetch($_ENV['table']['member'],array('user_id','reg_date'),"where `is_leave`='FALSE' and `name`='$name' and `jumin`='$jumin'");
			} else {
				$check = $mDB->DBfetch($_ENV['table']['member'],array('user_id','reg_date'),"where `is_leave`='FALSE' and `name`='$name' and `email`='$email'");
			}

			if (isset($check['user_id']) == true) {
				$returnXML.= '<item result="TRUE" user_id="'.GetString($check['user_id'],'xml').'" reg_date="'.GetTime('Y.m.d H:i:s',$check['reg_date']).'" />';
			} else {
				$returnXML.= '<item result="FALSE" msg="입력하신 정보로 가입하신 내역이 없습니다. 실명 및 회원가입당시 입력하셨던 정보를 정확하게 입력하여 주십시오." />';
			}
		}
	}

	if ($get == 'password') {
		$user_id = GetAjaxParam('user_id');
		$name = GetAjaxParam('name');
		$email = GetAjaxParam('email');
		$jumin = GetAjaxParam('jumin');

		if (!$user_id) {
			$returnXML.= '<item result="FALSE" msg="아이디를 입력하여 주십시오." />';
		} elseif (!$name) {
			$returnXML.= '<item result="FALSE" msg="실명을 입력하여 주십시오." />';
		} elseif (!$email && !$jumin) {
			$returnXML.= '<item result="FALSE" msg="회원가입당시 입력하셨던 정보(이메일 또는 주민등록번호)를 입력하여 주십시오." />';
		} else {
			if ($jumin) {
				$check = $mDB->DBfetch($_ENV['table']['member'],array('password_question','password_answer'),"where `is_leave`='FALSE' and `user_id`='$user_id' and `name`='$name' and `jumin`='$jumin'");
			} else {
				$check = $mDB->DBfetch($_ENV['table']['member'],array('password_question','password_answer'),"where `is_leave`='FALSE' and `user_id`='$user_id' and `name`='$name' and `email`='$email'");
			}

			if (isset($check['password_question']) == true) {
				$question = $mDB->DBfetch($_ENV['table']['password'],array('question'),"where `idx`={$check['password_question']}");
				$returnXML.= '<item result="TRUE" question="'.GetString($question['question'],'xml').'" />';
			} else {
				$returnXML.= '<item result="FALSE" msg="입력하신 정보로 가입하신 내역이 없습니다. 아이디 및 회원가입당시 입력하셨던 정보를 정확하게 입력하여 주십시오." />';
			}
		}
	}

	if ($get == 'send') {
		$user_id = GetAjaxParam('user_id');
		$name = GetAjaxParam('name');
		$email = GetAjaxParam('email');
		$jumin = GetAjaxParam('jumin');
		$answer = GetAjaxParam('answer');

		if ($jumin) {
			$check = $mDB->DBfetch($_ENV['table']['member'],array('idx','name','email','password_answer'),"where `is_leave`='FALSE' and `user_id`='$user_id' and `name`='$name' and `jumin`='$jumin'");
		} else {
			$check = $mDB->DBfetch($_ENV['table']['member'],array('idx','name','email','password_answer'),"where `is_leave`='FALSE' and `user_id`='$user_id' and `name`='$name' and `email`='$email'");
		}

		if (isset($check['idx']) == true && $check['password_answer'] == $answer) {
			$returnXML.= '<item result="TRUE" email="'.GetString($check['email'],'xml').'" />';
			$password = GetRandomString(8);

			$mDB->DBupdate($_ENV['table']['member'],array('password'=>md5($password)),'',"where `idx`={$check['idx']}");

			$mEmail = new ModuleEmail();
			$mEmail->SetContent($check['name'].'님께서 요청하신 패스워드입니다.',$check['name'].'님의 패스워드가 아래와 같이 변경되었습니다.<br /><br /><b>'.$password.'</b><br /><br />아래의 패스워드로 로그인을 하신 뒤 패스워드를 변경하여 주십시오.',true);
			$mEmail->AddTo($check['email'],$check['name']);
			$mEmail->SendEmail();
		} else {
			$returnXML.= '<item result="FALSE" msg="입력하신 정답이 회원가입당시 입력했던 정답과 일치하지 않습니다.." />';
		}
	}
}

exit(json_encode($return));
?>