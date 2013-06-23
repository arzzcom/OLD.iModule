<?php
class ModulePoll extends Module {
	public $table;
	
	protected $mTemplet;
	
	public $userfile;
	public $thumbnail;
	
	protected $isHeaderIncluded;
	protected $isFooterIncluded;
	
	function __construct() {
		$this->table['setup'] = $_ENV['code'].'_poll_table';
		$this->table['post'] = $_ENV['code'].'_poll_post_table';
		$this->table['ment'] = $_ENV['code'].'_poll_ment_table';
		$this->table['item'] = $_ENV['code'].'_poll_item_table';
		$this->table['voter'] = $_ENV['code'].'_poll_voter_table';
		
		parent::__construct('poll');
		
		$this->userfile = '/poll/attach';
		$this->thumbnail = '/poll/thumbnail';
		
		$this->isHeaderIncluded = false;
		$this->isFooterIncluded = false;
	}
	
	function PrintHeader($title='') {
		if ($_ENV['isHeaderIncluded'] == false) {
			GetDefaultHeader($title);
		}
		
		if ($this->isHeaderIncluded == false) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" />'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
		}
		$this->isHeaderIncluded = true;
		
		echo "\n<!-- ModulePoll Start -->\n";
	}
	
	function PrintFooter() {
		$this->isFooterIncluded = true;
		echo "\n<!-- ModulePoll End -->\n";
	}
	
	function PrintError($msg='',$skin='') {
		$this->PrintHeader('에러');

		if ($skin && file_exists($this->modulePath.'/templet/poll/'.$skin.'/error.tpl') == true) {
			$this->mTemplet = new Templet($this->modulePath.'/templet/poll/'.$skin.'/error.tpl');
		} else {
			$this->mTemplet = new Templet($this->modulePath.'/templet/error.tpl');
		}
		$this->mTemplet->assign('msg',$msg);

		$this->PrintTemplet();

		$this->PrintFooter();
		return false;
	}
	
	function PrintTemplet() {
		$this->mTemplet->assign('moduleDir',$this->moduleDir);
		$this->mTemplet->PrintTemplet();
	}
	
	
	function PrintRecently($skin,$page,$limit,$find='') {
		$this->PrintHeader();
		
		if (is_dir($this->modulePath.'/templet/recently/'.$skin) == false) {
			return $this->PrintError('최근설문조사 스킨이 잘못지정되었습니다.');
		}
		
		if (file_exists($this->modulePath.'/templet/recently/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/recently/'.$skin.'/style.css" type="text/css" />'."\n";
		}
		
		if (file_exists($this->modulePath.'/templet/recently/'.$skin.'/script.js') == true) {
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/recently/'.$skin.'/script.js"></script>'."\n";
		}
		
		$link = array();
		$link['list'] = preg_match('/\?/',$page) == true ? $page.'&mode=list' : $page.'?mode=list';
		$link['view'] = preg_match('/\?/',$page) == true ? $page.'&mode=view' : $page.'?mode=view';
		
		$find = $find ? $find." and `reg_date`<=".GetGMT() : "where `reg_date`<=".GetGMT();
		
		$data = $this->mDB->DBfetchs($this->table['poll'],'*',$find,'reg_date,desc','0,'.$limit);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$item = $this->mDB->DBfetchs($this->table['item'],'*',"where `repto`='{$data[$i]['idx']}'",'sort,asc');
			$data[$i]['is_end'] = $data[$i]['end_date'] < GetGMT();
			$data[$i]['reg_date'] = GetTime('Y.m.d',$data[$i]['reg_date']);
			$data[$i]['end_date'] = GetTime('Y.m.d',$data[$i]['end_date']);
			$data[$i]['thumbnail'] = file_exists($_ENV['userfilePath'].$this->thumbnail.'/'.$data[$i]['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['idx'].'.thm' : '';
			$data[$i]['image'] = file_exists($_ENV['userfilePath'].$this->userfile.'/'.$data[$i]['idx'].'.file') == true ? $this->moduleDir.'/exec/ShowImage.do.php?idx="'.$data[$i]['idx'] : '';
			$data[$i]['item'] = $item;
			
			
			$data[$i]['view'] = $link['view'].'&idx='.$data[$i]['idx'];
			$data[$i]['result'] = $link['view'].'&result=true&idx='.$data[$i]['idx'];
			
			$data[$i]['formStart'] = '<form name="poll'.$data[$i]['idx'].'" method="post" action="'.$this->moduleDir.'/exec/Poll.do.php" target="execFrame'.$data[$i]['idx'].'" />'."\n";
			$data[$i]['formStart'].= '<input type="hidden" name="action" value="vote" />'."\n";
			$data[$i]['formStart'].= '<input type="hidden" name="repto" value="'.$data[$i]['idx'].'" />'."\n";
			$data[$i]['formStart'].= '<input type="hidden" name="redirect" value="'.$data[$i]['result'].'" />'."\n";
			$data[$i]['formEnd'] = '</form>'."\n".'<iframe name="execFrame'.$data[$i]['idx'].'" style="display:;"></iframe>'."\n";
		}
		
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/recently/'.$skin.'/list.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/recently/'.$skin);
		$this->mTemplet->assign('data',$data);
		$this->PrintTemplet();
		
		$this->PrintFooter();
	}
	
	function GetPermission($idx,$geter) {
		$data = $this->mDB->DBfetch($this->table['poll'],array('permission_'.$geter),"where `idx`='$idx'");
		$permission = isset($data['permission_'.$geter]) == true && $data['permission_'.$geter] ? $data['permission_'.$geter] : 'false';
		
		if ($this->member['type'] == 'ADMINISTRATOR') return true;

		return GetPermission($data['permission_'.$geter]);
	}
	
	function GetVoted($repto) {
		if ($this->member['idx'] == '0') {
			if ($this->mDB->DBcount($this->table['voter'],"where `repto`='$repto' and `ip`='{$_SERVER['REMOTE_ADDR']}'") > 0) return true;
			return false;
		} else {
			if ($this->mDB->DBcount($this->table['voter'],"where `repto`='$repto' and (`mno`='{$this->member['idx']}' or `ip`='{$_SERVER['REMOTE_ADDR']}')") > 0) return true;
			return false;
		}
	}
}
?>