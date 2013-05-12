<?php
class Keyword {
	public $mDB;
	private $keyword;

	function __construct($keyword='') {
		$this->mDB = &DB::instance();
		$this->keyword = $keyword;
	}
	
	function KeywordHit() {
		$keycode = $this->GetUTF8Code($this->keyword);
		$engcode = $this->GetEngCode($keycode);

		if ($this->keyword) {
			$keyData = $this->mDB->DBfetch('iboard_keyword_table',array('idx'),"where `keycode`='$keycode'");
			if (isset($keyData['idx']) == true) {
				$this->mDB->DBupdate($_ENV['table']['keyword'],array('last_search'=>GetGMT()),array('hit'=>'`hit`-1'),"where `idx`='{$keyData['idx']}'");
			} else {
				$this->mDB->DBinsert($_ENV['table']['keyword'],array('keycode'=>$keycode,'engcode'=>$engcode,'keyword'=>$this->keyword,'hit'=>'0','last_search'=>GetGMT()));
			}
		}
	}

	function GetFullTextKeyword($field=array()) {
		$keycode = $this->GetUTF8Code($this->keyword);
		$engcode = $this->GetEngCode($keycode);

		if ($this->keyword) {
			$keyData = $this->mDB->DBfetch('iboard_keyword_table',array('idx'),"where `keycode`='$keycode'");
			if (isset($keyData['idx']) == true) {
				$this->mDB->DBupdate($_ENV['table']['keyword'],array('last_search'=>GetGMT()),array('hit'=>'`hit`-1'),"where `idx`='{$keyData['idx']}'");
			} else {
				$this->mDB->DBinsert($_ENV['table']['keyword'],array('keycode'=>$keycode,'engcode'=>$engcode,'keyword'=>$this->keyword,'hit'=>'0','last_search'=>GetGMT()));
			}
		}

		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			$field[$i] = '`'.$field[$i].'`';
		}

		$keylist = explode(' ',$this->keyword);
		for ($i=0, $loop=sizeof($keylist);$i<$loop;$i++) {
			$keylist[$i] = '\'+'.$keylist[$i].'*\'';
		}
		$keylist = implode(' ',$keylist);
		return "MATCH (".implode(',',$field).") AGAINST ($keylist IN BOOLEAN MODE)";
	}

	function GetEngCode($str) {
		$arr_kor = array('ㄱ','ㄲ','ㄴ','ㄷ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ','ㄳ','ㄵ','ㄶ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅄ','ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ');

		$arr_eng = array('r','R','s','e','f','a','q','Q','t','T','d','w','W','c','z','x','v','g','rt','sw','sg','fr','fa','fq','ft','fx','fv','fg','qt','k','o','i','O','j','p','u','P','h','hk','ho','hl','y','n','nj','np','nl','u','m','ml','l');

		$engcode = str_replace($arr_kor,$arr_eng,$str);

		return $engcode;
	}

	function GetUTF8Code($str) {
		$arr_cho = array('ㄱ','ㄲ','ㄴ','ㄷ','ㄸ','ㄹ','ㅁ','ㅂ','ㅃ','ㅅ','ㅆ','ㅇ','ㅈ','ㅉ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');
		$arr_jung = array('ㅏ','ㅐ','ㅑ','ㅒ','ㅓ','ㅔ','ㅕ','ㅖ','ㅗ','ㅘ','ㅙ','ㅚ','ㅛ','ㅜ','ㅝ','ㅞ','ㅟ','ㅠ','ㅡ','ㅢ','ㅣ');
		$arr_jong = array('','ㄱ','ㄲ','ㄳ','ㄴ','ㄵ','ㄶ','ㄷ','ㄹ','ㄺ','ㄻ','ㄼ','ㄽ','ㄾ','ㄿ','ㅀ','ㅁ','ㅂ','ㅄ','ㅅ','ㅆ','ㅇ','ㅈ','ㅊ','ㅋ','ㅌ','ㅍ','ㅎ');

		$unicode = array();
		$values = array();
		$lookingFor = 1;

		for ($i=0, $loop=strlen($str);$i<$loop;$i++) {
			$thisValue = ord($str[$i]);

			if ($thisValue < 128) {
				$unicode[] = $thisValue;
			} else {
				if (count($values)==0) $lookingFor = $thisValue < 224 ? 2 : 3;
				$values[] = $thisValue;
				if (count($values) == $lookingFor) {
					$number = $lookingFor == 3 ? (($values[0]%16)*4096)+(($values[1]%64)*64)+($values[2]%64) : (($values[0]%32)*64)+($values[1]%64);
					$unicode[] = $number;
					$values = array();
					$lookingFor = 1;
				}
			}
		}

		$splitStr = '';
		while (list($key,$code) = each($unicode)) {
			if ($code >= 44032 && $code <= 55203) {
				$temp = $code-44032;
				$cho = (int)($temp/21/28);
				$jung = (int)(($temp%(21*28)/28));
				$jong = (int)($temp%28);

				$splitStr.= $arr_cho[$cho].$arr_jung[$jung].$arr_jong[$jong];
			} else {
				$temp = array($unicode[$key]);

				foreach ($temp as $ununicode) {
					if ($ununicode < 128) {
						$splitStr.= chr($ununicode);
					} elseif ($ununicode < 2048) {
						$splitStr.= chr(192+(($ununicode-($ununicode%64))/64));
						$splitStr.= chr(128+($ununicode%64));
					} else {
						$splitStr.= chr(224+(($ununicode-($ununicode%4096))/4096));
						$splitStr.= chr(128+((($ununicode%4096)-($ununicode%64))/64));
						$splitStr.= chr(128+($ununicode%64));
					}
				}
			}
		}

		$splitStr = str_replace(' ','',$splitStr);
		return $splitStr;
	}

	function GetMatchKeyword($input,$keyword,$style) {
		$match = '';
		$isMatch = false;
		for ($i=0, $loop=mb_strlen($input,'UTF-8');$i<$loop;$i++) {
			if (mb_substr($input,$i,1,'UTF-8') == mb_substr($keyword,$i,1,'UTF-8')) {
				if ($isMatch == false) {
					$match.= '<span '.$style.'>'.mb_substr($keyword,$i,1,'UTF-8');
					$isMatch = true;
				} else {
					$match.= mb_substr($keyword,$i,1,'UTF-8');
				}
			} else {
				break;
			}
		}
		if ($isMatch == true) $match.= '</span>';
		$match.= mb_substr($keyword,$i,mb_strlen($keyword,'UTF-8'),'UTF-8');

		return $match;
	}
}
?>