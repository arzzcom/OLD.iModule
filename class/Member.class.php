<?php
class Member {
	private $buffer;
	
	function &instance() {
		if(isset($GLOBALS['_MEMBER_']) == false || !$GLOBALS['_MEMBER_']) $GLOBALS['_MEMBER_'] = new Member();
		return $GLOBALS['_MEMBER_'];
	}

	function __construct() {
		$this->mDB = &DB::instance();
		$this->buffer = array();
		
		if (Request('loginAuth') != null) {
			if ($loginAuth == 'LOGOUT') {
				$this->Logout();
			} else {
				$loginAuth = unserialize(ArzzDecoder(Request('loginAuth')));
	
				$check = $this->mDB->DBfetch($_ENV['table']['member'],array('idx'),"where `user_id`='{$loginAuth['user_id']}' and `password`='{$loginAuth['password']}'");
				if ($loginAuth['ip'] == $_SERVER['REMOTE_ADDR'] && isset($check['idx']) == true) {
					$_SESSION['logged'] = $check['idx'];
				}
			}	
			$redirect = array_shift(explode('?',$_SERVER['REQUEST_URI'])).GetQueryString(array('loginAuth'=>''));
			Redirect($redirect);
			exit;
		}
		
		if (Request('AutoLogin','cookie') != null && $this->IsLogged() == false) {
			$uid = Request('AutoLogin','cookie');
			$check = $this->mDB->DBfetch($_ENV['table']['autologin'],array('mno'),"where `uid`='$uid'");

			if (isset($check['mno']) == true) {
				$data = $this->mDB->DBfetch($_ENV['table']['member'],array('last_login'),"where `idx`={$check['mno']} and `is_leave`='FALSE'");

				if (isset($data['last_login']) == true) {
					$this->mDB->DBupdate($_ENV['table']['autologin'],array('ip'=>$_SERVER['REMOTE_ADDR'],'last_login'=>GetGMT()),'',"where `uid`='$uid'");

					$_SESSION['logged'] = $check['mno'];
					if (date('Y-m-d') != GetTime('Y-m-d',$data['last_login'])) {
						$this->mDB->DBupdate($_ENV['table']['member'],array('last_login'=>GetGMT()),array('exp'=>'`exp`+10'),"where `idx`={$check['mno']}");
						$this->SendPoint($check['mno'],50,'회원로그인 적립포인트');
					} else {
						$this->mDB->DBupdate($_ENV['table']['member'],array('last_login'=>GetGMT()),'',"where `idx`={$check['mno']}");
					}
				} else {
					$this->mDB->DBdelete($_ENV['table']['autologin'],"where `uid`='$uid'");
					@SetCookie('AutoLogin','',time()-60*60);
				}
			} else {
				@SetCookie('AutoLogin','',time()-60*60);
			}
		}
	}

	function IsLogged() {
		return isset($_SESSION['logged']);
	}

	function IsAdmin() {
		$member = $this->GetMemberInfo();
		return $member['type'] == 'ADMINISTRATOR';
	}

	function Login($idx,$autologin=false) {
		if ($autologin == true) {
			$uid = md5($idx.'-'.time().'-'.rand(100,999));
			SetCookie('AutoLogin',$uid,time()+60*60*24*365,'/');
			if ($this->mDB->DBcount($_ENV['table']['autologin'],"where `uid`='$uid'") > 0) {
				$this->mDB->DBupdate($_ENV['table']['autologin'],array('mno'=>$idx,'ip'=>$_SERVER['REMOTE_ADDR'],'last_login'=>GetGMT()),'',"where `uid`='$uid'");
			} else {
				$this->mDB->DBinsert($_ENV['table']['autologin'],array('uid'=>$uid,'mno'=>$idx,'ip'=>$_SERVER['REMOTE_ADDR'],'last_login'=>GetGMT()));
			}
		}
		$data = $this->mDB->DBfetch($_ENV['table']['member'],array('last_login'),"where `idx`=$idx");
		if (date('Y-m-d') != GetTime('Y-m-d',$data['last_login'])) {
			$this->mDB->DBupdate($_ENV['table']['member'],array('last_login'=>GetGMT()),array('exp'=>'`exp`+10'),"where `idx`=$idx");
			$this->SendPoint($idx,50,'회원로그인 적립포인트');
		} else {
			$this->mDB->DBupdate($_ENV['table']['member'],array('last_login'=>GetGMT()),'',"where `idx`=$idx");
		}
		$_SESSION['logged'] = $idx;
	}

	function Logout() {
		unset($_SESSION['logged']);
		unset($_SESSION['isAdminLog']);
		if (Request('AutoLogin','cookie')) {
			$this->mDB->DBdelete($_ENV['table']['autologin'],"where `uid`='".Request('AutoLogin','cookie')."'");
			SetCookie('AutoLogin','',time()-60*60);
		}
	}

	function GetMemberInfo($mno='') {
		$mno = $mno == '' ? (isset($_SESSION['logged']) == true ? $_SESSION['logged'] : 0) : $mno;
		
		if (isset($this->buffer[$mno]) == true) return $this->buffer[$mno];

		if ($this->mDB->DBfind($_ENV['table']['member']) == true) {
			$data = $this->mDB->DBfetch($_ENV['table']['member'],'*',"where `idx`='$mno'");
		}
		
		if (isset($data['idx']) == true) {
			$data['level'] = $this->GetLevel($data['exp']);

			$data['nickcon'] = file_exists($_ENV['userfilePath'].'/member/nickcon/'.$mno.'.gif') == true ? $_ENV['userfileDir'].'/member/nickcon/'.$mno.'.gif' : '';
			$data['photo'] = file_exists($_ENV['userfilePath'].'/member/photo/'.$mno) == true ? $_ENV['userfileDir'].'/member/photo/'.$mno : $_ENV['dir'].'/images/common/nomempic60.gif';

			$cellphone = explode('||',$data['cellphone']);
			$data['cellphone'] = array();
			$data['cellphone']['provider'] = isset($cellphone[1]) == true ? $cellphone[1] : '';
			$data['cellphone']['cellphone'] = $cellphone[0];
			$cellphone = explode('-',$data['cellphone']['cellphone']);
			$data['cellphone']['cellphone1'] = isset($cellphone[0]) == true ? $cellphone[0] : '';
			$data['cellphone']['cellphone2'] = isset($cellphone[1]) == true ? $cellphone[1] : '';
			$data['cellphone']['cellphone3'] = isset($cellphone[2]) == true ? $cellphone[2] : '';
			
			$telephone = sizeof(explode('-',$data['telephone'])) == 3 ? explode('-',$data['telephone']) : array('','','');
			$data['telephone'] = array('telephone'=>$data['telephone'],'telephone1'=>$telephone[0],'telephone2'=>$telephone[1],'telephone3'=>$telephone[2]);
			
			$data['last_login'] = $data['last_login'] == 0 ? $data['reg_date'] : $data['last_login'];

			$address = explode('||',$data['address'] ? $data['address'] : '||');
			$data['address'] = sizeof($address) == 2 ? array('address'=>$address[0].' '.$address[1],'address1'=>$address[0],'address2'=>$address[1]) : array('address'=>'','address1'=>'','address2'=>'');
			
			$birthday = explode('-',$data['birthday']);
			$data['birthday'] = array('year'=>$birthday[0],'month'=>(int)($birthday[1]),'day'=>(int)($birthday[2]));
			$data['extra'] = unserialize($data['extra_data']);
			unset($data['extra_data']);
		} else {
			if ($this->mDB->DBfind($_ENV['table']['level']) == true) {
				$level = $this->mDB->DBfetch($_ENV['table']['level'],array('lv','exp','next'),"where `lv`=0");
			} else {
				$level = array('lv'=>'0','exp'=>0,'next'=>100);
			}

			$data['idx'] = 0;
			$data['group'] = '';
			$data['user_id'] = '';
			$data['name'] = '';
			$data['nickname'] = '';
			$data['nickcon'] = '';
			$data['point'] = 0;
			$data['level']['lv'] = $level['lv']+1;
			$data['level']['exp'] = 0;
			$data['level']['next'] = $level['next'];
			$data['level']['remain'] = $level['next'];
			$data['photo'] = $_ENV['dir'].'/images/common/nomempic60.gif';
			$data['type'] = 'GUEST';
			$data['cellphone'] = array('provider'=>'','cellphone'=>'','cellphone1'=>'','cellphone2'=>'','cellphone3'=>'');
			$data['extra'] = array();
		}
		
		$this->buffer[$mno] = $data;
		return $data;
	}

	function GetMemberName($mno='',$type='nickname',$use_nickcon=true,$use_menu=true) {
		$mno = $mno == '' ? (isset($_SESSION['logged']) == true ? $_SESSION['logged'] : 0) : $mno;
		$data = $this->GetMemberInfo($mno);

		$sHTML = '';
		if ($use_menu == true) {
			$sHTML.= '<span class="iModuleMemberMenu" idx="'.$data['idx'].'" email="'.$data['email'].'" homepage="'.$data['homepage'].'" isMemberMenu="TRUE">';
		}
		if ($use_nickcon == true && file_exists($_ENV['userfilePath'].'/member/nickcon/'.$mno.'.gif') == true) {
			$sHTML.= '<img src="'.$_ENV['userfileDir'].'/member/nickcon/'.$mno.'.gif" alt="'.$data[$type].'" isMemberMenu="TRUE" />';
		} else {
			$sHTML.= $data[$type];
		}
		if ($use_menu == true) {
			$sHTML.= '</span>';
		}
		
		return $sHTML;
	}

	function GetLevel($exp) {
		$level = $this->mDB->DBfetch($_ENV['table']['level'],array('lv','exp','next'),"where `exp`<=$exp",'lv,desc','0,1');
		$lastLevel = $this->mDB->DBfetch($_ENV['table']['level'],array('MAX(lv)'));
		$data['lv'] = $lastLevel[0] < $level['lv']+1 ? $lastLevel[0] : $level['lv']+1;
		$data['exp'] = $exp-$level['exp'];
		if ($data['lv'] == $lastLevel[0]) {
			$data['next'] = $data['exp'];
			$data['remain'] = 0;
		} else {
			$data['next'] = $level['next'] == 0 ? $exp-$level['exp'] : $level['next']-$level['exp'];
			$data['remain'] = $level['next']-$data['exp'];
		}
		return $data;
	}

	function GetMessage($mno='') {

	}

	function SendExp($mno,$exp) {
		if ($this->mDB->DBcount($_ENV['table']['member'],"where `idx`=$mno") == 1) {
			$this->mDB->DBupdate($_ENV['table']['member'],'',array('exp'=>'`exp`+'.$exp),"where `idx`=$mno");

			return true;
		} else {
			return false;
		}
	}

	function SendPoint($mno,$point,$msg='',$url='',$module='',$isAlways=false,$time='') {
		if ($point == 0) return true;
		$msg = $msg ? $msg : '포인트 '.($point > 0 ? '적립' : '차감');
		$time = $time ? $time : GetGMT();

		if ($this->mDB->DBcount($_ENV['table']['member'],"where `idx`=$mno") == 1) {
			if ($point < 0 && $isAlways == false) {
				$check = $this->GetMemberInfo($mno);
				if ($check['point'] < $point*-1) return false;
			}
			$this->mDB->DBupdate($_ENV['table']['member'],'',array('point'=>'`point`+'.$point),"where `idx`=$mno");
			$this->mDB->DBinsert($_ENV['table']['point'],array('mno'=>$mno,'point'=>$point,'msg'=>$msg,'url'=>$url,'module'=>$module,'reg_date'=>$time));

			return true;
		} else {
			return false;
		}
	}

	function SendMessage($to,$msg,$url='',$from='',$time='') {
		if ($from == '' && $this->IsLogged() == false) return;

		$member = $this->GetMemberInfo();
		$from = $from ? $from : $member['idx'];
		$time = $time ? $time : GetGMT();

		if (is_array($msg) == true) {
			$msg = serialize($msg);
			$isSystem = 'TRUE';
		} else {
			$isSystem = 'FALSE';
		}

		if ($from != '-1') {
			$this->mDB->DBinsert($_ENV['table']['message'],array('mno'=>$from,'frommno'=>$from,'tomno'=>$to,'message'=>$msg,'url'=>$url,'reg_date'=>$time,'is_read'=>'TRUE','is_system'=>$isSystem));
		}
		if ($from != $to) {
			$this->mDB->DBinsert($_ENV['table']['message'],array('mno'=>$to,'frommno'=>$from,'tomno'=>$to,'message'=>$msg,'url'=>$url,'reg_date'=>$time,'is_read'=>'FALSE','is_system'=>$isSystem));
		}
	}
}
?>