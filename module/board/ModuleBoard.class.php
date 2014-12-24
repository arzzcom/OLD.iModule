<?php
class ModuleBoard extends Module {
	public $table = array();
	public $bid = null;

	protected $mTemplet;
	protected $mPlugin;
	protected $mKeyword;

	public $find;
	public $setup;

	public $userfile;
	public $thumbnail;
	public $skinThumbnail;

	public $totalpost;
	public $skinPath;
	public $skinDir;
	public $recentlyPath;
	public $recentlyDir;
	public $mode;
	public $idx;
	
	public $baseURL;
	public $baseQueryString;

	protected $isHeaderIncluded;
	protected $isFooterIncluded;
	protected $mUploader;

	protected $link = array();
	protected $action = array();
	
	protected $linkedModule = array();

	function __construct($bid='',$setup='') {
		$this->table['setup'] = $_ENV['code'].'_board_table';
		$this->table['post'] = $_ENV['code'].'_board_post_table';
		$this->table['ment'] = $_ENV['code'].'_board_ment_table';
		$this->table['category'] = $_ENV['code'].'_board_category_table';
		$this->table['file'] = $_ENV['code'].'_board_file_table';
		$this->table['log'] = $_ENV['code'].'_board_log_table';
		$this->table['autosave'] = $_ENV['code'].'_board_autosave_table';
		$this->table['status'] = $_ENV['code'].'_board_status_table';
		$this->userfile = '/board';
		$this->thumbnail = '/board/thumbnail';
		$this->skinThumbnail = '/board/skin';
		
		parent::__construct('board');
		
		if ($this->CheckInstalled('keyword') == true) {
			$this->mKeyword = new ModuleKeyword();
		} else {
			$this->mKeyword = new Keyword();
		}

		if ($bid) {
			$this->bid = $bid;
			$this->find = "where `bid`='{$bid}' and `is_delete`='FALSE'";
		}

		$this->idx = $this->mDB->AntiInjection(Request('idx'));
		$this->mode = Request('mode') ? Request('mode') : ($this->idx == null ? 'list' : 'view');

		$this->isHeaderIncluded = false;
		$this->isFooterIncluded = false;
		$this->mUploader = null;

		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));
		$this->baseQueryString = sizeof(explode('?',$_SERVER['REQUEST_URI'])) > 1 ? array_pop(explode('?',$_SERVER['REQUEST_URI'])) : '';

		if ($bid) {
			$this->setup = $this->mDB->DBfetch($this->table['setup'],'*',"where `bid`='{$bid}'");
			
			if (isset($this->setup['skin']) == true) {
				$this->skinPath = $this->modulePath.'/templet/board/'.$this->setup['skin'];
				$this->skinDir = $this->moduleDir.'/templet/board/'.$this->setup['skin'];
				$this->setup['width'] = preg_match('/%/',$this->setup['width']) ? $this->setup['width'] : $this->setup['width'].'px';
			
				if (is_array($setup) == true) {
					foreach ($setup as $key=>$value) $this->setup[$key] = $value;
				}
			
				if ($_ENV['isMobile'] == true) {
					$this->setup['pagenum'] = 3;
				}
		
				if (isset($setup['skin']) == true) {
					$this->setup['skin'] = $setup['skin'];
					$this->skinPath = $this->modulePath.'/templet/board/'.$this->setup['skin'];
					$this->skinDir = $this->moduleDir.'/templet/board/'.$this->setup['skin'];
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
	
	// 작성자정보
	function GetAuthorInfo($info) {
		$author = array();
		$author = $this->mMember->GetMemberInfo($info['mno']);
		if ($info['mno'] == '0') {
			$author['name'] = $author['nickname'] = $info['name'];
			if (isset($info['email']) == true) $author['email'] = $info['email'];
			if (isset($info['homepage']) == true) $author['homepage'] = $info['homepage'];
		} else {
			$author['name'] = $this->mMember->GetMemberName($info['mno'],'name',true,true);
			$author['nickname'] = $this->mMember->GetMemberName($info['mno'],'nickname',true,true);
			if (isset($info['email']) == true) $author['email'] = $info['email'] ? $info['email'] : $author['email'];
			if (isset($info['homepage']) == true) $author['homepage'] = $info['homepage'] ? $info['homepage'] : $author['homepage'];
		}
		
		return $author;
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

	function GetCategoryName($category) {
		if ($category != null && $category != '0') {
			$data = $this->mDB->DBfetch($this->table['category'],array('category'),"where `idx`='$category'");
			return isset($data['category']) == true ? $data['category'] : '';
		} else {
			return '';
		}
	}

	function GetBoardTitle($bid='') {
		$bid = $bid ? $bid : $this->bid;
		$data = $this->mDB->DBfetch($this->table['setup'],array('title'),"where `bid`='$bid'");
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
		if ($_ENV['isMobile'] == true) $content = '<section><div class="smartOutputMobile">'.$content.'</div></section>';
		else $content = '<section><div class="smartOutput">'.$content.'</div></section>';

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
		$this->link['rss'] = 'http://'.$_SERVER['HTTP_HOST'].$this->moduleDir.'/rss.php?bid='.$this->bid;

		$this->mTemplet->assign('bid',$this->bid);
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

		$this->mTemplet->assign('bid',$this->bid);
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
	function PrintHeader($isBoard=true) {
		if ($this->isHeaderIncluded == true) return;

		if ($_ENV['isMobile'] == true) {
			if ($_ENV['isHeaderIncluded'] == false) {
				GetMobileHeader($this->setup['title'],'');
			}

			echo "\n".'<script type="text/javascript">var isMobile = true;</script>'."\n";
		} else {
			if ($_ENV['isHeaderIncluded'] == false) {
				GetDefaultHeader($this->setup['title']);
			}

			echo "\n".'<script type="text/javascript">var isMobile = false;</script>'."\n";
		}
		
		echo "\n".'<!-- Module Board Start -->'."\n";

		if ($isBoard == true) {
			if ($this->isHeaderIncluded == false) {
				echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" />'."\n";
				echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
			}
			$this->isHeaderIncluded = true;
	
			if ($this->mode != 'list') {
				echo '<script type="text/javascript" src="'.$_ENV['dir'].'/module/wysiwyg/script/wysiwyg.js"></script>'."\n";
			}
	
			if ($this->bid) {
				echo '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" title="style" />'."\n";
				echo '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";
				echo '<div class="ModuleBoard" style="width:'.$this->setup['width'].'">'."\n";
			}
		}
	}

	// 푸터출력
	function PrintFooter($isBoard=true) {
		if ($this->isFooterIncluded == true) return;
		$this->isFooterIncluded = true;

		if ($isBoard == true && $this->bid) echo "\n".'</div>'."\n";
		echo "\n".'<!-- Module Board End -->'."\n";
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

		if ($this->setup['use_select'] == 'TRUE') {
			$use_select = false;
			$select = $this->mDB->DBfetch($this->table['ment'],'*',"where `repto`=$idx and `is_select`='TRUE' and `is_delete`='FALSE'");
			if (isset($select['idx']) == true) {
				$use_select = false;
				$select['reg_date'] = strtotime(GetTime('c',$select['reg_date']));
				if ($select['is_mobile'] == 'TRUE') $select['content'] = nl2br($select['content']);
				$select['content'] = $this->GetContent($select['content']);

				$select['author'] = $this->GetAuthorInfo($select);

				$file = $this->mDB->DBfetchs($this->table['file'],'*',"where `type`='ment' and `repto`={$select['idx']}");

				for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
					$file[$i]['filesize'] = GetFileSize($file[$i]['filesize']);
					$file[$i]['link'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file[$i]['idx'];
				}
				$select['file'] = $file;
			} else {
				$use_select = true;
			}
		} else {
			$select = '';
			$use_select = false;
		}

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			if ($data[$i]['is_mobile'] == 'TRUE') $data[$i]['content'] = nl2br($data[$i]['content']);
			$data[$i]['content'] = $this->GetContent($data[$i]['content']);
			$data[$i]['reply'] = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="20" /><col width="100%" /><tr><td></td><td id="MentReplyForm'.$data[$i]['idx'].'"></td></tr><tr><td></td><td id="MentReplyList'.$data[$i]['idx'].'"></td></tr></table>';

			$data[$i]['author'] = $this->GetAuthorInfo($data[$i]);

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

			$data[$i]['select'] = false;
			if ($use_select == true) {
				if ($data[$i]['is_select'] == 'FALSE' && ($this->GetPermission('select') == true || ($data[$i]['mno'] != $this->member['idx'] && $post['mno'] != '0' && $post['mno'] == $this->member['idx']))) {
					$data[$i]['select'] = true;
				}
			}

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
		$mentStart = '<form name="ModuleBoardMent'.$idx.'" method="post" action="'.$this->moduleDir.'/exec/Board.do.php" target="'.$actionTarget.'" onsubmit="return CheckMent(this)" enctype="multipart/form-data">'."\n";
		if ($_ENV['isMobile'] == true) $mentStart.= '<input type="hidden" name="is_mobile" value="TRUE" />';
		$mentStart.= '<input type="hidden" name="action" value="ment" />'."\n";
		$mentStart.= '<input type="hidden" name="bid" value="'.$this->bid.'" />'."\n";
		$mentStart.= '<input type="hidden" name="mode" value="post" />'."\n";
		$mentStart.= '<input type="hidden" name="repto" value="'.$idx.'" />'."\n";
		$mentStart.= '<input type="hidden" name="parent" value="" />'."\n";
		$mentEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";
		if ($_ENV['isMobile'] == true) {
			$mentEnd.= '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite'.$idx.'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/mobile.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("MentWrite'.$idx.'");</script>';
		} else {
			$mentEnd.= '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite'.$idx.'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("MentWrite'.$idx.'");</script>';
		}

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
			$this->mTemplet->assign('formName','ModuleBoardMent'.$idx);
			$this->mTemplet->assign('wysiwygName','MentWrite'.$idx);
			$this->mTemplet->assign('uploaderName','MentUploader'.$idx);
			$this->mTemplet->assign('formStart',$formStart);
			$this->mTemplet->assign('formEnd',$formEnd);
			$this->mTemplet->assign('data',array('name'=>GetString(Request('iModuleBoardName','cookie'),'ext'),'password'=>'','email'=>GetString(Request('iModuleBoardEmail','cookie'),'ext'),'homepage'=>GetString(Request('iModuleBoardHomepage','cookie'),'ext'),'content'=>'','is_msg'=>'TRUE'));
			$this->mTemplet->assign('mode','post');
			$this->mTemplet->assign('antispam',$antispam);
			$this->mTemplet->register_object('mBoard',$this,array('PrintUploader'));

			$ment_write = $this->GetTemplet();
		} else {
			$ment_write = '';
		}

		$this->link['postment'] = $this->baseURL.GetQueryString(array('mode'=>'ment_write','repto'=>$idx,'idx'=>''));

		$this->mTemplet = new Templet($this->skinPath.'/ment.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('select',$select);
		$this->mTemplet->assign('ment_write',$ment_write);
		$this->mTemplet->assign('mentStart',$mentStart);
		$this->mTemplet->assign('mentEnd',$mentEnd);
		$this->mTemplet->assign('permission',$permission);

		return $this->GetTemplet();
	}
	
	function GetFileCount($val) {
		$find = 'where 1';
		if (isset($val['idx']) == true && $val['idx']) $find.= " and `repto`='{$val['idx']}'";
		if (isset($val['type']) == true && $val['idx']) $find.= " and `filetype`='{$val['type']}'"; 
		return $this->mDB->DBcount($this->table['file'],$find,'idx');
	}
	
	// 나의 게시물 출력
	function PrintMyList($skin,$listnum,$mno='') {
		$this->PrintHeader(false);
		
		$mno = $mno ? $mno : $this->member['idx'];
		
		$find = "where `mno`='$mno' and `is_delete`='FALSE'";
		$pagenum = 10;
		
		$keyword = Request('keyword') ? urldecode(Request('keyword')) : '';
		if ($keyword != null) {
			$keyQuery = $this->mKeyword->GetFullTextKeyword($keyword,array('title','search'));
			$find.= ' and '.$keyQuery;
		}
		
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;
		$totalpost = $this->mDB->DBcount($this->table['post'],$find,'idx');
		$totalpage = ceil($totalpost/$listnum) == 0 ? 1 : ceil($totalpost/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		if ($sort == 'idx' && $dir == 'desc') {
			$sort = 'loop';
			$dir = 'asc';
		}

		$orderer = $sort.','.$dir;
		$sort = Request('sort') ? Request('sort') : 'idx';
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = $this->mDB->DBfetchs($this->table['post'],'*',$find,$orderer,$limiter);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = $data[$i]['is_html_title'] == 'TRUE' ? $data[$i]['title'] : GetString($data[$i]['title'],'replace');
			$data[$i]['postlink'] = $this->moduleDir.'/board.php?bid='.$data[$i]['bid'].'&amp;mode=view&amp;idx='.$data[$i]['idx'];
			
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			$data[$i]['hit'] = number_format($data[$i]['hit']);
			$data[$i]['vote'] = number_format($data[$i]['vote']);
			$data[$i]['avgvote'] = $data[$i]['voter'] > 0 ? sprintf('%0.2f',$data[$i]['vote']/$data[$i]['voter']) : '0.00';

			$data[$i]['is_secret'] = $data[$i]['is_secret'] == 'TRUE';
			$data[$i]['is_file'] = $this->mDB->DBcount($this->table['file'],"where `type`='POST' and `repto`={$data[$i]['idx']} and `filetype`!='IMG'") > 0;
			$data[$i]['is_image'] = $data[$i]['image'] != '0';
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;

			$data[$i]['categoryIDX'] = $data[$i]['category'];
			if ($this->setup['use_category'] != 'FALSE' && $data[$i]['category'] != '0') {
				$data[$i]['category'] = $this->GetCategoryName($data[$i]['category']);
			} else {
				$data[$i]['category'] = '';
			}

			$data[$i]['board'] = $this->GetBoardTitle($data[$i]['bid']);

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
	function PrintBoard($find='') {
		if ($find) $this->find.= " and ($find)";
		if ($this->module === false) return;
		if (preg_match('/\$/',$this->bid) == false && isset($this->setup['bid']) == false) {
			return $this->PrintError($this->bid.' 게시판을 찾을 수 없습니다.');
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
		$viewer = explode(',',$this->setup['view_list']);

		$category = Request('category');
		$select = Request('select');
		$find = $this->find;

		if ($category != null) $find.= " and `category`=$category";
		if ($select == 'true') $find.= " and `is_select`='TRUE'";
		elseif ($select == 'false') $find.= " and `is_select`='FALSE'";
		elseif ($select == 'my' && $this->mMember->IsLogged() == true) $find.= " and `mno`='{$this->member['idx']}'";

		$key = Request('key') ? Request('key') : 'tc';
		$keyword = Request('keyword') ? urldecode(Request('keyword')) : '';

		if ($keyword != null) {
			if ($key == 'tc') {
				$keyQuery = $this->mKeyword->GetFullTextKeyword($keyword,array('title','search'));
				$find.= ' and '.$keyQuery;
			}

			if ($key == 'name') {
				$searchMember = $this->mDB->DBfetchs($_ENV['table']['member'],array('idx'),"where `name` like '%$keyword%' or `nickname` like '%$keyword%'");
				$mno = array();
				for ($i=0, $loop=sizeof($searchMember);$i<$loop;$i++) {
					$mno[] = $searchMember[$i]['idx'];
				}
				$keyQuery = "`name`='$keyword'";
				if (sizeof($mno) > 0) {
					$find.= ' and ('.$keyQuery.' or `mno` IN ('.implode(',',$mno).'))';
				} else {
					$find.= ' and '.$keyQuery;
				}
			}

			if ($key == 'ment') {
				$keyQuery = $this->mKeyword->GetFullTextKeyword($keyword,array('search'));
				$searchMent = $this->mDB->DBfetchs($this->table['ment'],array('repto'),"where `is_delete`='FALSE' and `bid`='".$this->bid."' and ".$keyQuery);
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

		if ($this->setup['view_notice_page'] != 'NONE' && ($this->setup['view_notice_page'] == 'ALL' || $p == '1')) {
			$notice = $this->mDB->DBfetchs($this->table['post'],'*',$this->find." and `is_notice`='TRUE'",'loop,asc');
			
			for ($i=0, $loop=sizeof($notice);$i<$loop;$i++) {
				$notice[$i]['title'] = $notice[$i]['is_html_title'] == 'TRUE' ? $notice[$i]['title'] : GetString($notice[$i]['title'],'replace');
				$notice[$i]['is_read'] = $notice[$i]['idx'] == Request('idx');
				$notice[$i]['postlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','p'=>$p,'idx'=>$notice[$i]['idx']));
				$notice[$i]['reg_date'] = strtotime(GetTime('c',$notice[$i]['reg_date']));
				$notice[$i]['hit'] = number_format($notice[$i]['hit']);
				$notice[$i]['vote'] = number_format($notice[$i]['vote']);
				$notice[$i]['avgvote'] = $notice[$i]['voter'] > 0 ? sprintf('%0.2f',$notice[$i]['vote']/$notice[$i]['voter']) : '0.00';

				$notice[$i]['author'] = $this->GetAuthorInfo($notice[$i]);

				$notice[$i]['is_secret'] = $notice[$i]['is_secret'] == 'TRUE';
				$notice[$i]['is_mobile'] = $notice[$i]['is_mobile'] == 'TRUE';
				$notice[$i]['is_new'] = $notice[$i]['reg_date'] > GetGMT()-60*60*24;
				$notice[$i]['is_file'] = $this->mDB->DBcount($this->table['file'],"where `type`='POST' and `repto`={$notice[$i]['idx']} and `filetype`!='IMG'") > 0;
				$notice[$i]['is_image'] = $notice[$i]['image'] != '0';
				$notice[$i]['is_newment'] = $notice[$i]['last_ment'] > GetGMT()-60*60*24;

				$notice[$i]['categoryIDX'] = $notice[$i]['category'];
				if ($this->setup['use_category'] != 'FALSE' && $notice[$i]['category'] != '0') {
					$notice[$i]['category'] = $this->GetCategoryName($notice[$i]['category']);
				} else {
					$notice[$i]['category'] = '';
				}

				$notice[$i]['is_select'] = false;
				if ($this->setup['use_select'] == 'TRUE') {
					if ($this->mDB->DBcount($this->table['ment'],"where `repto`={$notice[$i]['idx']} and `is_select`='TRUE'") > 0) $notice[$i]['is_select'] = true;
				}
			}
		} else {
			$notice = array();
		}

		if ($this->setup['view_notice_page'] != 'NONE' && $this->setup['view_notice_count'] == 'INCLUDE') {
			$listnum = $listnum-sizeof($notice);
		}

		if ($this->setup['view_notice_page'] != 'NONE' && $this->setup['view_notice_list'] == 'FALSE') $find.= " and `is_notice`='FALSE'";

		if ($select != null || $keyword != null) {
			$totalpost = $this->mDB->DBcount($this->table['post'],$find,'idx');
		} elseif ($category != null) {
			$temp = $this->mDB->DBfetch($this->table['category'],array('post'),"where `idx`='$category'");
			$totalpost = $temp['post'];
		} else {
			$temp = $this->mDB->DBfetch($this->table['setup'],array('post'),"where `bid`='{$this->bid}'");
			$totalpost = $temp['post'];
		}
		$totalpage = ceil($totalpost/$listnum) == 0 ? 1 : ceil($totalpost/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		if ($sort == 'idx' and $dir == 'desc') {
			$sort = 'loop';
			$dir = 'asc';
		}

		if ($this->idx != null && $p == null && preg_match('/'.array_shift(explode('?',$_SERVER['REQUEST_URI'])).'/',$_SERVER['HTTP_REFERER']) == false) {
			$idx = $this->idx;
			$post = $this->mDB->DBfetch($this->table['post'],array($sort,'is_notice'),"where `idx`='$idx'");
			if ($post['is_notice'] == 'TRUE' && Request('p') != null) {
				$p = Request('p');
			} else {
				$prevFind = $find.' and (`'.$sort.'`'.($dir == 'desc' ? '>=' : '<=')."'".$post[$sort]."')";
				$prevNum = $this->mDB->DBcount($this->table['post'],$prevFind,'idx');
				$p = ceil($prevNum/$listnum);
			}
		}
		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;
		
		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';

		$data = $this->mDB->DBfetchs($this->table['post'],'*',$find,$orderer,$limiter);
		$loopnum = $totalpost-($p-1)*$listnum;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = $data[$i]['is_html_title'] == 'TRUE' ? $data[$i]['title'] : GetString($data[$i]['title'],'replace');
			$data[$i]['postlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data[$i]['idx']));
			
			$data[$i]['is_read'] = $data[$i]['idx'] == Request('idx');
			$data[$i]['loopnum'] = $loopnum--;
			
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));
			$data[$i]['hit'] = number_format($data[$i]['hit']);
			$data[$i]['vote'] = number_format($data[$i]['vote']);
			$data[$i]['avgvote'] = $data[$i]['voter'] > 0 ? sprintf('%0.2f',$data[$i]['vote']/$data[$i]['voter']) : '0.00';

			$data[$i]['author'] = $this->GetAuthorInfo($data[$i]);

			$data[$i]['is_secret'] = $data[$i]['is_secret'] == 'TRUE';
			$data[$i]['is_mobile'] = $data[$i]['is_mobile'] == 'TRUE';
			$data[$i]['is_new'] = $data[$i]['reg_date'] > GetGMT()-60*60*24;
			$data[$i]['is_file'] = $this->mDB->DBcount($this->table['file'],"where `type`='POST' and `repto`={$data[$i]['idx']} and `filetype`!='IMG'") > 0;
			$data[$i]['is_image'] = $data[$i]['image'] != '0';
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;

			$data[$i]['categoryIDX'] = $data[$i]['category'];
			if ($this->setup['use_category'] != 'FALSE' && $data[$i]['category'] != '0') {
				$data[$i]['category'] = $this->GetCategoryName($data[$i]['category']);
			} else {
				$data[$i]['category'] = '';
			}

			if ($this->setup['use_select'] == 'TRUE') {
				if ($data[$i]['is_select'] == 'TRUE') {
					if ($this->mDB->DBcount($this->table['ment'],"where `repto`='{$data[$i]['idx']}' and `is_delete`='FALSE' and `is_select`='TRUE'") > 0) {
						$data[$i]['is_select'] = 'TRUE';
					} else {
						$data[$i]['is_select'] = 'COMPLETE';
					}
				} else {
					$data[$i]['is_select'] = 'FALSE';
				}
			}
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

		$searchFormStart = '<form name="ModuleBoardSearch" action="'.$this->baseURL.'" enctype="application/x-www-form-urlencoded">';

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
			$categoryList = $this->mDB->DBfetchs($this->table['category'],array('idx','category'),"where `bid`='{$this->bid}'",'sort,asc');
			for ($i=0, $loop=sizeof($categoryList);$i<$loop;$i++) {
				if ($categoryList[$i]['idx'] == $category) $categoryName = $categoryList[$i]['category'];
			}
		}

		$this->mTemplet = new Templet($this->skinPath.'/list.tpl');
		$this->mTemplet->assign('is_view_loopnum',in_array('loopnum',$viewer));
		$this->mTemplet->assign('is_view_name',in_array('name',$viewer));
		$this->mTemplet->assign('is_view_reg_date',in_array('reg_date',$viewer));
		$this->mTemplet->assign('is_view_hit',in_array('hit',$viewer));
		$this->mTemplet->assign('is_view_vote',in_array('vote',$viewer));
		$this->mTemplet->assign('is_view_avgvote',in_array('avgvote',$viewer));
		$this->mTemplet->assign('notice',$notice);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('sort',$sort);
		$this->mTemplet->assign('dir',$dir);
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
		$this->mTemplet->assign('select',$select);
		$this->mTemplet->assign('categoryName',$categoryName);
		$this->mTemplet->assign('categoryList',$categoryList);
		$this->mTemplet->assign('p',$p);
		$this->mTemplet->register_object('mBoard',$this,array('GetSortLink','GetThumbnail','GetFileCount'));
		$this->PrintTemplet();
	}

	// 읽기출력
	function PrintView() {
		$idx = $this->idx;

		$find = $this->find;
		$find.= " and `idx`='$idx'";
		$data = $this->mDB->DBfetch($this->table['post'],'*',$find);

		if ($this->module === false) return;
		if (isset($data['idx']) == false || $data['is_delete'] == 'TRUE') {
			return $this->PrintError('해당 게시물을 찾을 수 없습니다.<br />글이 지워졌거나, 링크가 잘못되었습니다.');
		}

		echo "\n".'<script type="text/javascript">document.title = document.title+" » '.strip_tags($data['title']).'";</script>'."\n";

		if ($data['is_secret'] == 'TRUE' && $this->GetPermission('secret') == false) {
			if ($data['mno'] != '0' && $data['mno'] != $this->member['idx']) {
				return $this->PrintError('비밀글을 읽을 수 있는 권한이 없습니다.');
			} else if ($data['mno'] == '0') {
				$password = Request('password');
				if ($password == null) {
					return $this->PrintInputPassword('비밀글을 읽기위해 게시물의 패스워드를 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
				} else {
					if (md5($password) != $data['password']) {
						return $this->PrintInputPassword('패스워드가 일치하지 않습니다.<br />패스워드를 정확히 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
					}
				}
			}
		}

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
			$this->mDB->DBinsert($this->table['log'],array('bid'=>$data['bid'],'repto'=>$idx,'type'=>'HIT','mno'=>($this->mMember->IsLogged() == true ? $this->member['idx'] : -1),'ip'=>$_SERVER['REMOTE_ADDR'],'reg_date'=>GetGMT()));
			if ($this->mMember->IsLogged() == true) $this->mMember->SendExp($this->member['idx'],2);
		}

		$data['author'] = $this->GetAuthorInfo($data);

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

		if ($data['is_mobile'] == 'TRUE') $data['content'] = nl2br($data['content']);

		$data['title'] = $data['is_html_title'] == 'TRUE' ? $data['title'] : GetString($data['title'],'replace');
		$data['content'] = $this->GetContent($data['content']);
		$data['reg_date'] = strtotime(GetTime('c',$data['reg_date']));

		if ($this->setup['use_category'] != 'FALSE' && $data['category'] != '0') {
			$data['category'] = $this->GetCategoryName($data['category']);
		} else {
			$data['category'] = '';
		}

		$permission = array();
		$permission['ment'] = $this->GetPermission('ment') == true && $data['is_ment'] == 'TRUE';
		$permission['delete'] = $this->GetPermission('delete');

		$file = $this->mDB->DBfetchs($this->table['file'],'*',"where `type`='post' and `repto`=$idx");

		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$file[$i]['filesize'] = GetFileSize($file[$i]['filesize']);
			$file[$i]['link'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file[$i]['idx'];
		}

		$ment = $this->GetMent($idx);
		$this->action['vote'] = 'PostVote('.$idx.')';
		$this->action['complete'] = 'CompletePost('.$idx.');';
		$this->link['postment'] = $this->baseURL.GetQueryString(array('mode'=>'ment_write','repto'=>$idx,'idx'=>''));
		
		$data['select'] = false;
		if ($this->setup['use_select'] == 'TRUE' && $data['is_select'] == 'FALSE'  && ($this->GetPermission('select') == true || ($data['mno'] == $this->member['idx'] && $data['mno'] != '0'))) {
			$data['select'] = true;
		}

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

		$data['author'] = $this->GetAuthorInfo($data);

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

		if ($data['is_mobile'] == 'TRUE') $data['content'] = nl2br($data['content']);

		$data['title'] = $data['is_html_title'] == 'TRUE' ? $data['title'] : GetString($data['title'],'replace');
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
		if ($this->setup['view_alllist'] == 'TRUE' && $_ENV['isMobile'] == false) {
			echo '<div class="height30"></div>'."\n";
			$this->PrintList();
		}
	}

	// 쓰기 출력
	function PrintWrite() {
		$idx = $this->idx;
		$mode = Request('mode') == 'modify' && $idx != null ? 'modify' : 'post';

		if ($mode == 'modify') {
			$post = $this->mDB->DBfetch($this->table['post'],'*',"where `bid`='{$this->bid}' and `idx`='$idx'");

			if ($this->GetPermission('modify') == false) {
				if ($post['mno'] == '0') {
					$password = Request('password');
					if ($password == null) {
						return $this->PrintInputPassword('게시물의 패스워드를 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
					} else {
						if (md5($password) != $post['password']) {
							return $this->PrintInputPassword('패스워드가 일치하지 않습니다.<br />패스워드를 정확히 입력하여 주십시오.',$this->baseURL.$this->GetQueryString());
						}
					}
				} elseif ($post['mno'] != $this->member['idx']) {
					return $this->PrintError('글을 수정할 권한이 없습니다.');
				}
			}
			$post['title'] = GetString($post['title'],'inputbox');
			$post['content'] = str_replace('{$moduleDir}',$this->moduleDir,$post['content']);
			$post['content'] = str_replace('{$moduleHost}','http://'.$_SERVER['HTTP_HOST'],$post['content']);
			$password = ArzzEncoder(Request('password'));
			$image = $post['image'];

			$extraValue = unserialize($post['extra_content']);
			if (is_array($extraValue) == true) {
				foreach ($extraValue as $extra=>$value) {
					$post['extra_'.$extra] = $value;
				}
			}

			if ($this->setup['use_uploader'] == 'FALSE') {
				$files = $this->mDB->DBfetchs($this->table['file'],'*',"where `type`='POST' and `repto`=$idx",'idx,asc');
				$file = array();
				for ($i=0, $loop=sizeof($files);$i<$loop;$i++) {
					$file[$i] = $files[$i]['idx'].'|'.$files[$i]['filetype'].'|'.$files[$i]['filename'].'|'.$files[$i]['filesize'].'|'.$files[$i]['wysiwyg'];
					if ($files[$i]['filetype'] == 'IMG') $file[$i].= '|'.$_ENV['dir'].$this->thumbnail.'/'.$files[$i]['idx'].'.thm';
				}
				$file = implode(',',$file);
			}
		} else {
			if ($this->GetPermission('post') == false) return $this->PrintError('글을 작성할 수 있는 권한이 없습니다.');
			if ($this->setup['use_select'] == 'TRUE' && $this->mMember->IsLogged() == true && $this->mDB->DBcount($this->table['post'],$this->find." and `mno`='{$this->member['idx']}' and `is_select`='FALSE' and `is_notice`='FALSE' and `reg_date`<".(GetGMT()-60*60*24*14),'idx') > 0) return $this->PrintError('답변을 채택하지 않거나, 완료처리를 하지 않은 2주일 이전질문이 있습니다.<br />이전 질문을 완료하신 후 새 질문을 등록하여 주십시오.<br /><br /><a href="'.$this->baseURL.$this->GetQueryString(array('mode'=>'list','select'=>'my','p'=>'1','key'=>'','keyword'=>'')).'">나의 질문목록보기</a>');
			$post = array('name'=>GetString(Request('iModuleBoardName','cookie'),'ext'),'category'=>Request('category'),'title'=>'','content'=>'','email'=>GetString(Request('iModuleBoardEmail','cookie'),'ext'),'homepage'=>GetString(Request('iModuleBoardHomepage','cookie'),'ext'),'is_notice'=>'FALSE','is_html_title'=>'FALSE','is_secret'=>'FALSE','is_ment'=>'TRUE','is_msg'=>'TRUE');
			$password = '';
			$image = '';
		}
		$actionTarget = 'postFrame'.rand(100,999);
		$formStart = '<form name="ModuleBoardPost" method="post" action="'.$this->moduleDir.'/exec/Board.do.php" target="'.$actionTarget.'" onsubmit="return CheckPost(this)" enctype="multipart/form-data">'."\n";
		if ($_ENV['isMobile'] == true) $formStart.= '<input type="hidden" name="is_mobile" value="TRUE" />';
		$formStart.= '<input type="hidden" name="action" value="post" />'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.$mode.'" />'."\n";
		$formStart.= '<input type="hidden" name="bid" value="'.$this->bid.'" />'."\n";
		$formStart.= '<input type="hidden" name="idx" value="'.$idx.'" />'."\n";
		$formStart.= '<input type="hidden" name="check_password" value="'.$password.'" />'."\n";
		$formStart.= '<input type="hidden" name="image" value="'.$image.'" />'."\n";
		$formEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";
		$formEnd.= '<div id="AutoSaverAlertBox" style="display:none;"></div>'."\n";

		if ($mode == 'modify' && $this->setup['use_uploader'] == 'TRUE') $formEnd.= '<script type="text/javascript">ModuleUploaderLoad("repto='.$idx.'");</script>';

		$autosaveFind = "where `bid`='{$this->bid}'";
		$autosaveFind.= $idx != null ? " and `repto`=$idx" : '';
		$autosaveFind.= " and `ip`='".$_SERVER['REMOTE_ADDR']."'";
		$autosave = $this->mDB->DBfetch($this->table['autosave'],array('tid','bid','reg_date'),$autosaveFind);

		if (isset($autosave['tid']) == true) $formEnd.= '<script type="text/javascript">GetAutoSave("'.$autosave['bid'].'","'.$autosave['tid'].'","'.GetTime('Y년 m월 d일 H시 i분',$autosave['reg_date']).'","'.$this->moduleDir.'/exec/Ajax.get.php","'.$this->setup['use_uploader'].'");</script>'."\n";

		$categoryName = '';
		$categoryList = array();
		if ($this->setup['use_category'] != 'FALSE') {
			$cData = $this->mDB->DBfetchs($this->table['category'],array('idx','category','permission'),"where `bid`='{$this->bid}'",'sort,asc');
			$categoryList = array();
			for ($i=0, $loop=sizeof($cData);$i<$loop;$i++) {
				$cData[$i]['permission'] = str_replace('{$category.idx}',$cData[$i]['idx'],$cData[$i]['permission']);
				$cData[$i]['permission'] = str_replace('{$category.name}',$cData[$i]['category'],$cData[$i]['permission']);
				
				if ($cData[$i]['permission'] == '' || GetPermission($cData[$i]['permission']) == true) {
					if ($post['category'] == $cData[$i]['idx']) $categoryName = $cData[$i]['category'];
					$categoryList[] = array('idx'=>$cData[$i]['idx'],'category'=>$cData[$i]['category']);
				}
			}
		}

		if ($this->mMember->IsLogged() == false) {
			$mAntiSpam = new AntiSpam();
			$antispam = $mAntiSpam->GetAntiSpamCode();
		} else {
			$antispam = '';
		}

		$this->mTemplet = new Templet($this->skinPath.'/write.tpl');
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('formName','ModuleBoardPost');
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('post',$post);
		$this->mTemplet->assign('category','카테고리');
		$this->mTemplet->assign('categoryList',$categoryList);
		$this->mTemplet->assign('categoryName',$categoryName);
		$this->mTemplet->assign('antispam',$antispam);
		$this->mTemplet->register_object('mBoard',$this,array('PrintUploader','PrintWysiwyg'));
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
				$this->PrintInputPassword('게시물을 삭제하기 위하여 패스워드를 입력하여 주십시오.',$this->moduleDir.'/exec/Board.do.php?action=delete&mode=post&idx='.$idx,'execFrame');
			} elseif ($data['mno'] == $this->member['idx']) {
				$this->PrintConfirm('게시물을 삭제하시겠습니까?',$this->moduleDir.'/exec/Board.do.php?action=delete&mode=post&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view')),'execFrame');
			} else {
				return $this->PrintError('글을 삭제할 권한이 없습니다.');
			}
		} else {
			$this->PrintConfirm('게시물을 삭제하시겠습니까?',$this->moduleDir.'/exec/Board.do.php?action=delete&mode=post&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view')),'execFrame');
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
			$data['is_secret'] = $data['is_secret'];
			$data['is_msg'] = $data['is_msg'];
			$data['is_email'] = $data['is_email'];
		} else {
			if ($this->GetPermission('ment') == false) return $this->PrintError('댓글을 작성할 권한이 없습니다.');
			$password = '';
			$data = array('name'=>GetString(Request('iModuleBoardName','cookie'),'ext'),'password'=>'','email'=>GetString(Request('iModuleBoardEmail','cookie'),'ext'),'homepage'=>GetString(Request('iModuleBoardHomepage','cookie'),'ext'),'content'=>'','parent'=>(Request('parent') ? Request('parent') : '0'),'is_msg'=>'TRUE');
		}

		$actionTarget = 'mentFrame'.rand(100,999);
		$formStart = $formEnd = '';
		$formStart = '<form name="ModuleBoardMent'.$repto.'" method="post" action="'.$this->moduleDir.'/exec/Board.do.php" target="'.$actionTarget.'" onsubmit="return CheckMent(this)" enctype="multipart/form-data">'."\n";
		if ($_ENV['isMobile'] == true) $formStart.= '<input type="hidden" name="is_mobile" value="TRUE" />';
		$formStart.= '<input type="hidden" name="action" value="ment" />'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.($mode == 'ment_modify' ? 'modify' : 'post').'" />'."\n";
		$formStart.= '<input type="hidden" name="bid" value="'.$this->bid.'" />'."\n";
		$formStart.= '<input type="hidden" name="repto" value="'.$repto.'" />'."\n";
		$formStart.= '<input type="hidden" name="idx" value="'.$idx.'" />'."\n";
		$formStart.= '<input type="hidden" name="parent" value="'.$data['parent'].'" />'."\n";
		$formStart.= '<input type="hidden" name="check_password" value="'.$password.'" />'."\n";
		$formEnd = '</form>'."\n".'<iframe name="'.$actionTarget.'" style="display:none;"></iframe>'."\n";
		if ($_ENV['isMobile'] == true) {
			$formEnd.= '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite'.$repto.'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/mobile.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("MentWrite'.$repto.'");</script>';
		} else {
			$formEnd.= '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite'.$repto.'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("MentWrite'.$repto.'");</script>';
		}

		if ($mode == 'ment_modify' && $this->setup['use_uploader'] == 'TRUE') $formEnd.= '<script type="text/javascript">ModuleUploaderLoad("repto='.$idx.'");</script>';

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
		$this->mTemplet->assign('formName','ModuleBoardMent'.$repto);
		$this->mTemplet->assign('wysiwygName','MentWrite'.$repto);
		$this->mTemplet->assign('uploaderName','MentUploader'.$repto);
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('mode',$mode == 'ment_modify' ? 'modify' : 'post');
		$this->mTemplet->assign('antispam',$antispam);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->register_object('mBoard',$this,array('PrintUploader'));

		$this->PrintTemplet();
	}

	// 댓글삭제출력
	function PrintMentDelete() {
		$idx = $this->idx;
		$data = $this->mDB->DBfetch($this->table['ment'],'*',"where `idx`='$idx'");

		if ($this->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				$this->PrintInputPassword('댓글을 삭제하기 위하여 패스워드를 입력하여 주십시오.',$this->moduleDir.'/exec/Board.do.php?action=delete&mode=ment&idx='.$idx,'execFrame');
			} elseif ($data['mno'] == $this->member['idx']) {
				$this->PrintConfirm('댓글을 삭제하시겠습니까?',$this->moduleDir.'/exec/Board.do.php?action=delete&mode=ment&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data['repto'])),'execFrame');
			} else {
				return $this->PrintError('댓글을 삭제할 권한이 없습니다.');
			}
		} else {
			$this->PrintConfirm('댓글을 삭제하시겠습니까?',$this->moduleDir.'/exec/Board.do.php?action=delete&mode=ment&idx='.$idx,$this->baseURL.$this->GetQueryString(array('mode'=>'view','idx'=>$data['repto'])),'execFrame');
		}
	}

	// 최근게시물 출력
	function PrintRecently($skin,$page,$row,$limit='',$title='',$finder='',$orderer='') {
		$this->PrintHeader(false);
		
		if (preg_match('/\$/',$this->bid) == false && isset($this->setup['bid']) == false) {
			return $this->PrintError($this->bid.' 게시판을 찾을 수 없습니다.');
		}

		$title = $title ? $title : $this->setup['title'];
		$find = $this->find;
		$find.= $finder ? ' and '.$finder : '';
		$orderer = $orderer ? $orderer : 'loop,asc';
		$data = $this->mDB->DBfetchs($this->table['post'],array('idx','category','name','mno','title','content','search','image','reg_date','ment','last_ment','is_html_title','is_secret','image'),$find,$orderer,'0,'.$row);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = $limit ? GetCutString($data[$i]['title'],$limit,true) : $data[$i]['title'];
			$data[$i]['title'] = $data[$i]['is_html_title'] == 'TRUE' ? $data[$i]['title'] : GetString($data[$i]['title'],'replace');
			$data[$i]['content'] = $this->GetContent($data[$i]['content']);
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;
			$data[$i]['postlink'] = $page.(preg_match('/\?/',$page) == true ? '&amp;' : '?').'mode=view&amp;idx='.$data[$i]['idx'];
			$data[$i]['image'] = $data[$i]['image'] != '0' ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['image'].'.thm' : '';
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));

			$data[$i]['author'] = $this->GetAuthorInfo($data[$i]);

			if ($data[$i]['is_secret'] == 'TRUE' && ($data[$i]['mno'] != 0 && $data[$i]['mno'] != $this->member['idx'] || $data[$i]['mno'] == '0') && $this->GetPermission('secret') == false) {
				$data[$i]['content'] = $data[$i]['search'] = '이 글은 비밀글입니다. 권한이 없으므로 내용을 보실 수 없습니다.';
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

		$this->PrintFooter(false);
	}

	// 인기게시물 출력
	function PrintRecentlyHot($skin,$page,$row,$hotPosition=100,$limit='',$title='',$finder='') {
		$this->PrintHeader(false);

		$title = $title ? $title : $this->setup['title'];
		$find = $this->find;
		$find.= $finder ? ' and '.$finder : '';

		$mLast = array_pop($this->mDB->DBfetchs($this->table['post'],array('reg_date'),$find,'reg_date,desc','0,'.$hotPosition));
		$find.= " and `reg_date`>=".(isset($mLast['reg_date']) == true ? $mLast['reg_date'] : '0');
		$find.= $finder ? ' and '.$finder : '';
		$data = $this->mDB->DBfetchs($this->table['post'],array('idx','name','mno','title','search','image','reg_date','ment','last_ment','is_secret','is_html_title','image'),$find,'hit,desc','0,'.$row);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['title'] = $limit ? GetCutString($data[$i]['title'],$limit,true) : $data[$i]['title'];
			$data[$i]['title'] = $data[$i]['is_html_title'] == 'TRUE' ? $data[$i]['title'] : GetString($data[$i]['title'],'replace');
			$data[$i]['content'] = $data[$i]['search'];
			$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;
			$data[$i]['postlink'] = $page.(preg_match('/\?/',$page) == true ? '&amp;' : '?').'mode=view&amp;idx='.$data[$i]['idx'];
			$data[$i]['image'] = $data[$i]['image'] != '0' ? $_ENV['userfileDir'].$this->thumbnail.'/'.$data[$i]['image'].'.thm' : '';
			$data[$i]['reg_date'] = strtotime(GetTime('c',$data[$i]['reg_date']));

			if ($data[$i]['is_secret'] == 'TRUE' && ($data[$i]['mno'] != 0 && $data[$i]['mno'] != $this->member['idx'] || $data[$i]['mno'] == '0') && $this->GetPermission('secret') == false) {
				$data[$i]['content'] = '이 글은 비밀글입니다. 권한이 없으므로 내용을 보실 수 없습니다.';
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
		$this->mTemplet->assign('mode','board');
		$this->mTemplet->PrintTemplet();

		$this->PrintFooter(false);
	}

	function PrintMyPost($skin,$row,$limit='',$page='',$title='나의 최근 글 목록',$finder='') {
		$this->PrintHeader(false);
		$title = $title ? $title : $this->setup['title'];

		if ($this->mMember->IsLogged() == true) {
			$find = "where `mno`={$this->member['idx']} and `is_delete`='FALSE'";
			$find.= $finder ? ' and '.$finder : '';
			$data = $this->mDB->DBfetchs($this->table['post'],array('idx','bid','category','name','mno','title','search','image','is_html_title','reg_date','ment','last_ment'),$find,'loop,asc','0,'.$row);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$data[$i]['title'] = $limit ? GetCutString($data[$i]['title'],$limit,true) : $data[$i]['title'];
				$data[$i]['title'] = $data[$i]['is_html_title'] == 'TRUE' ? $data[$i]['title'] : GetString($data[$i]['title'],'replace');
				$data[$i]['content'] = $data[$i]['search'];
				$data[$i]['is_newment'] = $data[$i]['last_ment'] > GetGMT()-60*60*24;
				$data[$i]['postlink'] = $this->moduleDir.'/board.php?bid='.$data[$i]['bid'].'&amp;mode=view&amp;idx='.$data[$i]['idx'];
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

		$this->PrintFooter(false);
	}

	function PrintMyPostMent($skin,$row,$limit='',$page='',$title='나의 글의 최근 댓글 목록',$finder='') {
		$this->PrintHeader(false);
		$title = $title ? $title : $this->setup['title'];

		if ($this->mMember->IsLogged() == true) {
			$find = "where `postmno`={$this->member['idx']} and `mno`!={$this->member['idx']}";
			$find.= $finder ? ' and '.$finder : '';

			$data = $this->mDB->DBfetchs($this->table['ment'],array('repto','name','mno','search','reg_date'),$find,'idx,desc','0,'.$row);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$post = $this->mDB->DBfetch($this->table['post'],array('idx','bid','title','ment','last_ment'),"where `idx`='{$data[$i]['repto']}'");
				$data[$i]['title'] = $limit ? GetCutString($post['title'],$limit,true) : $post['title'];
				$data[$i]['ment'] = $post['ment'];
				$data[$i]['is_newment'] = $post['last_ment'] > GetGMT()-60*60*24;
				$data[$i]['content'] = $data[$i]['search'];
				$data[$i]['postlink'] = $this->moduleDir.'/board.php?bid='.$post['bid'].'&amp;mode=view&amp;idx='.$post['idx'];
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

		$this->PrintFooter(false);
	}

	function PrintUploader($var) {
		$use_uploader = false;
		if ($this->setup['use_uploader'] == 'TRUE') {
			if ($this->mUploader == null) {
				$use_uploader = true;
				$this->mUploader = new ModuleUploader();
			} else {
				$use_uploader = true;
			}
		}

		if ($use_uploader == true) {
			$this->mUploader->SetCaller('board',$this);
			$this->mUploader->SetUploadPath($this->moduleDir.'/exec/FileUpload.do.php?type='.strtoupper($var['type']).'&wysiwyg='.$var['wysiwyg']);
			$this->mUploader->SetLoadPath($this->moduleDir.'/exec/FileLoad.do.php?type='.strtoupper($var['type']).'&wysiwyg='.$var['wysiwyg']);
			
			$this->mUploader->PrintUploader($var['skin'],$var['id'],$var['form'],$var['wysiwyg']);
		}
	}

	function PrintWysiwyg($var) {
		if ($_ENV['isMobile'] == true) return '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"'.$var['id'].'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/mobile.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("'.$var['id'].'");</script>';
		else return '<script type="text/javascript">nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"'.$var['id'].'",sSkinURI:"'.$_ENV['dir'].'/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"}); UsedWysiwyg.push("'.$var['id'].'");</script>';
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
		$permission = $this->setup['permission'] && is_array(unserialize($this->setup['permission'])) == true ? unserialize($this->setup['permission']) : array('list'=>true,'post'=>true,'ment'=>true,'select'=>false,'secret'=>false,'notice'=>false,'modify'=>false,'delete'=>false);
		if ($this->member['type'] == 'ADMINISTRATOR') return true;
		if (isset($permission[$geter]) == false || $permission[$geter] === '') return true;

		return GetPermission($permission[$geter]);
	}

	function GetThumbnail($var) {
		if (CreateDirectory($_ENV['userfilePath'].$this->skinThumbnail.'/'.$this->bid) == true) {
			if ($var['post']['image'] == '0') {
				return $var['error'];
			} elseif(file_exists($_ENV['userfilePath'].$this->skinThumbnail.'/'.$this->bid.'/'.$var['post']['idx'].'.thm') == false) {
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

				if ($width > 0 && $height > 0 && GetThumbnail($_ENV['userfilePath'].$this->userfile.$image['filepath'],$_ENV['userfilePath'].$this->skinThumbnail.'/'.$this->bid.'/'.$var['post']['idx'].'.thm',$width,$height) == true) {
					return $_ENV['userfileDir'].$this->skinThumbnail.'/'.$this->bid.'/'.$var['post']['idx'].'.thm';
				} else {
					return $var['error'];
				}
			} else {
				return $_ENV['userfileDir'].$this->skinThumbnail.'/'.$this->bid.'/'.$var['post']['idx'].'.thm';
			}
		}
	}

	function FileDelete($idx) {
		$file = $this->mDB->DBfetch($this->table['file'],array('filepath'),"where `idx`='$idx'");
		$this->mDB->DBdelete($this->table['file'],"where `idx`='$idx'");

		if ($this->mDB->DBcount($this->table['file'],"where `filepath`='{$file['filepath']}'",'idx') == 0) {
			@unlink($_ENV['userfilePath'].$this->userfile.$file['filepath']);
		}
	}

	function GetTable() {
		return $this->table;
	}
	
	function InsertPostAPI($bid='',$post,$file,$isImage=true,$referer='') {
		if (isset($post['title']) == true && isset($post['content']) == true && isset($post['name']) == true) {
			$insert = array();
			$insert['bid'] = $bid ? $bid : $this->bid;
			$insert['category'] = isset($post['category']) == true ? $post['category'] : '0';
			$insert['mno'] = isset($post['mno']) == true ? $post['mno'] : '0';
			$insert['name'] = $post['name'];
			$insert['email'] = isset($post['email']) == true ? $post['email'] : '';
			$insert['homepage'] = isset($post['homepage']) == true ? $post['homepage'] : '0';
			$insert['password'] = isset($post['password']) == true ? md5(strtolower($post['passord'])) : md5('0');
			$insert['title'] = $post['title'];
			$insert['email'] = isset($post['email']) == true ? $post['email'] : '';
			if ($isImage == false) {
				$insert['content'] = $this->SetContent($post['content']);
				$insert['search'] = GetIndexingText($post['content']);
			}
			$insert['extra_content'] = isset($post['extra_content']) == true ? $post['extra_content'] : '';
			$insert['field1'] = isset($post['field1']) == true ? $post['field1'] : '';
			$insert['field2'] = isset($post['field2']) == true ? $post['field2'] : '';
			$insert['field3'] = isset($post['field3']) == true ? $post['field3'] : '';
			$insert['reg_date'] = isset($post['reg_date']) == true ? $post['reg_date'] : GetGMT();
			$insert['ip'] = isset($post['ip']) == true ? $post['ip'] : $_SERVER['SERVER_ADDR'];
			$insert['is_notice'] = isset($post['is_notice']) == true ? $post['is_notice'] : 'FALSE';
			$insert['is_html_title'] = isset($post['is_html_title']) == true ? $post['is_html_title'] : 'FALSE';
			$insert['is_secret'] = isset($post['is_secret']) == true ? $post['is_secret'] : 'FALSE';
			$insert['is_ment'] = isset($post['is_ment']) == true ? $post['is_ment'] : 'TRUE';
			$insert['is_trackback'] = isset($post['is_trackback']) == true ? $post['is_trackback'] : 'FALSE';
			$insert['is_msg'] = isset($post['is_msg']) == true ? $post['is_msg'] : 'FALSE';
			$insert['is_email'] = isset($post['is_email']) == true ? $post['is_email'] : 'FALSE';
			$insert['is_hidename'] = isset($post['is_hidename']) == true ? $post['is_hidename'] : 'FALSE';
			
			$idx = $this->mDB->DBinsert($this->table['post'],$insert);
			
			$update = array();
			$update['loop'] = $idx*-1;
			
			for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
				if ($file[$i][0] == '') continue;
				$type = 'POST';
				$wysiwyg = 'content';
				$filename = $file[$i][1];
				$temppath = $file[$i][0];
				$filesize = filesize($temppath);
				$filetype = GetFileType($filename,$temppath);
				$filepath = '/attach/'.date('Ym').'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);
	
				if (CreateDirectory($_ENV['userfilePath'].$this->userfile.'/attach/'.date('Ym')) == true) {
					if ($temppath) {
						@copy($temppath,$_ENV['userfilePath'].$this->userfile.$filepath);
						$fidx = $this->mDB->DBinsert($this->table['file'],array('type'=>$type,'repto'=>$idx,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>$insert['reg_date']));
						
						if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$this->thumbnail) == true) {
							GetThumbnail($_ENV['userfilePath'].$this->userfile.$filepath,$_ENV['userfilePath'].$this->thumbnail.'/'.$fidx.'.thm',150,120,false);
							$update['image'] = isset($update['image']) == false ? $fidx : $update['image'];
						}
						
						@unlink($temppath);
					}
				}
			}
			
			if ($isImage == true) {
				$update['content'] = $post['content'];
				$imageExec = array('','gif','jpg','png');
				$mCrawler = new Crawler();
				if (preg_match_all('/<img(.*?)src="(.*?)"(.*?)>/i',$update['content'],$match) == true) {
					for ($i=0, $loop=sizeof($match[0]);$i<$loop;$i++) {
						$temppath = $mCrawler->GetFile($match[2][$i],$referer);
						if ($temppath) {
							$check = getimagesize($temppath);
							if (in_array($check[2],array('1','2','3')) == true) {
								$type = 'POST';
								$wysiwyg = 'content';
								$filename = array_shift(explode('.',array_pop(explode('/',$match[2][$i])))).'.'.$imageExec[$check[2]];
								$filesize = filesize($temppath);
								$filetype = 'IMG';
								$filepath = '/attach/'.date('Ym').'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);
								
								if (CreateDirectory($_ENV['userfilePath'].$this->userfile.'/attach/'.date('Ym')) == true) {
									@copy($temppath,$_ENV['userfilePath'].$this->userfile.$filepath);
									$fidx = $this->mDB->DBinsert($this->table['file'],array('type'=>$type,'repto'=>$idx,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>$insert['reg_date']));
									
									if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$this->thumbnail) == true) {
										GetThumbnail($_ENV['userfilePath'].$this->userfile.$filepath,$_ENV['userfilePath'].$this->thumbnail.'/'.$fidx.'.thm',150,120,false);
										$update['image'] = isset($update['image']) == false ? $fidx : $update['image'];
									}
									
									$update['content'] = str_replace($match[0][$i],'<img name="InsertFile" file="'.$fidx.'" src="/iModule/module/board/exec/ShowImage.do.php?idx='.$fidx.'">',$update['content']);
								}
							}
							@unlink($temppath);
						}
					}
				}
				
				$update['content'] = $this->SetContent($update['content']);
				$update['search'] = GetIndexingText($update['content']);
			}
			
			if (sizeof($update) > 0) {
				$this->mDB->DBupdate($this->table['post'],$update,'',"where `idx`='$idx'");
			}
		}
	}
}
?>
