<?php
REQUIRE_ONCE $_ENV['path'].'/class/Member.class.php';

class ModuleMember extends member {
	protected $module;
	protected $modulePath;
	protected $moduleDir;
	protected $baseURL;

	function __construct() {
		$this->mDB = &DB::instance();
		$mModule = new Module('member');
		$this->module = $mModule->GetConfig();
		$this->module['signin_alert'] = isset($this->module['signin_alert']) == false ? 'off' : $this->module['signin_alert'];
		$this->module['signin_realname'] = isset($this->module['signin_realname']) == false ? 'off' : $this->module['signin_realname'];
		$this->module['signin_inactive'] = isset($this->module['signin_inactive']) == false ? 'off' : $this->module['signin_inactive'];
		$this->moduleDir = $mModule->GetModuleDir();
		$this->modulePath = $mModule->GetModulePath();
		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));

		if (Request('AutoLogin','cookie') != null && $this->IsLogged() == false) {
			$uid = Request('AutoLogin','cookie');
			$check = $this->mDB->DBfetch($_ENV['table']['autologin'],array('mno'),"where `uid`='$uid'");
			if (isset($check['mno']) == true) {
				$data = $this->mDB->DBfetch($_ENV['table']['member'],array('last_login'),"where `idx`={$check['mno']} and `is_leave`='FALSE'");

				if (isset($data['last_login']) == true) {
					$this->mDB->DBupdate($_ENV['table']['autologin'],array('ip'=>$_SERVER['REMOTE_ADDR'],'last_login'=>GetGMT()),'',"where `uid`='$uid'");

					$_SESSION['logged'] = $check['mno'];
					if (date('Y-m-d') != date('Y-m-d',$data['last_login'])) {
						$this->mDB->DBupdate($_ENV['table']['member'],array('last_login'=>GetGMT()),array('exp'=>'`exp`+10'),"where `idx`={$check['mno']}");
						$this->SendPoint($check['mno'],50,'회원로그인 적립포인트');
					} else {
						$this->mDB->DBupdate($_ENV['table']['member'],array('last_login'=>GetGMT()),'',"where `idx`={$check['mno']}");
					}
				} else {
					$this->mDB->DBdelete($_ENV['table']['autologin'],"where `uid`='$uid'");
					unset($_COOKIE['AutoLogin']);
				}
			} else {
				unset($_COOKIE['AutoLogin']);
			}
		}
	}
	
	function PrintError($msg='') {
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" title="style" />'."\n";
		
		$mTemplet = new Templet($this->modulePath.'/templet/error.tpl');
		$mTemplet->assign('moduleDir',$this->moduleDir);
		$mTemplet->assign('msg',$msg);

		$mTemplet->PrintTemplet();
		
		return false;
	}

	function PrintLoginForm($skin) {
		echo '<!-- LoginForm Start -->'."\n";

		echo '<div class="ModuleMember">'."\n";

		$link = array();
		if (is_dir($this->modulePath.'/templet/login/'.$skin) == false) {
			$this->PrintError('아웃로그인 스킨이 잘못 지정되었습니다.');
		} else {
			if (file_exists($this->modulePath.'/templet/login/'.$skin.'/style.css') == true) {
				echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/login/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
			}
			if (file_exists($this->modulePath.'/templet/login/'.$skin.'/script.js') == true) {
				echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/login/'.$skin.'/script.js"></script>'."\n";
			}
			if ($this->IsLogged() == true) {
				$member = $this->GetMemberInfo();
				$mTemplet = new Templet($this->modulePath.'/templet/login/'.$skin.'/logged.tpl');
				$formStart = '';
				$multiDomain = $this->mDB->DBfetchs($_ENV['table']['multidomain'],array('domain'));
				$formEnd = '<iframe name="loginFrame'.$skin.'" style="display:none;"></iframe>'."\n";
				for ($i=0, $loop=sizeof($multiDomain);$i<$loop;$i++) {
					$formEnd.= '<iframe src="http://'.$multiDomain[$i]['domain'].$_ENV['dir'].'/module/member/session.php?loginAuth='.urlencode(ArzzEncoder(serialize(array('user_id'=>$member['user_id'],'password'=>$member['password'],'ip'=>$_SERVER['REMOTE_ADDR'])))).'" style="display:none;"></iframe>';
				}
				

				$link['logout'] = $_ENV['dir'].'/exec/Member.do.php?action=logout';
				$link['myinfo'] = $this->module['myinfo'];
				$link['msgbox'] = $this->module['msgbox'];

				$id = 'MemberMessge'.rand(100000,999999);
				$message = array();
				$message['new'] = '<span id="'.$id.'New">0</span>';
				$message['all'] = '<span id="'.$id.'All">0</span>';
				$message['checker'] = '<script type="text/javascript">GetEmbed("'.$id.'Checker","'.$this->moduleDir.'/flash/MessageCountChecker.swf",8,8,"id='.$id.'&skin='.$this->moduleDir.'/templet/login/'.$skin.'&check='.urlencode($_ENV['dir'].'/exec/Ajax.get.php?action=checkMessage&mno='.$member['idx']).'");</script>';
				$mTemplet->assign('formStart',$formStart);
				$mTemplet->assign('formEnd',$formEnd);
				$mTemplet->assign('message',$message);
				$mTemplet->assign('member',$this->GetMemberInfo());
			} else {
				$formStart = '<form name="OutLogin'.$skin.'" method="post" action="'.$_ENV['dir'].'/exec/Member.do.php" target="loginFrame'.$skin.'" onsubmit="return MemberLoginCheck(this.name);">'."\n";
				$formStart.= '<input type="hidden" name="action" value="login" />'."\n";
				$formEnd = '</form>'."\n".'<iframe name="loginFrame'.$skin.'" style="display:none;"></iframe>'."\n";
				$multiDomain = $this->mDB->DBfetchs($_ENV['table']['multidomain'],array('domain'));
				for ($i=0, $loop=sizeof($multiDomain);$i<$loop;$i++) {
					$formEnd.= '<iframe src="http://'.$multiDomain[$i]['domain'].$_ENV['dir'].'/module/member/session.php?session='.Request('PHPSESSID','cookie').'&auto='.Request('AutoLogin','cookie').'&check='.md5($_SERVER['REMOTE_ADDR'].Request('PHPSESSID','cookie')).'" style="display:none;"></iframe>';
				}

				$link['signin'] = $this->module['signin'];
				$link['help'] = $this->module['help'];
				$mTemplet = new Templet($this->modulePath.'/templet/login/'.$skin.'/login.tpl');
				$mTemplet->assign('formStart',$formStart);
				$mTemplet->assign('formEnd',$formEnd);
			}
			$mTemplet->assign('skinDir',$this->moduleDir.'/templet/login/'.$skin);
			$mTemplet->assign('link',$link);
			$mTemplet->assign('execTarget','loginFrame'.$skin);
			$mTemplet->PrintTemplet();
		}

		echo '</div>'."\n";
		echo '<!-- LoginForm End -->'."\n";
	}

	function PrintMyInfo($skin) {
		echo '<!-- MyInfo Start -->'."\n";

		if ($this->IsLogged() == false) {
			$this->PrintError('먼저 로그인을 하여주시기 바랍니다.');
		}

		$member = $this->GetMemberInfo();
		$group = $member['group'];
		
		if (is_dir($this->modulePath.'/templet/signin/'.$skin) == false) {
			$this->PrintError('회원정보수정 스킨이 잘못 지정되었습니다.');
		} else {
			if (file_exists($this->modulePath.'/templet/signin/'.$skin.'/style.css') == true) {
				echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/signin/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
			}
	
			$mTemplet = new Templet($this->modulePath.'/templet/signin/'.$skin.'/myinfo.tpl');
	
			$isJumin = false;
			$form = array();
			$signform = $this->mDB->DBfetchs($_ENV['table']['signin'],'*',"where `group`='$group'",'sort,asc');
			for ($i=0, $loop=sizeof($signform);$i<$loop;$i++) {
				if (in_array($signform[$i]['name'],array('agreement','privacy','youngpolicy')) == false) {
					$signform[$i]['value'] = $signform[$i]['value'] ? unserialize($signform[$i]['value']) : array();
					$form[] = $signform[$i];
				}
	
				if ($signform[$i]['type'] == 'jumin') $isJumin = true;
			}
	
	
			$execFrame = 'MemberSignInFrame'.rand(1000,9999);
			$formStart = '<form name="MemberSignIn" method="post" action="'.$_ENV['dir'].'/exec/Member.do.php" target="'.$execFrame.'" enctype="multipart/form-data">'."\n";
			$formStart.= '<input type="hidden" name="action" value="myinfo" />'."\n";
			$formStart.= '<input type="hidden" name="group" value="'.$group.'" />'."\n";
			$formEnd = '</form>'."\n".'<iframe name="'.$execFrame.'" style="display:none;"></iframe>'."\n";
			$name = Request('checkname');
			$mTemplet->assign('name',$name);
	
			if ($isJumin == true && $member['jumin']) {
				$member['jumin1'] = array_shift(explode('-',$member['jumin']));
				$member['jumin2'] = array_pop(explode('-',$member['jumin']));
			} else {
				$member['jumin1'] = $member['jumin2'] = '';
			}
	
	
			$passwords = $this->mDB->DBfetchs($_ENV['table']['password'],'*');
			if ($member['password_question'] == '0') {
				$password = array('idx'=>'','question'=>'비밀번호 재발급시 사용할 질문을 선택하여 주십시오.');
			} else {
				$question = $this->mDB->DBfetch($_ENV['table']['password'],'*',"where `idx`={$member['password_question']}");
				if (isset($question['idx']) == true) $password = array('idx'=>$question['idx'],'question'=>$question['question']);
				else $password = array('idx'=>'','question'=>'비밀번호 재발급시 사용할 질문을 선택하여 주십시오.');
			}
	
			$mTemplet->assign('password',$password);
			$mTemplet->assign('passwords',$passwords);
	
			$mTemplet->assign('skinDir',$this->moduleDir.'/templet/signin/'.$skin);
			$mTemplet->assign('is_realname',isset($this->module['signin_realname']) == true && $this->module['signin_realname'] == 'on');
			$mTemplet->assign('form',$form);
			$mTemplet->assign('formStart',$formStart);
			$mTemplet->assign('formEnd',$formEnd);
			$mTemplet->assign('member',$member);
			$mTemplet->PrintTemplet();
		}
		echo '<!-- MyInfo End -->'."\n";
	}

	function PrintLeave($skin) {
		echo '<!-- MemberLeave Start -->'."\n";
		$member = $this->GetMemberInfo();

		if (is_dir($this->modulePath.'/templet/signin/'.$skin) == false) {
			$this->PrintError('회원탈퇴 스킨이 잘못 지정되었습니다.');
		} else {
			if (file_exists($this->modulePath.'/templet/signin/'.$skin.'/style.css') == true) {
				echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/signin/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
			}
	
			$mTemplet = new Templet($this->modulePath.'/templet/signin/'.$skin.'/leave.tpl');
	
			$execFrame = 'MemberSignInFrame'.rand(1000,9999);
			$formStart = '<form name="MemberSignIn" method="post" action="'.$_ENV['dir'].'/exec/Member.do.php" target="'.$execFrame.'" enctype="multipart/form-data" onsubmit="return confirm(\'정말 탈퇴하시겠습니까?\\n탈퇴이후 같은 아이디, 같은닉네임은 1개월간 사용이 중지됩니다.\');">'."\n";
			$formStart.= '<input type="hidden" name="action" value="leave" />'."\n";
			$formEnd = '</form>'."\n".'<iframe name="'.$execFrame.'" style="display:none;"></iframe>'."\n";
	
			$mTemplet->assign('skinDir',$this->moduleDir.'/templet/signin/'.$skin);
			$mTemplet->assign('formStart',$formStart);
			$mTemplet->assign('formEnd',$formEnd);
			$mTemplet->assign('member',$member);
			$mTemplet->PrintTemplet();
		}
		echo '<!-- MemberLeave End -->'."\n";
	}

	function PrintSignIn($skin,$group='default',$actionFile='') {
		$step = Request('step') ? Request('step') : 1;

		echo '<!-- Signin Start -->'."\n";

		if (is_dir($this->modulePath.'/templet/signin/'.$skin) == false) {
			$this->PrintError('회원가입 스킨이 잘못 지정되었습니다.');
		} elseif ($this->mDB->DBcount($_ENV['table']['group'],"where `group`='$group'") == 0) {
			$this->PrintError('['.$group.']그룹은 생성되지 않은 그룹입니다.');
		} else {
			if (file_exists($this->modulePath.'/templet/signin/'.$skin.'/style.css') == true) {
				echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/signin/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
			}

			$mTemplet = new Templet($this->modulePath.'/templet/signin/'.$skin.'/step'.$step.'.tpl');

			$isJumin = false;
			$isCompanyNo = false;
			$form = array('step1'=>array(),'step2'=>array(),'step3'=>array(),'step4'=>array(),'step5'=>array());
			$signform = $this->mDB->DBfetchs($_ENV['table']['signin'],'*',"where `group`='$group'",'sort,asc');
			for ($i=0, $loop=sizeof($signform);$i<$loop;$i++) {
				if (in_array($signform[$i]['name'],array('agreement','privacy','youngpolicy')) == true) {
					$signform[$i]['value'] = '<div class="smartOutput">'.$signform[$i]['value'].'</div>';
					$form['step1'][] = $signform[$i];
				} else {
					$signform[$i]['value'] = $signform[$i]['value'] ? unserialize($signform[$i]['value']) : array();
					$form['step4'][] = $signform[$i];
				}

				if ($signform[$i]['type'] == 'jumin') $isJumin = true;
				if ($signform[$i]['type'] == 'companyno') $isCompanyNo = true;
			}

			$formStart = '<form name="MemberSignIn" method="post" action="'.$this->baseURL.GetQueryString(array('step'=>($step == 1 && (isset($this->module['signin_realname']) == false || $this->module['signin_realname'] != 'on') ? '3' : ($step+1))),'',false).'" onsubmit="return MemberSignInCheck('.$step.')">'."\n";
			$formEnd = '</form>'."\n";

			if ($step == '3') {
				if (isset($this->module['signin_realname']) == true && $this->module['signin_realname'] == 'on' && $isJumin == true) {
					$name = Request('realname');
					$jumin = Request('jumin1').'-'.Request('jumin2');
					$formStart.= '<input type="hidden" name="checkname" value="'.$name.'" />'."\n";
					$formStart.= '<input type="hidden" name="jumin" value="'.$jumin.'" />'."\n";

					$check = $this->mDB->DBfetch($_ENV['table']['member'],array('user_id','reg_date'),"where `name`='$name' and `jumin`='$jumin'");

					if (isset($check['user_id']) == true) {
						$isFind = true;
						$user_id = '';
						for ($i=0, $loop=strlen($check['user_id'])-3;$i<$loop;$i++) {
							$user_id.= substr($check['user_id'],$i,1);
						}
						$user_id.= '***';
						$reg_date = strtotime(GetTime('c',$data[$i]['reg_date']));
					} else {
						$isFind = false;
						$user_id = $reg_date = '';
					}

					$mTemplet->assign('isForm',false);
					$mTemplet->assign('isFind',$isFind);
					$mTemplet->assign('user_id',$user_id);
					$mTemplet->assign('reg_date',$reg_date);
				} else {
					$mTemplet->assign('isForm',true);
					$mTemplet->assign('isJumin',$isJumin);
					$mTemplet->assign('isCompanyNo',$isCompanyNo);
				}
			}

			if ($step == '4') {
				$execFrame = 'MemberSignInFrame'.rand(1000,9999);
				$formStart = '<form name="MemberSignIn" method="post" action="'.$_ENV['dir'].'/exec/Member.do.php" target="'.$execFrame.'" enctype="multipart/form-data">'."\n";
				$formStart.= '<input type="hidden" name="action" value="signin" />'."\n";
				$formStart.= '<input type="hidden" name="group" value="'.$group.'" />'."\n";
				$formEnd.= '</form>'."\n".'<iframe name="'.$execFrame.'" style="display:none;"></iframe>'."\n";
				$name = Request('checkname');
				$mTemplet->assign('name',$name);

				if ($isJumin == true) {
					$mTemplet->assign('jumin1',Request('jumin1'));
					$mTemplet->assign('jumin2',Request('jumin2'));
					$mTemplet->assign('email','');
				} elseif ($isCompanyNo == true) {
					$mTemplet->assign('companyno1',Request('companyno1'));
					$mTemplet->assign('companyno2',Request('companyno2'));
					$mTemplet->assign('companyno3',Request('companyno3'));
					$mTemplet->assign('email','');
				} else {
					$email = Request('email');
					$mTemplet->assign('email',$email);
					$mTemplet->assign('jumin1','');
					$mTemplet->assign('jumin2','');
				}

				$passwords = $this->mDB->DBfetchs($_ENV['table']['password'],'*');
				$password = array('idx'=>'','question'=>'');
				$mTemplet->assign('password',$password);
				$mTemplet->assign('passwords',$passwords);
			}

			if ($step == '5') {
				$idx = Request('idx');
				$data = $this->mDB->DBfetch($_ENV['table']['member'],array('name','user_id'),"where `idx`='$idx'");
				if ($actionFile != '' && file_exists($actionFile) == true) {
					REQUIRE_ONCE $actionFile;
				}
				
				$groupInfo = $this->mDB->DBfetch($_ENV['table']['group'],array('allow_active'),"where `group`='$group'");
				
				$mTemplet->assign('name',$data['name']);
				$mTemplet->assign('user_id',$data['user_id']);
				$mTemplet->assign('inactive',$groupInfo == 'FALSE' || $this->module['signin_inactive'] == 'on');
				$mTemplet->assign('link',array('confirm'=>$this->module['signin_redirect']));
			}

			$mTemplet->assign('skinDir',$this->moduleDir.'/templet/signin/'.$skin);
			$mTemplet->assign('is_realname',isset($this->module['signin_realname']) == true && $this->module['signin_realname'] == 'on');
			$mTemplet->assign('form',$form['step'.$step]);
			$mTemplet->assign('formStart',$formStart);
			$mTemplet->assign('formEnd',$formEnd);
			$mTemplet->PrintTemplet();
		}

		echo '<!-- Signin End -->'."\n";
	}

	function MemberSignIn() {
		$group = Request('group');
		$insert = array();
		$insert['group'] = $group;
		$insert['extra_data'] = array();
		$form = $this->mDB->DBfetchs($_ENV['table']['signin'],array('name','type','title','allowblank','value'),"where `group`='$group'");
		for ($i=0, $loop=sizeof($form);$i<$loop;$i++) {
			if (in_array($form[$i]['type'],array('agreement','privacy','youngpolicy')) == false) {
				switch ($form[$i]['type']) {
					case 'user_id' :
						$insert['user_id'] = CheckUserID(Request('user_id')) == true ? Request('user_id') : Alertbox('회원아이디를 아이디규칙에 맞게 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `user_id`='{$insert['user_id']}' and ((`is_leave`='TRUE' and `leave_date`>".(GetGMT()-60*60*24*180).") or `is_leave`='FALSE')") > 0) {
							Alertbox('아이디가 중복됩니다.');
						}
					break;

					case 'name' :
						$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
					break;

					case 'nickname' :
						$insert['nickname'] = CheckNickname(Request('nickname')) == true ? Request('nickname') : Alertbox('닉네임을 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `nickname`='{$insert['nickname']}' and `is_leave`='FALSE'") > 0) {
							Alertbox('닉네임이 중복됩니다.');
						}
					break;

					case 'password' :
						$insert['password'] = Request('password1') != null && Request('password1') == Request('password2') ? md5(strtolower(Request('password1'))) : Alertbox('패스워드를 정확하게 입력하여 주십시오.');
						$insert['password_question'] = Request('password_question') ? Request('password_question') : Alertbox('패스워드 재발급 질문을 선택하여 주십시오.');
						$insert['password_answer'] = Request('password_answer') ? Request('password_answer') : Alertbox('패스워드 재발급 답변을 입력하여 주십시오.');
					break;

					case 'jumin' :
						$insert['jumin'] = CheckJumin(Request('jumin1').'-'.Request('jumin2')) == true ? Request('jumin1').'-'.Request('jumin2') : Alertbox('주민등록번호를 정확하게 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `jumin`='{$insert['jumin']}' and `is_leave`='FALSE'") > 0) {
							Alertbox('주민등록번호가 중복됩니다.');
						}
					break;
					
					case 'companyno' :
						$insert['companyno'] = CheckCompanyNo(Request('companyno1').'-'.Request('companyno2').'-'.Request('companyno3')) == true ? Request('companyno1').'-'.Request('companyno2').'-'.Request('companyno3') : Alertbox('사업자등록번호를 정확하게 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `companyno`='{$insert['companyno']}' and `is_leave`='FALSE'") > 0) {
							Alertbox('사업자등록번호가 중복됩니다.');
						}
					break;

					case 'email' :
						$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : Alertbox('이메일주소를 정확하게 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `email`='{$insert['email']}' and `is_leave`='FALSE'") > 0) {
							Alertbox('이메일주소가 중복됩니다.');
						}
					break;
					
					case 'telephone' :
						$telephone = Request('telephone1').'-'.Request('telephone2').'-'.Request('telephone3');
						if (CheckPhoneNumber($telephone) == true) {
							$insert['telephone'] = $telephone;
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('전화번호를 정확하게 입력하여 주십시오.');
						}
					break;

					case 'cellphone' :
						$value = unserialize($form[$i]['value']);
						$cellphone = Request('cellphone1').'-'.Request('cellphone2').'-'.Request('cellphone3');
						if (Request('cellphone1') != null && Request('cellphone2') != null && Request('cellphone3') != null && (isset($value['provider']) == false || ($value['provider'] == 'on' && Request('provider')))) {
							if (isset($value['realphone']) == true && $value['realphone'] == 'on') {
								$pcode = Request('pcode');
								if ($this->mDB->DBcount($_ENV['table']['phone'],"where `phone`='$cellphone' and `pcode`='$pcode'")) {
									$insert['cellphone'] = $cellphone;
									$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
								} else {
									Alertbox('인증코드가 일치하지 않습니다.');
								}
							} else {
								if (CheckPhoneNumber($cellphone) == true) {
									$insert['cellphone'] = $cellphone;
									$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
								} else {
									Alertbox('핸드폰 번호를 정확하게 입력하여 주십시오.');
								}
							}
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('핸드폰 번호를 정확하게 입력하여 주십시오.');
						}
					break;
					
					case 'birthday' :
						if (Request('birthday1') != null && Request('birthday2') != null && Request('birthday3') != null) {
							$insert['birthday'] = date('Y-m-d',mktime(0,0,0,Request('birthday2'),Request('birthday3'),Request('birthday1')));
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('생년월일을 입력하여 주십시오.');
						}
					break;

					case 'address' :
						if (Request('zipcode') != null && Request('address1') != null && Request('address2') != null) {
							$insert['zipcode'] = Request('zipcode');
							$insert['address'] = Request('address1').'||'.Request('address2');
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('주소를 정확하게 입력하여 주십시오.');
						}
					break;

					case 'gender' :
						$insert['gender'] = Request('gender') ? Request('gender') : ($form[$i]['allowblank'] == 'FALSE' ? Alertbox('성별을 선택하여 주십시오.') : '');
					break;

					case 'homepage' :
						if (Request('homepage') != null) {
							$insert['homepage'] = preg_match('/http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage');
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('홈페이지 주소를 입력하여 주십시오.');
						}
					break;

					case 'voter' :
						$value = unserialize($form[$i]['value']);
						$votePoint = $value['vote'];
						$voterPoint = $value['voter'];

						if (Request('voter') != null) {
							$voter = Request('voter');
							$check = $this->mDB->DBfetch($_ENV['table']['member'],array('idx'),"where `user_id`='$voter' and `is_leave`='FALSE'");
							if (isset($check['idx']) == false) {
								Alertbox('추천인을 찾을 수 없습니다.');
							} else {
								$insert['voter'] = $check['idx'];
							}
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('추천인을 입력하여 주십시오.');
						}
					break;
					
					case 'input' :
						$input = Request($form[$i]['name']);
						$value = unserialize($form[$i]['value']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						if ($value) {
							if (preg_match('/'.$value.'/',$input) == true) {
								$insert['extra_data'][$field] = $input;
							} else {
								Alertbox($form[$i]['title'].'이 잘못입력되었습니다.');
							}
						} else {
							$insert['extra_data'][$field] = $input;
						}
					break;
					
					case 'textarea' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'select' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'radio' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'checkbox' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && sizeof($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'search_address' :
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if (Request($form[$i]['name'].'-zipcode') != null && Request($form[$i]['name'].'-address1') != null && Request($form[$i]['name'].'-address2') != null) {
							$insert['extra_data'][$field] = array('zipcode'=>Request($form[$i]['name'].'-zipcode'),'address1'=>Request($form[$i]['name'].'-address1'),'address2'=>Request($form[$i]['name'].'-address2'));
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						}
					break;
					
					case 'phone' :
						$input = Request($form[$i]['name'].'-1').'-'.Request($form[$i]['name'].'-2').'-'.Request($form[$i]['name'].'-3');
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if (CheckPhoneNumber($input) == true) {
							$insert['extra_data'][$field] = $input;
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						}
					break;
					
					case 'date' :
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if (Request($form[$i]['name'].'-1') != null && Request($form[$i]['name'].'-2') != null && Request($form[$i]['name'].'-3') != null) {
							$insert['extra_data'][$field] = date('Y-m-d',mktime(0,0,0,Request($form[$i]['name'].'-2'),Request($form[$i]['name'].'-3'),Request($form[$i]['name'].'-1')));
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						}
					break;
				}
			}
		}
		
		
		$insert['extra_data'] = serialize($insert['extra_data']);

		if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
			$check = @getimagesize($_FILES['nickcon']['tmp_name']);
			if ($check[2] != '1') Alertbox('닉네임아이콘은 GIF확장자만 가능합니다.');
			if ($check[0] > 80 || $check[1] > 16) Alertbox('닉네임아이콘은 가로 80픽셀, 세로 16픽셀 이하만 가능합니다.');
		}

		if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
			$check = @getimagesize($_FILES['photo']['tmp_name']);
			if (in_array($check[2],array('1','2','3')) == false) Alertbox('이미지 파일만 가능합니다.');
		}

		$insert['reg_date'] = GetGMT();
		$insert['point'] = $this->module['default_point'];
		$insert['exp'] = $this->module['default_exp'];
		
		$groupInfo = $this->mDB->DBfetch($_ENV['table']['group'],array('allow_active'),"where `group`='$group'");
		
		if ($this->module['signin_inactive'] == 'on' || $groupInfo['allow_active'] == 'FALSE') {
			$insert['is_active'] = 'FALSE';
		}
		
		$idx = $this->mDB->DBinsert($_ENV['table']['member'],$insert);

		if (isset($insert['voter']) == true && $insert['voter']) {
			$this->mDB->DBinsert($_ENV['table']['voter'],array('tomno'=>$idx,'frommno'=>$insert['voter'],'reg_date'=>GetGMT()));
			$this->SendPoint($idx,$votePoint,'회원가입시 추천인 적립포인트');
			$this->SendPoint($insert['voter'],$voterPoint,'추천적립포인트 ('.$insert['user_id'].'님 추천)');
		}

		if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
			@move_uploaded_file($_FILES['nickcon']['tmp_name'],$_ENV['userfilePath'].'/member/nickcon/'.$idx.'.gif');
		}

		if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
			@unlink($_ENV['userfilePath'].'/member/photo/'.$member['idx']);
			GetThumbnail($_FILES['photo']['tmp_name'],$_ENV['userfilePath'].'/member/photo/'.$idx,60,60);
		}
		
		if ($this->module['signin_inactive'] != 'on') {
			$this->Login($idx);
		}

		Redirect(str_replace('step=4','step=5&idx='.$idx,$_SERVER['HTTP_REFERER']),'parent');
	}

	function MemberMyInfo() {
		if ($this->IsLogged() == false) Alert('회원로그인이 필요합니다.');

		$member = $this->GetMemberInfo();
		$group = $member['group'];

		$insert = array();
		$insert['extra_data'] = array();
		$form = $this->mDB->DBfetchs($_ENV['table']['signin'],array('name','type','allowblank','value'),"where `group`='$group'");
		for ($i=0, $loop=sizeof($form);$i<$loop;$i++) {
			if (in_array($form[$i]['type'],array('agreement','privacy','youngpolicy')) == false) {
				switch ($form[$i]['type']) {
					case 'name' :
						if (Request('name') != null) $insert['name'] = Request('name');
					break;

					case 'nickname' :
						$insert['nickname'] = CheckNickname(Request('nickname')) == true ? Request('nickname') : Alertbox('닉네임을 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `nickname`='{$insert['nickname']}' and `is_leave`='FALSE' and `idx`!='{$member['idx']}'") > 0) {
							Alertbox('닉네임이 중복됩니다.');
						}
					break;

					case 'password' :
						if (Request('password_modify') == 'TRUE') {
							if (Request('password') == null) {
								Alertbox('기존패스워드를 정확하게 입력하여 주십시오.');
							} else {
								if (md5(strtolower(Request('password'))) != $member['password']) Alertbox('기존패스워드가 일치하지 않습니다.');
							}
							$insert['password'] = Request('password1') != null && Request('password1') == Request('password2') ? md5(strtolower(Request('password1'))) : Alertbox('패스워드를 정확하게 입력하여 주십시오.');
						}

						$insert['password_question'] = Request('password_question') ? Request('password_question') : Alertbox('패스워드 재발급 질문을 선택하여 주십시오.');
						$insert['password_answer'] = Request('password_answer') ? Request('password_answer') : Alertbox('패스워드 재발급 답변을 입력하여 주십시오.');
					break;

					case 'jumin' :
						if (!$member['jumin']) {
							$insert['jumin'] = CheckJumin(Request('jumin1').'-'.Request('jumin2')) == true ? Request('jumin1').'-'.Request('jumin2') : Alertbox('주민등록번호를 정확하게 입력하여 주십시오.');
							if ($this->mDB->DBcount($_ENV['table']['member'],"where `jumin`='{$insert['jumin']}' and `is_leave`='FALSE' and `idx`!='{$member['idx']}'") > 0) {
								Alertbox('주민등록번호가 중복됩니다.');
							}
						}
					break;
					
					case 'companyno' :
						$insert['companyno'] = CheckCompanyNo(Request('companyno1').'-'.Request('companyno2').'-'.Request('companyno3')) == true ? Request('companyno1').'-'.Request('companyno2').'-'.Request('companyno3') : Alertbox('사업자등록번호를 정확하게 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `companyno`='{$insert['companyno']}' and `is_leave`='FALSE' and `idx`!='{$member['idx']}'") > 0) {
							Alertbox('사업자등록번호가 중복됩니다.');
						}
					break;

					case 'email' :
						$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : Alertbox('이메일주소를 정확하게 입력하여 주십시오.');
						if ($this->mDB->DBcount($_ENV['table']['member'],"where `email`='{$insert['email']}' and `is_leave`='FALSE' and `idx`!='{$member['idx']}'") > 0) {
							Alertbox('이메일주소가 중복됩니다.');
						}
					break;
					
					case 'telephone' :
						$telephone = Request('telephone1').'-'.Request('telephone2').'-'.Request('telephone3');
						if (CheckPhoneNumber($telephone) == true) {
							$insert['telephone'] = $telephone;
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('전화번호를 번호를 정확하게 입력하여 주십시오.');
						}
					break;

					case 'cellphone' :
						$value = unserialize($form[$i]['value']);
						$cellphone = Request('cellphone1').'-'.Request('cellphone2').'-'.Request('cellphone3');
						if (Request('cellphone1') != null && Request('cellphone2') != null && Request('cellphone3') != null && (isset($value['provider']) == false || ($value['provider'] == 'on' && Request('provider')))) {
							if (isset($value['realphone']) == true && $value['realphone'] == 'on') {
								if ($member['cellphone']['cellphone'] == $cellphone) {
									$insert['cellphone'] = $cellphone;
									$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
								} else {
									$pcode = Request('pcode');
									if ($this->mDB->DBcount($_ENV['table']['phone'],"where `phone`='$cellphone' and `pcode`='$pcode'")) {
										$insert['cellphone'] = $cellphone;
										$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
									} else {
										Alertbox('인증코드가 일치하지 않습니다.');
									}
								}
							} else {
								if (CheckPhoneNumber($cellphone) == true) {
									$insert['cellphone'] = $cellphone;
									$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
								} else {
									Alertbox('핸드폰 번호를 정확하게 입력하여 주십시오.');
								}
							}
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('핸드폰 번호를 정확하게 입력하여 주십시오.');
						}
					break;
					
					case 'birthday' :
						if (Request('birthday1') != null && Request('birthday2') != null && Request('birthday3') != null) {
							$insert['birthday'] = date('Y-m-d',mktime(0,0,0,Request('birthday2'),Request('birthday3'),Request('birthday1')));
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('생년월일을 입력하여 주십시오.');
						}
					break;

					case 'address' :
						if (Request('zipcode') != null && Request('address1') != null && Request('address2') != null) {
							$insert['zipcode'] = Request('zipcode');
							$insert['address'] = Request('address1').'||'.Request('address2');
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('주소를 정확하게 입력하여 주십시오.');
						}
					break;

					case 'gender' :
						$insert['gender'] = Request('gender') ? Request('gender') : ($form[$i]['allowblank'] == 'FALSE' ? Alertbox('성별을 선택하여 주십시오.') : '');
					break;

					case 'homepage' :
						if (Request('homepage') != null) {
							$insert['homepage'] = preg_match('/http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage');
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox('홈페이지 주소를 입력하여 주십시오.');
						}
					break;
					
					case 'input' :
						$input = Request($form[$i]['name']);
						$value = unserialize($form[$i]['value']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						if ($value) {
							if (preg_match('/'.$value.'/',$input) == true) {
								$insert['extra_data'][$field] = $input;
							} else {
								Alertbox($form[$i]['title'].'이 잘못입력되었습니다.');
							}
						}
					break;
					
					case 'textarea' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'select' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'radio' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && strlen($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'checkbox' :
						$input = Request($form[$i]['name']);
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if ($form[$i]['allowblank'] == 'FALSE' && sizeof($input) == 0) Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						
						$insert['extra_data'][$field] = $input;
					break;
					
					case 'search_address' :
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if (Request($form[$i]['name'].'-zipcode') != null && Request($form[$i]['name'].'-address1') != null && Request($form[$i]['name'].'-address2') != null) {
							$insert['extra_data'][$field] = array('zipcode'=>Request($form[$i]['name'].'-zipcode'),'address1'=>Request($form[$i]['name'].'-address1'),'address2'=>Request($form[$i]['name'].'-address2'));
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						}
					break;
					
					case 'phone' :
						$input = Request($form[$i]['name'].'-1').'-'.Request($form[$i]['name'].'-2').'-'.Request($form[$i]['name'].'-3');
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if (CheckPhoneNumber($input) == true) {
							$insert['extra_data'][$field] = $input;
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						}
					break;
					
					case 'date' :
						$field = preg_replace('/^extra_/','',$form[$i]['name']);
						
						if (Request($form[$i]['name'].'-1') != null && Request($form[$i]['name'].'-2') != null && Request($form[$i]['name'].'-3') != null) {
							$insert['extra_data'][$field] = date('Y-m-d',mktime(0,0,0,Request($form[$i]['name'].'-2'),Request($form[$i]['name'].'-3'),Request($form[$i]['name'].'-1')));
						} else {
							if ($form[$i]['allowblank'] == 'FALSE') Alertbox($form[$i]['title'].'은(는) 필수항목입니다.');
						}
					break;
				}
			}
		}
		
		$insert['extra_data'] = serialize($insert['extra_data']);

		if (Request('nickcon_delete') == 'TRUE') @unlink($_ENV['userfilePath'].'/member/nickcon/'.$member['idx'].'.gif');
		if (Request('photo_delete') == 'TRUE') @unlink($_ENV['userfilePath'].'/member/photo/'.$member['idx']);

		if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
			$check = @getimagesize($_FILES['nickcon']['tmp_name']);
			if ($check[2] != '1') Alertbox('닉네임아이콘은 GIF확장자만 가능합니다.');
			if ($check[0] > 80 || $check[1] > 16) Alertbox('닉네임아이콘은 가로 80픽셀, 세로 16픽셀 이하만 가능합니다.');
		}

		if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
			$check = @getimagesize($_FILES['photo']['tmp_name']);
			if (in_array($check[2],array('1','2','3')) == false) Alertbox('이미지 파일만 가능합니다.');
		}

		if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
			@unlink($_ENV['userfilePath'].'/member/nickcon/'.$member['idx'].'.gif');
			@move_uploaded_file($_FILES['nickcon']['tmp_name'],$_ENV['userfilePath'].'/member/nickcon/'.$member['idx'].'.gif');
		}

		if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
			@unlink($_ENV['userfilePath'].'/member/photo/'.$member['idx']);
			GetThumbnail($_FILES['photo']['tmp_name'],$_ENV['userfilePath'].'/member/photo/'.$member['idx'],60,60);
		}

		$this->mDB->DBupdate($_ENV['table']['member'],$insert,'',"where `idx`={$member['idx']}");

		Alertbox('회원정보를 성공적으로 수정하였습니다.',3,'reload','parent');
	}

	function PrintMyPoint($skin,$month=0,$listnum=30,$pagenum=10) {
		$member = $this->GetMemberInfo();

		if (file_exists($this->modulePath.'/templet/point/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/point/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		echo '<div class="ModuleMember">'."\n";

		$find = $month > 0 ? "where `mno`={$member['idx']} and `reg_date`>".(GetGMT()-60*60*24*30*$month) : "where `mno`={$member['idx']}";
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$total = $this->mDB->DBcount($_ENV['table']['point'],$find);
		$totalpage = ceil($total/$listnum) == 0 ? 1 : ceil($total/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$data = $this->mDB->DBfetchs($_ENV['table']['point'],'*',$find,'idx,desc',($p-1)*$listnum.','.$listnum);

		for ($i=0,$loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			$data[$i]['save'] = $data[$i]['point'] > 0 ? $data[$i]['point'] : 0;
			$data[$i]['use'] = $data[$i]['point'] < 0 ? $data[$i]['point']*-1 : 0;
			$data[$i]['url'] = $data[$i]['url'] ? $_ENV['dir'].$data[$i]['url'] : '';
		}

		$page = array();
		$startpage = floor(($p-1)/$pagenum)*$pagenum+1;
		$endpage = $startpage+$pagenum-1 > $totalpage ? $totalpage : $startpage+$pagenum-1;
		$prevpage = $startpage > $pagenum ? $startpage-$pagenum : false;
		$nextpage = $endpage < $totalpage ? $endpage+1 : false;
		$prevlist = $p > 1 ? $p-1 : false;
		$nextlist = $p < $endpage ? $p+1 : false;

		for ($i=$startpage;$i<=$endpage;$i++) {
			$page[] = $i;
		}

		$link = array('page'=>$this->baseURL.GetQueryString(array('p'=>''),'',true).'?p=');

		$mTemplet = new Templet($this->modulePath.'/templet/point/'.$skin.'/list.tpl');
		$mTemplet->assign('data',$data);
		$mTemplet->assign('p',$p);
		$mTemplet->assign('page',$page);
		$mTemplet->assign('pagenum',$pagenum);
		$mTemplet->assign('prevpage',$prevpage);
		$mTemplet->assign('nextpage',$nextpage);
		$mTemplet->assign('prevlist',$prevlist);
		$mTemplet->assign('nextlist',$nextlist);
		$mTemplet->assign('total',number_format($total));
		$mTemplet->assign('totalpage',number_format($totalpage));
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/point/'.$skin);
		$mTemplet->assign('link',$link);
		$mTemplet->PrintTemplet();

		echo '</div>';
	}

	function PrintMessagebox() {
		if ($this->IsLogged() == false) Alert('회원로그인이 필요합니다.');

		$member = $this->GetMemberInfo();
		$method = Request('method') ? Request('method') : 'date';
		$finder = Request('finder') ? Request('finder') : 'today';

		if ($_ENV['isHeaderIncluded'] == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/php2js.php"></script>'."\n";
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/default.js"></script>'."\n";
		}

		if (file_exists($this->modulePath.'/templet/messagebox/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/messagebox/style.css" type="text/css" title="style" />'."\n";
		}

		if (file_exists($this->modulePath.'/templet/messagebox/script.js') == true) {
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/messagebox/script.js"></script>'."\n";
		}

		echo '<div class="ModuleMember">'."\n";

		if ($method == 'sent') {
			$find = "where `mno`={$member['idx']} and `frommno`={$member['idx']}";
		} else {
			$find = "where `mno`={$member['idx']} and `tomno`={$member['idx']}";
		}
		if ($method == 'date') {
			if ($finder == 'today') $find.= " and `reg_date`>=".GetGMT(date('Y-m-d'));
			if ($finder == 'week') $find.= " and `reg_date`>=".GetGMT(date('Y-m-d',(time()-date('w')*60*60*24)));
			if ($finder == 'lastweek') $find.= " and `reg_date`>=".GetGMT(date('Y-m-d',(time()-date('w')*60*60*24-60*60*24*7)))." and `reg_date`<".GetGMT(date('Y-m-d',(time()-date('w')*60*60*24)));
			if ($finder == 'month') $find.= " and `reg_date`>=".GetGMT(date('Y-m-01'));
			if ($finder == 'lastmonth') $find.= " and `reg_date`>=".GetGMT(date(mktime(0,0,0,1,date('m')-1,date('Y'))))." and `reg_date`<".GetGMT(date('Y-m-01'));
			if ($finder == 'old') $find.= " and `reg_date`<".GetGMT(date(mktime(0,0,0,1,date('m')-1,date('Y'))));
			if ($finder == 'unread') $find.= " and `is_read`='FALSE'";
		}

		if ($method == 'sent') {
			if ($finder == 'today') $find.= " and `reg_date`>=".GetGMT(date('Y-m-d'));
			if ($finder == 'week') $find.= " and `reg_date`>=".GetGMT(date('Y-m-d',(time()-date('w')*60*60*24)));
			if ($finder == 'lastweek') $find.= " and `reg_date`>=".GetGMT(date('Y-m-d',(time()-date('w')*60*60*24-60*60*24*7)))." and `reg_date`<".GetGMT(date('Y-m-d',(time()-date('w')*60*60*24)));
			if ($finder == 'month') $find.= " and `reg_date`>=".GetGMT(date('Y-m-01'));
			if ($finder == 'lastmonth') $find.= " and `reg_date`>=".GetGMT(date(mktime(0,0,0,1,date('m')-1,date('Y'))))." and `reg_date`<".GetGMT(date('Y-m-01'));
			if ($finder == 'old') $find.= " and `reg_date`<".GetGMT(date(mktime(0,0,0,1,date('m')-1,date('Y'))));
		}

		if ($method == 'id') {
			$find.= " and `frommno`=$finder";
		}

		$listnum = 30;
		$pagenum = 5;
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$totalmessage = $this->mDB->DBcount($_ENV['table']['message'],$find);
		$totalpage = ceil($totalmessage/$listnum) == 0 ? 1 : ceil($totalmessage/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;
		$orderer = 'idx,desc';
		$limiter = ($p-1)*$listnum.','.$listnum;

		$message = $this->mDB->DBfetchs($_ENV['table']['message'],'*',$find,$orderer,$limiter);
		for ($i=0, $loop=sizeof($message);$i<$loop;$i++) {
			if ($method == 'sent') $memberData = $this->GetMemberInfo($message[$i]['tomno']);
			else $memberData = $this->GetMemberInfo($message[$i]['frommno']);
			$message[$i]['checkbox'] = '<input type="checkbox" name="idx[]" value="'.$message[$i]['idx'].'" />';

			if ($message[$i]['is_read'] == 'FALSE') {
				$this->mDB->DBupdate($_ENV['table']['message'],array('is_read'=>'TRUE'),'',"where `idx`='{$message[$i]['idx']}'");
			}

			if ($message[$i]['is_system'] == 'FALSE') {
				$message[$i]['name'] = $memberData['nickcon'] ? '<img src="'.$memberData['nickcon'].'" alt="'.$memberData['name'].'" title="'.$memberData['name'].'" style="vertical-align:middle;" />' : $memberData['name'];
				$message[$i]['name'] = '<span class="pointer" onclick="OpenMessage('.$message[$i]['frommno'].')" style="vertical-align:middle;">'.$message[$i]['name'].'</span>';
				$message[$i]['nickname'] = $memberData['nickcon'] ? '<img src="'.$memberData['nickcon'].'" alt="'.$memberData['nickname'].'" title="'.$memberData['nickname'].'" />' : $memberData['nickname'];
				$message[$i]['nickname'] = '<span class="pointer" onclick="OpenMessage('.$message[$i]['frommno'].')">'.$message[$i]['nickname'].'</span>';
				$message[$i]['message'] = '<div class="smartOutput">'.$message[$i]['message'].'</div>';
			} else {
				$message[$i]['name'] = $message[$i]['nickname'] = '<span class="SystemID">SYSTEM</span>';
				$message[$i]['system'] = unserialize($message[$i]['message']);

				if ($message[$i]['system']['mno'] != '0') {
					$memberData = $this->GetMemberInfo($message[$i]['system']['mno']);
					$message[$i]['system']['name'] = $memberData['nickcon'] ? '<img src="'.$memberData['nickcon'].'" alt="'.$memberData['name'].'" title="'.$memberData['name'].'" style="vertical-align:middle;" />' : $memberData['name'];
					$message[$i]['system']['name'] = '<span class="pointer" onclick="OpenMessage('.$message[$i]['system']['mno'].')">'.$message[$i]['system']['name'].'</span>';
					$message[$i]['system']['nickname'] = $memberData['nickcon'] ? '<img src="'.$memberData['nickcon'].'" alt="'.$memberData['nickname'].'" title="'.$memberData['nickname'].'" style="vertical-align:middle;" />' : $memberData['nickname'];
					$message[$i]['system']['nickname'] = '<span class="pointer" onclick="OpenMessage('.$message[$i]['system']['mno'].')">'.$message[$i]['system']['nickname'].'</span>';
				} else {
					$message[$i]['system']['name'] = $message[$i]['system']['nickname'] = $message[$i]['system']['nickname'];
				}

				$message[$i]['system']['parent'] = '<div class="smartOutput">'.$message[$i]['system']['parent'].'</div>';
				$message[$i]['system']['message'] = '<div class="smartOutput">'.$message[$i]['system']['message'].'</div>';
			}
			$message[$i]['url'] = $message[$i]['url'] ? '<span class="pointer" onclick="OpenPopup(\''.$_ENV['dir'].$message[$i]['url'].'\',700,500,true);">'.$message[$i]['url'].'</span>' : '';
		}

		$ids = $this->mDB->DBfetchs($_ENV['table']['message'],array('count(*)','frommno'),"where `mno`={$member['idx']} and `frommno`!='-1' and `tomno`={$member['idx']} group by `frommno`",'0,desc');
		for ($i=0, $loop=sizeof($ids);$i<$loop;$i++) {
			$memberData = $this->GetMemberInfo($ids[$i]['frommno']);
			$ids[$i]['idx'] = $ids[$i]['frommno'];
			$ids[$i]['name'] = $memberData['nickcon'] ? '<img src="'.$memberData['nickcon'].'" alt="'.$memberData['name'].'" title="'.$memberData['name'].'" />' : $memberData['name'];
			$ids[$i]['nickname'] = $memberData['nickcon'] ? '<img src="'.$memberData['nickcon'].'" alt="'.$memberData['nickname'].'" title="'.$memberData['nickname'].'" />' : $memberData['nickname'];
			$ids[$i]['user_id'] = $memberData['user_id'];
			$ids[$i]['link'] = $this->baseURL.GetQueryString(array('method'=>'id','finder'=>$ids[$i]['frommno'],'p'=>'1'),'',true);
			$ids[$i]['message'] = $ids[$i][0];
		}

		$page = array();
		$startpage = floor(($p-1)/$pagenum)*$pagenum+1;
		$endpage = $startpage+$pagenum-1 > $totalpage ? $totalpage : $startpage+$pagenum-1;
		$prevpage = $startpage > $pagenum ? $startpage-$pagenum : false;
		$nextpage = $endpage < $totalpage ? $endpage+1 : false;
		$prevlist = $p > 1 ? $p-1 : false;
		$nextlist = $p < $endpage ? $p+1 : false;

		for ($i=$startpage;$i<=$endpage;$i++) {
			$page[] = $i;
		}

		$link = array();
		$link['method'] = array();
		$link['method']['today'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'today','p'=>'1'),'',true);
		$link['method']['week'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'week','p'=>'1'),'',true);
		$link['method']['lastweek'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'lastweek','p'=>'1'),'',true);
		$link['method']['month'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'month','p'=>'1'),'',true);
		$link['method']['lastmonth'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'lastmonth','p'=>'1'),'',true);
		$link['method']['old'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'old','p'=>'1'),'',true);
		$link['method']['unread'] = $this->baseURL.GetQueryString(array('method'=>'date','finder'=>'unread','p'=>'1'),'',true);

		$link['method']['sentToday'] = $this->baseURL.GetQueryString(array('method'=>'sent','finder'=>'today','p'=>'1'),'',true);
		$link['method']['sentWeek'] = $this->baseURL.GetQueryString(array('method'=>'sent','finder'=>'week','p'=>'1'),'',true);
		$link['method']['sentLastweek'] = $this->baseURL.GetQueryString(array('method'=>'sent','finder'=>'lastweek','p'=>'1'),'',true);
		$link['method']['sentMonth'] = $this->baseURL.GetQueryString(array('method'=>'sent','finder'=>'month','p'=>'1'),'',true);
		$link['method']['sentLastmonth'] = $this->baseURL.GetQueryString(array('method'=>'sent','finder'=>'lastmonth','p'=>'1'),'',true);
		$link['method']['sentOld'] = $this->baseURL.GetQueryString(array('method'=>'sent','finder'=>'old','p'=>'1'),'',true);

		$link['page'] = $this->baseURL.GetQueryString(array('p'=>'','method'=>$method,'finder'=>$finder),'',true).'&amp;p=';

		$mTemplet = new Templet($this->modulePath.'/templet/messagebox/list.tpl');
		$mTemplet->assign('method',$method);
		$mTemplet->assign('finder',$finder);
		$mTemplet->assign('ids',$ids);
		$mTemplet->assign('message',$message);
		$mTemplet->assign('page',$page);
		$mTemplet->assign('pagenum',$pagenum);
		$mTemplet->assign('prevpage',$prevpage);
		$mTemplet->assign('nextpage',$nextpage);
		$mTemplet->assign('prevlist',$prevlist);
		$mTemplet->assign('nextlist',$nextlist);
		$mTemplet->assign('totalmessage',number_format($totalmessage));
		$mTemplet->assign('totalpage',number_format($totalpage));
		$mTemplet->assign('p',$p);
		$mTemplet->assign('link',$link);
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/messagebox');
		$mTemplet->PrintTemplet();

		echo '</div>';
	}

	function PrintMessageView() {
		$mno = Request('mno');

		if (file_exists($this->modulePath.'/templet/messagebox/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/messagebox/style.css" type="text/css" title="style" />'."\n";
		}

		if (file_exists($this->modulePath.'/templet/messagebox/script.js') == true) {
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/messagebox/script.js"></script>'."\n";
		}

		echo '<link rel="stylesheet" href="'.$_ENV['dir'].'/module/wysiwyg/css/default.css" type="text/css" />'."\n";
		echo '<script type="text/javascript" src="'.$_ENV['dir'].'/module/wysiwyg/script/wysiwyg.js"></script>'."\n";

		echo '<input type="hidden" id="PrevTime" value="'.GetGMT().'" /><input type="hidden" id="NextTime" value="0" /><input type="hidden" id="mno" value="'.$mno.'" />';
		echo '<div class="ModuleMember">'."\n";

		$formStart = '<form name="message" method="post" onsubmit="return SendMessage(); return false;">';
		$wysiwyg = '<textarea id="message" name="message" style="width:100%; height:80px;"></textarea><script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"message",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/message.php",fCreator:"createSEditorInIFrame"});</script>';
		$formEnd = '</form>';

		$checker = '<script type="text/javascript">GetEmbed("MessageChecker","'.$this->moduleDir.'/flash/MessageAutoChecker.swf",1,1);</script>';

		$mTemplet = new Templet($this->modulePath.'/templet/messagebox/view.tpl');
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/messagebox');
		$mTemplet->assign('formStart',$formStart);
		$mTemplet->assign('wysiwyg',$wysiwyg);
		$mTemplet->assign('formEnd',$formEnd);
		$mTemplet->assign('checker',$checker);
		$mTemplet->PrintTemplet();

		echo '<script type="text/javascript">GetMessage("next");</script>';

		echo '</div>';
	}

	function PrintHelp($skin) {
		if ($_ENV['isHeaderIncluded'] == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/php2js.php"></script>'."\n";
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/default.js"></script>'."\n";
		}

		if (file_exists($this->modulePath.'/templet/help/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/help/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		if (file_exists($this->modulePath.'/templet/help/'.$skin.'/script.js') == true) {
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/help/'.$skin.'/script.js"></script>'."\n";
		}

		echo '<div class="ModuleMember">'."\n";

		$formStart = '<form name="PointGift" method="post" action="'.$_ENV['dir'].'/exec/Member.do.php" onsubmit="return confirm(\'포인트를 선물하시겠습니까?\');" target="execFrame">';
		$formStart.= '<input type="hidden" name="action" value="pointgift" />';
		$formEnd = '</form><iframe name="execFrame" style="display:none;"></iframe>';

		$mTemplet = new Templet($this->modulePath.'/templet/help/'.$skin.'/help.tpl');
		$mTemplet->assign('formStart',$formStart);
		$mTemplet->assign('formEnd',$formEnd);
		$mTemplet->assign('mypoint',$member['point']);
		$mTemplet->assign('user_id',$user_id);
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/help/'.$skin);
		$mTemplet->PrintTemplet();

		echo '</div>';
	}

	function PrintPointGift() {
		if ($this->IsLogged() == false) Alertbox('회원로그인이 필요합니다.');
		if (GetPermission($this->module['permission_pointgift']) == false) Alertbox('포인트를 선물할 수 있는 권한이 없습니다.',2);

		$user_id = Request('user_id');
		$mno = Request('mno');
		if ($mno) {
			$temp = $this->GetMemberInfo($mno);
			$user_id = $temp['user_id'];
		}
		$member = $this->GetMemberInfo();

		if ($_ENV['isHeaderIncluded'] == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/php2js.php"></script>'."\n";
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/default.js"></script>'."\n";
		}

		if (file_exists($this->modulePath.'/templet/pointgift/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/pointgift/style.css" type="text/css" title="style" />'."\n";
		}

		if (file_exists($this->modulePath.'/templet/pointgift/script.js') == true) {
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/pointgift/script.js"></script>'."\n";
		}

		echo '<div class="ModuleMember">'."\n";

		$formStart = '<form name="PointGift" method="post" action="'.$_ENV['dir'].'/exec/Member.do.php" onsubmit="return confirm(\'포인트를 선물하시겠습니까?\');" target="execFrame">';
		$formStart.= '<input type="hidden" name="action" value="pointgift" />';
		$formEnd = '</form><iframe name="execFrame" style="display:none;"></iframe>';

		$mTemplet = new Templet($this->modulePath.'/templet/pointgift/pointgift.tpl');
		$mTemplet->assign('formStart',$formStart);
		$mTemplet->assign('formEnd',$formEnd);
		$mTemplet->assign('mypoint',$member['point']);
		$mTemplet->assign('user_id',$user_id);
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/pointgift');
		$mTemplet->PrintTemplet();

		echo '</div>';
	}

	function MemberLeave() {
		if ($this->IsLogged() == false) Alert('회원로그인이 필요합니다.');
		$member = $this->GetMemberInfo();

		$jumin = Request('jumin1').'-'.Request('jumin2');
		$jumin = $jumin == '-' ? '' : $jumin;

		$password = md5(Request('password'));

		if ($password != $member['password']) Alertbox('패스워드가 일치하지 않습니다.');
		if ($jumin != $member['jumin']) Alertbox('주민등록번호가 일치하지 않습니다.');

		$this->mDB->DBinsert($_ENV['table']['leave'],array('mno'=>$member['idx'],'msg'=>Request('msg')));
		$this->mDB->DBupdate($_ENV['table']['member'],array('is_leave'=>'TRUE','leave_date'=>GetGMT()),'',"where `idx`={$member['idx']}");

		$this->Logout();
		Alertbox('성공적으로 탈퇴처리되었습니다.\n그동안 이용해주셔서 감사드립니다.','3','/','parent');
	}
	
	function RemoveAllMemberData($idx) {
		if (is_array($idx) == false) $idx = array($idx);
		
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$this->mDB->DBdelete($_ENV['table']['point'],"where `mno`='{$idx[$i]}'");
			$this->mDB->DBdelete($_ENV['table']['message'],"where `idx`='{$idx[$i]}'");
			$this->mDB->DBdelete($_ENV['table']['autologin'],"where `mno`='{$idx[$i]}'");
			$this->mDB->DBdelete($_ENV['table']['leave'],"where `mno`='{$idx[$i]}'");
			$this->mDB->DBdelete($_ENV['table']['voter'],"where `tomno`='{$idx[$i]}' or `frommno`='{$idx[$i]}'");
			$this->mDB->DBdelete($_ENV['table']['member'],"where `idx`='{$idx[$i]}'");
			
			@unlink($_ENV['userfilePath'].'/member/nickcon/'.$idx[$i].'.gif');
			@unlink($_ENV['userfilePath'].'/member/photo/'.$idx[$i]);
		}
	}
}
?>