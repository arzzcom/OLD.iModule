<?php
class Keyword {
	function __construct() {
	}
	
	function GetFullTextKeyword($keyword,$field=array()) {
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			$field[$i] = '`'.$field[$i].'`';
		}

		$keylist = explode(' ',$keyword);
		for ($i=0, $loop=sizeof($keylist);$i<$loop;$i++) {
			$keylist[$i] = '\'+'.$keylist[$i].'*\'';
		}
		$keylist = implode(' ',$keylist);
		return "MATCH (".implode(',',$field).") AGAINST ($keylist IN BOOLEAN MODE)";
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