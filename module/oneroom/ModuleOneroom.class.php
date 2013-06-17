<?php
class ModuleOneroom extends Module  {
	public $table;
	public $baseURL;
	public $baseQueryString;
	protected $mTemplet;
	
	protected $isHeaderIncluded = false;
	protected $isFooterIncluded = false;
	protected $mode;
	protected $setup;
	protected $skinPath;
	protected $skinDir;
	protected $link;
	protected $find;

	function __construct() {
		$this->table['region'] = $_ENV['code'].'_oneroom_region_table';
		$this->table['category'] = $_ENV['code'].'_oneroom_category_table';
		$this->table['option'] = $_ENV['code'].'_oneroom_option_table';
		$this->table['subway'] = $_ENV['code'].'_oneroom_subway_table';
		$this->table['university'] = $_ENV['code'].'_oneroom_university_table';
		
		$this->table['item'] = $_ENV['code'].'_oneroom_item_table';
		$this->table['file'] = $_ENV['code'].'_oneroom_file_table';
		
		$this->table['premium_item'] = $_ENV['code'].'_oneroom_premium_item_table';
		$this->table['region_item'] = $_ENV['code'].'_oneroom_region_item_table';
		
		$this->table['slot'] = $_ENV['code'].'_oneroom_slot_table';
		$this->table['user_slot'] = $_ENV['code'].'_oneroom_user_slot_table';
		
		$this->table['prodealer'] = $_ENV['code'].'_oneroom_prodealer_table';
		$this->table['prodealer_default'] = $_ENV['code'].'_oneroom_prodealer_default_table';
		
		$this->table['log'] = $_ENV['code'].'_oneroom_log_table';
		
		$this->userfile = '/oneroom';
		$this->thumbnail = '/oneroom/thumbnail';
		
		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));
		$this->baseQueryString = sizeof(explode('?',$_SERVER['REQUEST_URI'])) > 1 ? array_pop(explode('?',$_SERVER['REQUEST_URI'])) : '';
		
		$this->skinDir = $this->skinPath = '';
		$this->link = array();
		
		parent::__construct('oneroom');
		
		$closing = $this->mDB->DBfetchs($this->table['item'],array('idx'),"where `is_open`='TRUE' and `end_date`>0 and `end_date`<".GetGMT());
		for ($i=0, $loop=sizeof($closing);$i<$loop;$i++) {
			$closing[$i] = $closing[$i]['idx'];
		}
		if (sizeof($closing) > 0) {
			$closing = implode(',',$closing);
			$this->mDB->DBupdate($this->table['item'],array('is_open'=>'FALSE','is_premium'=>'FALSE','is_regionitem'=>'FALSE','is_default_premium'=>'FALSE','is_default_regionitem'=>'FALSE'),'',"where `idx` IN ($closing)");
			$this->mDB->DBdelete($this->table['premium_item'],"where `ino` IN ($closing) and `type` IN ('SLOT','POINT')");
			$this->mDB->DBupdate($this->table['user_slot'],array('ino'=>'0'),'',"where `ino` IN ($closing)");
			$this->mDB->DBupdate($this->table['premium_item'],array('ino'=>'0'),'',"where `ino` IN ($closing)");
		}
	}
	
	function PrintManagerProgram($button,$width,$height) {
		$appID = 'kr.imodule.oneroom.manager';
		$appPath = 'http://www.imodule.kr/AIR/iModuleOneroomManager.air';
		$siteURL = GetAntiAIRParams('http://'.$_SERVER['HTTP_HOST'].$_ENV['dir']);
		$loginAuth = $this->mMember->IsLogged() == true ? ArzzEncoder(serialize(array('user_id'=>$this->member['user_id'],'password'=>$this->member['password'],'ip'=>$_SERVER['REMOTE_ADDR']))) : '';
		$loginAuth = GetAntiAIRParams($loginAuth);
		
		echo '<script type="text/javascript">GetEmbed("InstallAIR'.rand(100000,999999).time().'","'.$_ENV['dir'].'/flash/RunAIR.swf",'.$width.','.$height.',"buttonPath='.$button.'&appID='.$appID.'&appPath='.$appPath.'&siteURL='.$siteURL.'&loginAuth='.$loginAuth.'");</script>';
	}
	
	function PrintTemplet() {
		$time = array('server'=>time(),'gmt'=>GetGMT());

		$this->mTemplet->assign('member',$this->member);
		$this->mTemplet->assign('moduleDir',$this->moduleDir);
		$this->mTemplet->assign('thumbnailDir',$_ENV['dir'].$this->thumbnail);
		$this->mTemplet->assign('time',$time);
		
		if ($this->skinDir) $this->mTemplet->assign('skinDir',$this->skinDir);
		if (sizeof($this->link) > 0) $this->mTemplet->assign('link',$this->link);
		$this->mTemplet->PrintTemplet();
	}
	
	function PrintHeader() {
		if ($this->isHeaderIncluded == true) return;
		
		if ($_ENV['isHeaderIncluded'] == false) {
			GetDefaultHeader($this->setup['title']);
		}

		echo "\n".'<!-- Module Oneroom Start -->'."\n";
		if ($this->isHeaderIncluded == false) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" />'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
		}
		$this->isHeaderIncluded = true;

		if ($this->mode != 'list' && CheckIncluded('wysiwyg') == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/module/wysiwyg/script/wysiwyg.js"></script>'."\n";
		}

		echo '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" title="style" />'."\n";
		echo '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";
		echo '<div class="ModuleOneroom">'."\n";
	}
	
	// 푸터출력
	function PrintFooter() {
		if ($this->isFooterIncluded == true) return;
		$this->isFooterIncluded = true;

		echo "\n".'</div>'."\n";
		echo "\n".'<!-- Module Oneroom End -->'."\n";
	}
	
	function PrintOneroom($find='') {
		if ($this->module === false) return;
		
		$this->setup = array('skin'=>'default','width'=>'100%','listnum'=>10,'pagenum'=>11);
		
		$this->find = $find ? $find : "where 1";
		$this->mode = Request('mode') ? Request('mode') : 'list';
		$this->skinDir = $this->moduleDir.'/templet/oneroom/'.$this->setup['skin'];
		$this->skinPath = $this->modulePath.'/templet/oneroom/'.$this->setup['skin'];
		
		$this->link['page'] = $this->baseURL.$this->GetQueryString(array('mode'=>'list','idx'=>'','p'=>'')).'&amp;p=';

		$this->PrintHeader();

		switch ($this->mode) {
			case 'list' :
				$this->PrintList();
			break;

			case 'view' :
				$this->PrintView();
			break;
		}

		$this->PrintFooter();
	}
	
	function PrintList() {
		if ($this->GetPermission('list') == false) return $this->PrintError('목록을 볼 수 있는 권한이 없습니다.');

		$category = Request('category');
		$select = Request('select');
		$find = $this->find;
		
		$searchOption = Request('search_option');
		
		if ($searchOption == 'default') {
			$region1 = $this->GetZeroValue('region1');
			$region2 = $this->GetZeroValue('region2');
			$region3 = $this->GetZeroValue('region3');
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
		
			$priceType = Request('price_type');
			if ($priceType == '1') $find.= " and `is_buy`='TRUE'";
			elseif ($priceType == '2') $find.= " and `is_rent_all`='TRUE'";
			elseif ($priceType == '3') $find.= " and (`is_rent_month`='TRUE' or `is_rent_short`='TRUE')";
			
			$price1 = Request('price1') != null ? explode('-',Request('price1')) : array('','');
			$price2 = Request('price2') != null ? explode('-',Request('price2')) : array('','');
			
			if ($price1[0] || $price1[1]) {
				if ($priceType == '1') {
					if ($price1[0]) $find.= " and `price_buy`>='{$price1[0]}'";
					if ($price1[1]) $find.= " and `price_buy`<='{$price1[1]}'";
				} elseif ($priceType == '2') {
					if ($price1[0]) $find.= " and `price_rent_all`>='{$price1[0]}'";
					if ($price1[1]) $find.= " and `price_rent_all`<='{$price1[1]}'";
				} elseif ($priceType == '3') {
					if ($price1[0]) $find.= " and `price_rent_deposit`>='{$price1[0]}'";
					if ($price1[1]) $find.= " and `price_rent_deposit`<='{$price1[1]}'";
					if ($price2[0]) $find.= " and `price_rent_month`>='{$price2[0]}'";
					if ($price2[1]) $find.= " and `price_rent_month`<='{$price2[1]}'";
				}
			}
		} elseif ($searchOption == 'detail') {
			$searchType = Request('search_type') ? Request('search_type') : 'region';
			if ($searchType == 'region') {
				$region1 = $this->GetZeroValue('region1');
				$region2 = $this->GetZeroValue('region2');
				$region3 = $this->GetZeroValue('region3');
				if ($region1 != null) $find.= " and `region1`='$region1'";
				if ($region2 != null) $find.= " and `region2`='$region2'";
				if ($region3 != null) $find.= " and `region3`='$region3'";
			} elseif ($searchType == 'university') {
				$university_parent = Request('university_parent');
				$university_idx = Request('university_idx');
				
				if ($university_parent) {
					if (!$university_idx) {
						$university = $this->mDB->DBfetchs($this->table['university'],array('idx'),"where `parent`='$university_parent'");
						$temp = array();
						for ($i=0, $loop=sizeof($university);$i<$loop;$i++) $temp[] = $university[$i]['idx'];
						$university_idx = implode(',',$temp);
					}
					
					$find.= " and `university` IN ($university_idx)";
				}
			} elseif ($searchType == 'subway') {
				$subway_parent = Request('subway_parent');
				$subway_idx = Request('subway_idx');
				
				if ($subway_parent) {
					if (!$subway_idx) {
						$subway = $this->mDB->DBfetchs($this->table['subway'],array('idx'),"where `parent`='$subway_parent'");
						$temp = array();
						for ($i=0, $loop=sizeof($subway);$i<$loop;$i++) $temp[] = $subway[$i]['idx'];
						$subway_idx = implode(',',$temp);
					}
					
					$find.= " and `subway` IN ($subway_idx)";
				}
			}
			
			$priceFind = array();
			if (Request('is_buy') == 'TRUE') {
				$temp = "(`is_buy`='TRUE'";
				$price_buy1 = is_numeric(Request('price_buy1')) == true ? Request('price_buy1') : '';
				$price_buy2 = is_numeric(Request('price_buy2')) == true ? Request('price_buy2') : '';
				if ($price_buy1) $temp.= " and `price_buy`>=$price_buy1";
				if ($price_buy2) $temp.= " and `price_buy`<=$price_buy2";
				$temp.= ")";
				$priceFind[] = $temp;
			}
			
			if (Request('is_rent_all') == 'TRUE') {
				$temp = "(`is_rent_all`='TRUE'";
				$price_rent_all1 = is_numeric(Request('price_rent_all1')) == true ? Request('price_rent_all1') : '';
				$price_rent_all2 = is_numeric(Request('price_rent_all2')) == true ? Request('price_rent_all2') : '';
				if ($price_rent_all1) $temp.= " and `price_rent_all`>=$price_rent_all1";
				if ($price_rent_all2) $temp.= " and `price_rent_all`<=$price_rent_all12";
				$temp.= ")";
				$priceFind[] = $temp;
			}
			
			if (Request('is_rent_month') == 'TRUE') {
				$temp = "(`is_rent_month`='TRUE'";
				$price_rent_deposit1 = is_numeric(Request('price_rent_deposit1')) == true ? Request('price_rent_deposit1') : '';
				$price_rent_deposit2 = is_numeric(Request('price_rent_deposit2')) == true ? Request('price_rent_deposit2') : '';
				if ($price_rent_deposit1) $temp.= " and `price_rent_deposit`>=$price_rent_deposit1";
				if ($price_rent_deposit2) $temp.= " and `price_rent_deposit`<=$price_rent_deposit2";
				
				$price_rent_month1 = is_numeric(Request('price_rent_month1')) == true ? Request('price_rent_month1') : '';
				$price_rent_month2 = is_numeric(Request('price_rent_month2')) == true ? Request('price_rent_month2') : '';
				if ($price_rent_month1) $temp.= " and `price_rent_month`>=$price_rent_month1";
				if ($price_rent_month2) $temp.= " and `price_rent_month`<=$price_rent_month2";
				$temp.= ")";
				$priceFind[] = $temp;
			}
			
			if (Request('is_rent_short') == 'TRUE') {
				$temp = "(`is_rent_short`='TRUE'";
				$price_rent_month1 = is_numeric(Request('price_rent_month1')) == true ? Request('price_rent_month1') : '';
				$price_rent_month2 = is_numeric(Request('price_rent_month2')) == true ? Request('price_rent_month2') : '';
				if ($price_rent_month1) $temp.= " and `price_rent_month`>=$price_rent_month1";
				if ($price_rent_month2) $temp.= " and `price_rent_month`<=$price_rent_month2";
				$temp.= ")";
				$priceFind[] = $temp;
			}
			
			if (sizeof($priceFind) > 0) $find.= " and (".implode(' or ',$priceFind).")";
			
			if (Request('is_double') == 'TRUE') $find.= " and `is_double`='TRUE'";
			if (Request('is_under') == 'TRUE') $find.= " and `is_under`='TRUE'";
			if (Request('is_parkings') == 'TRUE') $find.= " and `parkings`>0";
		}
		
		$keyword = Request('keyword') ? urldecode(Request('keyword')) : '';

		if ($keyword != null) {
			$mKeyword = new Keyword($keyword);

			$keyQuery = $mKeyword->GetFullTextKeyword(array('title','search'));
			$find.= ' and '.$keyQuery;
		}
		
		$listnum = $this->setup['listnum'];
		$pagenum = $this->setup['pagenum'];
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;

		$totalitem = $this->mDB->DBcount($this->table['item'],$find);
		$totalpage = ceil($totalitem/$listnum) == 0 ? 1 : ceil($totalitem/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';

		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['item'],array('idx','region1','region2','region3','category1','category2','category3','title','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','price_maintenance','areasize','floor','is_under','is_double','subway','subway_distance','university','rooms','parkings','build_year','image'),$find,$orderer,$limiter);

		$loopnum = $totalitem-($p-1)*$listnum;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i] = $this->GetItemValue($data[$i]);
			$data[$i]['itemlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data[$i]['idx']));
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

		/*
		$searchFormStart = '<form name="ModuleBoardSearch" action="'.$this->baseURL.'" enctype="application/x-www-form-urlencoded">';

		if (isset($_SERVER['REDIRECT_URL']) == true) {
			$temp = explode('?',$_SERVER['REQUEST_URI']);
			$queryString = isset($temp[1]) == true ? $temp[1] : '';
			$querys = explode('&',$queryString);

			for ($i=0, $loop=sizeof($querys);$i<$loop;$i++) {
				$temp = explode('=',$querys[$i]);
				if (in_array($temp[0],array('key','keyword')) == false) $searchFormStart.= '<input type="hidden" name="'.$temp[0].'" value="'.GetString(Request($temp[0]),'inputbox').'" />';
			}
		} else {
			foreach ($_GET as $keyname=>$value) {
				if (in_array($keyname,array('key','keyword')) == false) $searchFormStart.= '<input type="hidden" name="'.$keyname.'" value="'.GetString(Request($keyname),'inputbox').'" />';
			}
		}
		foreach ($_POST as $keyname=>$value) {
			if (in_array($keyname,array('key','keyword')) == false) $searchFormStart.= '<input type="hidden" name="'.$keyname.'" value="'.GetString(Request($keyname),'inputbox').'" />';
		}
		$searchFormEnd = '</form>';
		*/

		/*
		$categoryName = '';
		$categoryList = array();
		if ($this->setup['use_category'] == 'TRUE') {
			$categoryList = $this->mDB->DBfetchs($this->table['category'],array('idx','category'),"where `bid`='{$this->bid}'",'order,asc');
			for ($i=0, $loop=sizeof($categoryList);$i<$loop;$i++) {
				if ($categoryList[$i]['idx'] == $category) $categoryName = $categoryList[$i]['category'];
			}
		}
		*/

		$this->mTemplet = new Templet($this->skinPath.'/list.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('totalitem',number_format($totalitem));
		$this->mTemplet->assign('totalpage',number_format($totalpage));
		
		$this->mTemplet->assign('keyword',$keyword);
		$this->mTemplet->assign('category',$category);
		$this->mTemplet->assign('select',$select);
		$this->mTemplet->assign('p',$p);


		//$this->mTemplet->register_object('mBoard',$this,array('GetSortLink','GetThumbnail'));
		$this->PrintTemplet();
	}
	
	function PrintView() {
		$idx = Request('idx');
		$data = $this->mDB->DBfetch($this->table['item'],'*',"where `idx`='$idx'");
		$default_image = $data['image'];
		if ($this->mDB->DBcount($this->table['log'],"where `repto`='$idx' and (`mno`={$this->member['idx']} or `ip`='".$_SERVER['REMOTE_ADDR']."')") == 0) {
			$this->mDB->DBupdate($this->table['item'],'',array('hit'=>'`hit`+1'),"where `idx`='$idx'");
			$this->mDB->DBinsert($this->table['log'],array('repto'=>$idx,'mno'=>($this->mMember->IsLogged() == true ? $this->member['idx'] : -1),'ip'=>$_SERVER['REMOTE_ADDR'],'reg_date'=>GetGMT()));
		}
		
		$this->SetHistory($idx);
		
		$data = $this->GetItemValue($data);
		
		$image = array();
		$images = $this->mDB->DBfetchs($this->table['file'],array('idx'),"where `repto`='$idx'");
		for ($i=0, $totalimage = sizeof($images);$i<$totalimage;$i++) {
			$images[$i]['thumbnail'] = file_exists($_ENV['userfilePath'].$this->thumbnail.'/'.$images[$i]['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->thumbnail.'/'.$images[$i]['idx'].'.thm' : '';
			$images[$i]['image'] = $this->moduleDir.'/exec/ShowImage.do.php?idx='.$images[$i]['idx'];
			if ($images[$i]['idx'] == $default_image) {
				array_unshift($image,$images[$i]);
			} else {
				array_push($image,$images[$i]);
			}
		}
		
		$itemOption = explode(',',$data['options']);
		$option = array();
		$options = $this->mDB->DBfetchs($this->table['option'],array('idx','title'),"where `parent`=0",'sort,asc');
		for ($i=0, $loop=sizeof($options);$i<$loop;$i++) {
			$selects = $this->mDB->DBfetchs($this->table['option'],array('idx','title'),"where `parent`={$options[$i]['idx']}",'sort,asc');
			for ($j=0, $loopj=sizeof($selects);$j<$loopj;$j++) {
				$selects[$j]['checked'] = in_array($selects[$j]['idx'],$itemOption);
			}
			$option[$i] = array('title'=>$options[$i]['title'],'select'=>$selects);
		}
		
		$mModule = new Module('map');
		if ($mModule->IsSetup() == true) {
			$mMap = new ModuleMap(array('enableWheelZoom'=>'false','mapMode'=>'1'));
			$mMap->SetMapTypeButton(true);
			$mMap->SetZoomControl(true);
			$mMap->SetAddress($data['address']);
			$map = $mMap->GetMap();
		} else {
			$map = '';
		}
		
		$this->mTemplet = new Templet($this->skinPath.'/view.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('totalimage',$totalimage);
		$this->mTemplet->assign('image',$image);
		$this->mTemplet->assign('option',$option);
		$this->mTemplet->assign('map',$map);
		$this->mTemplet->assign('dealer',$this->GetDealer($data['mno']));
		//$this->mTemplet->register_object('mBoard',$this,array('GetSortLink','GetThumbnail'));
		$this->PrintTemplet();
	}
	
	function PrintSearchForm($skin,$searchURL='') {
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/searchform/'.$skin.'/style.css" type="text/css" />'."\n";
		echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/searchform/'.$skin.'/script.js"></script>'."\n";

		$searchURL = $searchURL ? $searchURL : $this->baseURL;
		$formStart = '<form name="OneroomSearchForm-'.$skin.'" action="'.$searchURL.'" enctype="application/x-www-form-urlencoded">';
		
		if ($this->baseQueryString) {
			$querys = explode('&',$this->baseQueryString);

			for ($i=0, $loop=sizeof($querys);$i<$loop;$i++) {
				$temp = explode('=',$querys[$i]);
				if (in_array($temp[0],array('search_option','search_type','region1','region2','region3','university_parent','university_idx','subway_parent','subway_idx','price_buy1','price_buy2','price_rent_all1','price_rent_all2','price_rent_deposit1','price_rent_deposit2','price_rent_month1','price_rent_month2','x','y')) == false) $formStart.= '<input type="hidden" name="'.$temp[0].'" value="'.GetString(Request($temp[0]),'inputbox').'" />';
			}
		}
		
		$formEnd = '</form>';
		
		if (Request('price_type') == '1') {
			$_REQUEST['is_buy'] = 'TRUE';
			$temp = explode('-',Request('price1'));
			$_REQUEST['price_buy1'] = $temp[0];
			$_REQUEST['price_buy2'] = $temp[1];
		} elseif (Request('price_type') == '2') {
			$_REQUEST['is_rent_all'] = 'TRUE';
			$temp = explode('-',Request('price1'));
			$_REQUEST['price_rent_all1'] = $temp[0];
			$_REQUEST['price_rent_all2'] = $temp[1];
		} elseif (Request('price_type') == '3') {
			$_REQUEST['is_rent_month'] = 'TRUE';
			$_REQUEST['is_rent_short'] = 'TRUE';
			$temp = explode('-',Request('price1'));
			$_REQUEST['price_rent_deposit1'] = $temp[0];
			$_REQUEST['price_rent_deposit2'] = $temp[1];
			$temp = explode('-',Request('price2'));
			$_REQUEST['price_rent_month1'] = $temp[0];
			$_REQUEST['price_rent_month2'] = $temp[1];
		}

		$this->mTemplet = new Templet($this->modulePath.'/templet/searchform/'.$skin.'/form.tpl');
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/searchform/'.$skin);
		$this->mTemplet->assign('region',$this->mDB->DBfetchs($this->table['region'],array('idx','title'),"where `parent`=0",'sort,asc'));
		$this->mTemplet->assign('university',$this->mDB->DBfetchs($this->table['university'],array('idx','title'),"where `parent`=0",'sort,asc'));
		$this->mTemplet->assign('subway',$this->mDB->DBfetchs($this->table['subway'],array('idx','title'),"where `parent`=0",'sort,asc'));
		$this->mTemplet->assign(array(
			'search_type'=>$this->GetZeroValue('search_type'),
			'price_type'=>$this->GetZeroValue('price_type'),
			'region1'=>$this->GetZeroValue('region1'),
			'region2'=>$this->GetZeroValue('region2'),
			'region3'=>$this->GetZeroValue('region3'),
			'university_parent'=>$this->GetZeroValue('university_parent'),
			'university_idx'=>$this->GetZeroValue('university_idx'),
			'subway_parent'=>$this->GetZeroValue('subway_parent'),
			'subway_idx'=>$this->GetZeroValue('subway_idx'),
			'is_buy'=>Request('is_buy') == 'TRUE' ? 'TRUE' : '',
			'is_rent_all'=>Request('is_rent_all') == 'TRUE' ? 'TRUE' : '',
			'is_rent_month'=>Request('is_rent_month') == 'TRUE' ? 'TRUE' : '',
			'is_rent_short'=>Request('is_rent_short') == 'TRUE' ? 'TRUE' : '',
			'is_double'=>Request('is_double') == 'TRUE' ? 'TRUE' : '',
			'is_under'=>Request('is_under') == 'TRUE' ? 'TRUE' : '',
			'is_parkings'=>Request('is_parkings') == 'TRUE' ? 'TRUE' : '',
			'price_buy1'=>Request('price_buy1') ? Request('price_buy1') :'0',
			'price_buy2'=>Request('price_buy2') ? Request('price_buy2') :'0',
			'price_rent_all1'=>Request('price_rent_all1') ? Request('price_rent_all1') :'0',
			'price_rent_all2'=>Request('price_rent_all2') ? Request('price_rent_all2') :'0',
			'price_rent_deposit1'=>Request('price_rent_deposit1') ? Request('price_rent_deposit1') :'0',
			'price_rent_deposit2'=>Request('price_rent_deposit2') ? Request('price_rent_deposit2') :'0',
			'price_rent_month1'=>Request('price_rent_month1') ? Request('price_rent_month1') :'0',
			'price_rent_month2'=>Request('price_rent_month2') ? Request('price_rent_month2') :'0',
			'keyword'=>Request('keyword')
		));
		$this->mTemplet->register_object('mOneroom',$this,array('GetRegionName','GetUniversityName','GetSubwayName'));
		$this->PrintTemplet();
	}
	
	function PrintProDealer($skin,$page,$limit) {
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/prodealer/'.$skin.'/style.css" type="text/css" />'."\n";
		
		$find = '';
		if ($this->GetConfig('prodealer_method') == 'auction') {
			if ($this->mDB->DBcount($this->table['prodealer'],"where `type`='AUCTION' and `month`='".date('Y-m')."' and `status`='NEW'") > 0) {
				$update = $this->mDB->DBfetchs($this->table['prodealer'],array('idx','status'),"where `type`='AUCTION' and `month`='".date('Y-m')."'",array('point,desc','last_bidding,asc'));
				for ($i=0, $loop=sizeof($update);$i<$loop;$i++) {
					if ($update[$i]['status'] == 'NEW') {
						$status = $i < $this->GetConfig('prodealer_limit') ? 'SUCCESS' : 'FAIL';
						$this->mDB->DBupdate($this->table['prodealer'],array('status'=>$status),'',"where `idx`='{$update[$i]['idx']}'");
					}
				}
			}
			$find = "where `type`='AUCTION' and `month`='".date('Y-m')."' and `status`='SUCCESS'";
			$orderer = 'random';
		}

		$data = $this->mDB->DBfetchs($this->table['prodealer'],array('mno','region1','region2','region3'),$find,$orderer,'0,'.$limit);
		$defaults = $this->mDB->DBfetchs($this->table['prodealer_default'],array('mno','region1','region2','region3'));
		for ($i=0, $loop=sizeof($defaults);$i<$loop;$i++) {
			if (sizeof($data) == $limit) break;
			if (in_array($defaults[$i],$data) == false) {
				$data[] = $defaults[$i];
			}
		}

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['item'] = $this->mDB->DBcount($this->table['item'],"where `mno`='{$data[$i]['mno']}'");
			$data[$i]['dealer'] = $this->mMember->GetMemberInfo($data[$i]['mno']);
			$data[$i]['region'] = $this->GetRegion($data[$i]['region1'],$data[$i]['region2'],$data[$i]['region3']);
			$data[$i]['dealerlink'] = $page.(preg_match('/\?/',$page) == true ? '&amp;' : '?').'mno='.$data[$i]['mno'];
		}
		$this->mTemplet = new Templet($this->modulePath.'/templet/prodealer/'.$skin.'/list.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/prodealer/'.$skin);
		$this->mTemplet->register_object('mOneroom',$this,array('GetRegionName','GetUniversityName','GetSubwayName'));
		$this->PrintTemplet();
	}

	function GetPremiumItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all=array()) {
		$find = 'where 1';
		$orderer = 'idx,asc';
		if ($this->GetConfig('premium_method') == 'slot') {
			$find = "where `type`='SLOT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT();
			$orderer = $this->GetConfig('premium_sort') == 'random' ? 'random' : 'start_time,desc';
		} elseif ($this->GetConfig('premium_method') == 'auction') {
			if ($this->mDB->DBcount($this->table['premium_item'],"where `type`='AUCTION' and `month`='".date('Y-m')."' and `status`='NEW'") > 0) {
				$update = $this->mDB->DBfetchs($this->table['premium_item'],array('idx','status'),"where `type`='AUCTION' and `month`='".date('Y-m')."'",array('point,desc','last_bidding,asc'));
				for ($i=0, $loop=sizeof($update);$i<$loop;$i++) {
					if ($update[$i]['status'] == 'NEW') {
						$status = $i < $this->GetConfig('premium_limit') ? 'SUCCESS' : 'FAIL';
						$this->mDB->DBupdate($this->table['premium_item'],array('status'=>$status),'',"where `idx`='{$update[$i]['idx']}'");
					}
				}
			}
			$find = "where `type`='AUCTION' and `month`='".date('Y-m')."' and `status`='SUCCESS'";
			$orderer = $this->GetConfig('premium_sort') == 'random' ? 'random' : 'point,desc';
		} elseif ($this->GetConfig('premium_method') == 'point') {
			$find = "where `type`='POINT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT();
			$orderer = $this->GetConfig('premium_sort') == 'random' ? 'random' : 'start_time,asc';
		}

		if ($this->GetConfig('premium_searching') == 'on') {
			if ($category1 != null) $find.= " and `category1`='$category1'";
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
		}

		$lists = $this->mDB->DBfetchs($this->table['premium_item'],array('ino'),$find,$orderer);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['ino'] != '0' && in_array($lists[$i]['ino'],$all) == false) {
				$item = $this->mDB->DBfetch($this->table['item'],array('idx','is_open'),"where `idx`='{$lists[$i]['ino']}'");
				if (isset($item['idx']) == true && $item['is_open'] == 'TRUE') $all[] = $item['idx'];
			}
		}
		
		if ($this->GetConfig('premium_searching') == 'on' && sizeof($all) < $limit) {
			if ($category1 != null) $category1 = null;
			elseif ($region3 != null) $region3 = null;
			elseif ($region2 != null) $region2 = null;
			elseif ($region1 != null) $region1 = null;
			else return $all;
			
			return $this->GetPremiumItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all);
		} else {
			return $all;
		}
	}
	
	function GetRegionItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all=array()) {
		$find = 'where 1';
		$orderer = 'idx,asc';
		if ($this->GetConfig('regionitem_method') == 'slot') {
			$find = "where `type`='SLOT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT();
			$orderer = $this->GetConfig('regionitem_sort') == 'random' ? 'random' : 'start_time,desc';
		} elseif ($this->GetConfig('regionitem_method') == 'auction') {
			if ($this->mDB->DBcount($this->table['region_item'],"where `type`='AUCTION' and `month`='".date('Y-m')."' and `status`='NEW'") > 0) {
				$update = $this->mDB->DBfetchs($this->table['region_item'],array('idx','status'),"where `type`='AUCTION' and `month`='".date('Y-m')."'",array('point,desc','last_bidding,asc'));
				for ($i=0, $loop=sizeof($update);$i<$loop;$i++) {
					if ($update[$i]['status'] == 'NEW') {
						$status = $i < $this->GetConfig('regionitem_limit') ? 'SUCCESS' : 'FAIL';
						$this->mDB->DBupdate($this->table['region_item'],array('status'=>$status),'',"where `idx`='{$update[$i]['idx']}'");
					}
				}
			}
			$find = "where `type`='AUCTION' and `month`='".date('Y-m')."' and `status`='SUCCESS'";
			$orderer = $this->GetConfig('regionitem_sort') == 'random' ? 'random' : 'point,desc';
		} elseif ($this->GetConfig('regionitem_method') == 'point') {
			$find = "where `type`='POINT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT();
			$orderer = $this->GetConfig('regionitem_sort') == 'random' ? 'random' : 'start_time,asc';
		}

		if ($this->GetConfig('regionitem_searching') == 'on') {
			if ($category1 != null) $find.= " and `category1`='$category1'";
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
		}

		$lists = $this->mDB->DBfetchs($this->table['region_item'],array('ino'),$find,$orderer);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($lists[$i]['ino'] != '0' && in_array($lists[$i]['ino'],$all) == false) {
				$item = $this->mDB->DBfetch($this->table['item'],array('idx','is_open'),"where `idx`='{$lists[$i]['ino']}'");
				if (isset($item['idx']) == true && $item['is_open'] == 'TRUE') $all[] = $item['idx'];
			}
		}
		
		if ($this->GetConfig('regionitem_searching') == 'on' && sizeof($all) < $limit) {
			if ($category1 != null) $category1 = null;
			elseif ($region3 != null) $region3 = null;
			elseif ($region2 != null) $region2 = null;
			elseif ($region1 != null) $region1 = null;
			else return $all;
			
			return $this->GetRegionItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all);
		} else {
			return $all;
		}
	}
	
	function GetDefaultPremiumItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all=array()) {
		$find = "where `is_default_premium`='TRUE'";

		if ($this->GetConfig('premium_searching') == 'on') {
			if ($category1 != null) $find.= " and `category1`='$category1'";
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
		}
		
		$lists = $this->mDB->DBfetchs($this->table['item'],array('idx'),$find,'random');
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if (sizeof($all) == $limit) break;
			if (in_array($lists[$i]['idx'],$all) == false) {
				$all[] = $lists[$i]['idx'];
			}
		}
		
		if ($this->GetConfig('premium_searching') == 'on' && sizeof($all) < $limit) {
			if ($category1 != null) $category1 = null;
			elseif ($region3 != null) $region3 = null;
			elseif ($region2 != null) $region2 = null;
			elseif ($region1 != null) $region1 = null;
			else return $all;
			
			return $this->GetDefaultPremiumItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all);
		} else {
			return $all;
		}
	}
	
	function GetDefaultRegionItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all=array()) {
		$find = "where `is_default_regionitem`='TRUE'";

		if ($this->GetConfig('regionitem_searching') == 'on') {
			if ($category1 != null) $find.= " and `category1`='$category1'";
			if ($region1 != null) $find.= " and `region1`='$region1'";
			if ($region2 != null) $find.= " and `region2`='$region2'";
			if ($region3 != null) $find.= " and `region3`='$region3'";
		}
		
		$lists = $this->mDB->DBfetchs($this->table['item'],array('idx'),$find,'random');
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if (sizeof($all) == $limit) break;
			if (in_array($lists[$i]['idx'],$all) == false) {
				$all[] = $lists[$i]['idx'];
			}
		}
		
		if ($this->GetConfig('regionitem_searching') == 'on' && sizeof($all) < $limit) {
			if ($category1 != null) $category1 = null;
			elseif ($region3 != null) $region3 = null;
			elseif ($region2 != null) $region2 = null;
			elseif ($region1 != null) $region1 = null;
			else return $all;
			
			return $this->GetDefaultRegionItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$all);
		} else {
			return $all;
		}
	}

	function PrintPremiumItem($skin,$page,$limit) {
		$region1 = $this->GetZeroValue('region1');
		$region2 = $this->GetZeroValue('region2');
		$region3 = $this->GetZeroValue('region3');

		$category1 = $this->GetZeroValue('category1');
		$category2 = $this->GetZeroValue('category2');
		$category3 = $this->GetZeroValue('category3');
		
		if ($this->GetConfig('premium_method') != 'admin') {
			$data = $this->GetPremiumItem($category1,$category2,$category3,$region1,$region2,$region3,$limit);
		} else {
			$data = array();
		}
		
		if (sizeof($data) < $limit) {
			$data = $this->GetDefaultPremiumItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$data);
		}

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i] = $this->mDB->DBfetch($this->table['item'],array('idx','region1','region2','region3','category1','category2','category3','title','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','price_maintenance','areasize','real_areasize','floor','is_under','is_double','subway','subway_distance','university','rooms','parkings','build_year','image'),"where `idx`='{$data[$i]}'");
			$data[$i] = $this->GetItemValue($data[$i]);
			$data[$i]['itemlink'] = $page.(preg_match('/\?/',$page) == true ? '&amp;' : '?').'mode=view&amp;idx='.$data[$i]['idx'];
		}
		
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/itemlist/'.$skin.'/style.css" type="text/css" />'."\n";
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/itemlist/'.$skin.'/list.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/itemlist/'.$skin);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->register_object('mOneroom',$this,array('GetRegionName'));
		$this->PrintTemplet();
	}
	
	function PrintRegionItem($skin,$page,$limit) {
		$region1 = $this->GetZeroValue('region1');
		$region2 = $this->GetZeroValue('region2');
		$region3 = $this->GetZeroValue('region3');

		$category1 = $this->GetZeroValue('category1');
		$category2 = $this->GetZeroValue('category2');
		$category3 = $this->GetZeroValue('category3');
		
		if ($this->GetConfig('regionitem_method') != 'admin') {
			$data = $this->GetRegionItem($category1,$category2,$category3,$region1,$region2,$region3,$limit);
		} else {
			$data = array();
		}
		
		if (sizeof($data) < $limit) {
			$data = $this->GetDefaultRegionItem($category1,$category2,$category3,$region1,$region2,$region3,$limit,$data);
		}

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i] = $this->mDB->DBfetch($this->table['item'],array('idx','region1','region2','region3','category1','category2','category3','title','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','price_maintenance','areasize','real_areasize','floor','is_under','is_double','subway','subway_distance','university','rooms','parkings','build_year','image'),"where `idx`='{$data[$i]}'");
			$data[$i] = $this->GetItemValue($data[$i]);
			$data[$i]['itemlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data[$i]['idx']));
		}
		
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/itemlist/'.$skin.'/style.css" type="text/css" />'."\n";
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/itemlist/'.$skin.'/list.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/itemlist/'.$skin);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->register_object('mOneroom',$this,array('GetRegionName'));
		$this->PrintTemplet();
	}
	
	function PrintItemList($skin,$limit,$find='') {
		$find = $find ? 'where 1 and '.$find : 'where 1';
		$data = $this->mDB->DBfetchs($this->table['item'],array('idx','region1','region2','region3','category1','category2','category3','title','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','price_rent_all','price_rent_deposit','price_rent_month','price_maintenance','areasize','real_areasize','floor','is_under','is_double','subway','subway_distance','university','rooms','parkings','build_year','image'),$find);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i] = $this->GetItemValue($data[$i]);
			$data[$i]['itemlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data[$i]['idx']));
		}
		
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/itemlist/'.$skin.'/style.css" type="text/css" />'."\n";
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/itemlist/'.$skin.'/list.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/itemlist/'.$skin);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->register_object('mOneroom',$this,array('GetRegionName'));
		$this->PrintTemplet();
	}
	
	function PrintMybar($skin) {
		$historys = Request('OneroomHistory','session') == null ? array() : Request('OneroomHistory','session');
		$history = array();
		for ($i=0, $loop=sizeof($historys);$i<$loop;$i++) {
			$item = $this->GetItemValue($historys[$i],array('idx','title','region1','region2','region3','category1','category2','category3','is_buy','is_rent_all','is_rent_month','is_rent_short','price_buy','areasize','price_rent_all','price_rent_deposit','price_rent_month','image'));
			if (isset($item['idx']) == true) $history[] = $item;
		}
		
		echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/mybar/'.$skin.'/script.js"></script>'."\n";
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/mybar/'.$skin.'/style.css" type="text/css" />'."\n";
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/mybar/'.$skin.'/mybar.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/mybar/'.$skin);
		$this->mTemplet->assign('totalhistory',sizeof($history));
		$this->mTemplet->assign('history',$history);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->register_object('mOneroom',$this,array('GetRegionName'));
		$this->PrintTemplet();
	}
	
	function GetZeroValue($param) {
		$value = Request($param) == null || Request($param) == '0' ? null : Request($param);
		return $value;
	}
	
	function GetQueryString($var=array(),$queryString='',$encode=true) {
		$queryString = $queryString ? $queryString : $this->baseQueryString;
		if ($this->GetZeroValue('region1') != null) $var['region1'] = $this->GetZeroValue('region1');
		if ($this->GetZeroValue('region2') != null) $var['region2'] = $this->GetZeroValue('region2');
		if ($this->GetZeroValue('region3') != null) $var['region3'] = $this->GetZeroValue('region3');
		if ($this->GetZeroValue('category1') != null) $var['category1'] = $this->GetZeroValue('category1');
		if ($this->GetZeroValue('category2') != null) $var['category2'] = $this->GetZeroValue('category2');
		if ($this->GetZeroValue('category3') != null) $var['category3'] = $this->GetZeroValue('category3');
		
		if (Request('university_parent') != null) $var['university_parent'] = Request('university_parent');
		if (Request('university_idx') != null) $var['university_idx'] = Request('university_idx');
		if (Request('subway_parent') != null) $var['subway_parent'] = Request('subway_parent');
		if (Request('subway_idx') != null) $var['subway_idx'] = Request('subway_idx');
		if (Request('is_buy') != null) $var['is_buy'] = Request('is_buy');
		if (Request('is_rent_all') != null) $var['is_rent_all'] = Request('is_rent_all');
		if (Request('is_rent_month') != null) $var['is_rent_month'] = Request('is_rent_month');
		if (Request('is_rent_short') != null) $var['is_rent_short'] = Request('is_rent_short');
		if (Request('is_double') != null) $var['is_double'] = Request('is_double');
		if (Request('is_under') != null) $var['is_under'] = Request('is_under');
		if (Request('keyword') != null) $var['keyword'] = urlencode(Request('is_parkings'));
		
		if ($this->GetZeroValue('price_buy1') != null) $var['price_buy1'] = $this->GetZeroValue('price_buy1');
		if ($this->GetZeroValue('price_buy2') != null) $var['price_buy2'] = $this->GetZeroValue('price_buy2');
		if ($this->GetZeroValue('price_rent_all1') != null) $var['price_rent_all1'] = $this->GetZeroValue('price_rent_all1');
		if ($this->GetZeroValue('price_rent_all2') != null) $var['price_rent_all2'] = $this->GetZeroValue('price_rent_all2');
		if ($this->GetZeroValue('price_rent_deposit1') != null) $var['price_rent_deposit1'] = $this->GetZeroValue('price_rent_deposit1');
		if ($this->GetZeroValue('price_rent_deposit2') != null) $var['price_rent_deposit2'] = $this->GetZeroValue('price_rent_deposit2');
		if ($this->GetZeroValue('price_rent_month1') != null) $var['price_rent_month1'] = $this->GetZeroValue('price_rent_month1');
		if ($this->GetZeroValue('price_rent_month2') != null) $var['price_rent_month2'] = $this->GetZeroValue('price_rent_month2');
		
		if (Request('search_type') == 'region') {
			$var['university_parent'] = '';
			$var['university_idx'] = '';
			$var['subway_parent'] = '';
			$var['subway_idx'] = '';
		} elseif (Request('search_type') == 'university') {
			$var['region1'] = '';
			$var['region2'] = '';
			$var['region3'] = '';
			$var['subway_parent'] = '';
			$var['subway_idx'] = '';
		} elseif (Request('search_type') == 'subway') {
			$var['region1'] = '';
			$var['region2'] = '';
			$var['region3'] = '';
			$var['university_parent'] = '';
			$var['university_idx'] = '';
		}

		return GetQueryString($var,$queryString,$encode);
	}
	
	function GetItemValue($item,$params='*') {
		if (is_array($item) == true) {
			if (isset($item['title']) == true) $item['title'] = GetString($item['title'],'replace');
			if (isset($item['category1']) == true) $item['category'] = $this->GetCategory($item['category1'],isset($item['category2']) == true ? $item['category2'] : '0',isset($item['category3']) == true ? $item['category3'] : '0');
			if (isset($item['region1']) == true) $item['region'] = $this->GetRegion($item['region1'],isset($item['region2']) == true ? $item['region2'] : '0',isset($item['reigon3']) == true ? $item['region3'] : '0');
			
			if (isset($item['areasize']) == true) $item['areasize'] = $item['areasize'].'평형 ('.sprintf('%0.2f',$item['areasize']*3.3058).'㎡)';
			if (isset($item['real_areasize']) == true) $item['real_areasize'] = $item['real_areasize'].'평 ('.sprintf('%0.2f',$item['real_areasize']*3.3058).'㎡)';
			if (isset($item['address1']) == true || isset($item['address2']) == true) {
				$item['address'] = trim((isset($item['address1']) == true ? $item['address1'].' ' : '').(isset($item['address2']) == true ? $item['address2'] : ''));
			}
			if (isset($item['floor']) == true) $item['floor'] = $item['is_under'] == 'TRUE' ? '반지하' : '전체 '.array_pop(explode('/',$item['floor'])).'층중 '.array_shift(explode('/',$item['floor'])).'층';
			if (isset($item['subway']) == true) $item['subway'] = $this->GetSubway($item['subway'],true);
			if (isset($item['university']) == true) $item['university'] = $this->GetUniversity($item['university'],true);
			
			if (isset($item['image']) == true) $item['image'] = $item['image'] && file_exists($_ENV['userfilePath'].$this->thumbnail.'/'.$item['image'].'.thm') == true ? $_ENV['userfileDir'].$this->thumbnail.'/'.$item['image'].'.thm' : '';
			
			if (isset($item['detail']) == true) $item['detail'] = '<div class="smartOutput">'.str_replace('{$moduleDir}',$this->moduleDir,$item['detail']).'</div>';
		} elseif (is_numeric($item) == true) {
			$item = $this->mDB->DBfetch($this->table['item'],$params,"where `idx`='$item'");
			return $this->GetItemValue($item);
		}
		return $item;
	}
	
	function GetCategoryName($category) {
		$category = $this->mDB->DBfetch($this->table['category'],array('title'),"where `idx`=$category");
		return $category['title'];
	}
	
	function GetCategory($category1,$category2='',$category3='') {
		$category = $this->GetCategoryName($category1);
		if ($category2 > 0) $category.= ' > '.$this->GetCategoryName($category2);
		if ($category3 > 0) $category.= ' > '.$this->GetCategoryName($category3);
		
		return $category;
	}
	
	function GetRegionName($region) {
		if (is_array($region) == true) $region = $region['region'];
		$region = $this->mDB->DBfetch($this->table['region'],array('title'),"where `idx`=$region");
		return $region['title'];
	}
	
	function GetUniversityName($university) {
		if (is_array($university) == true) $university = $university['university'];
		$university = $this->mDB->DBfetch($this->table['university'],array('title'),"where `idx`=$university");
		return $university['title'];
	}
	
	function GetSubwayName($subway) {
		if (is_array($subway) == true) $subway = $subway['subway'];
		$subway = $this->mDB->DBfetch($this->table['subway'],array('title'),"where `idx`=$subway");
		return $subway['title'];
	}
	
	function GetDealer($dealer) {
		return $this->mMember->GetMemberInfo($dealer);
	}
	
	function GetDealerName($dealer,$type='name') {
		$type = in_array($type,array('name','nickname')) == true ? $type : 'name';
		return $this->mMember->GetMemberInfo($dealer,$type);
	}
	
	function GetRegion($region1,$region2='',$region3='') {
		$region = $this->GetRegionName($region1);
		if ($region2 > 0) $region.= ' '.$this->GetRegionName($region2);
		if ($region3 > 0) $region.= ' '.$this->GetRegionName($region3);
		
		return $region;
	}
	
	function GetSubway($subway,$is_line=true) {
		$subway = $this->mDB->DBfetch($this->table['subway'],array('parent','title'),"where `idx`=$subway");
		if (isset($subway['parent']) == true && $subway['parent'] != '0') {
			if ($is_line == true) {
				$line = $this->mDB->DBfetch($this->table['subway'],array('parent','title'),"where `idx`={$subway['parent']}");
				return $line['title'].' '.$subway['title'].(preg_match('/역$/',$subway['title']) == true ? '' : '역');
			} else {
				return $subway['title'];
			}
		}
		
		return '';
	}
	
	function GetUniversity($university,$is_region=true) {
		$university = $this->mDB->DBfetch($this->table['university'],array('parent','title'),"where `idx`=$university");
		if (isset($university['parent']) == true && $university['parent'] != '0') {
			if ($is_region == true) {
				$region = $this->mDB->DBfetch($this->table['university'],array('parent','title'),"where `idx`={$university['parent']}");
				return $region['title'].' '.$university['title'];
			} else {
				return $university['title'];
			}
		}
		
		return '';
	}
	
	function GetPermission($mode) {
		return true;
	}
	
	function SetHistory($idx) {
		$history = Request('OneroomHistory','session') == null ? array() : Request('OneroomHistory','session');
		array_unshift($history, $idx);
		$history = array_unique($history);
		$nHistory = array();
		while (sizeof($nHistory) < 10) array_push($nHistory,array_shift($history));
		$_SESSION['OneroomHistory'] = $nHistory;
	}
	
	function CheckAgent() {
		return $this->mDB->DBcount($this->table['agent'],"where `mno`={$this->member['idx']}") > 0;
	}
	
	function CheckDealer() {
		return $this->mDB->DBcount($this->table['dealer'],"where `mno`={$this->member['idx']} and `status`='ACTIVE'") > 0;
	}
	
	function CheckPrivateDealer() {
		return $this->mDB->DBcount($this->table['dealer'],"where `mno`={$this->member['idx']} and `agent`='0' and `status`='ACTIVE'") > 0;
	}
	
	function CheckRegisterNumber($number) {
		$temp = explode('-',$number);
		
		if (is_numeric($temp[0]) == true && is_numeric($temp[1]) == true && is_numeric($temp[2]) == true && strlen($number) == 13) {
			return true;
		} else {
			return false;
		}
	}
}