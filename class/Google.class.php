<?php
class Google {
	protected $site;
	protected $query;
	protected $keyword;
	protected $url;
	protected $language;
	protected $ie;
	protected $oe;
	protected $html;
	protected $listCount;
	protected $start;

	function __construct() {
		$this->ie = 'UTF-8';
		$this->oe = 'UTF-8';
		$this->url = 'http://www.google.com/search';
		$this->listCount = 20;
		$this->language = 'en';
		$this->start = 0;
	}

	function SetListCount($listCount) {
		$this->listCount = $listCount;
	}

	function SetSite($site) {
		$this->site = $site;
	}

	function SetKeyword($keyword) {
		$this->keyword = $keyword;
	}

	function SetPage($page) {
		$this->start = ($page-1)*$this->listCount;
	}

	function GetGoogleResult() {
		if ($this->html) return;

		$this->query = $this->site ? 'site:'.$this->site.' '.$this->keyword : $this->keyword;

		$url = $this->url.'?hl='.$this->language.'&ie='.$this->ie.'&oe='.$this->oe.'&num='.$this->listCount.'&start='.$this->start.'&q='.urlencode($this->query);
		$parseURL = parse_url($url);

		$sendHeader = "GET {$url} HTTP/1.0\r\n";
		$sendHeader.= "Host: {$parseURL['host']}\r\n";
		$sendHeader.= "User-Agent: Mozilla\r\n";
		$sendHeader.= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$sendHeader.= "Accept-Language: ko-kr,ko;q=0.8,en-us;q=0.5,en;q=0.3\r\n";
		$sendHeader.= "Accept-Charset: EUC-KR,utf-8;q=0.7,*;q=0.7\r\n";
		$sendHeader.= "Connection: close\r\n";
		$sendHeader.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$sendHeader.= "\r\n";

		$fp = fsockopen(($parseURL['scheme'] == 'https' ? 'ssl://' : '').$parseURL['host'],80,$errno,$errstr,10);
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

		$this->html = $recvData;
	}

	function GetResult() {
		$this->GetGoogleResult();
		$body = $this->html;

		$results = array();
		if (preg_match_all('/<li class="g"><h3 class="r"><a href="([^"]+)">(.*?)<\/a><\/h3>(.*?)<cite>(.*?)<\/cite>(.*?)<span class="st">(.*?)<\/span>/',$body,$match) == true) {
			for ($i=0, $loop=sizeof($match[0]);$i<$loop;$i++) {
				$result = array('title'=>$match[2][$i],'url'=>'http://'.$match[4][$i],'content'=>str_replace('<br>','',$match[6][$i]));
				$results[] = $result;
			}
		}

		return $results;
	}

	function GetResultCount() {
		$this->GetGoogleResult();
		$count = 0;
		if (preg_match('/About (.*?) results/',$this->html,$match) == true || preg_match('/about (.*?) results/',$this->html,$match) == true) {
			$count = str_replace(',','',trim($match[1]));
		}

		return $count;
	}
}
?>