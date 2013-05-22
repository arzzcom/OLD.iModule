<?php
REQUIRE_ONCE $_ENV['path'].'/module/email/class/class.phpmailer.php';

class ModuleEmail extends Module {
	public $table = array();

	protected $PHPMailer;
	protected $templet;
	protected $from;
	protected $to;
	protected $toList = array();
	protected $subject;
	protected $body;

	public $userfile;
	public $thumbnail;
	
	public function __construct($isSMTP=true) {
		$this->table['email'] = $_ENV['code'].'_email_table';
		$this->table['file'] = $_ENV['code'].'_email_file_table';
		$this->table['send'] = $_ENV['code'].'_email_send_table';
		$this->table['temp'] = $_ENV['code'].'_email_temp_table';
		$this->templet = '';

		parent::__construct('email');

		$this->PHPMailer = new PHPMailer();
		$this->PHPMailer->PluginDir = $this->modulePath.'/class/';

		if ($isSMTP == true && $this->module['smtp_server']) {
			$this->PHPMailer->IsSMTP();
			$this->PHPMailer->SMTPSecure = $this->module['smtp_secure'];
			$this->PHPMailer->Host = $this->module['smtp_server'];
			$this->PHPMailer->Port = $this->module['smtp_port'];

			if ($this->module['smtp_user'] && $this->module['smtp_password']) {
				$this->PHPMailer->SMTPAuth = true;
				$this->PHPMailer->Username = $this->module['smtp_user'];
				$this->PHPMailer->Password = $this->module['smtp_password'];
			}
		}

		$this->PHPMailer->IsHTML(true);
		$this->PHPMailer->Encoding = 'base64';
		$this->PHPMailer->CharSet = 'UTF-8';

		$this->PHPMailer->SetFrom($this->module['email'], '=?UTF-8?b?'.base64_encode($this->module['name']).'?=');
		$this->from = array($this->module['name'],$this->module['email']);
		
		$this->userfile = '/email';
		$this->thumbnail = '/email/thumbnail';
	}

	public function SetTemplet($templet) {

	}

	public function GetTemplet() {
		if ($this->templet) {

		} else {
			$templet = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style type="text/css">BODY, TH, TD, DIV, SPAN, P, INPUT {font-size:12px; line-height:17px;} BODY, DIV {text-align:justify;}</style></head><body>{$content}</body></html>';
		}

		return $templet;
	}

	public function SetFrom($email=null,$name=null) {
		if ($email != null && $name != null && $email) {
			$this->PHPMailer->SetFrom($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			if ($name) $this->from = array($name,$email);
			else $this->from = array('',$email);
		} elseif ($email != null && $email) {
			$this->PHPMailer->SetFrom($email, '=?UTF-8?b?'.base64_encode($this->module['name']).'?=');
			$this->from = array($this->module['name'],$email);
		} elseif ($name != null) {
			$this->PHPMailer->SetFrom($this->module['email'], '=?UTF-8?b?'.base64_encode($name).'?=');
			if ($name) $this->from = array($name,$this->module['email']);
			else $this->from = array('',$this->module['email']);
		}
	}

	public function SetContent($subject,$body,$isHTML=false) {
		if ($isHTML == false) {
			$body = nl2br($body);
		}

		$this->subject = $subject;
		$this->body = $body;
	}

	public function AddTo($email,$name='') {
		if ($name) {
			$this->PHPMailer->AddAddress($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->to = array($name,$email);
		} else {
			$this->PHPMailer->AddAddress($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->to = array('',$email);
		}
	}

	public function AddAttach($filename,$filepath) {
		if (file_exists($filepath) == false) $filepath = $_ENV['path'].$filepath;

		if (file_exists($filepath) == true && filesize($filepath) < 10*1024*1024) {
			$this->PHPMailer->AddAttachment($filepath,$filename);
		}
	}

	public function SendEmail($idx=null) {
		$templet = $this->GetTemplet();

		if ($idx == null) {
			$repto = $this->mDB->DBinsert($this->table['email'],array('subject'=>$this->subject,'body'=>$this->body));
			$idx = $this->mDB->DBinsert($this->table['send'],array('repto'=>$repto,'from'=>serialize($this->from),'to'=>serialize($this->to),'result'=>'WAIT'));
		}

		$this->PHPMailer->Subject = '=?UTF-8?b?'.base64_encode($this->subject).'?=';
		$this->PHPMailer->Body = str_replace('{$content}',$this->body.'<img src="http://'.$_SERVER['HTTP_HOST'].$this->moduleDir.'/exec/CheckEmail.do.php?idx='.$idx.'" style="width:1px; height:1px;" />',$templet);

		$result = $this->PHPMailer->Send();

		$this->mDB->DBupdate($this->table['send'],array('result'=>($result == true ? 'TRUE' : 'FALSE'),'send_date'=>GetGMT()),'',"where `idx`='$idx'");

		return $result;
	}
}
?>