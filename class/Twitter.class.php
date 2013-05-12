<?php
if (!defined('TWITTER_ROOT')) {
	define('TWITTER_ROOT', dirname(__FILE__) . '/');
}

REQUIRE_ONCE TWITTER_ROOT.'/Twitter/OAuth.php';
REQUIRE_ONCE TWITTER_ROOT.'/Twitter/twitteroauth.php';

class Twitter {
	protected $CONSUMER_KEY;
	protected $CONSUMER_SECRET;
	protected $OAUTH_CALLBACK;
	protected $IsLogged = null;

	protected $connection = null;
	protected $accessConnection;

	function __construct($consumer_key,$consumer_secret,$oauth_callback='') {
		$this->CONSUMER_KEY = $consumer_key;
		$this->CONSUMER_SECRET = $consumer_secret;
		$this->OAUTH_CALLBACK = $oauth_callback;
	}
	
	function IsLogged() {
		if ($this->IsLogged != null) return $this->IsLogged;
		if (isset($_SESSION['access_token']) == true) {
			$this->IsLogged = true;
		} else {
			$this->IsLogged = false;
		}
		
		return $this->IsLogged;
	}

	function Login() {
		$this->Logout();
		$this->connection = new TwitterOAuth($this->CONSUMER_KEY,$this->CONSUMER_SECRET);
		$request_token = $this->connection->getRequestToken($this->OAUTH_CALLBACK);

		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

		switch ($this->connection->http_code) {
			case 200 :
				$url = $this->connection->getAuthorizeURL($token);
				header('Location: '.$url);
			break;

			default:
				echo 'Could not connect to Twitter. Refresh the page or try again later.';
		}
	}

	function Logout() {
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		unset($_SESSION['access_token']);
		unset($_SESSION['status']);
		unset($_SESSION['oauth_status']);
	}

	function CheckToken() {
		$loginCallback = isset($_SERVER['REDIRECT_URL']) == true && $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_SERVER['PHP_SELF'];
		$loginCallback.= GetQueryString(array('oauth_token'=>'','oauth_verifier'=>''),false,true);
		
		$result = true;
		if (isset($_SESSION['oauth_token']) == false) $result = false;
		if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) $result = false;

		if ($result == true) {
			$connection = new TwitterOAuth($this->CONSUMER_KEY,$this->CONSUMER_SECRET,$_SESSION['oauth_token'],$_SESSION['oauth_token_secret']);
			unset($_SESSION['oauth_token']);
			unset($_SESSION['oauth_token_secret']);
		
			if ($connection->http_code == 200 || true) {
				$_SESSION['access_token'] = $connection->getAccessToken($_REQUEST['oauth_verifier']);
				$_SESSION['status'] = 'verified';
				header('Location:'.$this->OAUTH_CALLBACK);
			} else {
				$_SESSION['status'] = 'oldtoken';
				echo 'NOT 200';
				$result = false;
			}
		}
		
		if ($result == false) {
			echo 'Login Try';
		}
	}

	function GetToken() {
		if ($this->IsLogged() == true) {
			$this->accessConnection = new TwitterOAuth($this->CONSUMER_KEY,$this->CONSUMER_SECRET,$_SESSION['access_token']['oauth_token'],$_SESSION['access_token']['oauth_token_secret']);
			return $this->accessConnection;
		}
	}
	
	function GetUser($user_id) {
		$url = 'http://api.twitter.com/1/users/show.xml?screen_name='.$user_id;
		$parseURL = parse_url($url);
		
		$host = isset($parseURL['host']) == true ? $parseURL['host'] : '';
		$port = isset($parseURL['port']) == true ? $parseURL['port'] : ($parseURL['scheme'] == 'https' ? '443' : '80');
		$path = isset($parseURL['path']) == true ? $parseURL['path'] : '';
		$query = isset($parseURL["query"]) == true ? $parseURL["query"] : '';
		$sendHeader = "GET {$path}?{$query} HTTP/1.0\r\n";
		$sendHeader.= "Host: $host\r\n";
		$sendHeader.= "User-Agent: {$_SERVER['USER_AGENT']}\r\n";
		$sendHeader.= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$sendHeader.= "Accept-Language: ko-kr,ko;q=0.8,en-us;q=0.5,en;q=0.3\r\n";
		$sendHeader.= "Accept-Charset: EUC-KR,utf-8;q=0.7,*;q=0.7\r\n";
		$sendHeader.= "Connection: close\r\n";
		$sendHeader.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$sendHeader.= "\r\n";
		
		$fp = fsockopen(($parseURL['scheme'] == 'https' ? 'ssl://' : '').$host,$port,$errno,$errstr,10);
		if (!$fp) {
			return false;
		} else {
			$body = false;
			$recvData = '';
			fwrite($fp,$sendHeader);
			while (!feof($fp)) {
				$recv = @fgets($fp,4096);
				if ($body == true) $recvData.= $recv;
				if ($recv == "\r\n") $body = true;
			}
			fclose($fp);
		}

		return new SimpleXMLElement($recvData);
	}
}
?>