<?php
function GetDefaultHeader($title='',$body='',$resource=array()) {
	REQUIRE $_ENV['path'].'/inc/header.inc.php';
}

function GetDefaultFooter() {
	REQUIRE $_ENV['path'].'/inc/footer.inc.php';
}

function CheckIncluded($code) {
	if (isset($_ENV['isIncluded'][$code]) == false || $_ENV['isIncluded'][$code] == false) {
		$_ENV['isIncluded'][$code] = true;
		return false;
	} else {
		return true;
	}
}

function HtmlFlush() {
	fastcgi_finish_request();
	flush();
}

function GetPermission($permission='true') {
	$member = &Member::instance()->GetMemberInfo();

	if ($member['type'] == 'ADMINISTRATOR') return true;
	$permission = str_replace(
		array('{$member.type}','{$member.level}','{$member.user_id}'),
		array($member['type'],$member['level']['lv'],$member['user_id']),
	$permission);

	if (@eval("return $permission;") == true) {
		return true;
	} else {
		return false;
	}
}

function ArzzEncoder($text,$key='') {
	if (isset($_ENV['key']) == false && $key == '') {
		$readFile = @file($_ENV['path'].'/config/key.conf.php') or die('Not Found key.conf.php File!!<br>Please Install Solution Again!!');
		$_ENV['key'] = trim($readFile[1]);
	}
	return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key ? $key : $_ENV['key'],$text,MCRYPT_MODE_ECB,''));
}

function ArzzDecoder($text,$key='') {
	if (isset($_ENV['key']) == false && $key == '') {
		$readFile = @file($_ENV['path'].'/config/key.conf.php') or die('Not Found key.conf.php File!!<br>Please Install Solution Again!!');
		$_ENV['key'] = trim($readFile[1]);
	}

	return mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key ? $key : $_ENV['key'],base64_decode($text),MCRYPT_MODE_ECB,'');
}

function Alertbox($msg,$code=0,$redirect=null,$target=null) {
	if ($_ENV['isHeaderIncluded'] == false) GetDefaultHeader('Error');

	if ($redirect) {
		$goUrl = $redirect;
		$goTarget = $target!=null ? $target.'.' : '';
	} else {
		$goUrl = '';
		$goTarget = '';
	}

	$print = '<script type="text/javascript">';
	if (is_array($redirect)==true) {
		$print.= 'if (confirm("'.$msg.'")==true) {';
		$print.= $goTarget.'location.href = "'.$redirect[0].'";';
		$print.= '} else {';
		$print.= $goTarget.'location.href = "'.$redirect[1].'";';
		$print.= '}';
	} else {
		if ($msg) $print.= 'alert("'.$msg.'");';
		switch ($code) {
			case 1 :
				$print.= ($target!=null ? $target.'.' : '').'history.go(-1);';
			break;

			case 2 :
				$print.= ($target!=null ? $target.'.' : '').'window.close();';
			break;

			case 3 :
				$print.= $goUrl!='reload' ? $goTarget.'location.href = "'.$goUrl.'";' : $goTarget.'location.href = '.$goTarget.'location.href;';
			break;

			case 5 :
				$print.= $goTarget.$goUrl;
			break;
			
			case 6 :
				$print.= ($target!=null ? $target.'.' : '').'opener.location.href = '.($target!=null ? $target.'.' : '').'opener.location.href;';
				$print.= ($target!=null ? $target.'.' : '').'window.close();';
			break;
		}
	}
	$print.= 'try { top.FormSubmitWaiting(false); } catch(e) {};';
	$print.= '</script>';

	echo $print;

	if ($code != 4) {
		if ($_ENV['isFooterIncluded'] == false) REQUIRE_ONCE $_ENV['path'].'/inc/footer.inc.php';
		exit;
	}
}

function Redirect($url,$target='') {
	$target = $target ? $target.'.' : '';
	echo '<script type="text/javascript">';
	if ($url == 'reload') {
		echo $target.'location.href = '.$target.'location.href;';
	} else {
		echo $target.'location.href = "'.$url.'";';
	}
	echo '</script>';
}

function Request($var,$type='request') {
	global $_REQUEST, $_SESSION;

	switch ($type) {
		case 'request' :
			$value = isset($_REQUEST[$var])==true ? (is_array($_REQUEST[$var]) == true ? $_REQUEST[$var] : $_REQUEST[$var]) : null;
		break;

		case 'session' :
			$value = isset($_SESSION[$var])==true ? $_SESSION[$var] : null;
		break;

		case 'cookie' :
			$value = isset($_COOKIE[$var])==true ? $_COOKIE[$var] : null;
		break;
	}

	if (is_array($value) == false) {
		$value = trim($value);
	}

	return $value;
}

function CreateDirectory($path) {
	$isSuccess = true;
	$serverPath = '';
	$dir = explode('/',str_replace($_SERVER['DOCUMENT_ROOT'].'/','',$path));
	for ($i=0, $total=sizeof($dir);$i<$total;$i++) {
		$serverPath.= '/'.$dir[$i];

		if (is_dir($_SERVER['DOCUMENT_ROOT'].$serverPath) == false) {
			@mkdir($_SERVER['DOCUMENT_ROOT'].$serverPath) or $isSuccess = false;
			@chmod($_SERVER['DOCUMENT_ROOT'].$serverPath,0707);
		}
	}

	return $isSuccess;
}

function RemoveDirectory($path) {
	$isSuccess = true;
	if (is_dir($path) == true) {
		$dir = @opendir($path);
		while ($name = @readdir($dir)) {
			if ($name != '.' && $name != '..') {
				if (is_dir($path.'/'.$name) == true) {
					$isSuccess = RemoveDirectory($path.'/'.$name);
				} else {
					@unlink($path.'/'.$name) or $isSuccess = false;
				}
				
				if ($isSuccess == false) break;
			}
		}
		@closedir($dir);
		if ($isSuccess == true) @rmdir($path) or $isSuccess = false;
	} else {
		$isSuccess = false;
	}
	
	return $isSuccess;
}

function GetRandomString($length,$type='ALL') {
	$alphabet = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	$numberic = array('1','2','3','4','5','6','7','8','9','0');
	$special = array('!','@','#','$','%','^','&','*','(',')','-','=');

	if ($type == 'ALPHA') $random = $alphabet;
	elseif ($type == 'ALPHANUMBER') $random = array_merge($alphabet,$numberic);
	elseif ($type == 'NUMBER') $random = $numberic;
	else $random = array_merge($alphabet,$numberic,$special);

	$randomString = '';
	for ($i=0;$i<$length;$i++) {
		$randomString.= $random[array_rand($random)];
	}

	return $randomString;
}

function GetUTF8($str) {
	$encording = mb_detect_encoding($str,'EUC-KR,UTF-8,ASCII,EUC-JP,AUTO');

	if ($encording=='UTF-8') {
		return $str;
	} else {
		$encording = isset($encording)==false || !$encording ? 'euc-kr' : $encording;
		return @iconv($encording,'UTF-8//IGNORE',$str);
	}
}

function GetAntiAIRParams($str) {
	$str = str_replace(array('/','.','=','+'),array(':S:',':D:',':E:',':P:'),$str);
	return $str;
}

function GetUTF8Divide($str) {
	$arr_cho = array('ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ','ㅂ', 'ㅃ', 'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ','ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ');
	$arr_jung = array('ㅏ', 'ㅐ', 'ㅑ', 'ㅒ', 'ㅓ', 'ㅔ', 'ㅕ','ㅖ', 'ㅗ', 'ㅘ', 'ㅙ', 'ㅚ', 'ㅛ', 'ㅜ','ㅝ', 'ㅞ', 'ㅟ', 'ㅠ', 'ㅡ', 'ㅢ', 'ㅣ');
	$arr_jong = array('', 'ㄱ', 'ㄲ', 'ㄳ', 'ㄴ', 'ㄵ', 'ㄶ','ㄷ', 'ㄹ', 'ㄺ', 'ㄻ', 'ㄼ', 'ㄽ', 'ㄾ','ㄿ', 'ㅀ', 'ㅁ', 'ㅂ', 'ㅄ', 'ㅅ', 'ㅆ','ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ');

	$unicode = array();
	$values = array();
	$lookingFor = 1;

	for ($i=0, $loop=strlen($str);$i<$loop;$i++) {
		$thisValue = ord($str[$i]);

		if ($thisValue < 128) {
			$unicode[] = $thisValue;
		} else {
			if (count($values) == 0) $lookingFor = $thisValue < 224 ? 2 : 3;
			$values[] = $thisValue;

			if (count($values) == $lookingFor) {
				$number = $lookingFor == 3 ? (($values[0]%16)*4096)+(($values[1]%64)*64)+($values[2]%64) : (($values[0]%32)*64)+($values[1]%64);
				$unicode[] = $number;
				$values = array();
				$lookingFor = 1;
			}
		}
	}

	$explodeStr = '';
	while (list($key,$code) = each($unicode)) {
		if ($code >= 44032 && $code <= 55203) {
			$temp = $code-44032;

			$cho = (int)($temp/21/28);
			$jung = (int)(($temp%(21*28)/28));
			$jong = (int)($temp%28);

			$explodeStr.= $arr_cho[$cho].$arr_jung[$jung].$arr_jong[$jong];
		} else {
			$temp = array($unicode[$key]);

			foreach ($temp as $ununicode) {
				if ($ununicode < 128) {
					$explodeStr.= chr($ununicode);
				} elseif ($ununicode < 2048) {
					$explodeStr.= chr(192+(($ununicode-($ununicode%64))/64));
					$explodeStr.= chr(128+($ununicode%64));
				} else {
					$explodeStr.= chr(224+(($ununicode-($ununicode%4096))/4096));
					$explodeStr.= chr(128+((($ununicode%4096)-($ununicode%64))/64));
					$explodeStr.= chr(128+($ununicode%64));
				}
			}
		}
	}
	$explodeStr = str_replace(' ','',$explodeStr);

	return $explodeStr;
}

function GetExtData($var) {
	$var = GetAjaxParam($var);

	$data = array();

	if (preg_match_all('/\{([^\{\}]+)\}/',$var,$rows) == true) {
		for ($i=0, $loop=sizeof($rows[0]);$i<$loop;$i++) {
			$row = $rows[0][$i];

			if (preg_match_all('/\"([^\"]+)\":\"([^\"]*)\"/',$row,$cols) == true) {
				$col = array();
				for ($j=0, $loopj=sizeof($cols[0]);$j<$loopj;$j++) {
					$col[$cols[1][$j]] = $cols[2][$j];
				}
			}
			$data[$i] = $col;
		}
	}

	return $data;
}

function GetExtDataToArray($var) {
	$var = urldecode($var);
	$var = str_replace('#*plus*#','+',$var);

	$data = array();

	if (preg_match_all('/\{([^\{\}]+)\}/',$var,$rows) == true) {
		for ($i=0, $loop=sizeof($rows[0]);$i<$loop;$i++) {
			$row = $rows[0][$i];

			if (preg_match_all('/\"([^\"]+)\":\"([^\"]*)\"/',$row,$cols) == true) {
				$col = array();
				for ($j=0, $loopj=sizeof($cols[0]);$j<$loopj;$j++) {
					$col[$cols[1][$j]] = $cols[2][$j];
				}
			}
			$data[$i] = $col;
		}
	}

	return $data;
}

function GetArrayToExtData($arr) {
	$data = array();

	for ($i=0, $loop=sizeof($arr);$i<$loop;$i++) {
		$fields = array();
		foreach ($arr[$i] as $field=>$value) {
			$fields[] = '"'.$field.'":"'.GetString($value,'ext').'"';
		}
		$data[$i] = '{'.implode(',',$fields).'}';
	}

	return $data;
}

function GetArrayToExtXML($arr,$result=true) {
	$xml = '<message success="'.(sizeof($arr) > 0 && $result == true ? 'true' : 'false').'"><form>';
	foreach ($arr as $key=>$value) {
		$xml.= '<'.$key.'>'.GetString($value,'xml').'</'.$key.'>';
	}

	$xml.= '</form></message>';
	return $xml;
}

function GetExtDecoder($var) {
	$data = array();

	if (preg_match_all('/\{([^\{\}]+)\}/',$var,$rows) == true) {
		for ($i=0, $loop=sizeof($rows[0]);$i<$loop;$i++) {
			$row = $rows[0][$i];

			if (preg_match_all('/\"([^\"]+)\":\"([^\"]*)\"/',$row,$cols) == true) {
				$col = array();
				for ($j=0, $loopj=sizeof($cols[0]);$j<$loopj;$j++) {
					$col[$cols[1][$j]] = $cols[2][$j];
				}
			}
			$data[$i] = $col;
		}
	}

	return $data;
}

function GetBBCodeToHtml($str) {
	$str = str_replace('[B]','<b>',$str);
	$str = str_replace('[/B]','</b>',$str);
	$str = str_replace('[U]','<u>',$str);
	$str = str_replace('[/U]','</u>',$str);
	$str = str_replace('[I]','<i>',$str);
	$str = str_replace('[/I]','</i>',$str);


	$str = preg_replace('/\[COLOR=(#[a-zA-Z0-9]+)\]/','<span style="color:$1;">',$str);
	$str = str_replace('[/COLOR]','</span>',$str);

	return $str;
}

function GetAjaxParam($var) {
	$str = urldecode(stripslashes(Request($var)));
	$str = str_replace('#*plus*#','+',$str);

	return $str;
}

function GetRemoveEnterTab($str,$change=' ') {
	$str = preg_replace('/\r\n/',$change,$str);
	$str = str_replace("\n",$change,$str);
	$str = str_replace("\t",$change,$str);
	$str = preg_replace('/[[:space:]]+/',' ',$str);
	return trim($str);
}

function GetIndexingText($str) {
	$str = preg_replace('/<(P|p)>/',' <p>',$str);
	$str = strip_tags($str);
	$str = preg_replace('/&[a-z]+;/',' ',$str);
	$str = preg_replace('/\r\n/',' ',$str);
	$str = str_replace("\n",' ',$str);
	$str = str_replace("\t",' ',$str);
	$str = preg_replace('/[[:space:]]+/',' ',$str);
	return trim($str);
}

function GetNumberFormat($number) {
	$temp = explode('.',$number);
	if (sizeof($temp) == 2) {
		$dotlength = strlen($temp[1]);
	} else {
		$dotlength = 0;
	}
	return number_format($number,$dotlength,'.',',');
}

function GetString($str,$code) {
	switch ($code) {
		case 'ext' :
			$str = str_replace('\\','\\\\',$str);
			$str = str_replace('"','\"',$str);
			$str = str_replace("'",'\'',$str);
			$str = preg_replace('/\r\n/',' ',$str);
			$str = str_replace("\n",' ',$str);
			$str = str_replace("\t",' ',$str);
		break;

		case 'extreplace' :
			$str = str_replace('"','&quot;',$str);
			$str = str_replace("'",'&apos;',$str);
			$str = preg_replace('/\r\n/','[:line:]',$str);
			$str = str_replace("\n",'[:line:]',$str);
			$str = str_replace("\t",'[:tab:]',$str);
		break;

		case 'inputbox' :
			$str = str_replace('<','&lt;',$str);
			$str = str_replace('>','&gt;',$str);
			$str = str_replace('"','&quot;',$str);
			$str = str_replace("'",'\'',$str);
		break;
		
		case 'decode' :
			$str = str_replace('&lt;','<',$str);
			$str = str_replace('&gt;','>',$str);
			$str = str_replace('&#39;','\'',$str);
		break;

		case 'replace' :
			$str = str_replace('<','&lt;',$str);
			$str = str_replace('>','&gt;',$str);
			$str = str_replace('"','&quot;',$str);
		break;

		case 'xml' :
			$str = str_replace('&','&amp;',$str);
			$str = str_replace('<','&lt;',$str);
			$str = str_replace('>','&gt;',$str);
			$str = str_replace('"','&quot;',$str);
			$str = str_replace("'",'&apos;',$str);
		break;

		case 'default' :
			$allow = '<p>,<br>,<b>,<span>,<a>,<img>,<embed>,<i>,<u>,<strike>,<font>,<center>,<ol>,<li>,<ul>,<strong>,<em>,<div>,<table>,<tr>,<td>';
			$str = strip_tags($str, $allow);
		break;

		case 'delete' :
			$str = stripslashes($str);
			$str = strip_tags($str);
			$str = str_replace('&nbsp;','',$str);
			$str = str_replace('"','&quot;',$str);
		break;

		case 'encode' :
			$str = urlencode($str);
		break;
		
		case 'reg' :
			$str = str_replace('[','\[',$str);
			$str = str_replace(']','\]',$str);
			$str = str_replace('(','\(',$str);
			$str = str_replace(')','\)',$str);
			$str = str_replace('?','\?',$str);
			$str = str_replace('.','\.',$str);
			$str = str_replace('*','\*',$str);
			$str = str_replace('-','\-',$str);
			$str = str_replace('+','\+',$str);
			$str = str_replace('^','\^',$str);
			$str = str_replace('\\','\\\\',$str);
			$str = str_replace('$','\$',$str);
		break;
	}
	return $str;
}

function GetCutString($str,$limit,$is_html=false) {
	$str = strip_tags($str,'<b><span><strong><i><u><font>');
	$length = mb_strlen($str,'UTF-8');

	$tags = array();
	$htmlLength = 0;
	$countLength = 0;

	$tag = false;
	if ($is_html == true) {
		for ($i=0; $i<=$length && $countLength<$limit;$i++) {
			$LastStr = mb_substr($str,$i,1,'UTF-8');
			if ($LastStr == '<' && preg_match('/^(b|span|strong|i|u|font)+/i',mb_substr($str,$i+1,$length-$i,'UTF-8'),$matchs) == true) {
				$tag = true;
				$tempLength = mb_strlen($matchs[1]);
				$htmlLength = $htmlLength+$tempLength+1;
				$i = $i+$tempLength;
				$tags[] = $matchs[1];

				continue;
			}

			if ($LastStr == '<' && preg_match('/^\/(b|span|strong|i|u|font)+/i',mb_substr($str,$i+1,$length-$i,'UTF-8'),$matchs) == true) {
				$tag = true;
				$tempLength = mb_strlen($matchs[1]);
				$htmlLength = $htmlLength+$tempLength+2;
				$i = $i+$tempLength+1;

				if (strlen(array_search($matchs[1],$tags)) > 0) {
					$tags[array_search($matchs[1],$tags)] = '-1';
				}

				continue;
			}

			if ($tag == true && $LastStr == '>') {
				$tag = false;
				$htmlLength++;
				continue;
			}

			if ($tag == true) {
				$htmlLength++;
				continue;
			}

			if ($tag == false) {
				$countLength++;
			}

			if ($countLength > $limit) {
				break;
			}
		}

		$limit = $limit+$htmlLength;
	}

	$isCut = false;
	if ($length >= $limit) {
		$isCut = true;
		$str = mb_substr($str,0,$limit,"UTF-8");
	} else {
		$str = $str;
	}

	if (sizeof($tags) > 0) {
		$tags = array_reverse($tags);
		for ($i=0, $loop=sizeof($tags);$i<$loop;$i++) {
			if ($tags[$i] != '-1') $str.= '</'.$tags[$i].'>';
		}
	}

	if ($isCut == true) $str.= '...';

	return $str;
}

function GetQueryString($var=array(),$queryString='',$encode=true) {
	$arg = array();
	$queryString = $queryString ? $queryString : array_pop(explode('?',$_SERVER['REQUEST_URI']));
	$querys = explode('&',$queryString);

	for ($i=0, $total=sizeof($querys);$i<$total;$i++) {
		$temp = explode('=',$querys[$i]);
		if (isset($temp[1]) == true) {
			$arg[$temp[0]] = $temp[1];
		}
	}

	//replace
	foreach ($var as $key=>$value) {
		$arg[$key] = $value;
	}

	//sum
	$queryString = '';

	foreach ($arg as $key=>$value) {
		if (strlen($value) > 0) {
			$queryString.= $queryString == '' ? '?' : '&';
			$queryString .= $key."=".$value;
		}
	}

	if ($encode == true) $queryString = str_replace('&','&amp;',$queryString);

	return $queryString;
}

function GetFileDownload($path,$name='',$size='') {
	if (file_exists($path) == false) {
		Alertbox('파일을 찾을 수 없습니다.');
	}
	if (!$name) {
		$temp = explode('/',$path);
		$name = array_pop($temp);
	}
	if (!$size) {
		$size = filesize($path);
	}

	header("Cache-control: private");

	if (preg_match('/IE/',$_ENV['browser']) == true || preg_match('/OP/',$_ENV['browser']) == true) {
		Header("Content-type:application/octet-stream");
		Header("Content-Length:".$size);
		Header("Content-Disposition:attachment;filename=".iconv('UTF-8','CP949//IGNORE',str_replace(' ','_',$name)));
		Header("Content-Transfer-Encoding:binary");
		header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
		Header("Pragma:no-cache");
		Header("Expires:0");
		Header("Connection:close");
	} else {
		Header("Content-type:".GetFileMime($name));
		Header("Content-Length:".$size);
		Header("Content-Disposition:attachment; filename=".str_replace(' ','_',$name));
		Header("Content-Description:PHP3 Generated Data");
		header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
		Header("Pragma: no-cache");
		Header("Expires: 0");
		Header("Connection:close");
	}

	$fp = fopen($path,'rb');
	while(!feof($fp)) {
		echo fread($fp,1024*1024);
		sleep(1);
		flush();
	}
	fclose($fp);
	exit;
}

function GetFileSize($filesize,$format='%0.2f') {
	if (!$filesize) return '0B';
	if($filesize < 1024) {
		return $filesize.'B';
	} else if($filesize < 1048576) {
		return sprintf($format,$filesize/1024).'KiB';
	} else if ($filesize < 1073741824) {
		return sprintf($format,$filesize/1048576).'MiB';
	} else if ($filesize < 1099511627776) {
		return sprintf($format,$filesize/1073741824).'GiB';
	} else {
		return sprintf($format,$filesize/1099511627776).'TiB';
	}
}

function GetFileExec($name) {
	return strtolower(array_pop(explode('.',$name)));
}

function GetFileMime($name) {
	$exec = GetFileExec($name);
	$mime_type = array();
	$mime_type['dwg']='application/acad';
	$mime_type['ccad']='application/clariscad';
	$mime_type['dxf']='application/dxf';
	$mime_type['mdb']='application/msaccess';
	$mime_type['doc']='application/msword';
	$mime_type['bin']='application/octet-stream';
	$mime_type['pdf']='application/pdf';
	$mime_type['ai']='application/postscript';
	$mime_type['ps']='application/postscript';
	$mime_type['eps']='application/postscript';
	$mime_type['rtf']='application/rtf';
	$mime_type['rtf']='application/rtf';
	$mime_type['xls']='application/vnd.ms-excel';
	$mime_type['ppt']='application/vnd.ms-powerpoint';
	$mime_type['cdf']='application/x-cdf';
	$mime_type['csh']='application/x-csh';
	$mime_type['csh']='application/x-csh';
	$mime_type['dvi']='application/x-dvi';
	$mime_type['js']='application/x-javascript';
	$mime_type['latex']='application/x-latex';
	$mime_type['mif']='application/x-mif';
	$mime_type['xlsx']='application/x-msexcel';
	$mime_type['ppt']='application/x-mspowerpoint';
	$mime_type['tcl']='application/x-tcl';
	$mime_type['tex']='application/x-tex';
	$mime_type['texinfo']='application/x-texinfo';
	$mime_type['texi']='application/x-texinfo';
	$mime_type['t']='application/x-troff';
	$mime_type['tr']='application/x-troff';
	$mime_type['roff']='application/x-troff';
	$mime_type['man']='application/x-troff-man';
	$mime_type['me']='application/x-troff-me';
	$mime_type['ms']='application/x-troff-ms';
	$mime_type['src']='application/x-wais-source';
	$mime_type['zip']='application/zip';
	$mime_type['au']='audio/basic';
	$mime_type['snd']='audio/basic';
	$mime_type['aif']='audio/x-aiff';
	$mime_type['aiff']='audio/x-aiff';
	$mime_type['aifc']='audio/x-aiff';
	$mime_type['wav']='audio/x-wav';
	$mime_type['gif']='image/gif';
	$mime_type['ief']='image/ief';
	$mime_type['jpeg']='image/jpeg';
	$mime_type['jpg']='image/jpeg';
	$mime_type['jpe']='image/jpeg';
	$mime_type['tiff']='image/tiff';
	$mime_type['tif']='image/tiff';
	$mime_type['png']='image/png';
	$mime_type['ras']='image/x-cmu-raster';
	$mime_type['pnm']='image/x-portable-anymap';
	$mime_type['pbm']='image/x-portable-bitmap';
	$mime_type['pgm']='image/x-portable-graymap';
	$mime_type['ppm']='image/x-portable-pixmap';
	$mime_type['rgb']='image/x-rgb';
	$mime_type['xbm']='image/x-xbitmap';
	$mime_type['xpm']='image/x-xpixmap';
	$mime_type['xwd']='image/x-xwindowdump';
	$mime_type['gzip']='multipart/x-gzip';
	$mime_type['zip']='multipart/x-zip';
	$mime_type['css']='text/css';
	$mime_type['html']='text/html';
	$mime_type['htm']='text/html';
	$mime_type['txt']='text/plain';
	$mime_type['rtx']='text/richtext';
	$mime_type['tsv']='text/tab-separated-values';
	$mime_type['xml']='text/xml';
	$mime_type['etx']='text/x-setext';
	$mime_type['xsl']='text/xsl';
	$mime_type['mpeg']='video/mpeg';
	$mime_type['mpg']='video/mpeg';
	$mime_type['mpe']='video/mpeg';
	$mime_type['mov']='video/quicktime';
	$mime_type['qt']='video/quicktime';
	$mime_type['avi']='video/x-msvideo';
	$mime_type['movie']='video/x-sgi-movie';
	$mime_type['swf']='application/x-shockwave-flash';

	if(isset($mime_type[$exec])){
		return $_ENV['browser'] != 'IE' && $_ENV['browser'] != 'OP' ? str_replace('application','file',$mime_type[$exec]) : $mime_type[$exec];
	} else {
		return $_ENV['browser'] != 'IE' && $_ENV['browser'] != 'OP' ? 'file/unknown' : 'application/octet-stream';
	}
}

function GetFileType($name,$path='') {
	$type = null;

	if ($path && file_exists($path) == true) {
		$check = @getimagesize($path);

		if (in_array($check[2],array('1','2','3')) == true) {
			$type = 'IMG';
		} elseif ($check[2] == '4') {
			$type = 'MOV';
		} else {
			$type = 'ETC';
		}
	}

	if ($type == null || $type == 'ETC') {
		$exec = GetFileExec($name);

		if (in_array($exec,array('mp3','wav','mid')) == true) {
			$type = 'SND';
		} elseif (in_array($exec,array('jpg','jpeg','png','gif')) == true) {
			$type = $type == 'ETC' ? 'ETC' : 'IMG';
		} elseif (in_array($exec,array('mov','flv','mpg','avi','wmv','rm','mpeg','mkv','mp4')) == true) {
			$type = 'MOV';
		} elseif (in_array($exec,array('txt')) == true) {
			$type = 'TXT';
		} else {
			$type = 'ETC';
		}
	}

	return $type;
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

function GetJumin($jumin) {
	$jumin = str_replace('-','',$jumin);
	return substr($jumin,0,6).'-'.substr($jumin,6,7);
}

function GetPhoneNumber($phone) {
	$phone = str_replace('-','',$phone);

	if (substr($phone,0,2) == '02') {
		if (strlen($phone) == 10) {
			$value = substr($phone,0,2).'-'.substr($phone,2,4).'-'.substr($phone,6,4);
		} else {
			$value = substr($phone,0,2).'-'.substr($phone,2,3).'-'.substr($phone,5,4);
		}
	} else {
		if (strlen($phone) == 11) {
			$value = substr($phone,0,3).'-'.substr($phone,3,4).'-'.substr($phone,7,4);
		} else {
			$value = substr($phone,0,3).'-'.substr($phone,3,3).'-'.substr($phone,6,4);
		}
	}

	return $value;
}

// Check
function CheckJumin($jumin) {
	$jumin = str_replace('-','',$jumin);
	if (!$jumin || strlen($jumin) != 13 || is_numeric($jumin) == false) {
		return false;
	}

	$jumin1 = substr($jumin,0,6);
	$jumin2 = substr($jumin,6,7);
	$year = substr($jumin1,0,2);
	$month = (int)(substr($jumin1,2,2));
	$day = (int)(substr($jumin1,4,2));
	$gender = (int)(substr($jumin2,0,1));

	if ($month < 1 || $month > 12 || $day < 1 || $day > 31 || $gender < 1 || $gender > 8) {
		return false;
	}

	$year = ($gender == 1 || $gender == 2 || $gender == 5 || $gender == 6) ? '19'.$year : '20'.$year;

	if (checkdate($month,$day,$year) == false) {
		return false;
	}

	$n = 2;
	$sum = 0;
	for ($i=0;$i<12;$i++) {
		$sum+= (int)(substr($jumin,$i,1))*$n++;
		if ($n == 10) $n = 2;
	}

	$c = (int)(11-$sum%11);
	if ($c == 11) $c = 1;
	if ($c == 10) $c = 0;

	if ($c != (int)(substr($jumin,12,1))) {
		return false;
	} else {
		return true;
	}
}

function CheckCompanyNo($companyno) {
	$companyno = str_replace('-','',$companyno);
	if (!$companyno || strlen($companyno) != 10 || is_numeric($companyno) == false) {
		return false;
	}

	$companyno1 = substr($companyno,0,3);
	$companyno2 = substr($companyno,3,2);
	$companyno3 = substr($companyno,5,5);
	
	return true;
}

function CheckCompanyNumber($comany_number) {
	$comany_number = str_replace('-','',$comany_number);
	if (!$comany_number || strlen($comany_number) != 10 || is_numeric($comany_number) == false) {
		return false;
	} else {
		return true;
	}
}

function CheckPhoneNumber($phone) {
	$phone = str_replace('-','',$phone);

	if (is_numeric($phone) == false) {
		return false;
	}

	if (substr($phone,0,2) == '02') {
		if (strlen($phone) == 10 || strlen($phone) == 9) {
			return true;
		} else {
			return false;
		}
	} else {
		if (strlen($phone) == 11 || strlen($phone) == 10) {
			return true;
		} else {
			return false;
		}
	}
}

function CheckEmail($email) {
	return preg_match('/^[[:alnum:]]+([_.-\]\+[[:alnum:]]+)*[_.-]*@([[:alnum:]]+([.-][[:alnum:]]+)*)+.[[:alpha:]]{2,4}$/',$email);
}

function CheckUserID($user_id) {
	return preg_match('/^[a-zA-z]{1}[[:alnum:]]{4,20}$/',$user_id);
}

function CheckNickname($nickname) {
	return mb_strlen($nickname,'UTF-8') <= 15 && mb_strlen($nickname,'UTF-8') > 0;
}

function SaveAdminLog($module,$log,$link='') {
	$mDB = &DB::instance();
	$member = &Member::instance()->GetMemberInfo();
	$mDB->DBinsert($_ENV['table']['adminlog'],array('mno'=>$member['idx'],'module'=>$module,'log'=>$log,'link'=>$link,'reg_date'=>GetGMT()));
}

// Time
function GetGMT($time='',$timezone='') {
	$time = $time ? (is_numeric($time) == true ? $time : strtotime($time)) : time();
	$timezone = $timezone ? $timezone : date('O');
	$timezone = ((int)(substr($timezone,1,2))*60+(int)(substr($timezone,3,2)))*60*(substr($timezone,0,1) == '+' ? -1 : 1);
	return $time+$timezone;
}

function GetTime($format,$time='') {
	$time = $time ? $time : GetGMT();
	$time = $time+((int)(substr($_ENV['timezone'],1,2))*60+(int)(substr($_ENV['timezone'],3,2)))*60*(substr($_ENV['timezone'],0,1) == '+' ? 1 : -1);
	return date($format,$time);
}

function GetTimer($time) {
	$timerText = $time > 0 ? ' 후' : ' 전';
	$time = abs($time);

	if ($time >= 60) {
		$time =  floor($time/60);
	} else return $time.'초'.$timerText;

	if ($time >= 60) {
		$time =  floor($time/60);
	} else return $time.'분'.$timerText;

	if ($time >= 24) {
		$time =  floor($time/24);
	} else return $time.'시간'.$timerText;

	if ($time >= 7) {
		$time =  floor($time/7);
	} else return $time.'일'.$timerText;

	if ($time >= 4) {
		$time =  floor($time/4);
	} else return $time.'주일'.$timerText;

	if ($time >= 72) {
		return $time =  floor($time/60).'년'.$timerText;
	} else return $time.'달'.$timerText;
}

function GetMicrotime() {
	$microtimestmp = explode(" ",microtime());
	return $microtimestmp[0]+$microtimestmp[1];
}

function ExcelProgress($percent,$text) {
	echo '<script type="text/javascript">try { parent.ExcelConvertProgress('.$percent.',"'.$text.'"); } catch(e) {}</script>';
	ob_flush();
	flush();
}

function ExcelError($text) {
	echo '<script type="text/javascript">try { parent.ExcelError("'.$text.'"); } catch(e) {}</script>';
	exit;
}
?>