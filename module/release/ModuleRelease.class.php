<?php
class ModuleRelease extends Module {
	public $table = array();
	public $rid = null;

	protected $mTemplet;
	protected $mPlugin;

	public $find;
	public $setup;

	public $userfile;
	public $thumbnail;
	public $skinThumbnail;

	public $totalpost;
	public $skinPath;
	public $skinDir;
	public $mode;
	public $idx;

	public $recentlyPath;
	public $recentlyDir;
	public $baseURL;
	public $baseQueryString;

	protected $isHeaderIncluded;
	protected $isFooterIncluded;
	protected $mUploader;

	protected $link = array();
	protected $action = array();
	
	protected $linkedModule = array();

	function __construct($rid='',$setup='') {
		$this->table['setup'] = $_ENV['code'].'_release_table';
		$this->table['post'] = $_ENV['code'].'_release_post_table';
		$this->table['version'] = $_ENV['code'].'_release_version_table';
		$this->table['ment'] = $_ENV['code'].'_release_ment_table';
		$this->table['category'] = $_ENV['code'].'_release_category_table';
		$this->table['file'] = $_ENV['code'].'_release_file_table';
		$this->table['log'] = $_ENV['code'].'_release_log_table';
		$this->table['payment'] = $_ENV['code'].'_release_payment_table';
		$this->table['status'] = $_ENV['code'].'_release_status_table';
		$this->userfile = '/release';
		$this->thumbnail = '/release/thumbnail';
		$this->skinThumbnail = '/release/skin';
		$this->logo = '/release/logo';
		$this->file = '/release/file';

		parent::__construct('release');

		if ($rid) {
			$this->rid = $rid;
			$this->find = "where `rid`='{$rid}' and `is_delete`='FALSE'";
		}

		$this->idx = $this->mDB->AntiInjection(Request('idx'));
		$this->mode = Request('mode') ? Request('mode') : ($this->idx == null ? 'list' : 'view');

		$this->totalpost = $this->mDB->DBcount($this->table['post'],$this->find);
		$this->isHeaderIncluded = false;
		$this->isFooterIncluded = false;
		$this->mUploader = null;

		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));
		$this->baseQueryString = sizeof(explode('?',$_SERVER['REQUEST_URI'])) > 1 ? array_pop(explode('?',$_SERVER['REQUEST_URI'])) : '';

		if ($rid) {
			$this->setup = $this->mDB->DBfetch($this->table['setup'],array('rid','skin','title','width','use_category','use_charge','listnum','pagenum','view_alllist','post_point','ment_point','tax_point','permission'),"where `rid`='{$rid}'");
			
			if (isset($this->setup['skin']) == true) {
				$this->skinPath = $this->modulePath.'/templet/release/'.$this->setup['skin'];
				$this->skinDir = $this->moduleDir.'/templet/release/'.$this->setup['skin'];
				$this->setup['width'] = preg_match('/%/',$this->setup['width']) ? $this->setup['width'] : $this->setup['width'].'px';
			
				if (is_array($setup) == true) {
					foreach ($setup as $key=>$value) $this->setup[$key] = $value;
				}
		
				if (isset($setup['skin']) == true) {
					$this->setup['skin'] = $setup['skin'];
					$this->skinPath = $this->modulePath.'/templet/release/'.$this->setup['skin'];
					$this->skinDir = $this->moduleDir.'/templet/release/'.$this->setup['skin'];
				}
			} else {
				$this->setup['width'] = '100%';
			}
		}

		$this->recentlyPath = $this->modulePath.'/templet/recently';
		$this->recentlyDir = $this->moduleDir.'/templet/recently';

		$this->mPlugin = new Plugin($this);
	}
	
	function GetSetup($key) {
		return isset($this->setup[$key]) == true ? $this->setup[$key] : '';
	}

	// GET 변수 정리
	function GetQueryString($var=array(),$queryString='',$encode=true) {
		$queryString = $queryString ? $queryString : $this->baseQueryString;
		if (Request('keyword') == null) {
			$var['key'] = '';
			$var['keyword'] = '';
			$var['amp;mode'] = '';
		}

		return GetQueryString($var,$queryString,$encode);
	}

	// 회원정보
	function GetMemberInfo($mno) {
		$mData = $this->mMember->GetMemberInfo($mno);
		$uniqueID = 'Release'.$mno.'-'.GetMicrotime();
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

	function GetCategoryName($category) {
		if ($category != null && $category != '0') {
			$data = $this->mDB->DBfetch($this->table['category'],array('category'),"where `idx`='$category'");
			return isset($data['category']) == true ? $data['category'] : '';
		} else {
			return '';
		}
	}

	function GetReleaseTitle($rid='') {
		$rid = $rid ? $rid : $this->rid;
		$data = $this->mDB->DBfetch($this->table['setup'],array('title'),"where `rid`='$rid'");
		return $data['title'];
	}

	function CheckAdmin() {
		return $this->member['type'] == 'ADMINISTRATOR' || $this->member['type'] == 'MODERATOR';
	}

	function SetContent($content) {
		$content = str_replace('http://'.$_SERVER['HTTP_HOST'],'{$moduleHost}',$content);
		$content = str_replace($this->moduleDir,'{$moduleDir}',$content);

		return $content;
	}

	function GetContent($content) {
		$content = str_replace('{$moduleDir}',$this->moduleDir,$content);
		$content = str_replace('{$moduleHost}','http://'.$_SERVER['HTTP_HOST'],$content);
		$content = strip_tags($content,'<p>,<a>,<embed>,<blockquote>,<table>,<tr>,<td>,<b>,<i>,<u>,<div>,<font>,<span>,<img>,<br>');
		$content = str_replace(array('onclick','onload','onerror'),'event',$content);
		$content = '<section class="smartOutput">'.$content.'</section>';

		if (preg_match_all('/<img[^>]+file="([^"]+)"[^>]+movie="([^\"]+)"[^>]+(style="[^"]+")[^>]*>/',$content,$match) == true) {
			for ($i=0, $loop=sizeof($match[0]);$i<$loop;$i++) {
				$file = $this->mDB->DBfetch($this->table['file'],array('filepath','filetype'),"where `idx`='{$match[1][$i]}'");
				if ($file['filetype'] == 'MOV') {
					if (preg_match('/(Safari|Chrome)/',$_SERVER['HTTP_USER_AGENT']) == true) {
						$content = str_replace($match[0][$i],'<video src="'.$_ENV['userfileDir'].$this->userfile.$file['filepath'].'" '.$match[3][$i].' controls="controls" preload="preload"></video>',$content);
					} else {
						$content = str_replace($match[0][$i],'<embed src="'.$_ENV['userfileDir'].$this->userfile.$file['filepath'].'" '.$match[3][$i].' autostart="false" showcontrols="true" showstatusbar="true" controller="true"></embed>',$content);
					}
				}
			}
		}

		return $this->GetReplaceKeyword($content);
	}

	function GetReplaceKeyword($content) {
		if (Request('keyword') != null) {
			$keyword = str_replace(' ','|',GetString(Request('keyword'),'reg'));

			$content = preg_replace('/('.$keyword.')/','<span class="keyword">\\1</span>',$content);
		}
		return $content;
	}

	function GetSortLink($params) {
		if ($params['sort'] == 'title') $dir = 'asc';
		else $dir = 'desc';
		return $this->baseURL.$this->GetQueryString(array('p'=>'','mode'=>'list','idx'=>'','sort'=>$params['sort'],'dir'=>$dir));
	}

	// 템플릿 출력
	function PrintTemplet() {
		$time = array('server'=>time(),'gmt'=>GetGMT());
		$this->link['prevURL'] = isset($this->setup['prevURL']) == true ? $this->setup['prevURL'] : '';
		$this->link['page'] = $this->baseURL.$this->GetQueryString(array('p'=>'','mode'=>'list','idx'=>'')).'&amp;p=';
		$this->link['list'] = $this->baseURL.$this->GetQueryString(array('mode'=>'list','idx'=>''));
		$this->link['post'] = $this->baseURL.$this->GetQueryString(array('sort'=>'','dir'=>'','key'=>'','keyword'=>'','p'=>'','mode'=>'write','idx'=>''));
		$this->link['modify'] = $this->baseURL.$this->GetQueryString(array('mode'=>'modify','idx'=>Request('idx')));
		$this->link['delete'] = $this->baseURL.$this->GetQueryString(array('mode'=>'delete','idx'=>Request('idx')));
		$this->link['back'] = isset($_SERVER['HTTP_REFERER']) == true ? $_SERVER['HTTP_REFERER'] : '';

		$this->mTemplet->assign('rid',$this->rid);
		$this->mTemplet->assign('setup',$this->setup);
		$this->mTemplet->assign('member',$this->member);
		$this->mTemplet->assign('skinDir',$this->skinDir);
		$this->mTemplet->assign('moduleDir',$this->moduleDir);
		$this->mTemplet->assign('thumbnailDir',$_ENV['userfileDir'].$this->thumbnail);
		$this->mTemplet->assign('time',$time);
		$this->mTemplet->assign('link',$this->link);
		$this->mTemplet->assign('action',$this->action);
		if (sizeof($this->linkedModule) > 0) {
			foreach ($this->linkedModule as $name=>$module) {
				$this->mTemplet->assign_by_ref($name,$module);
			}
		}
		$this->mTemplet->PrintTemplet();
	}

	// 템플릿 처리
	function GetTemplet() {
		$time = array('server'=>time(),'gmt'=>GetGMT());
		$this->link['page'] = $this->baseURL.$this->GetQueryString(array('p'=>'','mode'=>'list','idx'=>'')).'&amp;p=';
		$this->link['list'] = $this->baseURL.$this->GetQueryString(array('mode'=>'list','idx'=>''));
		$this->link['post'] = $this->baseURL.$this->GetQueryString(array('sort'=>'','dir'=>'','key'=>'','keyword'=>'','p'=>'','mode'=>'write','idx'=>''));
		$this->link['modify'] = $this->baseURL.$this->GetQueryString(array('mode'=>'modify'));
		$this->link['delete'] = $this->baseURL.$this->GetQueryString(array('mode'=>'delete'));
		$this->link['back'] = isset($_SERVER['HTTP_REFERER']) == true ? $_SERVER['HTTP_REFERER'] : '';

		$this->mTemplet->assign('rid',$this->rid);
		$this->mTemplet->assign('member',$this->member);
		$this->mTemplet->assign('skinDir',$this->skinDir);
		$this->mTemplet->assign('moduleDir',$this->moduleDir);
		$this->mTemplet->assign('time',$time);
		$this->mTemplet->assign('link',$this->link);
		if (sizeof($this->linkedModule) > 0) {
			foreach ($this->linkedModule as $name=>$module) {
				$this->mTemplet->assign_by_ref($name,$module);
			}
		}
		return $this->mTemplet->GetTemplet();
	}

	// 헤더출력
	function PrintHeader() {
		if ($this->isHeaderIncluded == true) return;

		if ($_ENV['isHeaderIncluded'] == false) {
			GetDefaultHeader($this->setup['title']);
		}

		echo "\n".'<script type="text/javascript">var isMobile = false;</script>'."\n";

		echo "\n".'<!-- Module Release Start -->'."\n";
		if ($this->isHeaderIncluded == false) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" />'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
		}
		$this->isHeaderIncluded = true;

		if ($this->mode != 'list' && CheckIncluded('wysiwyg') == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/module/wysiwyg/script/wysiwyg.js"></script>'."\n";
		}

		if ($this->rid) {
			echo '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" title="style" />'."\n";
			echo '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";
			echo '<div class="ModuleRelease" style="width:'.$this->setup['width'].'">'."\n";
		}
	}

	// 푸터출력
	function PrintFooter() {
		if ($this->isFooterIncluded == true) return;
		$this->isFooterIncluded = true;

		if ($this->rid) echo "\n".'</div>'."\n";
		echo "\n".'<!-- Module Release End -->'."\n";
	}

	// 에러출력
	function PrintError($msg='') {
		$this->PrintHeader();

		if (file_exists($this->skinPath.'/error.tpl') == true) {
			$this->mTemplet = new Templet($this->skinPath.'/error.tpl');
		} else {
			$this->mTemplet = new Templet($this->modulePath.'/templet/error.tpl');
		}
		$this->mTemplet->assign('msg',$msg);

		$this->PrintTemplet();

		$this->PrintFooter();
		return false;
	}

	// 패스워드 입력폼 출력
	function PrintInputPassword($msg='',$action='',$target='_self') {
		$this->PrintHeader();

		if (file_exists($this->skinPath.'/password.tpl') == true) {
			$this->mTemplet = new Templet($this->skinPath.'/password.tpl');
		} else {
			$this->mTemplet = new Templet($this->modulePath.'/templet/password.tpl');
		}
		$formStart = '<form name="InputPassword" method="post" action="'.$action.'" target="'.$target.'">';
		$formEnd = '</form><iframe name="execFrame" style="display:none;"></iframe>';
		$this->mTemplet->assign('msg',$msg);
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);

		$this->PrintTemplet();

		$this->PrintFooter();
		return false;
	}

	// 확인창 출력
	function PrintConfirm($msg='',$action='',$back='',$target='') {
		$this->PrintHeader();

		if (file_exists($this->skinPath.'/confirm.tpl') == true) {
			$this->mTemplet = new Templet($this->skinPath.'/confirm.tpl');
		} else {
			$this->mTemplet = new Templet($this->modulePath.'/templet/confirm.tpl');
		}
		$formStart = '<form name="Confirm" method="post" action="'.$action.'" target="'.$target.'">';
		$formEnd = '</form><iframe name="execFrame" style="display:none;"></iframe>';
		$this->link['cancel'] = $back;
		$this->mTemplet->assign('msg',$msg);
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);

		$this->PrintTemplet();

		$this->PrintFooter();
		return false;
	}

	// 댓글처리
	function GetMent($idx) {
		$post = $this->mDB->DBfetch($this->table['post'],array('mno','is_ment'),"where `idx`='$idx'");
		$data = $this->mDB->DBfetchs($this->table['ment'],'*',"where `repto`=$idx",'idx,asc');

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			$data[$i]['content'] = $this->GetContent($data[$i]['content']);
			$data[$i]['reply'] = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="20" /><col width="100%" /><tr><td></td><td id="MentReplyForm'.$data[$i]['idx'].'"></td></tr><tr><td></td><td id="MentReplyList'.$data[$i]['idx'].'"></td></tr></table>';

			if ($data[$i]['mno'] == '0') {
				$data[$i]['photo'] = $_ENV['dir'].'/images/common/nomempic60.gif';
				$data[$i]['nickname'] = $data[$i]['name'];
			} else {
				$mData = $this->GetMemberInfo($data[$i]['mno']);
				$data[$i]['name'] = $mData['name'];
				$data[$i]['nickname'] = $mData['nickname'];
				$data[$i]['photo'] = $mData['photo'];
				$data[$i]['email'] = $data[$i]['email'] ? $data[$i]['email'] : $mData['email'];
				$data[$i]['homepage'] = $data[$i]['homepage'] ? $data[$i]['homepage'] : $mData['homepage'];
			}

			if ($data[$i]['parent']) {
				$data[$i]['replyStart'] = '<div id="ReplyMent'.$data[$i]['idx'].'">'."\n";
				$data[$i]['replyEnd'] = '</div>'."\n".'<script type="text/javascript">ReplyMentPosition('.$data[$i]['parent'].','.$data[$i]['idx'].');</script>'."\n";
			}

			$data[$i]['is_delete'] = $data[$i]['is_delete'] == 'TRUE';

			$data[$i]['link'] = array();
			$data[$i]['link']['modify'] = $this->baseURL.$this->GetQueryString(array('mode'=>'ment_modify','repto'=>$idx,'idx'=>$data[$i]['idx']));
			$data[$i]['link']['delete'] = $this->baseURL.$this->GetQueryString(array('mode'=>'ment_delete','idx'=>$data[$i]['idx']));
			$data[$i]['link']['reply'] = $this->baseURL.$this->GetQueryString(array('mode'=>'ment_write','repto'=>$idx,'parent'=>$data[$i]['idx']));

			$data[$i]['action'] = array();
			$data[$i]['action']['reply'] = 'ReplyMent('.$idx.','.$data[$i]['idx'].');';
			$data[$i]['action']['select'] = 'SelectMent('.$data[$i]['idx'].');';

			if ($data[$i]['last_modify_hit'] > 0) {
				$data[$i]['last_modify'] = array();
				if ($data[$i]['last_modify_mno'] != 0) {
					$mData = $this->mMember->GetMemberInfo($data[$i]['last_modify_mno']);
					$data[$i]['last_modify']['editor'] = $mData['user_id'];
				} else {
					$data[$i]['last_modify']['editor'] = 'Unknown';
				}
				$data[$i]['last_modify']['date'] = strtotime(GetTime('c',$data[$i]['last_modify_date']));
				$data[$i]['last_modify']['hit'] = $data[$i]['last_modify_hit'];
			} else {
				$data[$i]['last_modify'] = array('hit'=>0,'editor'=>'','date'=>'');
			}

			$file = $this->mDB->DBfetchs($this->table['file'],'*',"where `type`='ment' and `repto`={$data[$i]['idx']}");

			for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
				$file[$j]['filesize'] = GetFileSize($file[$j]['filesize']);
				$file[$j]['link'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file[$j]['idx'];
			}
			$data[$i]['file'] = $file;
		}

		$actionTarget = 'mentFrame'.rand(100,999);
		$mentStart = $mentEnd = '';
		$mentStart = '<form name="ModuleReleaseMent'.$idx.'" method="post" action="'.$this->moduleDir.'/exec/Release.do.php" target="'.$actionTarget.'" onsubmit="return CheckMent(this)" enctype="multipart/form-data">'."\n";
		$mentStart.= '<input type="hidden" name="action" value="ment" />'."\n";
		$mentStart.= '<input type="hidden" name="rid" value="'.$this->rid.'" />'."\n";
		$mentStart.= '<input type="hidden" name="mode" value="post" />'."\n";
		$mentStart.= '<input type="hidden" name="repto" value="'.$idx.'" />'."\n";
		$mentStart.= '<input type="hidden" name="parent" value="" />'."\n";
		$mentEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";
		$mentEnd.= '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite'.$idx.'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("MentWrite'.$idx.'");</script>';

		$permission = array();
		$permission['ment'] = ($this->GetPermission('ment') == true && $post['is_ment'] == 'TRUE') || $this->CheckAdmin();

		// 댓글입력폼 출력
		if ($permission['ment'] == true) {
			$actionTarget = 'mentFrame'.rand(100,999);

			$formStart = '<div id="PostMentForm'.$idx.'">'."\n";
			$formEnd = '</div>'."\n";

			if ($this->mMember->IsLogged() == false) {
				$mAntiSpam = new AntiSpam();
				$antispam = $mAntiSpam->GetAntiSpamCode();
			} else {
				$antispam = '';
			}

			$this->mTemplet = new Templet($this->skinPath.'/ment_write.tpl');
			$this->mTemplet->assign('formName','ModuleReleaseMent'.$idx);
			$this->mTemplet->assign('wysiwygName','MentWrite'.$idx);
			$this->mTemplet->assign('uploaderName','MentUploader'.$idx);
			$this->mTemplet->assign('formStart',$formStart);
			$this->mTemplet->assign('formEnd',$formEnd);
			$this->mTemplet->assign('data',array('name'=>GetString(Request('iModuleReleaseName','cookie'),'ext'),'password'=>'','email'=>GetString(Request('iModuleReleaseEmail','cookie'),'ext'),'homepage'=>GetString(Request('iModuleReleaseHomepage','cookie'),'ext'),'content'=>'','is_msg'=>'TRUE'));
			$this->mTemplet->assign('mode','post');
			$this->mTemplet->assign('antispam',$antispam);
			$this->mTemplet->register_object('mRelease',$this,array('PrintUploader'));

			$ment_write = $this->GetTemplet();
		}

		$this->link['postment'] = $this->baseURL.GetQueryString(array('mode'=>'ment_write','repto'=>$idx,'idx'=>''));

		$this->mTemplet = new Templet($this->skinPath.'/ment.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('ment_write',$ment_write);
		$this->mTemplet->assign('mentStart',$mentStart);
		$this->mTemplet->assign('mentEnd',$mentEnd);
		$this->mTemplet->assign('permission',$permission);

		return $this->GetTemplet();
	}
	
	function CheckPayment($repto,$mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		
		return $this->mDB->DBcount($this->table['payment'],"where `repto`='$repto' and `mno`='$mno'") > 0;
	}
	
	// 나의 게시물 출력
	function PrintMyList($skin,$listnum,$mno='') {
		$this->PrintHeader();
		
		$mno = $mno ? $mno : $this->member['idx'];
		
		$find = "where `mno`='$mno' and `is_delete`='FALSE'";
		$pagenum = 10;
		
		$keyword = Request('keyword') ? urldecode(Request('keyword')) : '';
		if ($keyword != null) {
			$mKeyword = new Keyword($keyword);
			$keyQuery = $mKeyword->GetFullTextKeyword(array('title','search'));
			$find.= ' and '.$keyQuery;
		}
		
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$totalpost = $this->mDB->DBcount($this->table['post'],$find);
		$totalpage = ceil($totalpost/$listnum) == 0 ? 1 : ceil($totalpost/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		if ($sort == 'idx' && $dir == 'desc') {
			$sort = 'loop';
			$dir = 'asc';
		}

		if ($this->idx != null) {
			$idx = $this->idx;
			$post = $this->mDB->DBfetch($this->table['post'],array($sort,'is_notice'),"where `idx`='$idx'");
			if ($post['is_notice'] == 'TRUE' && Request('p') != null) {
				$p = Request('p');
			} else {
				$prevFind = $find.' and (`'.$sort.'`'.($dir == 'desc' ? '>=' : '<=')."'".$post[$sort]."')";
				$prevNum = $this->mDB->DBcount($this->table['post'],$prevFind);
				$p = ceil($prevNum/$listnum);
			}
		}
		$orderer = $sort.','.$dir;
		$sort = Request('sort') ? Request('sort') : 'idx';
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['post'],'*',$find,$orderer,$limiter);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = GetString($data[$i]['title'],'replace');
			$data[$i]['title'] = $this->GetReplaceKeyword($data[$i]['title']);
			$data[$i]['postlink'] = $this->moduleDir.'/release.php?rid='.$data[$i]['rid'].'&amp;mode=view&amp;idx='.$data[$i]['idx'];
			
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			$data[$i]['hit'] = number_format($data[$i]['hit']);
			$data[$i]['vote'] = number_format($data[$i]['vote']);
			$data[$i]['avgvote'] = $data[$i]['voter'] > 0 ? sprintf('%0.2f',$data[$i]['vote']/$data[$i]['voter']) : '0.00';

			$data[$i]['is_file'] = $this->mDB->DBcount($this->table['file'],"where `repto`={$data[$i]['idx']} and `filetype`!='IMG'") > 0;
			$data[$i]['is_image'] = $data[$i]['image'] != '0';
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;

			$data[$i]['categoryIDX'] = $data[$i]['category'];
			if ($this->setup['use_category'] != 'FALSE' && $data[$i]['category'] != '0') {
				$data[$i]['category'] = $this->GetCategoryName($data[$i]['category']);
			} else {
				$data[$i]['category'] = '';
			}

			$data[$i]['board'] = $this->GetReleaseTitle($data[$i]['rid']);

			$data[$i]['is_select'] = false;
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
		
		if (file_exists($this->modulePath.'/templet/mylist/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/mylist/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		$query = $this->GetQueryString(array('p'=>''));
		$link = array(
			'page'=>$this->baseURL.$query.(preg_match('/\?/',$query) == true ? '&amp;' : '?').'p=',
			'sort'=>array(
				'idx'=>$this->baseURL.$this->GetQueryString(array('p'=>'','sort'=>'idx')),
				'last_ment'=>$this->baseURL.$this->GetQueryString(array('p'=>'','sort'=>'last_ment')),
				'hit'=>$this->baseURL.$this->GetQueryString(array('p'=>'','sort'=>'last_ment')),
				'vote'=>$this->baseURL.$this->GetQueryString(array('p'=>'','sort'=>'last_ment'))
			)
		);
		
		$searchFormStart = '<form name="ModuleBoardSearch" action="'.$this->baseURL.'" enctype="application/x-www-form-urlencoded">';
		$searchFormEnd = '</form>';
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/mylist/'.$skin.'/list.tpl');
		$this->mTemplet->assign('skinDir',$this->moduleDir.'/templet/mylist/'.$skin);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('totalpost',number_format($totalpost));
		$this->mTemplet->assign('totalpage',number_format($totalpage));
		$this->mTemplet->assign('searchFormStart',$searchFormStart);
		$this->mTemplet->assign('searchFormEnd',$searchFormEnd);
		$this->mTemplet->assign('keyword',GetString(urldecode($keyword),'inputbox'));
		$this->mTemplet->assign('p',$p);
		$this->mTemplet->assign('sort',$sort);
		$this->mTemplet->assign('link',$link);
		$this->mTemplet->PrintTemplet();
		
		$this->PrintFooter();
	}

	// 상황별 페이지 출력
	function PrintRelease($find='') {
		if ($find) $this->find.= " and ($find)";
		if ($this->module === false) return;
		if (preg_match('/\$/',$this->rid) == false && isset($this->setup['rid']) == false) {
			return $this->PrintError($this->rid.' 릴리즈게시판을 찾을 수 없습니다.');
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
			
			case 'version_write' :
				$this->PrintVersionWrite();
			break;
			
			case 'version_modify' :
				$this->PrintVersionWrite();
			break;
			
			case 'version_delete' :
				$this->PrintVersionDelete();
			break;

			case 'view' :
				$this->PrintView();
			break;

			case 'trash' :
				$this->PrintTrash();
			break;

			case 'delete' :
				$this->PrintDelete();
			break;

			case 'ment_write' :
				$this->PrintMentWrite();
			break;

			case 'ment_modify' :
				$this->PrintMentWrite();
			break;

			case 'ment_delete' :
				$this->PrintMentDelete();
			break;
		}

		$this->PrintFooter();
	}

	// 목록출력
	function PrintList() {
		if ($this->GetPermission('list') == false) return $this->PrintError('목록을 볼 수 있는 권한이 없습니다.');

		$category = Request('category');
		$find = $this->find;

		if ($category != null) $find.= " and `category`=$category";

		$key = Request('key') ? Request('key') : 'tc';
		$keyword = Request('keyword') ? urldecode(Request('keyword')) : '';

		if ($keyword != null) {
			$mKeyword = new Keyword($keyword);

			if ($key == 'tc') {
				$keyQuery = $mKeyword->GetFullTextKeyword(array('title','search'));
				$find.= ' and '.$keyQuery;
			}

			if ($key == 'name') {
				$searchMember = $this->mDB->DBfetchs($_ENV['table']['member'],array('idx'),"where `name` like '%$keyword%' or `nickname` like '%$keyword%'");
				$mno = array();
				for ($i=0, $loop=sizeof($searchMember);$i<$loop;$i++) {
					$mno[] = $searchMember[$i]['idx'];
				}
				$keyQuery = $mKeyword->GetFullTextKeyword(array('name'));
				if (sizeof($mno) > 0) {
					$find.= ' and ('.$keyQuery.' or `mno` IN ('.implode(',',$mno).'))';
				} else {
					$find.= ' and '.$keyQuery;
				}
			}

			if ($key == 'ment') {
				$keyQuery = $mKeyword->GetFullTextKeyword(array('search'));
				$searchMent = $this->mDB->DBfetchs($this->table['ment'],array('repto'),"where `is_delete`='FALSE' and `rid`='".$this->rid."' and ".$keyQuery);
				$ment = array();
				for ($i=0, $loop=sizeof($searchMent);$i<$loop;$i++) {
					if (in_array($searchMent[$i]['repto'],$ment) == false)$ment[] = $searchMent[$i]['repto'];
				}

				if (sizeof($ment) > 0) $find.= " and `idx` IN ('".implode('\',\'',$ment)."')";
			}
		}

		$listnum = $this->setup['listnum'];
		$pagenum = $this->setup['pagenum'];
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;

		$totalpost = $this->mDB->DBcount($this->table['post'],$find);
		$totalpage = ceil($totalpost/$listnum) == 0 ? 1 : ceil($totalpost/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		if ($sort == 'idx' and $dir == 'desc') {
			$sort = 'loop';
			$dir = 'asc';
		}

		if ($this->idx != null) {
			$idx = $this->idx;
			$post = $this->mDB->DBfetch($this->table['post'],array($sort),"where `idx`='$idx'");
			$prevFind = $find.' and (`'.$sort.'`'.($dir == 'desc' ? '>=' : '<=')."'".$post[$sort]."')";
			$prevNum = $this->mDB->DBcount($this->table['post'],$prevFind);
			$p = ceil($prevNum/$listnum);
		}
		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['post'],'*',$find,$orderer,$limiter);

		$loopnum = $totalpost-($p-1)*$listnum;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = GetString($data[$i]['title'],'replace');
			$data[$i]['title'] = $this->GetReplaceKeyword($data[$i]['title']);
			$data[$i]['is_read'] = $data[$i]['idx'] == Request('idx');
			$data[$i]['loopnum'] = $loopnum--;
			$data[$i]['postlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data[$i]['idx']));
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			$data[$i]['hit'] = number_format($data[$i]['hit']);
			$data[$i]['vote'] = number_format($data[$i]['vote']);
			$data[$i]['avgvote'] = $data[$i]['voter'] > 0 ? sprintf('%0.2f',$data[$i]['vote']/$data[$i]['voter']) : '0.00';
			$data[$i]['logo'] = file_exists($_ENV['userfilePath'].$this->logo.'/'.$data[$i]['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->logo.'/'.$data[$i]['idx'].'.thm' : '';

			$mData = $this->GetMemberInfo($data[$i]['mno']);
			$data[$i]['name'] = $mData['name'];
			$data[$i]['nickname'] = $mData['nickname'];
			$data[$i]['member'] = $this->mMember->GetMemberInfo($data[$i]['mno']);

			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;

			$data[$i]['categoryIDX'] = $data[$i]['category'];
			if ($this->setup['use_category'] != 'FALSE' && $data[$i]['category'] != '0') {
				$data[$i]['category'] = $this->GetCategoryName($data[$i]['category']);
			} else {
				$data[$i]['category'] = '';
			}
			
			$version = $this->mDB->DBfetch($this->table['version'],'*',"where `repto`='{$data[$i]['idx']}'",'idx,desc','0,1');
			$data[$i]['last_date'] = isset($version['reg_date']) == true ? strtotime(GetTime('c',$version['reg_date'])) : '';
			$data[$i]['is_new'] = $data[$i]['last_date'] > GetGMT()-60*60*24;
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

		$searchFormStart = '<form name="ModuleReleaseSearch" action="'.$this->baseURL.'" enctype="application/x-www-form-urlencoded">';

		if ($this->baseQueryString) {
			$querys = explode('&',$this->baseQueryString);

			for ($i=0, $loop=sizeof($querys);$i<$loop;$i++) {
				$temp = explode('=',$querys[$i]);
				if (in_array($temp[0],array('key','keyword','idx','mode')) == false) $searchFormStart.= '<input type="hidden" name="'.$temp[0].'" value="'.GetString(Request($temp[0]),'inputbox').'" />';
			}
		}
		foreach ($_POST as $keyname=>$value) {
			if (in_array($keyname,array('key','keyword')) == false) $searchFormStart.= '<input type="hidden" name="'.$keyname.'" value="'.GetString(Request($keyname),'inputbox').'" />';
		}
		foreach ($_POST as $keyname=>$value) {
			if (in_array($keyname,array('key','keyword')) == false) $searchFormStart.= '<input type="hidden" name="'.$keyname.'" value="'.GetString(Request($keyname),'inputbox').'" />';
		}
		$searchFormEnd = '</form>';

		$categoryName = '';
		$categoryList = array();
		if ($this->setup['use_category'] != 'FALSE') {
			$categoryList = $this->mDB->DBfetchs($this->table['category'],array('idx','category'),"where `rid`='{$this->rid}'",'sort,asc');
			for ($i=0, $loop=sizeof($categoryList);$i<$loop;$i++) {
				if ($categoryList[$i]['idx'] == $category) $categoryName = $categoryList[$i]['category'];
			}
		}
		
		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';

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
		$this->mTemplet->assign('searchFormStart',$searchFormStart);
		$this->mTemplet->assign('searchFormEnd',$searchFormEnd);
		$this->mTemplet->assign('key',$key);
		$this->mTemplet->assign('keyword',GetString(urldecode($keyword),'inputbox'));
		$this->mTemplet->assign('category',$category);
		$this->mTemplet->assign('categoryName',$categoryName);
		$this->mTemplet->assign('categoryList',$categoryList);
		$this->mTemplet->assign('sort',$sort);
		$this->mTemplet->assign('p',$p);

		$this->mTemplet->register_object('mRelease',$this,array('GetSortLink','GetThumbnail'));
		$this->PrintTemplet();
	}

	// 읽기출력
	function PrintView() {
		$idx = $this->idx;

		$find = $this->find;
		$find.= " and `idx`='$idx'";
		$data = $this->mDB->DBfetch($this->table['post'],'*',$find);

		if ($this->module === false) return;
		if (isset($data['idx']) == false) {
			return $this->PrintError('해당 프로그램을 찾을 수 없습니다.<br />글이 지워졌거나, 링크가 잘못되었습니다.');
		}

		echo "\n".'<script type="text/javascript">document.title = document.title+" » '.strip_tags($data['title']).'";</script>'."\n";

		if ($this->GetPermission('view') == false) {
			if ($data['mno'] != '0' && $data['mno'] != $this->member['idx']) {
				return $this->PrintError('글을 읽을 수 있는 권한이 없습니다.');
			} else if ($data['mno'] == '0') {
				$password = Request('password');
				if ($password == null) {
					return $this->PrintInputPassword('글을 읽기위해 게시물의 패스워드를 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
				} else {
					if (md5($password) != $data['password']) {
						return $this->PrintInputPassword('패스워드가 일치하지 않습니다.<br />패스워드를 정확히 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
					}
				}
			}
		}

		if ($this->mDB->DBcount($this->table['log'],"where `repto`=$idx and `type`='HIT' and (`mno`={$this->member['idx']} or `ip`='".$_SERVER['REMOTE_ADDR']."')") == 0) {
			$this->mDB->DBupdate($this->table['post'],'',array('hit'=>'`hit`+1'),"where `idx`='$idx'");
			$this->mDB->DBinsert($this->table['log'],array('rid'=>$data['rid'],'repto'=>$idx,'type'=>'HIT','mno'=>($this->mMember->IsLogged() == true ? $this->member['idx'] : -1),'ip'=>$_SERVER['REMOTE_ADDR'],'reg_date'=>GetGMT()));
			if ($this->mMember->IsLogged() == true) $this->mMember->SendExp($this->member['idx'],2);
		}

		$mData = $this->GetMemberInfo($data['mno']);
		$data['name'] = $mData['name'];
		$data['nickname'] = $mData['nickname'];
		$data['photo'] = $mData['photo'];
		$data['email'] = $mData['email'];
		$data['homepage'] = $data['homepage'] ? $data['homepage'] : $mData['homepage'];

		if ($data['last_modify_hit'] > 0) {
			$data['last_modify'] = array();
			if ($data['last_modify_mno'] != 0) {
				$mData = $this->mMember->GetMemberInfo($data['last_modify_mno']);
				$data['last_modify']['editor'] = $mData['user_id'];
			} else {
				$data['last_modify']['editor'] = 'Unknown';
			}
			$data['last_modify']['date'] = strtotime(GetTime('c',$data['last_modify_date']));
			$data['last_modify']['hit'] = $data['last_modify_hit'];
		} else {
			$data['last_modify'] = array('hit'=>0,'editor'=>'','date'=>'');
		}

		$data['title'] = GetString($data['title'],'replace');
		$data['content'] = $this->GetContent($data['content']);
		$data['reg_date'] = strtotime(GetTime('c',$data['reg_date']));
		$data['payment'] = $this->CheckPayment($idx);

		if ($this->setup['use_category'] != 'FALSE' && $data['category'] != '0') {
			$data['category'] = $this->GetCategoryName($data['category']);
		} else {
			$data['category'] = '';
		}
		
		$data['logo'] = file_exists($_ENV['userfilePath'].$this->logo.'/'.$data['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->logo.'/'.$data['idx'].'.thm' : '';

		$permission = array();
		$permission['addversion'] = $data['mno'] == $this->member['idx'];
		$permission['ment'] = $this->GetPermission('ment') == true && $data['is_ment'] == 'TRUE';
		$permission['modify'] = $data['mno'] == $this->member['idx'] || $this->GetPermission('modify');
		$permission['delete'] = $data['mno'] == $this->member['idx'] || $this->GetPermission('delete');

		$file = $this->mDB->DBfetchs($this->table['file'],'*',"where `type`='post' and `repto`=$idx");

		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$file[$i]['filesize'] = GetFileSize($file[$i]['filesize']);
			$file[$i]['link'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file[$i]['idx'];
		}
		
		$version = $this->mDB->DBfetchs($this->table['version'],'*',"where `repto`='$idx'",'idx,desc');
		for ($i=0, $loop=sizeof($version);$i<$loop;$i++) {
			$version[$i]['history'] = $this->GetContent($version[$i]['history']);
			$version[$i]['reg_date'] = strtotime(GetTime('c',$version[$i]['reg_date']));
			
			$version[$i]['link'] = array(
				'modify'=>$this->baseURL.GetQueryString(array('mode'=>'version_modify','idx'=>$idx,'vidx'=>$version[$i]['idx'])),
				'delete'=>$this->baseURL.GetQueryString(array('mode'=>'version_delete','idx'=>$idx,'vidx'=>$version[$i]['idx'])),
				'download'=>$this->moduleDir.'/exec/ProgramDownload.do.php?idx='.$idx.'&version='.$version[$i]['idx']
			);
		}

		$ment = $this->GetMent($idx);
		$this->action['vote'] = 'PostVote('.$idx.')';
		$this->link['addversion'] = $this->baseURL.GetQueryString(array('mode'=>'version_write','idx'=>$idx));
		$this->link['postment'] = $this->baseURL.GetQueryString(array('mode'=>'ment_write','repto'=>$idx,'idx'=>''));
		$this->link['download_lastest'] = $this->moduleDir.'/exec/ProgramDownload.do.php?idx='.$idx.'&version=lastest';

		$this->mTemplet = new Templet($this->skinPath.'/view.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('file',$file);
		$this->mTemplet->assign('ment',$ment);
		$this->mTemplet->assign('version',$version);
		$this->mTemplet->assign('permission',$permission);

		$this->PrintTemplet();

		echo "\n".'<iframe name="downloadFrame" style="display:none;"></iframe><iframe name="execFrame" style="display:none;"></iframe>'."\n";
		if ($this->setup['view_alllist'] == 'TRUE') {
			echo '<div class="height30"></div>'."\n";
			$this->PrintList();
		}
	}

	function PrintTrash() {
		$idx = $this->idx;

		$find = "where `idx`='$idx'";
		$data = $this->mDB->DBfetch($this->table['post'],'*',$find);

		if ($this->module === false) return;
		if (isset($data['idx']) == false) {
			return $this->PrintError('해당 게시물을 찾을 수 없습니다.<br />글이 완전 삭제되었거나, 링크가 잘못되었습니다.');
		}

		if ($this->GetPermission('trash') == false) {
			return $this->PrintError('삭제된 게시물을 열람할 권한이 없습니다.');
		}

		echo "\n".'<script type="text/javascript">document.title = document.title+" » '.strip_tags($data['title']).'";</script>'."\n";

		$mData = $this->GetMemberInfo($data['mno']);
		$data['name'] = $mData['name'];
		$data['nickname'] = $mData['nickname'];
		$data['photo'] = $mData['photo'];
		$data['email'] = $mData['email'];
		$data['homepage'] = $data['homepage'] ? $data['homepage'] : $mData['homepage'];

		if ($data['last_modify_hit'] > 0) {
			$data['last_modify'] = array();
			if ($data['last_modify_mno'] != 0) {
				$mData = $this->mMember->GetMemberInfo($data['last_modify_mno']);
				$data['last_modify']['editor'] = $mData['user_id'];
			} else {
				$data['last_modify']['editor'] = 'Unknown';
			}
			$data['last_modify']['date'] = strtotime(GetTime('c',$data['last_modify_date']));
			$data['last_modify']['hit'] = $data['last_modify_hit'];
		} else {
			$data['last_modify'] = array('hit'=>0,'editor'=>'','date'=>'');
		}

		$data['title'] = GetString($data['title'],'replace');
		$data['content'] = $this->GetContent($data['content']);
		$data['reg_date'] = strtotime(GetTime('c',$data['reg_date']));
		
		if ($this->setup['use_category'] != 'FALSE' && $data['category'] != '0') {
			$data['category'] = $this->GetCategoryName($data['category']);
		} else {
			$data['category'] = '';
		}

		$permission = array();
		$permission['ment'] = ($this->GetPermission('ment') == true && $data['is_ment'] == 'TRUE') || $this->CheckAdmin() == true;
		$permission['delete'] = $this->GetPermission('delete');

		$file = $this->mDB->DBfetchs($this->table['file'],'*',"where `type`='post' and `repto`=$idx");

		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$file[$i]['filesize'] = GetFileSize($file[$i]['filesize']);
			$file[$i]['link'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file[$i]['idx'];
		}

		$ment = $this->GetMent($idx);
		$this->action['vote'] = 'PostVote('.$idx.')';
		$this->link['postment'] = $this->baseURL.GetQueryString(array('mode'=>'ment_write','repto'=>$idx,'idx'=>''));

		$this->mTemplet = new Templet($this->skinPath.'/view.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('file',$file);
		$this->mTemplet->assign('ment',$ment);
		$this->mTemplet->assign('permission',$permission);

		$this->PrintTemplet();

		echo "\n".'<iframe name="downloadFrame" style="display:none;"></iframe><iframe name="execFrame" style="display:none;"></iframe>'."\n";
		if ($this->setup['view_alllist'] == 'TRUE') {
			echo '<div class="height30"></div>'."\n";
			$this->PrintList();
		}
	}

	// 쓰기 출력
	function PrintWrite() {
		$idx = $this->idx;
		$mode = Request('mode') == 'modify' && $idx != null ? 'modify' : 'post';

		if ($mode == 'modify') {
			$post = $this->mDB->DBfetch($this->table['post'],'*',"where `rid`='{$this->rid}' and `idx`='$idx'");

			if ($this->GetPermission('modify') == false && $post['mno'] != $this->member['idx']) {
				return $this->PrintError('글을 수정할 권한이 없습니다.');
			}
			$post['title'] = GetString($post['title'],'inputbox');
			$post['content'] = str_replace('{$moduleDir}',$this->moduleDir,$post['content']);
			$post['content'] = str_replace('{$moduleHost}','http://'.$_SERVER['HTTP_HOST'],$post['content']);

			$extraValue = unserialize($post['extra_content']);
			if (is_array($extraValue) == true) {
				foreach ($extraValue as $extra=>$value) {
					$post['extra_'.$extra] = $value;
				}
			}
		} else {
			if ($this->GetPermission('post') == false) return $this->PrintError('글을 작성할 수 있는 권한이 없습니다.');
			$post = array('image'=>'','category'=>Request('category'),'title'=>'','content'=>'','homepage'=>GetString(Request('iModuleReleaseHomepage','cookie'),'ext'),'is_ment'=>'TRUE','is_msg'=>'TRUE');
		}
		$actionTarget = 'postFrame'.rand(100,999);
		$formStart = '<form name="ModuleReleasePost" method="post" action="'.$this->moduleDir.'/exec/Release.do.php" target="'.$actionTarget.'" onsubmit="return CheckPost(this)" enctype="multipart/form-data">'."\n";
		$formStart.= '<input type="hidden" name="action" value="post" />'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.$mode.'" />'."\n";
		$formStart.= '<input type="hidden" name="rid" value="'.$this->rid.'" />'."\n";
		$formStart.= '<input type="hidden" name="idx" value="'.$idx.'" />'."\n";
		$formStart.= '<input type="hidden" name="image" value="'.$post['image'].'" />'."\n";
		$formEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";

		if ($mode == 'modify') $formEnd.= '<script type="text/javascript">AzUploaderComponent.load("repto='.$idx.'");</script>';

		$categoryName = '';
		$categoryList = array();
		if ($this->setup['use_category'] != 'FALSE') {
			$cData = $this->mDB->DBfetchs($this->table['category'],array('idx','category','permission'),"where `rid`='{$this->rid}'",'sort,asc');
			$categoryList = array();
			for ($i=0, $loop=sizeof($cData);$i<$loop;$i++) {
				if ($cData[$i]['permission'] == '' || eval($cData[$i]['permission'])) {
					if ($post['category'] == $cData[$i]['idx']) $categoryName = $cData[$i]['category'];
					$categoryList[] = array('idx'=>$cData[$i]['idx'],'category'=>$cData[$i]['category']);
				}
			}
		}

		$this->mTemplet = new Templet($this->skinPath.'/write.tpl');
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('formName','ModuleReleasePost');
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('post',$post);
		$this->mTemplet->assign('category','카테고리');
		$this->mTemplet->assign('categoryList',$categoryList);
		$this->mTemplet->assign('categoryName',$categoryName);
		$this->mTemplet->register_object('mRelease',$this,array('PrintUploader','PrintWysiwyg'));
		$this->PrintTemplet();
	}
	
	function PrintVersionWrite() {
		$idx = $this->idx;
		$vidx = Request('vidx');
		$post = $this->mDB->DBfetch($this->table['post'],'*',"where `rid`='{$this->rid}' and `idx`='$idx'");
		
		$mData = $this->GetMemberInfo($post['mno']);
		$post['member'] = $this->mMember->GetMemberInfo($post['mno']);
		$post['name'] = $mData['name'];
		$post['nickname'] = $mData['nickname'];
		$post['photo'] = $mData['photo'];
		$post['homepage'] = $post['homepage'] ? $post['homepage'] : $mData['homepage'];
		$post['reg_date'] = strtotime(GetTime('c',$post['reg_date']));
		$post['logo'] = file_exists($_ENV['userfilePath'].$this->logo.'/'.$post['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->logo.'/'.$post['idx'].'.thm' : '';
		
		$mode = Request('mode') == 'version_modify' && $idx != null ? 'modify' : 'post';

		if ($mode == 'modify') {
			$version = $this->mDB->DBfetch($this->table['version'],'*',"where `idx`='$vidx'");
			if (isset($version['idx']) == false) return $this->PrintError('해당 버전이 존재하지 않습니다.');
			if ($version['repto'] != $idx) return $this->PrintError('잘못된 접근입니다.');

			if ($this->GetPermission('modify') == false) {
				if ($post['mno'] != $this->member['idx']) {
					return $this->PrintError('글을 수정할 권한이 없습니다.');
				}
			}
		} else {
			$version = array();
			if ($post['mno'] != $this->member['idx']) return $this->PrintError('글을 작성할 수 있는 권한이 없습니다.');
		}
		
		$actionTarget = 'postFrame'.rand(100,999);
		$formStart = '<form name="ModuleReleasePost" method="post" action="'.$this->moduleDir.'/exec/Release.do.php" target="'.$actionTarget.'" onsubmit="return CheckPost(this)" enctype="multipart/form-data">'."\n";
		$formStart.= '<input type="hidden" name="action" value="version" />'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.$mode.'" />'."\n";
		$formStart.= '<input type="hidden" name="rid" value="'.$this->rid.'" />'."\n";
		$formStart.= '<input type="hidden" name="idx" value="'.$idx.'" />'."\n";
		$formStart.= '<input type="hidden" name="vidx" value="'.$vidx.'" />'."\n";
		$formEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";

		$this->mTemplet = new Templet($this->skinPath.'/version.tpl');
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('formName','ModuleReleasePost');
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('post',$post);
		$this->mTemplet->assign('version',$version);
		$this->mTemplet->register_object('mRelease',$this,array('PrintWysiwyg'));
		$this->PrintTemplet();
	}

	// 삭제출력
	function PrintDelete() {
		$idx = $this->idx;
		$find = $this->find;
		$find.= " and `idx`='$idx'";
		$data = $this->mDB->DBfetch($this->table['post'],'*',$find);

		if ($this->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				$this->PrintInputPassword('게시물을 삭제하기 위하여 패스워드를 입력하여 주십시오.',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=post&idx='.$idx,'execFrame');
			} elseif ($data['mno'] == $this->member['idx']) {
				$this->PrintConfirm('게시물을 삭제하시겠습니까?',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=post&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view')),'execFrame');
			} else {
				return $this->PrintError('글을 삭제할 권한이 없습니다.');
			}
		} else {
			$this->PrintConfirm('게시물을 삭제하시겠습니까?',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=post&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view')),'execFrame');
		}
	}
	
	function PrintVersionDelete() {
		$idx = $this->idx;
		$vidx = Request('vidx');
		$find = $this->find;
		$find.= " and `idx`='$idx'";
		$data = $this->mDB->DBfetch($this->table['post'],'*',$find);

		if ($this->GetPermission('delete') == false) {
			if ($data['mno'] == $this->member['idx']) {
				$this->PrintConfirm('해당 버전을 삭제하시겠습니까?',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=version&idx='.$idx.'&vidx='.$vidx,$this->baseURL.$this->GetQueryString(array('mode'=>'view')),'execFrame');
			} else {
				return $this->PrintError('버전을 삭제할 권한이 없습니다.');
			}
		} else {
			$this->PrintConfirm('해당 버전을 삭제하시겠습니까?',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=version&idx='.$idx.'&vidx='.$vidx,$this->baseURL.$this->GetQueryString(array('mode'=>'view')),'execFrame');
		}
	}

	// 댓글입력폼 출력
	function PrintMentWrite() {
		$repto = Request('repto');
		if ($repto == null) return $this->PrintError('해당 글을 찾을 수 없습니다.');
		$idx = $this->idx;
		$mode = Request('mode');
		$post = $this->mDB->DBfetch($this->table['post'],array('is_ment'),"where `idx`='{$repto}'");

		if ($mode == 'ment_modify') {
			$data = $this->mDB->DBfetch($this->table['ment'],'*',"where `idx`='$idx'");
			if (isset($data['idx']) == false) return $this->PrintError('해당 댓글을 찾을 수 없습니다.');
			$repto = $data['repto'];

			if ($this->GetPermission('modify') == false) {
				if ($data['mno'] == '0') {
					$password = Request('password');
					if ($password == null) {
						return $this->PrintInputPassword('댓글의 패스워드를 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
					} else {
						if (md5($password) != $data['password']) {
							return $this->PrintInputPassword('패스워드가 일치하지 않습니다.<br />패스워드를 정확히 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
						}
					}
				} elseif ($data['mno'] != $this->member['idx']) {
					return $this->PrintError('댓글을 수정할 권한이 없습니다.');
				}
			}

			$data['name'] = GetString($data['name'],'inputbox');
			$data['password'] = $password = ArzzEncoder(Request('password'));
			$data['email'] = GetString($data['email'],'inputbox');
			$data['homepage'] = GetString($data['homepage'],'inputbox');
			$data['content'] = str_replace('{$moduleDir}',$this->moduleDir,$data['content']);
			$data['is_msg'] = $data['is_msg'];
			$data['is_email'] = $data['is_email'];
		} else {
			if ($this->GetPermission('ment') == false) return $this->PrintError('댓글을 작성할 권한이 없습니다.');
			$password = '';
			$data = array('name'=>GetString(Request('iModuleReleaseName','cookie'),'ext'),'password'=>'','email'=>GetString(Request('iModuleReleaseEmail','cookie'),'ext'),'homepage'=>GetString(Request('iModuleReleaseHomepage','cookie'),'ext'),'content'=>'','parent'=>(Request('parent') ? Request('parent') : '0'),'is_msg'=>'TRUE');
		}

		$actionTarget = 'mentFrame'.rand(100,999);
		$formStart = $formEnd = '';
		$formStart = '<form name="ModuleReleaseMent'.$repto.'" method="post" action="'.$this->moduleDir.'/exec/Release.do.php" target="'.$actionTarget.'" onsubmit="return CheckMent(this)" enctype="multipart/form-data">'."\n";
		$formStart.= '<input type="hidden" name="action" value="ment" />'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.($mode == 'ment_modify' ? 'modify' : 'post').'" />'."\n";
		$formStart.= '<input type="hidden" name="rid" value="'.$this->rid.'" />'."\n";
		$formStart.= '<input type="hidden" name="repto" value="'.$repto.'" />'."\n";
		$formStart.= '<input type="hidden" name="idx" value="'.$idx.'" />'."\n";
		$formStart.= '<input type="hidden" name="parent" value="'.$data['parent'].'" />'."\n";
		$formStart.= '<input type="hidden" name="check_password" value="'.$password.'" />'."\n";
		$formEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";
		$formEnd.= '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite'.$repto.'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("MentWrite'.$repto.'");</script>';

		if ($mode == 'ment_modify') $formEnd.= '<script type="text/javascript">AzUploaderComponent.load("repto='.$idx.'");</script>';

		$permission = array();
		$permission['ment'] = $this->GetPermission('ment') == true && $post['is_ment'] == 'TRUE';

		if ($mode != 'ment_modify' && $permission['ment'] == false) {

		}

		$this->link['view'] = $this->baseURL.GetQueryString(array('mode'=>'view','idx'=>$repto,'repto'=>'','parent'=>''));

		if ($this->mMember->IsLogged() == false) {
			$mAntiSpam = new AntiSpam();
			$antispam = $mAntiSpam->GetAntiSpamCode();
		} else {
			$antispam = '';
		}

		$this->mTemplet = new Templet($this->skinPath.'/ment_write.tpl');
		$this->mTemplet->assign('formName','ModuleReleaseMent'.$repto);
		$this->mTemplet->assign('wysiwygName','MentWrite'.$repto);
		$this->mTemplet->assign('uploaderName','MentUploader'.$repto);
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('mode',$mode == 'ment_modify' ? 'modify' : 'post');
		$this->mTemplet->assign('antispam',$antispam);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->register_object('mRelease',$this,array('PrintUploader'));

		$this->PrintTemplet();
	}

	// 댓글삭제출력
	function PrintMentDelete() {
		$idx = $this->idx;
		$data = $this->mDB->DBfetch($this->table['ment'],'*',"where `idx`='$idx'");

		if ($this->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				$this->PrintInputPassword('댓글을 삭제하기 위하여 패스워드를 입력하여 주십시오.',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=ment&idx='.$idx,'execFrame');
			} elseif ($data['mno'] == $this->member['idx']) {
				$this->PrintConfirm('댓글을 삭제하시겠습니까?',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=ment&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data['repto'])),'execFrame');
			} else {
				return $this->PrintError('댓글을 삭제할 권한이 없습니다.');
			}
		} else {
			$this->PrintConfirm('댓글을 삭제하시겠습니까?',$this->moduleDir.'/exec/Release.do.php?action=delete&mode=ment&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data['repto'])),'execFrame');
		}
	}

	// 최근게시물 출력
	function PrintRecently($skin,$page,$row,$limit='',$title='',$finder='',$orderer='') {
		$this->PrintHeader();
		
		if (preg_match('/\$/',$this->rid) == false && isset($this->setup['rid']) == false) {
			return $this->PrintError($this->rid.' 게시판을 찾을 수 없습니다.');
		}

		$title = $title ? $title : $this->setup['title'];
		$find = $this->find;
		$find.= $finder ? ' and '.$finder : '';
		$orderer = $orderer ? $orderer : 'loop,asc';
		$data = $this->mDB->DBfetchs($this->table['post'],array('idx','category','name','mno','title','content','search','image','reg_date','ment','last_ment','image'),$find,$orderer,'0,'.$row);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = $limit ? GetCutString($data[$i]['title'],$limit,true) : $data[$i]['title'];
			$data[$i]['title'] = GetString($data[$i]['title'],'replace');
			$data[$i]['content'] = $this->GetContent($data[$i]['content']);
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;
			$data[$i]['postlink'] = $page.(preg_match('/\?/',$page) == true ? '&amp;' : '?').'mode=view&amp;idx='.$data[$i]['idx'];
			$data[$i]['image'] = $data[$i]['image'] != '0' ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['image'].'.thm' : '';
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));

			if ($data[$i]['mno'] == '0') {
				$data[$i]['photo'] = $_ENV['dir'].'/images/common/nomempic60.gif';
				$data[$i]['nickname'] = $data[$i]['name'];
			} else {
				$mData = $this->GetMemberInfo($data[$i]['mno']);
				$data[$i]['name'] = $mData['name'];
				$data[$i]['nickname'] = $mData['nickname'];
				$data[$i]['photo'] = $mData['photo'];
			}

			if ($this->setup['use_category'] != 'FALSE' && $data[$i]['category'] != '0') {
				$data[$i]['category'] = $this->GetCategoryName($data[$i]['category']);
			} else {
				$data[$i]['category'] = '';
			}
		}

		if (file_exists($this->recentlyPath.'/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->recentlyDir.'/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		$this->mTemplet = new Templet($this->recentlyPath.'/'.$skin.'/list.tpl');
		$this->mTemplet->assign('title',$title);
		$this->mTemplet->assign('skinDir',$this->recentlyDir.'/'.$skin);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->PrintTemplet();

		$this->PrintFooter();
	}

	// 인기게시물 출력
	function PrintRecentlyHot($skin,$page,$row,$hotPosition=100,$limit='',$title='',$finder='') {
		$this->PrintHeader();

		$title = $title ? $title : $this->setup['title'];
		$find = $this->find;
		$find.= $finder ? ' and '.$finder : '';

		$mLast = array_pop($this->mDB->DBfetchs($this->table['post'],array('reg_date'),$find,'reg_date,desc','0,'.$hotPosition));
		$find.= " and `reg_date`>=".(isset($mLast['reg_date']) == true ? $mLast['reg_date'] : '0');
		$find.= $finder ? ' and '.$finder : '';
		$data = $this->mDB->DBfetchs($this->table['post'],array('idx','mno','title','search','image','reg_date','ment','last_ment','image'),$find,'hit,desc','0,'.$row);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = $limit ? GetCutString($data[$i]['title'],$limit,true) : $data[$i]['title'];
			$data[$i]['title'] = GetString($data[$i]['title'],'replace');
			$data[$i]['content'] = $data[$i]['search'];
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;
			$data[$i]['postlink'] = $page.(preg_match('/\?/',$page) == true ? '&amp;' : '?').'mode=view&amp;idx='.$data[$i]['idx'];
			$data[$i]['image'] = $data[$i]['image'] != '0' ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['image'].'.thm' : '';
			$data[$i]['logo'] = file_exists($_ENV['userfilePath'].$this->logo.'/'.$data[$i]['idx'].'.thm') == true ? $_ENV['userfileDir'].$this->logo.'/'.$data[$i]['idx'].'.thm' : '';
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
		}

		if (file_exists($this->recentlyPath.'/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->recentlyDir.'/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		$this->mTemplet = new Templet($this->recentlyPath.'/'.$skin.'/list.tpl');
		$this->mTemplet->assign('title',$title);
		$this->mTemplet->assign('skinDir',$this->recentlyDir.'/'.$skin);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('mode','board');
		$this->mTemplet->PrintTemplet();

		$this->PrintFooter();
	}

	function PrintMyPost($skin,$row,$limit='',$page='',$title='나의 최근 글 목록',$finder='') {
		$this->PrintHeader();
		$title = $title ? $title : $this->setup['title'];

		if ($this->mMember->IsLogged() == true) {
			$find = "where `mno`={$this->member['idx']} and `is_delete`='FALSE'";
			$find.= $finder ? ' and '.$finder : '';
			$data = $this->mDB->DBfetchs($this->table['post'],array('idx','rid','category','name','mno','title','search','image','reg_date','ment','last_ment'),$find,'loop,asc','0,'.$row);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$data[$i]['title'] = $limit ? GetCutString($data[$i]['title'],$limit,true) : $data[$i]['title'];
				$data[$i]['title'] = GetString($data[$i]['title'],'replace');
				$data[$i]['content'] = $data[$i]['search'];
				$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;
				$data[$i]['postlink'] = $this->moduleDir.'/release.php?rid='.$data[$i]['rid'].'&amp;mode=view&amp;idx='.$data[$i]['idx'];
				$data[$i]['image'] = $data[$i]['image'] != '0' ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['image'].'.thm' : '';
				$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));

				if ($this->setup['use_category'] != 'FALSE' && $data[$i]['category'] != '0') {
					$data[$i]['category'] = $this->GetCategoryName($data[$i]['category']);
				} else {
					$data[$i]['category'] = '';
				}
			}
		} else {
			$data = array();
		}

		if (file_exists($this->recentlyPath.'/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->recentlyDir.'/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		$this->mTemplet = new Templet($this->recentlyPath.'/'.$skin.'/list.tpl');
		$this->mTemplet->assign('title',$title);
		$this->mTemplet->assign('skinDir',$this->recentlyDir.'/'.$skin);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('mode','mypost');
		$this->mTemplet->PrintTemplet();

		$this->PrintFooter();
	}

	function PrintMyPostMent($skin,$row,$limit='',$page='',$title='나의 글의 최근 댓글 목록',$finder='') {
		$this->PrintHeader();
		$title = $title ? $title : $this->setup['title'];

		if ($this->mMember->IsLogged() == true) {
			$find = "where `postmno`={$this->member['idx']} and `mno`!={$this->member['idx']}";
			$find.= $finder ? ' and '.$finder : '';

			$data = $this->mDB->DBfetchs($this->table['ment'],array('repto','name','mno','search','reg_date'),$find,'idx,desc','0,'.$row);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$post = $this->mDB->DBfetch($this->table['post'],array('idx','rid','title','ment','last_ment'),"where `idx`='{$data[$i]['repto']}'");
				$data[$i]['title'] = $limit ? GetCutString($post['title'],$limit,true) : $post['title'];
				$data[$i]['ment'] = $post['ment'];
				$data[$i]['is_newment'] = $post['last_ment'] > GetGMT()-60*60*24;
				$data[$i]['content'] = $data[$i]['search'];
				$data[$i]['postlink'] = $this->moduleDir.'/release.php?rid='.$post['rid'].'&amp;mode=view&amp;idx='.$post['idx'];
				$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			}
		} else {
			$data = array();
		}

		if (file_exists($this->recentlyPath.'/'.$skin.'/style.css') == true) {
			echo '<link rel="stylesheet" href="'.$this->recentlyDir.'/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		}

		$this->mTemplet = new Templet($this->recentlyPath.'/'.$skin.'/list.tpl');
		$this->mTemplet->assign('title',$title);
		$this->mTemplet->assign('skinDir',$this->recentlyDir.'/'.$skin);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('mode','myment');
		$this->mTemplet->PrintTemplet();

		$this->PrintFooter();
	}

	function PrintUploader($var) {
		$mModule = new Module('uploader');
		if ($mModule->IsSetup() == true) {
			$use_uploader = true;
			$this->mUploader = new ModuleUploader();
		}

		$this->mUploader->SetCaller('board',$this);
		$this->mUploader->SetUploadPath($this->moduleDir.'/exec/FileUpload.do.php?type='.strtoupper($var['type']).'&wysiwyg='.$var['wysiwyg']);
		$this->mUploader->SetLoadPath($this->moduleDir.'/exec/FileLoad.do.php?type='.strtoupper($var['type']).'&wysiwyg='.$var['wysiwyg']);
		$uploader = $this->mUploader->GetUploader($var['skin'],$var['id'],$var['form'],$var['wysiwyg']);

		return $uploader;
	}

	function PrintWysiwyg($var) {
		return '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"'.$var['id'].'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("'.$var['id'].'");</script>';
	}

	function GetChildMent($idx) {
		$child = $this->mDB->DBfetchs($this->table['ment'],array('idx','is_delete'),"where `parent`=$idx",'is_delete,asc');
		for ($i=0, $loop=sizeof($child);$i<$loop;$i++) {
			if ($child[$i]['is_delete'] == 'TRUE') return $this->GetChildMent($child[$i]['idx']);
			else return true;
		}
		return false;
	}

	function CheckParentMent($idx) {
		$data = $this->mDB->DBfetch($this->table['ment'],array('idx','parent','is_delete'),"where `idx`='$idx'");
		if ($data['is_delete'] == 'TRUE' && $this->GetChildMent($idx) == false) {
			$this->mDB->DBdelete($this->table['ment'],"where `idx`='$idx'");
		}

		if ($data['parent'] != '0' && $data['is_delete'] == 'TRUE') $this->CheckParentMent($data['parent']);
	}

	function GetPermission($geter) {
		if ($this->mMember->IsLogged() == false && ($geter == 'post' || $geter == 'modify')) return false;
		$permission = $this->setup['permission'] && is_array(unserialize($this->setup['permission'])) == true ? unserialize($this->setup['permission']) : array('list'=>true,'post'=>true,'ment'=>true,'select'=>false,'secret'=>false,'notice'=>false,'modify'=>false,'delete'=>false);
		if ($this->member['type'] == 'ADMINISTRATOR') return true;
		if (isset($permission[$geter]) == false || $permission[$geter] === '') return true;

		return GetPermission($permission[$geter]);
	}

	function GetThumbnail($var) {
		if (CreateDirectory($_ENV['userfilePath'].$this->skinThumbnail.'/'.$this->rid) == true) {
			if ($var['post']['image'] == '0') {
				return $var['error'];
			} elseif(file_exists($_ENV['userfilePath'].$this->skinThumbnail.'/'.$this->rid.'/'.$var['post']['idx'].'.thm') == false) {
				$image = $this->mDB->DBfetch($this->table['file'],array('filepath'),"where `idx`='{$var['post']['image']}'");
				if ($var['width'] > 0 && $var['height'] > 0) {
					$width = $var['width'];
					$height = $var['height'];
				} elseif ($var['width'] > 0) {
					$check = @getimagesize($_ENV['userfilePath'].$this->userfile.$image['filepath']);
					$width = $var['width'];
					$height = ceil($check[1]*$width/$check[0]);
				} elseif ($var['height'] > 0) {
					$check = @getimagesize($_ENV['userfilePath'].$this->userfile.$image['filepath']);
					$height = $var['height'];
					$width = ceil($check[0]*$height/$check[1]);
				}

				if ($width < $var['min_width']) $width = $var['min_width'];
				if ($height < $var['min_height']) $height = $var['min_height'];

				if ($width > 0 && $height > 0 && GetThumbnail($_ENV['userfilePath'].$this->userfile.$image['filepath'],$_ENV['userfilePath'].$this->skinThumbnail.'/'.$this->rid.'/'.$var['post']['idx'].'.thm',$width,$height) == true) {
					return $_ENV['userfileDir'].$this->skinThumbnail.'/'.$this->rid.'/'.$var['post']['idx'].'.thm';
				} else {
					return $var['error'];
				}
			} else {
				return $_ENV['userfileDir'].$this->skinThumbnail.'/'.$this->rid.'/'.$var['post']['idx'].'.thm';
			}
		}
	}

	function FileDelete($idx) {
		$file = $this->mDB->DBfetch($this->table['file'],array('filepath'),"where `idx`='$idx'");
		$this->mDB->DBdelete($this->table['file'],"where `idx`='$idx'");

		if ($this->mDB->DBcount($this->table['file'],"where `filepath`='{$file['filepath']}'") == 0) {
			@unlink($_ENV['userfilePath'].$this->userfile.$file['filepath']);
		}
	}

	function GetTable() {
		return $this->table;
	}
}
?>
