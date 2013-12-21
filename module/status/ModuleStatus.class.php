<?php
class ModuleStatus extends Module {
	protected $mno;
	public $table;
	public $bot;

	function __construct() {
		$this->mDB = &DB::instance();
		$this->mno = Request('logged','session') != null ? Request('logged','session') : 0;
		$this->table['day'] = $_ENV['code'].'_status_day_table';
		$this->table['hour'] = $_ENV['code'].'_status_hour_table';
		$this->table['log_visit'] = $_ENV['code'].'_status_log_visit_table';
		$this->table['log_bot'] = $_ENV['code'].'_status_log_bot_table';
		$this->table['referer'] = $_ENV['code'].'_status_referer_table';
		$this->table['keyword'] = $_ENV['code'].'_status_keyword_table';
		
		$this->bot = array('Daumoa'=>'다음봇(Daumoa)','Googlebot'=>'구글봇(Googlebot)','Yeti'=>'네이버봇(Yeti)','bingbot'=>'Bing봇(bingbot)','Baiduspider'=>'바이두봇(Baiduspider)','Mediapartners-Google'=>'구글(GooglePartner)','checkprivacy'=>'한국인터넷진흥원(KISA)');
		
		parent::__construct('status');
	}
	
	function GetAllBotCode() {
		return array_keys($this->bot);
	}

	function GetBotName($botname) {
		return isset($this->bot[$botname]) == true ? $this->bot[$botname] : '기타('.$botname.')';
	}
	
	function GetQueryString($query,$get) {
		if (preg_match('/(\?|&)'.$get.'=([^&]+)/',$query,$match) == true) {
			return GetUTF8(urldecode($match[2]));
		} else {
			return '';
		}
	}
	
	function GetCount($date,$mode='visit') {
		$mode = in_array($mode,array('visit','pageview')) == true ? $mode : 'visit';
		$count = $this->mDB->DBfetch($this->table['day'],array($mode),"where `date`='$date'");
		return isset($count[$mode]) == true ? $count[$mode] : 0;
	}
	
	function IsBot() {
		if (preg_match('/(TurnitinBot|checkprivacy|NaverBot|Daumoa|Googlebot|msnbot|WebAuto|Yeti|bingbot|Mediapartners-Google|nagios-plugins|Ezooms|MJ12bot|WBSearchBot|Wizdata_Crawler|facebook|first|AhrefsBot|Baiduspider)/',$_SERVER['HTTP_USER_AGENT'],$match) == true) {
			return $match[1];
		} else {
			return false;
		}
	}

	function SaveStatus() {
		$date = date('Y-m-d');
		$hour = date('G');
		if ($this->IsBot() !== false) {
			$botcode = $this->IsBot();
			$isBot = true;
			if ($this->GetConfig('bot') == 'on') {
				if ($this->mDB->DBcount($this->table['log_bot'],"where `date`='$date' and `botname`='{$botcode}'") == 0) {
					$this->mDB->DBinsert($this->table['log_bot'],array('date'=>$date,'botname'=>$botcode,'visit'=>1,'first_time'=>GetGMT(),'last_time'=>GetGMT(),'last_url'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
				} else {
					$this->mDB->DBupdate($this->table['log_bot'],array('last_time'=>GetGMT(),'last_url'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),array('visit'=>'`visit`+1'),"where `date`='$date' and `botname`='$botcode'");
				}
			}
		} else {
			$isBot = false;
			if ($this->GetConfig('user') == 'on') {
				$this->mDB->DBinsert($this->table['log_visit'],array('date'=>$date,'visit_time'=>GetGMT(),'pageurl'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'refererurl'=>$_SERVER['HTTP_REFERER'],'ip'=>$_SERVER['REMOTE_ADDR'],'mno'=>$this->mno,'user_agent'=>$_SERVER['HTTP_USER_AGENT']));
			}
		}
		if ($isBot == false && Request('CheckVisitor','cookie') != $date && Request('CheckVisitor','session') != $date) {
			@setcookie('CheckVisitor', $date, time()+60*60*24, '/');
			$_SESSION['CheckVisitor'] = $date;
			$isVisitor = true;
			
			if (isset($_SERVER['HTTP_REFERER']) == true && $_SERVER['HTTP_REFERER'] && preg_match('/'.$_SERVER['HTTP_HOST'].'/',$_SERVER['HTTP_REFERER']) == false) {
				$insert = array();
				$insert['date'] = $date;
				$insert['refererurl'] = $_SERVER['HTTP_REFERER'];
				$insert['visit_time'] = GetGMT();
				$insert['ip'] = $_SERVER['REMOTE_ADDR'];
				
				$isKeyword = false;
				$parseReferer = parse_url($_SERVER['HTTP_REFERER']);
				if (preg_match('/naver.com/',$parseReferer['host']) == true && $this->GetQueryString($parseReferer['query'],'query')) {
					$isKeyword = $this->GetQueryString($parseReferer['query'],'query');
				} elseif (preg_match('/google/',$parseReferer['host']) == true && $this->GetQueryString($parseReferer['query'],'q')) {
					$isKeyword = $this->GetQueryString($parseReferer['query'],'q');
				} elseif (preg_match('/daum.net/',$parseReferer['host']) == true && $this->GetQueryString($parseReferer['query'],'q')) {
					$isKeyword = $this->GetQueryString($parseReferer['query'],'q');
				}
				
				$insert['visit_page'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				if ($isKeyword != false) {
					$insert['keyword'] = $isKeyword;
					
					if ($this->GetConfig('keyword') == 'on') {
						if ($this->mDB->DBcount($this->table['keyword'],"where `keyword`='$isKeyword'") > 0) {
							$this->mDB->DBupdate($this->table['keyword'],array('last_time'=>GetGMT(),'last_refererurl'=>$_SERVER['HTTP_REFERER'],'last_visit_page'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),array('visit'=>'`visit`+1'),"where `keyword`='$isKeyword'");
						} else {
							$this->mDB->DBinsert($this->table['keyword'],array('keyword'=>$isKeyword,'last_time'=>GetGMT(),'last_refererurl'=>$_SERVER['HTTP_REFERER'],'last_visit_page'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'visit'=>1));
						}
					}
				}
				
				if ($this->GetConfig('referer') == 'on') $this->mDB->DBinsert($this->table['referer'],$insert);
			}
		} else {
			$isVisitor = false;
		}

		if ($isBot == false) {
			if ($this->mDB->DBcount($this->table['day'],"where `date`='$date'") == 0) {
				$insert = array();
				$insert['date'] = $date;
				$insert['pageview'] = 1;
				if ($isVisitor == true) $insert['visit'] = 1;
				else $insert['visit'] = 0;
				$this->mDB->DBinsert($this->table['day'],$insert);
				$insert['hour'] = $hour;
				$this->mDB->DBinsert($this->table['hour'],$insert);
			} else {
				if ($isVisitor == true) {
					$this->mDB->DBupdate($this->table['day'],'',array('visit'=>'`visit`+1','pageview'=>'`pageview`+1'),"where `date`='$date'");
					if ($this->mDB->DBcount($this->table['hour'],"where `date`='$date' and `hour`=$hour") == 0) {
						$this->mDB->DBinsert($this->table['hour'],array('date'=>$date,'hour'=>$hour,'visit'=>1,'pageview'=>1));
					} else {
						$this->mDB->DBupdate($this->table['hour'],'',array('visit'=>'`visit`+1','pageview'=>'`pageview`+1'),"where `date`='$date' and `hour`=$hour");
					}
				} else {
					$this->mDB->DBupdate($this->table['day'],'',array('pageview'=>'`pageview`+1'),"where `date`='$date'");
					if ($this->mDB->DBcount($this->table['hour'],"where `date`='$date' and `hour`=$hour") == 0) {
						$this->mDB->DBinsert($this->table['hour'],array('date'=>$date,'hour'=>$hour,'pageview'=>1));
					} else {
						$this->mDB->DBupdate($this->table['hour'],'',array('pageview'=>'`pageview`+1'),"where `date`='$date' and `hour`=$hour");
					}
				}
			}
		}
	}
}
?>