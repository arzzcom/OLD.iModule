<?php
class Crawler {
	public $agent;
	public $cookie;
	public $timeout;
	public $encode;

	function __construct() {
		$this->cookie = $_ENV['userfilePath'].'/temp/crawler.cookie.'.time().'.'.rand(10000,99999).'.txt';
		$this->agent = 'Mozilla/4.0 MSIE 8.0';
		$this->timeout = 30;
		$this->encode = '';
		$this->isDebug = false;
	}
	
	function SetEncode($encode) {
		$this->encode = $encode;
	}
	
	function SetCookieFile($cookie) {
		$this->cookie = $cookie;
	}
	
	function SetTimeout($timeout) {
		$this->timeout = $timeout;
	}

	function SetUserAgent($agent) {
		$this->agent = $agent;
	}

	function Login($url,$post=array()) {
		$parseURL = parse_url($url);

		$scheme = isset($parseURL['scheme']) == true ? $parseURL['scheme'] : '';
		$host = isset($parseURL['host']) == true ? $parseURL['host'] : '';
		$port = isset($parseURL['port']) == true ? $parseURL['port'] : ($parseURL['scheme'] == 'https' ? '443' : '80');
		$path = isset($parseURL['path']) == true ? $parseURL['path'] : '';
		$query = isset($parseURL["query"]) == true ? $parseURL["query"] : '';

		$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)';
		$curlsession = curl_init();
		if ($scheme == 'https') {
			curl_setopt($curlsession,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($curlsession,CURLOPT_SSLVERSION,3);
		}
		curl_setopt($curlsession,CURLOPT_URL,$url);
		curl_setopt($curlsession,CURLOPT_POST,1);
		curl_setopt($curlsession,CURLOPT_POSTFIELDS,$post);
		curl_setopt($curlsession,CURLOPT_REFERER,$url);
		curl_setopt($curlsession,CURLOPT_TIMEOUT,$this->timeout);
		curl_setopt($curlsession,CURLOPT_COOKIEJAR,$this->cookie);
		curl_setopt($curlsession,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curlsession);
		curl_close($curlsession);
		
		return $buffer;
	}

	function GetURLString($url) {
		$parseURL = parse_url($url);

		$scheme = isset($parseURL['scheme']) == true ? $parseURL['scheme'] : '';
		$host = isset($parseURL['host']) == true ? $parseURL['host'] : '';
		$port = isset($parseURL['port']) == true ? $parseURL['port'] : ($parseURL['scheme'] == 'https' ? '443' : '80');
		$path = isset($parseURL['path']) == true ? $parseURL['path'] : '';
		$query = isset($parseURL["query"]) == true ? $parseURL["query"] : '';

		$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)';
		$curlsession = curl_init();
		if ($scheme == 'https') {
			curl_setopt($curlsession,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($curlsession,CURLOPT_SSLVERSION,3);
		}
		curl_setopt($curlsession,CURLOPT_URL,$url);
		curl_setopt($curlsession,CURLOPT_POST,0);
		curl_setopt($curlsession,CURLOPT_USERAGENT,$this->agent);
		curl_setopt($curlsession,CURLOPT_REFERER,$url);
		curl_setopt($curlsession,CURLOPT_TIMEOUT,$this->timeout);
		if (file_exists($this->cookie) == true) {
			curl_setopt($curlsession,CURLOPT_COOKIEFILE,$this->cookie);
		}

		curl_setopt($curlsession,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curlsession);
		$cinfo = curl_getinfo($curlsession);
		curl_close($curlsession);
		
		if ($cinfo['http_code'] != 200) return '';
		else return $this->GetUTF8($buffer);
	}

	function GetSubString($str,$start='',$end='',$find=0) {
		if ($start) {
			$temp = explode($start,$str);
			if (sizeof($temp) == 1) return '';
			for ($i=0;$i<=$find;$i++) {
				array_shift($temp);
			}
			$str = implode($start,$temp);
		}

		if ($end) {
			if (sizeof(explode($end,$str)) == 1) return '';
			$str = array_shift(explode($end,$str));
		}

		return $str;
	}

	function GetImageList($str) {
		if (preg_match_all("/<img [^<>]*src=(\'|\")?([^<>\n\"\']+)(\'|\")?[^<>]*>/i",$str,$match) > 0) {
			return $match[2];
		} else {
			return array();
		}
	}

	function GetFile($url,$referer='') {
		$parseURL = parse_url($url);

		$scheme = isset($parseURL['scheme']) == true ? $parseURL['scheme'] : '';
		$host = isset($parseURL['host']) == true ? $parseURL['host'] : '';
		$port = isset($parseURL['port']) == true ? $parseURL['port'] : ($parseURL['scheme'] == 'https' ? '443' : '80');
		$path = isset($parseURL['path']) == true ? $parseURL['path'] : '';
		$query = isset($parseURL["query"]) == true ? $parseURL["query"] : '';

		$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)';
		$curlsession = curl_init();
		if ($scheme == 'https') {
			curl_setopt($curlsession,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($curlsession,CURLOPT_SSLVERSION,3);
		}
		curl_setopt($curlsession,CURLOPT_URL,$url);
		curl_setopt($curlsession,CURLOPT_POST,0);
		curl_setopt($curlsession,CURLOPT_USERAGENT,$this->agent);
		curl_setopt($curlsession,CURLOPT_REFERER,$referer ? $referer : $url);
		curl_setopt($curlsession,CURLOPT_TIMEOUT,$this->timeout);
		curl_setopt($curlsession,CURLOPT_FOLLOWLOCATION,true);
		if (file_exists($this->cookie) == true) {
			curl_setopt($curlsession,CURLOPT_COOKIEFILE,$this->cookie);
		}

		curl_setopt($curlsession,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curlsession);
		$cinfo = curl_getinfo($curlsession);
		curl_close($curlsession);
		if ($cinfo['http_code'] != 200 || preg_match('/text\/html/',$cinfo['content_type']) == true) {
			return '';
		}

		$filepath = $_ENV['userfilePath'].'/temp/'.time().'.'.rand(100000,999999).'.tmp';
		$fp = fopen($filepath,'w');
		fwrite($fp,$buffer);
		fclose($fp);

		if (file_exists($filepath) == false || filesize($filepath) == 0) {
			@unlink($filepath);
			$filepath = '';
		}

		return $filepath;
	}

	function GetThumbnail($imgPath,$thumbPath,$width,$height,$delete=false) {
		$result = true;
		$imginfo = @getimagesize($imgPath);
		$extName = $imginfo[2];

		switch($extName) {
			case '2' :
				$src = @ImageCreateFromJPEG($imgPath) or $result = false;
				$type = 'jpg';
				break;
			case '1' :
				$src = @ImageCreateFromGIF($imgPath) or $result = false;
				$type = 'gif';
				break;
			case '3' :
				$src = @ImageCreateFromPNG($imgPath) or $result = false;
				$type = 'png';
				break;
			default :
				$result = false;
		}

		if ($result == true) {
			if ($width == 0) {
				$width = ceil($height*$imginfo[0]/$imginfo[1]);
			}

			if ($height == 0) {
				$height = $width*$imginfo[1]/$imginfo[0];
			}

			$thumb = @ImageCreateTrueColor($width,$height);

			@ImageCopyResampled($thumb,$src,0,0,0,0,$width,$height,@ImageSX($src),@ImageSY($src)) or $result = false;

			// Change FileName
			if ($type=="jpg") {
				@ImageJPEG($thumb,$thumbPath,75) or $result = false;
			} elseif($type=="gif") {
				@ImageGIF($thumb,$thumbPath,75) or $result = false;
			} elseif($type=='png') {
				@imagePNG($thumb,$thumbPath) or $result = false;
			} else {
				$result = false;
			}
			@ImageDestroy($src);
			@ImageDestroy($thumb);
			@chmod($thumbPath,0755);
		}

		if ($delete == true) {
			@unlink($imgPath);
		}

		return $result;
	}

	function GetString($str) {
		$str = strip_tags($str);
		$str = preg_replace("/\r\n/"," ",$str);
		$str = str_replace("&nbsp;"," ",$str);
		$str = str_replace("&nbsp"," ",$str);
		$str = str_replace("\t"," ",$str);
		$str = str_replace("\n"," ",$str);
		$str = preg_replace("/[[:space:]]+/"," ",$str);
		$str = trim($str);

		return $str;
	}

	function GetUTF8($str) {
		if ($this->encode) {
			return @iconv($this->encode,'UTF-8//IGNORE',$str);
		}
		
		$encording = mb_detect_encoding($str,'EUC-KR,UTF-8,ASCII,EUC-JP,CP949,AUTO');
		
		if ($encording == '') {
			if (preg_match('/<meta (.*?)content="text\/html; charset=(.*?)"(.*?)>/i',$str,$match) == true) {
				$encording = $match[2] == 'KSC_5601' ? 'euc-kr' : $match[2];
			}
		}

		if ($encording == 'UTF-8' || $encording == '') {
			return $str;
		} else {
			return @iconv($encording,'UTF-8//IGNORE',$str);
		}
	}
}
?>