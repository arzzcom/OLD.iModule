<?php
class ModuleYoutube extends Module {
	function __construct() {
		parent::__construct('youtube');
		
		//echo $this->module['youtubeapi'];
		
		$this->module['user_id'] = 'arzzcom@gmail.com';
		$this->module['password'] = 'google0270';
		
		$this->module['timeout'] = 30;
	}
	
	function Login() {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); 
		curl_setopt($curl, CURLOPT_HEADER, 0); 
		curl_setopt($curl, CURLOPT_POST, true); 
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
		curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/accounts/ClientLogin');
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'Email='.urlencode($this->module['user_id']).'&Passwd='.urlencode($this->module['password']).'&service=youtube&source=DEVARZZ');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_USERAGENT, 'siriniGetGmail');
		
		$result = curl_exec($curl);
		$err = curl_error($curl);
		
		curl_close($curl);
		
		$temp = explode("\n",$result);
		
		$login = array('Auth'=>substr($temp[2],5));
		
		return $login;
	}
	
	function Upload($filePath) {
		$login = $this->Login();
		
		
/*
POST /feeds/api/users/default/uploads HTTP/1.1
Host: uploads.gdata.youtube.com
Authorization: AuthSub token="DXAA...sdb8"
GData-Version: 2
X-GData-Key: key=adf15ee97731bca89da876c...a8dc
Slug: video-test.mp4
Content-Type: multipart/related; boundary="f93dcbA3"
Content-Length: 1941255
Connection: close

--f93dcbA3
Content-Type: application/atom+xml; charset=UTF-8

<?xml version="1.0"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yt="http://gdata.youtube.com/schemas/2007">
  <media:group>
    <media:title type="plain">Bad Wedding Toast</media:title>
    <media:description type="plain">
      I gave a bad toast at my friend's wedding.
    </media:description>
    <media:category scheme="http://gdata.youtube.com/schemas/2007/categories.cat">People</media:category>
    <media:keywords>toast, wedding</media:keywords>
  </media:group>
</entry>
--f93dcbA3
Content-Type: video/mp4
Content-Transfer-Encoding: binary

<Binary File Data>
--f93dcbA3--
*/

$file = '';
$fp = fopen($_ENV['path'].'/userfile/board/201204/9b5e2f5e6d41891e57475528376b96b4.1335103284.3997.mp4','rb');
while(!feof($fp)) {
	$file.= fread($fp,1024*1024);
}
fclose($fp);

		$sendData = "--f93dcbA3\r\n";
		$sendData.= "Content-Type: application/atom+xml; charset=UTF-8\r\n\r\n";
		$sendData.= "<?xml version=\"1.0\"?>\r\n";
		$sendData.= "<entry xmlns=\"http://www.w3.org/2005/Atom\" xmlns:media=\"http://search.yahoo.com/mrss/\" xmlns:yt=\"http://gdata.youtube.com/schemas/2007\">\r\n";
		$sendData.= "<media:group>\r\n";
		$sendData.= "<media:title type=\"plain\">Bad Wedding Toast</media:title>\r\n";
		$sendData.= "<media:description type=\"plain\">Test</media:description>\r\n";
		$sendData.= "<media:category scheme=\"http://gdata.youtube.com/schemas/2007/categories.cat\">People</media:category>\r\n";
		$sendData.= "<media:keywords>toast, wedding</media:keywords>\r\n";
		$sendData.= "</media:group>\r\n";
		$sendData.= "</entry>\r\n";
		$sendData.= "--f93dcbA3\r\n";
		$sendData.= "Content-Type: video/mp4\r\n";
		$sendData.= "Content-Transfer-Encoding: binary\r\n\r\n";
		$sendData.= $file."\r\n";
		$sendData.= "--f93dcbA3--";

		$sendHeader = "POST /feeds/api/users/default/uploads HTTP/1.1\r\n";
		$sendHeader.= "Host: uploads.gdata.youtube.com\r\n";
		$sendHeader.= "Authorization: GoogleLogin auth=".$login['Auth']."\r\n";
		$sendHeader.= "GData-Version: 2\r\n";
		$sendHeader.= "X-GData-Key: key=".$this->module['youtubeapi']."\r\n";
		$sendHeader.= "Slug: video-test.mp4\r\n";
		$sendHeader.= "Content-Type: multipart/related; boundary=\"f93dcbA3\"\r\n";
		$sendHeader.= "Content-Length: ".strlen($sendData)."\r\n";
		$sendHeader.= "Connection: close\r\n\r\n";
		$sendHeader.= $sendData;

		$fp = fsockopen('uploads.gdata.youtube.com',80,$errno,$errstr,$this->module['timeout']);
		if (!$fp) {
			return false;
		} else {
			fwrite($fp,$sendHeader);
			
			stream_set_blocking($fp,FALSE);
			stream_set_timeout($fp,$this->module['timeout']);
			$info = stream_get_meta_data($fp);

			while (!feof($fp) && !$info['timed_out']) {
				echo $recv = fgets($fp,2048);
				$info = stream_get_meta_data($fp);
				if ($info['timed_out'] == true) echo 'timeout!'."\n";
				
				if ($this->isDebug == true) echo $recv;
				if ($body == true) $recvData.= $recv;
				if ($recv == "\r\n") $body = true;
			}
			fclose($fp);
		}
		
		echo '<pre>';
		echo $recvData;
		echo '</pre>';
	}
}
?>