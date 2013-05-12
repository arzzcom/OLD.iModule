<?php
class ModuleShop extends Module {
	public $table;
	protected $isHeaderIncluded;

	function __construct() {
		parent::__construct('shop');
		$this->table['category'] = $_ENV['code'].'_shop_category_table';
		$this->table['banner'] = $_ENV['code'].'_shop_banner_table';
		$this->table['bannerlist'] = $_ENV['code'].'_shop_bannerlist_table';
		$this->table['file'] = $_ENV['code'].'_shop_file_table';
		$this->table['item'] = $_ENV['code'].'_shop_item_table';
		$this->table['remain'] = $_ENV['code'].'_shop_remain_table';
		$this->table['option'] = $_ENV['code'].'_shop_option_table';
		$this->table['optionlist'] = $_ENV['code'].'_shop_optionlist_table';
		$this->isHeader = false;
	}

	function GetCategoryDepth($c) {
		$categorys = array();

		if (!$c) return $categorys;
		$category = $this->mDB->DBfetch($this->table['category'],array('idx','repto','depth','title'),"where `idx`=$c");

		$link = $_SERVER['PHP_SELF'].GetQueryString(array('v'=>'list','idx'=>'','c'=>'','p'=>'','keyword'=>''));

		if ($category['depth'] == '1') {
			$categorys[0] = array('idx'=>$category['idx'],'title'=>$category['title'],'link'=>$link.'&amp;c='.$category['idx']);
		} elseif ($category['depth'] == '2') {
			$depth1 = $this->mDB->DBfetch($this->table['category'],array('idx','title'),"where `idx`={$category['repto']}");
			$categorys[0] = array('idx'=>$depth1['idx'],'title'=>$depth1['title'],'link'=>$link.'&amp;c='.$depth1['idx']);
			$categorys[1] = array('idx'=>$category['idx'],'title'=>$category['title'],'link'=>$link.'&amp;c='.$category['idx']);
		} elseif ($category['depth'] == '3') {
			$depth2 = $this->mDB->DBfetch($this->table['category'],array('idx','repto','title'),"where `idx`={$category['repto']}");
			$depth1 = $this->mDB->DBfetch($this->table['category'],array('idx','title'),"where `idx`={$depth2['repto']}");
			$categorys[0] = array('idx'=>$depth1['idx'],'title'=>$depth1['title'],'link'=>$link.'&amp;c='.$depth1['idx']);
			$categorys[1] = array('idx'=>$depth2['idx'],'title'=>$depth2['title'],'link'=>$link.'&amp;c='.$depth2['idx']);
			$categorys[2] = array('idx'=>$category['idx'],'title'=>$category['title'],'link'=>$link.'&amp;c='.$category['idx']);
		}

		return $categorys;
	}

	function PrintTemplet(&$mTemplet) {
		$link = array();
		$link['list'] = $_SERVER['PHP_SELF'].GetQueryString(array('v'=>'list','idx'=>'','p'=>''));
		$link['page'] = $_SERVER['PHP_SELF'].GetQueryString(array('p'=>'')).'&amp;p=';
		$link['main'] = $_SERVER['PHP_SELF'].GetQueryString(array('v'=>'main','idx'=>'','c'=>'','p'=>'','keyword'=>''));

		$mTemplet->register_object('mShop',$this,array('PrintCategory','PrintBanner'));
		$mTemplet->assign('skinDir',$this->moduleDir.'/templet/'.$this->module['skin']);
		$mTemplet->assign('link',$link);
		$mTemplet->PrintTemplet();
	}

	function PrintHeader() {
		echo '<!-- Module Shop Header Start -->'."\n";
		if ($this->isHeaderIncluded == false) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/'.$this->module['skin'].'/style.css" type="text/css" title="style" />'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
			if (file_exists($this->modulePath.'/templet/'.$this->module['skin'].'/script.js') == true) {
				echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/'.$this->module['skin'].'/script.js"></script>'."\n";
			}
			$this->isHeaderIncluded = true;
		}
		echo '<!-- Module Shop Header End -->'."\n";
	}

	function PrintBanner($var='',$codes='',$width='',$height='') {
		if (is_array($var) == true) {
			if (sizeof($var) == 0) {
				$skin = 'banner';
				$code = '';
			} else {
				$skin = isset($var['skin']) == true ? $var['skin'] : 'banner';
				$code = isset($var['code']) == true ? $var['code'] : '';
				$width = $var['width'];
				$height = $var['height'];
			}
		} else {
			$skin = $skin ? $skin : 'banner';
		}

		$banners = array();
		$maxWidth = $maxHeight = 0;

		$find = $code ? "where `code`='$code'" : '';
		$banner = $this->mDB->DBfetchs($this->table['bannerlist'],array('idx','url','target','type','filepath'),$find);

		for ($i=0, $loop=sizeof($banner);$i<$loop;$i++) {
			$check = getimagesize($_ENV['path'].$banner[$i]['filepath']);
			$maxWidth = $maxWidth < $check[0] ? $check[0] : $maxWidth;
			$maxHeight = $maxHeight < $check[1] ? $check[1] : $maxHeight;

			$banners[$i]['image'] = $banner[$i]['type'] == 'IMG' ? '<img src="'.$banner[$i]['filepath'].'" alt="banner" />' : '<script type="text/javascript">GetEmbed("ShopBanner'.$banner[$i]['idx'].'","'.$banner[$i]['filepath'].'",'.$check[0].','.$check[1].');</script>';
			$banners[$i]['url'] = $banner[$i]['url'];
		}

		$width = $width ? $width : $maxWidth;
		$height = $height ? $height : $maxHeight;
		$random = $banners[array_rand($banners)];

		$mTemplet = new Templet($this->modulePath.'/templet/'.$this->module['skin'].'/'.$skin.'.tpl');
		$mTemplet->assign('width',$width);
		$mTemplet->assign('height',$height);
		$mTemplet->assign('banners',$banners);
		$mTemplet->assign('random',$random);

		$this->PrintTemplet($mTemplet);
	}

	function PrintCategory($skin='') {
		if (is_array($skin) == true) {
			if (sizeof($skin) == 0) {
				$skin = 'category';
			} else {
				$skin = $skin['skin'];
			}
		} else {
			$skin = $skin ? $skin : 'category';
		}

		$mTemplet = new Templet($this->modulePath.'/templet/'.$this->module['skin'].'/'.$skin.'.tpl');

		$loopnum = 0;
		$categorys = array();
		$depth1 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `depth`=1",'sort,asc');
		for ($i=0, $loop=sizeof($depth1);$i<$loop;$i++) {
			$categorys[$loopnum]['idx'] = $depth1[$i]['idx'];
			$categorys[$loopnum]['title'] = $depth1[$i]['title'];
			$categorys[$loopnum]['image'] = file_exists($_ENV['path'].'/userfile/shop/category/'.$depth1[$i]['idx']) == true ? $_ENV['dir'].'/userfile/shop/category/'.$depth1[$i]['idx'] : '';
			$categorys[$loopnum]['link'] = $this->module['shop'].GetQueryString(array('v'=>'list','c'=>$depth1[$i]['idx'],'idx'=>'','keyword'=>'','p'=>''));
			$categorys[$loopnum]['depth'] = 1;
			$categorys[$loopnum]['loopnum'] = $loopnum;

			$depth2 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `depth`=2 and `repto`={$depth1[$i]['idx']}",'sort,asc');
			for ($j=0, $loopj=sizeof($depth2);$j<$loopj;$j++) {
				$loopnum++;
				$categorys[$loopnum]['idx'] = $depth2[$j]['idx'];
				$categorys[$loopnum]['title'] = $depth2[$j]['title'];
				$categorys[$loopnum]['image'] = file_exists($_ENV['path'].'/userfile/shop/category/'.$depth2[$j]['idx']) == true ? $_ENV['dir'].'/userfile/shop/category/'.$depth2[$j]['idx'] : '';
				$categorys[$loopnum]['link'] = $this->module['shop'].GetQueryString(array('v'=>'list','c'=>$depth2[$j]['idx']));
				$categorys[$loopnum]['depth'] = 2;
				$categorys[$loopnum]['loopnum'] = $loopnum;

				$depth3 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `depth`=3 and `repto`={$depth2[$j]['idx']}",'sort,asc');
				for ($m=0, $loopm=sizeof($depth3);$m<$loopm;$m++) {
					$loopnum++;
					$categorys[$loopnum]['idx'] = $depth3[$m]['idx'];
					$categorys[$loopnum]['title'] = $depth3[$m]['title'];
					$categorys[$loopnum]['image'] = file_exists($_ENV['path'].'/userfile/shop/category/'.$depth3[$m]['idx']) == true ? $_ENV['dir'].'/userfile/shop/category/'.$depth3[$m]['idx'] : '';
					$categorys[$loopnum]['link'] = $this->module['shop'].GetQueryString(array('v'=>'list','c'=>$depth3[$m]['idx']));
					$categorys[$loopnum]['depth'] = 3;
					$categorys[$loopnum]['loopnum'] = $loopnum;
				}
			}
			$loopnum++;
		}

		$mTemplet->assign('categorys',$categorys);
		$this->PrintTemplet($mTemplet);
	}



	function PrintMain() {
		$mTemplet = new Templet($this->modulePath.'/templet/'.$this->module['skin'].'/main.tpl');
		$this->PrintTemplet($mTemplet);
	}

	function PrintList() {
		$c = Request('c');

		$category = $this->mDB->DBfetch($this->table['category'],array('idx','repto','depth','title'),"where `idx`=$c");

		if ($category['depth'] == '1') {
			$find = "where `category1`=$c";
		} elseif ($category['depth'] == '2') {
			$find = "where `category2`=$c";
		} elseif ($category['depth'] == '3') {
			$find = "where `category3`=$c";
		}

		$p = Request('p') > 0 ? Request('p') : 1;
		$listnum = $this->module['listnum'];
		$pagenum = $this->module['pagenum'];
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$totalitem = $this->mDB->DBcount($this->table['item'],$find);
		$totalpage = ceil($totalitem/$listnum) == 0 ? 1 : ceil($totalitem/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['item'],array('idx','title','selltype','type','list_image','price','point','is_soldout','is_hot','is_new','is_package','is_sale'),$find,$orderer,$limiter);

		$loopnum = $totalitem-($p-1)*$listnum;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$remainData = $this->mDB->DBfetch($this->table['remain'],array('SUM(remain)'),"where `itemno`={$data[$i]['idx']}");
			$data[$i]['remain'] = isset($remainData[0]) == true && $remainData[0] ? $remainData[0] : 0;
			$data[$i]['loopnum'] = $loopnum--;
			$data[$i]['itemlink'] = $_SERVER['PHP_SELF'].GetQueryString(array('keyword'=>'','idx'=>$data[$i]['idx'],'v'=>'view'));
			$data[$i]['list_image'] = $data[$i]['list_image'] ? $_ENV['dir'].$data[$i]['list_image'] : $this->moduleDir.'/images/nolistimg.gif';
		}

		$page = array();
		$startpage = floor($p/$pagenum)*$pagenum+1;
		$endpage = $startpage+$pagenum-1 > $totalpage ? $totalpage : $startpage+$pagenum-1;
		$prevpage = $startpage > $pagenum ? $startpage-$pagenum : false;
		$nextpage = $endpage < $totalpage ? $endpage+1 : false;
		$prevlist = $p > 1 ? $p-1 : false;
		$nextlist = $p < $endpage ? $p+1 : false;

		for ($i=$startpage;$i<=$endpage;$i++) {
			$page[] = $i;
		}

		$mTemplet = new Templet($this->modulePath.'/templet/'.$this->module['skin'].'/list.tpl');
		$mTemplet->assign('item',$data);
		$mTemplet->assign('rownum',$this->module['rownum']);
		$mTemplet->assign('page',$page);
		$mTemplet->assign('categorys',$this->GetCategoryDepth($c));
		$mTemplet->assign('p',$p);
		$mTemplet->assign('c',$c);

		$this->PrintTemplet($mTemplet);
	}

	function PrintView() {
		$idx = Request('idx');

		$data = $this->mDB->DBfetch($this->table['item'],'*',"where `idx`=$idx");

		$data['view_image'] = $_ENV['dir'].$data['view_image'];
		$data['pointcalc'] = floor($data['price']/100*$data['point']/10)*10;
		$data['content'] = '<div class="smartOutput">'.$data['content'].'</div>';

		$data['option'] = array();
		$option = $this->mDB->DBfetchs($this->table['option'],array('idx','title'),"where `itemno`=$idx",'idx,asc');

		for ($i=0, $loop=sizeof($option);$i<$loop;$i++) {
			$data['option'][$i] = array('idx'=>$option[$i]['idx'],'title'=>$option[$i]['title'],'loopnum'=>$i+1);
			$optionlist = $this->mDB->DBfetchs($this->table['optionlist'],array('idx','value','price'),"where `optionno`={$option[$i]['idx']}");
			$data['option'][$i]['disable'] = sizeof($optionlist) == 1 && $optionlist[$i]['value'] == '';
			$data['option'][$i]['list'] = $optionlist;
		}

		$mTemplet = new Templet($this->modulePath.'/templet/'.$this->module['skin'].'/view.tpl');
		$mTemplet->assign('data',$data);
		$mTemplet->assign('page',$page);
		$mTemplet->assign('categorys',$this->GetCategoryDepth($data['category3'] ? $data['category3'] : ($data['category2'] ? $data['category2'] : $data['category1'])));
		$mTemplet->assign('p',$p);
		$mTemplet->assign('c',$c);

		$this->PrintTemplet($mTemplet);
	}

	function PrintShop() {
		$this->PrintHeader();
		$v = Request('v') ? Request('v') : 'main';

		switch ($v) {
			case 'main' :
				$this->PrintMain();
			break;

			case 'list' :
				$this->PrintList();
			break;

			case 'view' :
				$this->PrintView();
			break;
		}
	}
}
?>