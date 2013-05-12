<?php
class ModuleBanner extends Module {
	public $table = array();
	
	public $percent = array();
	
	public $userfile;
	public $skinDir;
	public $skinPath;
	
	function __construct() {
		$this->table['section'] = $_ENV['code'].'_banner_section_table';
		$this->table['item'] = $_ENV['code'].'_banner_item_table';
		
		$this->table['log_count'] = $_ENV['code'].'_banner_log_count_table';
		$this->table['log_click'] = $_ENV['code'].'_banner_log_click_table';

		parent::__construct('banner');
		
		$this->userfile = '/banner';
		$this->skinDir = $this->moduleDir.'/templet';
		$this->skinPath = $this->modulePath.'/templet';
	}
	
	function GetItemCount($section) {
		if (isset($percent[$section]) == true) return $percent[$section];
		
		$totalCPM = $this->mDB->DBcount($this->table['item'],"where `code`='$section' and `is_active`='TRUE' and `type`='CPM' and `start_date`<='".date('Y-m-d')."' and `end_date`>='".date('Y-m-d')."'");
		$CPC = $this->mDB->DBfetch($this->table['item'],array('count(*)','SUM(point)'),"where `code`='$section' and `is_active`='TRUE' and `type`='CPC' and `paid_point`>0");
		$totalCPC = $CPC[0];
		$totalCPCPoint = $CPC[1];
		
		$percent[$section] = array('totalItem'=>$totalCPM+$totalCPC,'totalCPM'=>$totalCPM,'totalCPC'=>$totalCPC,'totalCPCPoint'=>$totalCPCPoint);
		return $percent[$section];
	}
	
	function GetItem($section,$limit,$all=array()) {
		$itemCount = $this->GetItemCount($section);
		
		$randMax = 0;
		$start = 0;
		$randPosition = array();
		
		$items = $this->mDB->DBfetchs($this->table['item'],array('idx','type','point','paid_point','start_date','end_date'),"where `code`='$section' and `is_active`='TRUE'");
		for ($i=0, $loop=sizeof($items);$i<$loop;$i++) {
			if (in_array($items[$i]['idx'],$all) == false) {
				if ($items[$i]['type'] == 'CPM' && $items[$i]['start_date'] <= date('Y-m-d') && $items[$i]['end_date'] >= date('Y-m-d')) {
					$percent = floor(1/$itemCount['totalItem']*10000);
					$randPosition[] = array('idx'=>$items[$i]['idx'],'start'=>$start,'end'=>$start+$percent,'percent'=>$percent);
					$start+= $percent;
				}
				
				if ($items[$i]['type'] == 'CPC' && $items[$i]['paid_point'] > 0) {
					$percent = floor($items[$i]['point']/$itemCount['totalCPCPoint']*($itemCount['totalCPC']/$itemCount['totalItem']*10000));
					$randPosition[] = array('idx'=>$items[$i]['idx'],'start'=>$start,'end'=>$start+$percent,'percent'=>$percent);
					$start+= $percent;
				}
			}
		}
		
		if ($start == 0) return $all;
		
		$rand = rand(0,$start-1);
		for ($i=0, $loop=sizeof($randPosition);$i<$loop;$i++) {
			if ($randPosition[$i]['start'] <= $rand && $randPosition[$i]['end'] > $rand) {
				$all[] = $randPosition[$i]['idx'];
				break;
			}
		}
		
		if (sizeof($all) < $limit) return $this->GetItem($section,$limit,$all);
		else return $all;
	}
	
	function PrintBannerSection($section,$skin,$limit) {
		$item = $this->GetItem($section,$limit);
		
		if (file_exists($this->skinPath.'/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->skinDir.'/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}
		
		$section = $this->mDB->DBfetch($this->table['section'],'*',"where `code`='$section'");
		$data = $this->mDB->DBfetchs($this->table['item'],'*',"where `idx` IN (".implode(',',$item).")");
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['bannerStart'] = '<div style="display:inline-block; width:'.$section['width'].'px; height:'.$section['height'].'; position:relative; cursor:pointer; font:0/0 arial;" onclick="window.open(\''.$this->moduleDir.'/exec/Click.do.php?idx='.$data[$i]['idx'].'\');">';
			
			if ($data[$i]['bannertype'] == 'IMG') {
				$data[$i]['bannerfile'] = '<img src="'.$this->moduleDir.'/exec/ShowBanner.do.php?idx='.$data[$i]['idx'].'" style="width:'.$section['width'].'px; height:'.$section['height'].'px;" />';
			} else {
				$data[$i]['bannerfile'] = '<script type="text/javascript">GetEmbed("Banner'.$section['code'].'-'.$data[$i]['idx'].'","'.$this->moduleDir.'/exec/ShowBanner.do.php?idx='.$data[$i]['idx'].'",'.$section['width'].','.$section['height'].');</script>';
			}
			$data[$i]['url'] = $this->moduleDir.'/exec/Click.do.php?idx='.$data[$i]['idx'];
			
			$data[$i]['bannerEnd'] = '</div>';
		}
		
		$mTemplet = new Templet($this->skinPath.'/'.$skin.'/list.tpl');
		$mTemplet->assign('data',$data);
		$mTemplet->assign('width',$section['width']);
		$mTemplet->assign('height',$section['height']);
		$mTemplet->PrintTemplet();
	}
	
	function ItemView($idx) {
		$date = date('Y-m-d');
		$hour = date('G');
		
		if ($this->mDB->DBcount($this->table['log_count'],"where `bno`='$idx' and `date`='$date' and `hour`='$hour'") == 0) {
			$this->mDB->DBinsert($this->table['log_count'],array('bno'=>$idx,'date'=>$date,'hour'=>$hour,'view'=>1,'hit'=>0));
		} else {
			$this->mDB->DBupdate($this->table['log_count'],'',array('view'=>'`view`+1'),"where `bno`='$idx' and `date`='$date' and `hour`='$hour'");
		}
		$this->mDB->DBupdate($this->table['item'],'',array('view'=>'`view`+1'),"where `idx`='$idx'");
	}

	function ItemClick($idx) {
		$date = date('Y-m-d');
		$hour = date('G');

		if ($this->mDB->DBcount($this->table['log_count'],"where `bno`='$idx' and `date`='$date' and `hour`='$hour'") == 0) {
			$this->mDB->DBinsert($this->table['log_count'],array('bno'=>$idx,'date'=>$date,'hour'=>$hour,'view'=>0,'hit'=>1));
		} else {
			$this->mDB->DBupdate($this->table['log_count'],'',array('hit'=>'`hit`+1'),"where `bno`='$idx' and `date`='$date' and `hour`='$hour'");
		}
		$this->mDB->DBupdate($this->table['item'],'',array('hit'=>'`hit`+1'),"where `idx`='$idx'");

		$this->mDB->DBinsert($this->table['log_click'],array('bno'=>$idx,'ip'=>$_SERVER['REMOTE_ADDR'],'referer'=>$_SERVER['HTTP_REFERER'],'reg_date'=>GetGMT()));
	}
}
?>