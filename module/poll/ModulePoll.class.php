<?php
class ModulePoll extends Module {
	public $table;
	
	protected $mTemplet;
	
	public $pid;
	public $find;
	public $totalpost;
	public $skinPath;
	public $skinDir;
	public $mode;
	public $idx;
	
	public $baseURL;
	public $baseQueryString;
	
	public $userfile;
	public $thumbnail;
	
	protected $isHeaderIncluded;
	protected $isFooterIncluded;
	
	function __construct($pid='') {
		$this->table['setup'] = $_ENV['code'].'_poll_table';
		$this->table['post'] = $_ENV['code'].'_poll_post_table';
		$this->table['ment'] = $_ENV['code'].'_poll_ment_table';
		$this->table['item'] = $_ENV['code'].'_poll_item_table';
		$this->table['voter'] = $_ENV['code'].'_poll_voter_table';
		
		parent::__construct('poll');
		
		$this->userfile = '/poll/attach';
		$this->thumbnail = '/poll/thumbnail';
		
		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));
		$this->baseQueryString = sizeof(explode('?',$_SERVER['REQUEST_URI'])) > 1 ? array_pop(explode('?',$_SERVER['REQUEST_URI'])) : '';
		
		$this->isHeaderIncluded = false;
		$this->isFooterIncluded = false;
		
		if ($pid) {
			$this->pid = $pid;
			$this->find = "where `pid`='{$this->pid}'";
			$this->setup = $this->mDB->DBfetch($this->table['setup'],'*',"where `pid`='{$this->pid}'");
			$this->skinPath = $this->modulePath.'/templet/poll/'.$this->setup['skin'];
			$this->skinDir = $this->moduleDir.'/templet/poll/'.$this->setup['skin'];
		}
		
		$this->idx = $this->mDB->AntiInjection(Request('idx'));
		$this->mode = Request('mode') ? Request('mode') : ($this->idx == null ? 'list' : 'view');
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
		
		if ($this->pid) {
			if (file_exists($this->skinPath.'/style.css') == true) echo '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" title="style" />'."\n";
			if (file_exists($this->skinPath.'/script.js') == true) echo '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";
			echo '<div class="ModulePoll" style="width:'.$this->setup['width'].'">'."\n";
		}
		
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
		$this->mTemplet->assign('setup',$this->setup);
		$this->mTemplet->assign('skinDir',$this->skinDir);
		$this->mTemplet->PrintTemplet();
	}
	
	function PrintPoll($find='') {
		if ($find) $this->find.= " and ($find)";
		if ($this->module === false) return;
		if (preg_match('/\$/',$this->pid) == false && isset($this->setup['pid']) == false) {
			return $this->PrintError($this->pid.' 설문조사를 찾을 수 없습니다.');
		}

		$this->PrintHeader();

		switch ($this->mode) {
			case 'list' :
				$this->PrintList();
			break;

			case 'write' :
				$this->PrintWrite();
			break;

			case 'modify' :
				$this->PrintWrite();
			break;

			case 'view' :
				$this->PrintView();
			break;

			case 'delete' :
				$this->PrintDelete();
			break;

			case 'ment_delete' :
				$this->PrintMentDelete();
			break;
		}

		$this->PrintFooter();
	}
	
	function PrintList() {
		if ($this->GetPermission('list') == false) return $this->PrintError('목록을 볼 수 있는 권한이 없습니다.');

		$result = Request('result');
		$find = $this->find;

		$listnum = $this->setup['listnum'];
		$pagenum = $this->setup['pagenum'];
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;

		$totalpost = $this->mDB->DBcount($this->table['post'],$find);
		$totalpage = ceil($totalpost/$listnum) == 0 ? 1 : ceil($totalpost/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';

		if ($this->idx != null) {
			$idx = $this->idx;
			$prevFind = $find.' and (`'.$sort.'`'.($dir == 'desc' ? '>=' : '<=')."'".$post[$sort]."')";
			$prevNum = $this->mDB->DBcount($this->table['post'],$prevFind);
			$p = ceil($prevNum/$listnum);
		}
		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;


		$link = array();
		$link['list'] = $this->baseURL.$this->GetQueryString(array('mode'=>'list','idx'=>'','p'=>''));
		$link['view'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>'','p'=>''));
		
		$data = $this->mDB->DBfetchs($this->table['post'],'*',$find,$orderer,$limiter);

		$loopnum = $totalpost-($p-1)*$listnum;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['reg_date'] = GetTime('Y.m.d',$data[$i]['reg_date']);
			$data[$i]['end_date'] = GetTime('Y.m.d',$data[$i]['end_date']);
			$data[$i]['thumbnail'] = file_exists($_ENV['userfilePath'].$this->thumbnail.'/'.$data[$i]['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['idx'].'.thm' : '';
			$data[$i]['image'] = file_exists($_ENV['userfilePath'].$this->userfile.'/'.$data[$i]['idx'].'.file') == true ? $this->moduleDir.'/exec/ShowImage.do.php?idx="'.$data[$i]['idx'] : '';
			
			if ($data[$i]['mno'] != '0') {
				$mData = $this->GetMemberInfo($data[$i]['mno']);
				$data[$i]['name'] = $mData['name'];
				$data[$i]['nickname'] = $mData['nickname'];
			} else {
				$data[$i]['nickname'] = $data[$i]['name'];
			}
			
			$data[$i]['view'] = $link['view'].'&idx='.$data[$i]['idx'];
			$data[$i]['result'] = $link['view'].'&result=true&idx='.$data[$i]['idx'];
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
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('totalpost',number_format($totalpost));
		$this->mTemplet->assign('totalpage',number_format($totalpage));
		$this->mTemplet->assign('p',$p);

		$this->PrintTemplet();
	}
	
	function PrintRecently($skin,$page,$limit,$finder='') {
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
		
		$find = $this->find;
		$find = $finder ? $find.' and '.$finder : $find;
		
		$data = $this->mDB->DBfetchs($this->table['post'],'*',$find,'idx,desc','0,'.$limit);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$item = $this->mDB->DBfetchs($this->table['item'],'*',"where `repto`='{$data[$i]['idx']}'",'sort,asc');
			for ($j=0, $loopj=sizeof($item);$j<$loopj;$j++) {
				$item[$j]['percent'] = $data[$i]['voter'] > 0 ? sprintf('%0.2f',$item[$j]['voter']/$data[$i]['voter']*100) : '0.00';
			}
			$data[$i]['viewmode'] = $data[$i]['end_date'] < GetGMT() || $this->GetVoted($data[$i]['idx']) == true ? 'RESULT' : 'POLL';
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
			$data[$i]['formEnd'] = '</form>'."\n".'<iframe name="execFrame'.$data[$i]['idx'].'" style="display:none;"></iframe>'."\n";
		}
		
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/recently/'.$skin.'/list.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/recently/'.$skin);
		$this->mTemplet->assign('data',$data);
		$this->PrintTemplet();
		
		$this->PrintFooter();
	}
	
	function GetPermission($geter) {
		$permission = $this->setup['permission'] && is_array(unserialize($this->setup['permission'])) == true ? unserialize($this->setup['permission']) : array('list'=>true,'post'=>true,'ment'=>true,'vote'=>true,'result'=>true,'modify'=>false,'delete'=>false);
		if ($this->member['type'] == 'ADMINISTRATOR') return true;
		if (isset($permission[$geter]) == false || $permission[$geter] === '') return true;

		return GetPermission($permission[$geter]);
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
	
	function GetQueryString($var=array(),$queryString='',$encode=true) {
		$queryString = $queryString ? $queryString : $this->baseQueryString;
		if (Request('keyword') == null) {
		}

		return GetQueryString($var,$queryString,$encode);
	}
	
	function GetMemberInfo($mno) {
		$mData = $this->mMember->GetMemberInfo($mno);
		$uniqueID = 'Board'.$mno.'-'.GetMicrotime();
		$info['name'] = $info['nickname'] = '<span id="'.$uniqueID.'" class="pointer bold" style="position:relative;" onclick="ToggleUserMenu(\''.$uniqueID.'\',{idx:'.$mno.',email:\''.$mData['email'].'\',homepage:\''.$mData['homepage'].'\'},event)" clicker="'.$uniqueID.'"><div style="position:absolute; display:none; z-index:1001; top:0px; left:0px;" class="UserMenu" clicker="'.$uniqueID.'">ssss</div>';
		if ($mData['nickcon']) {
			$info['name'].= '<img src="'.$mData['nickcon'].'" title="'.GetString($mData['name'],'inputbox').'" style="vertical-align:middle;" clicker="'.$uniqueID.'" />';
			$info['nickname'].= '<img src="'.$mData['nickcon'].'" title="'.GetString($mData['nickname'],'inputbox').'" style="vertical-align:middle;" clicker="'.$uniqueID.'" />';
		} else {
			$info['name'].= $mData['name'];
			$info['nickname'].= $mData['nickname'];
		}
		$info['name'].= '</span>';
		$info['nickname'].= '</span>';
		$info['photo'] = $mData['photo'];
		$info['email'] = $mData['email'];
		$info['homepage'] = $mData['homepage'];

		return $info;
	}
}
?>