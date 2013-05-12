<?php
class ModuleCoupon extends Module {
	protected $mode;
	protected $mTemplet;
	protected $skinPath;
	protected $skinDir;

	protected $idx;

	public $table;

	function __construct() {
		$this->table['item'] = $_ENV['code'].'_coupon_item_table';
		$this->table['user'] = $_ENV['code'].'_coupon_user_table';
		$this->userfile = '/userfile/coupon';
		$this->thumbneil = '/userfile/coupon/thumbneil';

		parent::__construct('coupon');

		$this->idx = Request('idx');
		$this->mode = Request('mode') ? Request('mode') : ($this->idx == null ? 'list' : 'view');

		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));

		$this->skinPath = $this->modulePath.'/templet';
		$this->skinDir = $this->moduleDir.'/templet';
	}

	// GET 변수 정리
	function GetQueryString($var=array(),$queryString='',$encode=true) {
		if (Request('keyword') == null) {
			$var['key'] = '';
			$var['keyword'] = '';
		}

		return GetQueryString($var,$queryString,$encode,true);
	}

	// 템플릿 출력
	function PrintTemplet() {
		$time = array('server'=>time(),'gmt'=>GetGMT());
		$this->link['page'] = $this->baseURL.$this->GetQueryString(array('p'=>'','mode'=>'list','idx'=>'')).'&amp;p=';
		$this->link['list'] = $this->baseURL.$this->GetQueryString(array('mode'=>'list','idx'=>''));
		$this->link['post'] = $this->baseURL.$this->GetQueryString(array('sort'=>'','dir'=>'','key'=>'','keyword'=>'','p'=>'','mode'=>'write','idx'=>''));
		$this->link['modify'] = $this->baseURL.$this->GetQueryString(array('mode'=>'modify'));
		$this->link['delete'] = $this->baseURL.$this->GetQueryString(array('mode'=>'delete'));
		$this->link['back'] = isset($_SERVER['HTTP_REFERER']) == true ? $_SERVER['HTTP_REFERER'] : '';

		$this->mTemplet->assign('member',$this->member);
		$this->mTemplet->assign('skinDir',$this->skinDir);
		$this->mTemplet->assign('moduleDir',$this->moduleDir);
		$this->mTemplet->assign('time',$time);
		$this->mTemplet->assign('link',$this->link);
		$this->mTemplet->PrintTemplet();
	}

	// 헤더출력
	function PrintHeader() {
		echo "\n".'<!-- Module Coupon Start -->'."\n";
		if ($_ENV['isHeaderIncluded'] == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/script/php2js.php"></script>'."\n";
		}
		if (CheckIncluded('ModuleCoupon') == false) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" />'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
			echo '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" title="style" />'."\n";
			echo '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";
		}
		echo '<div class="ModuleCoupon">'."\n";
	}

	// 푸터출력
	function PrintFooter() {
		echo "\n".'</div>'."\n";
		echo '<iframe name="buyFrame" style="display:none;"></iframe>';
		echo "\n".'<!-- Module Board End -->'."\n";
	}

	function PrintCoupon($mode='') {
		if ($this->module === false) return;

		$this->PrintHeader();

		switch ($mode ? $mode : $this->mode) {
			case 'list' :
				$this->PrintList();
			break;

			case 'mycoupon' :
				$this->PrintMyCoupon();
			break;
		}

		$this->PrintFooter();
	}

	// 목록출력
	function PrintList() {
		$category = Request('category');

		if ($category != null) $find = "where `category`=$category";
		else $find = '';

		$listnum = 10;
		$pagenum = 10;
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$totalcoupon = $this->mDB->DBcount($this->table['item'],$find);
		$totalpage = ceil($totalcoupon/$listnum) == 0 ? 1 : ceil($totalcoupon/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['item'],'*',$find,$orderer,$limiter);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['action'] = array();
			if ($this->mMember->IsLogged() == true) {
				$data[$i]['is_buy'] = $this->CheckUserCoupon($data[$i]['code']) == true ? 'TRUE' : 'FALSE';
			} else {
				$data[$i]['is_buy'] = 'FALSE';
			}
			$data[$i]['action']['buy'] = 'BuyCoupon(\''.$data[$i]['code'].'\',\''.$data[$i]['title'].'\',\''.number_format($data[$i]['point']).'\',\''.$data[$i]['is_buy'].'\')';
			$data[$i]['image'] = file_exists($_ENV['path'].$this->userfile.'/'.$data[$i]['idx'].'.gif') == true ? $_ENV['dir'].$this->userfile.'/'.$data[$i]['idx'].'.gif' : $this->moduleDir.'/images/noimage.gif';
		}

		$page = array();
		$startpage = floor(($p-1)/$pagenum)*$pagenum+1;
		$endpage = $startpage+$pagenum-1 > $totalpage ? $totalpage : $startpage+$pagenum-1;
		$prevpage = $startpage > $pagenum ? $startpage-$pagenum : false;
		$nextpage = $endpage < $totalpage ? $endpage+1 : false;
		$prevlist = $p > 1 ? $p-1 : false;
		$nextlist = $p < $endpage ? $p+1 : false;

		for ($i=$startpage;$i<=$endpage;$i++) {
			$page[] = $i;
		}

		$this->mTemplet = new Templet($this->skinPath.'/list.tpl');
		$this->mTemplet->assign('totalcoupon',$totalcoupon);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('p',$p);
		$this->mTemplet->assign('data',$data);

		return $this->PrintTemplet();
	}

	function PrintMyCoupon() {
		$find = "where `mno`={$this->member['idx']}";

		$listnum = 10;
		$pagenum = 10;
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$totalcoupon = $this->mDB->DBcount($this->table['user'],$find);
		$totalpage = ceil($totalcoupon/$listnum) == 0 ? 1 : ceil($totalcoupon/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['user'],'*',$find,$orderer,$limiter);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$coupon = $this->mDB->DBfetch($this->table['item'],array('idx','title','infor'),"where `code`='{$data[$i]['code']}'");

			$data[$i]['title'] = $coupon['title'];
			$data[$i]['infor'] = $coupon['infor'];
			$data[$i]['image'] = file_exists($_ENV['path'].$this->userfile.'/'.$coupon['idx'].'.gif') == true ? $_ENV['dir'].$this->userfile.'/'.$coupon['idx'].'.gif' : $this->moduleDir.'/images/noimage.gif';
		}

		$page = array();
		$startpage = floor(($p-1)/$pagenum)*$pagenum+1;
		$endpage = $startpage+$pagenum-1 > $totalpage ? $totalpage : $startpage+$pagenum-1;
		$prevpage = $startpage > $pagenum ? $startpage-$pagenum : false;
		$nextpage = $endpage < $totalpage ? $endpage+1 : false;
		$prevlist = $p > 1 ? $p-1 : false;
		$nextlist = $p < $endpage ? $p+1 : false;

		for ($i=$startpage;$i<=$endpage;$i++) {
			$page[] = $i;
		}

		$this->mTemplet = new Templet($this->skinPath.'/mycoupon.tpl');
		$this->mTemplet->assign('totalcoupon',$totalcoupon);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('p',$p);
		$this->mTemplet->assign('data',$data);

		return $this->PrintTemplet();
	}

	function GetCoupon($code) {
		return $this->mDB->DBfetch($this->table['item'],'*',"where `code`='$code'");
	}

	// 보유쿠폰수
	function GetUserCouponCount($code,$mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		return $this->mDB->DBcount($this->table['user'],"where `mno`=$mno and `code`='$code' and `is_used`='FALSE' and (`expire_date`=0 or `expire_date`>".GetGMT().")");
	}

	// 보유여부
	function CheckUserCoupon($code,$mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		return $this->mDB->DBcount($this->table['user'],"where `mno`=$mno and `code`='$code' and `is_used`='FALSE' and (`expire_date`=0 or `expire_date`>".GetGMT().")") > 0;
	}

	// 만료일자
	function CheckUserCouponExpire($code,$mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		$check = $this->mDB->DBfetch($this->table['user'],array('expire_date'),"where `mno`=$mno and `code`='$code'",'expire_date,desc','0,1');

		return isset($check['expire_date']) == true ? $check['expire_date'] : -1;
	}

	// 쿠폰사용
	function UseUserCoupon($code,$mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		$coupon = $this->mDB->DBfetch($this->table['user'],array('idx'),"where `mno`=$mno and `code`='$code' and `is_used`='FALSE'",'buy_date,asc','0,1');
		if (isset($coupon['idx']) == true) {
			$this->mDB->DBupdate($this->table['user'],array('is_used'=>'TRUE','use_date'=>GetGMT()),'',"where `idx`={$coupon['idx']}");
			return true;
		} else {
			return false;
		}
	}
}
?>