<?php

/*************************************************************************
 * 작성일 : 2008-12-08
 * 기  능 : 너나우리에서 제공하는 SMS XML 웹서비스를 호출하는 클래스
 * NUSOAP을 이용함
 * 작성자: 너나우리 SMS 팀 (sms@goodinternet.co.kr)
 *************************************************************************/

class ModuleSMS extends Module {
	protected $user_id;
	protected $password;
	protected $headmsg;
	protected $defaultnumber;
	protected $longmsg;
	protected $smsmodule;

	public $table;

	function __construct() {
		parent::__construct('sms');

		$this->user_id = $this->module['user_id'];
		$this->password = $this->module['password'];
		$this->headmsg = $this->module['headmsg'];
		$this->defaultnumber = $this->module['defaultnumber'];
		$this->table['send'] = $_ENV['code'].'_sms_table';
		$this->longmsg = $this->module['longmsg'] == 'on' ? true : false;
		$this->smsmodule = $this->module['smsmodule'] != '' ? $this->module['smsmodule'] : 'youiwe';
		
		REQUIRE $this->modulePath.'/class/'.$this->smsmodule.'/'.$this->smsmodule.'.class.php';
	}

	function SendSMS($rcv_number,$sms_content,$snd_number='',$is_header=true) {
		if ($snd_number == '') $snd_number = $this->defaultnumber;

		$snd_number = str_replace("-","",$snd_number);
		$rcv_number = str_replace("-","",$rcv_number);

		$byte = 0;
		$content = $is_header == true && $this->headmsg ? $this->headmsg : '';
		$next_content = '';
		for ($i=0, $loop=mb_strlen($sms_content,'UTF-8');$i<$loop;$i++) {
			$thisStr = mb_substr($sms_content,$i,1,'UTF-8');

			if (strlen($thisStr) == 1) {
				$byte = $byte + 1;
			} else {
				$byte = $byte + 2;
			}

			if ($byte < 80) $content.= $thisStr;
			else $next_content.= $thisStr;
		}

		if ($this->smsmodule == 'youiwe') {
			$sms = new SMS();
			$result = $sms->SendSMS($this->user_id,$this->password,$snd_number,$rcv_number,$content);


			if ($result == '1') {
				$result = true;
			} else {
				$result = false;
			}

			$insert['mno'] = $this->member['idx'];
			$insert['sender'] = $snd_number;
			$insert['receiver'] = $rcv_number;
			$insert['content'] = $content;
			$insert['send_date'] = GetGMT();
			$insert['result'] = $result == true ? 'TRUE' : 'FALSE';

			$this->mDB->DBinsert($this->table['send'],$insert);

			if ($result == true && $this->longmsg == true && $next_content != '') return $this->SendSMS($rcv_number,$next_content,$snd_number,$is_header);
			else return $result;
		} else {
			return false;
		}
	}

	function GetCheckLength($str) {
		$byte = 0;
		for ($i=0, $loop=mb_strlen($str,'UTF-8');$i<$loop;$i++) {
			$thisStr = mb_substr($str,$i,1,'UTF-8');

			if (strlen($thisStr) == 1) {
				$byte = $byte + 1;
			} else {
				$byte = $byte + 2;
			}
		}

		return $byte;
	}

	function GetTotalCount($limit='') {
		$find = $limit ? "where `send_date`>".(GetGMT()-60*60*24) : '';
		return $this->mDB->DBcount($this->table['send'],$find);
	}

	function GetRemainCount() {
		if ($this->smsmodule == 'youiwe') {
			$sms = new SMS();
			$result = $sms->GetRemainCount($this->user_id,$this->password);
		}
		
		return $result;
	}
	
	function PrintSMSForm($skin='',$receive='') {
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/smsform/'.$skin.'/style.css" type="text/css" />'."\n";
		
		$mTemplet = new Templet($this->modulePath.'/templet/smsform/'.$skin.'/form.tpl');
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/smsform/'.$skin);
		$mTemplet->PrintTemplet();
	}
}
?>
