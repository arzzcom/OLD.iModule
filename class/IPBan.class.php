<?php
class IPBan {
	protected $mDB;
	function __construct() {
		$this->mDB = &DB::instance();
	}
	
	function &instance() {
		if(isset($GLOBALS['_IPBan_']) == false || !$GLOBALS['_IPBan_']) $GLOBALS['_IPBan_'] = new IPBan();
		return $GLOBALS['_IPBan_'];
	}
	
	function CheckIP($ip='') {
		$ip = $ip ? $ip : $_SERVER['REMOTE_ADDR'];
		
		$check = $this->mDB->DBfetch($_ENV['table']['ipban'],'*',"where `ip`='$ip'");
		if (isset($check['idx']) == true) {
			$result = array('result'=>true,'ip'=>$ip,'memo'=>$check['memo'],'reg_date'=>$check['reg_date']);
		} else {
			$result = array('result'=>false);
		}
		
		return $result;
	}
	
	
	function __destruct() {
	
	}
}
?>