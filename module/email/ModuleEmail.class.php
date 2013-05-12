<?php
REQUIRE_ONCE $_ENV['path'].'/module/email/class/class.phpmailer.php';

class ModuleEmail extends Module {
	public $table = array();

	protected $PHPMailer;
	protected $templet;
	protected $from;
	protected $toList = array();
	protected $subject;
	protected $body;

	public function __construct($isSMTP=true) {
		$this->table['send'] = $_ENV['code'].'_email_table';
		$this->table['file'] = $_ENV['code'].'_email_file_table';
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
		$this->from = $this->module['name'].'<'.$this->module['email'].'>';
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
			if ($name) $this->from = $name.'<'.$email.'>';
			else $this->from = $email;
		} elseif ($email != null && $email) {
			$this->PHPMailer->SetFrom($email, '=?UTF-8?b?'.base64_encode($this->module['name']).'?=');
			$this->from = $this->module['name'].'<'.$email.'>';
		} elseif ($name != null) {
			$this->PHPMailer->SetFrom($this->module['email'], '=?UTF-8?b?'.base64_encode($name).'?=');
			if ($name) $this->from = $name.'<'.$this->module['email'].'>';
			else $this->from = $this->module['email'];
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
			$this->toList[] = $name.'<'.$email.'>';
		} else {
			$this->PHPMailer->AddAddress($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->toList[] = $email;
		}
	}

	public function AddBCC($email,$name='') {
		if ($name) {
			$this->PHPMailer->AddBCC($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->toList[] = $name.'<'.$email.'>';
		} else {
			$this->PHPMailer->AddBCC($email, '=?UTF-8?b?'.base64_encode($name).'?=');
			$this->toList[] = $email;
		}
	}

	public function AddAttach($filename,$filepath) {
		if (file_exists($filepath) == false) $filepath = $_ENV['path'].$filepath;

		if (file_exists($filepath) == true && filesize($filepath) < 10*1024*1024) {
			$this->PHPMailer->AddAttachment($filepath,$filename);
		}
	}

	public function SendEmail() {
		$templet = $this->GetTemplet();

		$idx = $this->mDB->DBinsert($this->table['send'],array('from'=>$this->from,'to'=>implode("\n",$this->toList),'subject'=>$this->subject,'body'=>$this->body,'send_date'=>GetGMT(),'result'=>'TRUE'));

		$this->PHPMailer->Subject = '=?UTF-8?b?'.base64_encode($this->subject).'?=';
		$this->PHPMailer->Body = str_replace('{$content}',$this->body.'<img src="http://'.$_SERVER['HTTP_HOST'].$this->moduleDir.'/exec/CheckEmail.do.php?idx='.$idx.'" style="width:1px; height:1px;" />',$templet);

		$result = $this->PHPMailer->Send();

		if ($result == false) {
			$this->mDB->DBupdate($this->table['send'],array('result'=>'FALSE'),'',"where `idx`=$idx");
		}

		return $result;
	}
}
?>