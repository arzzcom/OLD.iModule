<?php
REQUIRE_ONCE $_ENV['path'].'/module/tracker/class/bencode.php';
REQUIRE_ONCE $_ENV['path'].'/module/tracker/class/torrent.php';

class TorrentFileObject {
	public $file;
	public $filedetail;
	public $tracker;
	public $date;
	public $comment;
	public $createBy;
	public $hash;
	public $pieceLength;
	public $piece;
	public $filesize;
}

class ModuleTracker extends Module {
	private $torrent;
	private $bencode;
	
	public $table;
	public $skinDir;
	public $imagePath;
	public $torrentPath;
	public $thumbnail;
	public $artistThumbnail;
	public $groupImagepath;
	
	protected $link;
	protected $announceURL;
	protected $mTemplet;
	
	function __construct() {
		$this->torrent = new Torrent();
		
		$this->table = array();
		$this->table['category'] = $_ENV['code'].'_tracker_category_table';
		$this->table['layout'] = $_ENV['code'].'_tracker_layout_table';
		$this->table['tag'] = $_ENV['code'].'_tracker_tag_table';
		$this->table['user'] = $_ENV['code'].'_tracker_user_table';
		$this->table['user_grade'] = $_ENV['code'].'_tracker_user_grade_table';
		$this->table['peer'] = $_ENV['code'].'_tracker_peer_table';
		$this->table['snatch'] = $_ENV['code'].'_tracker_snatch_table';
		$this->table['invitecode'] = $_ENV['code'].'_tracker_invitecode_table';
		$this->table['file'] = $_ENV['code'].'_tracker_file_table';
		$this->table['artist'] = $_ENV['code'].'_tracker_artist_table';
		$this->table['group'] = $_ENV['code'].'_tracker_group_table';
		$this->table['episode'] = $_ENV['code'].'_tracker_episode_table';
		$this->table['torrent'] = $_ENV['code'].'_tracker_torrent_table';
		$this->table['ment'] = $_ENV['code'].'_tracker_ment_table';
		
		$this->table['daum_tv'] = $_ENV['code'].'_tracker_daum_tv_table';
		$this->table['daum_movie'] = $_ENV['code'].'_tracker_daum_movie_table';
		
		parent::__construct('tracker');
		
		$this->link = array();
		$this->announceURL = 'http://'.$_SERVER['HTTP_HOST'].$this->moduleDir.'/exec/Announce.php';
		$this->skinDir = $this->moduleDir.'/templet/tracker';
		$this->imagePath = '/userfile/tracker/image';
		$this->thumbnail = '/userfile/tracker/thumbnail';
		$this->artistThumbnail = '/userfile/tracker/artist';
		$this->torrentPath = '/userfile/tracker/torrent';
		$this->groupImagePath = '/userfile/tracker/group';
	}
	
	// 템플릿 출력
	function PrintTemplet() {
		$time = array('server'=>time(),'gmt'=>GetGMT());
		$this->link['page'] = $this->baseURL.$this->GetQueryString(array('p'=>'','mode'=>Request('mode') ? Request('mode') : 'list','idx'=>'')).'&amp;p=';
		$this->link['back'] = isset($_SERVER['HTTP_REFERER']) == true ? $_SERVER['HTTP_REFERER'] : '';

		$this->mTemplet->assign('member',$this->member);
		$this->mTemplet->assign('skinDir',$this->skinDir);
		$this->mTemplet->assign('moduleDir',$this->moduleDir);
		$this->mTemplet->assign('thumbnailDir',$_ENV['dir'].$this->thumbnail);
		$this->mTemplet->assign('time',$time);
		$this->mTemplet->assign('link',$this->link);
		$this->mTemplet->assign('action',$this->action);
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

		return $this->mTemplet->GetTemplet();
	}
	
	function GetDaumMovieAPI($item) {
		$daum = array();
		$daum['idx'] = str_replace('http://movie.daum.net/moviedetail/moviedetailMain.do?movieId=','',$item->title->link);
		$daum['title'] = (string)($item->title->content);
		$daum['eng_title'] = (string)($item->eng_title->content);
		$daum['year'] = (int)($item->year->content);
		$artist = array();
		for ($i=0, $loop=sizeof($item->director->content);$i<$loop;$i++) {
			$artist[] = (string)($item->director->content[$i]);
		}
		$daum['artist'] = implode(',',$artist);
		$subartist = array();
		for ($i=0, $loop=sizeof($item->actor->content);$i<$loop;$i++) {
			$subartist[] = (string)($item->actor->content[$i]);
		}
		$daum['subartist'] = implode(',',$subartist);
		$nation = array();
		for ($i=0, $loop=sizeof($item->nation->content);$i<$loop;$i++) {
			$nation[] = (string)($item->nation->content[$i]);
		}
		$daum['nation'] = implode(',',$nation);
		$genre = array();
		for ($i=0, $loop=sizeof($item->genre->content);$i<$loop;$i++) {
			$genre[] = (string)($item->genre->content[$i]);
		}
		$daum['genre'] = implode(',',$genre);
		if (preg_match('/애니메이션/',$daum['genre']) == true) {
			$daum['type'] = 'ANIMATION';
		} else {
			$daum['type'] = 'MOVIE';
		}
		$daum['grade'] = intval((float)($item->grades->content)*100);
		$daum['thumbnail'] = (string)($item->thumbnail->content);
		
		$daum['date'] = str_replace('.','-',(string)($item->open_info->content));
		
		if ($this->mDB->DBcount($this->table['daum_movie'],"where `idx`='{$daum['idx']}'") == 0) {
			$mKeyword = new Keyword();
			$daum['titlekey1'] = $mKeyword->GetUTF8Code($daum['title']);
			$daum['titlekey2'] = $mKeyword->GetEngCode($daum['titlekey1']);
			$daum['titlekey3'] = $mKeyword->GetUTF8Code($daum['eng_title']);
			$daum['last_check_time'] = GetGMT();
			
			$this->mDB->DBinsert($this->table['daum_movie'],$daum);
		}
		
		return $this->mDB->DBfetch($this->table['daum_movie'],'*',"where `idx`='{$daum['idx']}'");
	}

	function GetDaumTVAPI($item) {
		$daum = array('idx'=>'','title'=>'','eng_title'=>'','year'=>'','artist'=>'','subartist'=>'','nation'=>'','thumbnail'=>'');
		$daum['thumbnail'] = array_shift(array_shift($item->find('dt'))->find('img'))->src;
		$span = $item->find('span');
		$spanItem = 0;
		for ($i=0, $loop=sizeof($span);$i<$loop;$i++) {
			if ($span[$i]->class == 'fl srch') {
				$daum['idx'] = array_pop(explode('=',array_shift($span[$i]->find("a"))->href));
				$title = strip_tags($span[$i]->innertext);
				if (preg_match('/ \(([0-9]{4})\)$/',$title,$match) == true) {
					$daum['year'] = $match[1];
					$daum['title'] = str_replace($match[0],'',$title);
				} else {
					$daum['year'] = '0';
					$daum['title'] = $title;
				}
			}

			if ($span[$i]->class == 'fs13') {
				$daum['eng_title'] = strip_tags($span[$i]->innertext);
			} elseif ($span[$i]->class == "item") {
				if ($spanItem == 0) {
					$temp = preg_replace('/(&nbsp;|[[:space:]]{2})/','',strip_tags($span[$i]->innertext));
					$daum['nation'] = array_shift(explode('|',$temp));
				} elseif ($spanItem == 1) {
					$temp = preg_replace('/(&nbsp;|[[:space:]]{2})/','',strip_tags($span[$i]->innertext));
					if (preg_match('/연출 : /',$temp,$match) == true) {
						$daum['artist'] = str_replace(', ',',',str_replace($match[0],'',$temp));
					} elseif (preg_match('/출연 : /',$temp,$match) == true) {
						$daum['subartist'] = str_replace(', ',',',str_replace($match[0],'',$temp));
					}
				} elseif ($spanItem == 2) {
					$temp = preg_replace('/(&nbsp;|[[:space:]]{2})/','',strip_tags($span[$i]->innertext));
					if (preg_match('/연출 : /',$temp,$match) == true) {
						$daum['artist'] = str_replace(', ',',',str_replace($match[0],'',$temp));
					} elseif (preg_match('/출연 : /',$temp,$match) == true) {
						$daum['subartist'] = str_replace(', ',',',str_replace($match[0],'',$temp));
					}
				}
				
				if (preg_match('/애니메이션/',$span[$i]->innertext) == true) {
					$daum['type'] = 'ANIMATION';
				} else {
					$daum['type'] = 'TV';
				}
				$spanItem++;
			}
		}
	
		if ($this->mDB->DBcount($this->table['daum_tv'],"where `idx`='{$daum['idx']}'") == 0) {
			$mKeyword = new Keyword();
			$daum['titlekey1'] = $mKeyword->GetUTF8Code($daum['title']);
			$daum['titlekey2'] = $mKeyword->GetEngCode($daum['titlekey1']);
			$daum['titlekey3'] = $mKeyword->GetUTF8Code($daum['eng_title']);
			if (preg_match('/ 시즌 ([0-9]+)$/',$daum['title'],$match) == true) {
				$daum['season'] = $match[1];
				$daum['title'] = str_replace($match[0],'',$daum['title']);
			} elseif (preg_match('/ ([0-9]+)기$/',$daum['title'],$match) == true) {
				$daum['season'] = $match[1];
				$daum['title'] = str_replace($match[0],'',$daum['title']);
			} else {
				$daum['season'] = '';
			}
			$daum['last_check_time'] = GetGMT();
			
			$this->mDB->DBinsert($this->table['daum_tv'],$daum);
		} else {
			$this->mDB->DBupdate($this->table['daum_tv'],array('year'=>$daum['year'],'last_check_time'=>GetGMT()),'',"where `idx`='{$daum['idx']}'");
		}
		
		return $this->mDB->DBfetch($this->table['daum_tv'],'*',"where `idx`='{$daum['idx']}'");
	}
	
	function GetArtistIDX($name,$category='') {
		if (!$name) return;
		$mKeyword = new Keyword();
		$name = explode(',',$name);
		$idx = array();
		for ($i=0, $loop=sizeof($name);$i<$loop;$i++) {
			$namekey1 = $mKeyword->GetUTF8Code(trim($name[$i]));
			$namekey2 = $mKeyword->GetEngCode($namekey1);
			$find = $category ? "where `namekey1`='$namekey1' and `category1`='$category' and `type`='MAIN'" : "where `namekey1`='$namekey1' and `type`='MAIN'";
			
			$check = $this->mDB->DBfetch($this->table['artist'],array('idx'),$find);
			if (isset($check['idx']) == true) {
				$idx[$i] = $check['idx'];
			} else {
				$idx[$i] = $this->mDB->DBinsert($this->table['artist'],array('type'=>'MAIN','category1'=>$category,'name'=>trim($name[$i]),'namekey1'=>$namekey1,'namekey2'=>$namekey2));
			}
		}
		
		return implode(',',$idx);
	}
	
	function GetSubArtistIDX($name,$category='') {
		$mKeyword = new Keyword();
		$name = explode(',',$name);
		if (!$name) return;
		$idx = array();
		for ($i=0, $loop=sizeof($name);$i<$loop;$i++) {
			$namekey1 = $mKeyword->GetUTF8Code(trim($name[$i]));
			$namekey2 = $mKeyword->GetEngCode($namekey1);
			$find = $category ? "where `namekey1`='$namekey1' and `category1`='$category' and `type`='SUB'" : "where `namekey1`='$namekey1' and `type`='SUB'";
			
			$check = $this->mDB->DBfetch($this->table['artist'],array('idx'),$find);
			if (isset($check['idx']) == true) {
				$idx[] = $check['idx'];
			} else {
				$idx[] = $this->mDB->DBinsert($this->table['artist'],array('type'=>'SUB','category1'=>$category,'name'=>trim($name[$i]),'namekey1'=>$namekey1,'namekey2'=>$namekey2));
			}
		}
		
		return implode(',',$idx);
	}
	
	function GetArtistName($idx,$link=false) {
		if (is_array($idx) == false) $idx = array($idx);
		$name = array();
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$check = $this->mDB->DBfetch($this->table['artist'],array('name'),"where `idx`='{$idx[$i]}'");
			if (isset($check['name']) == true) {
				$name[] = $link == true ? '<a href="'.$this->baseURL.GetQueryString(array('mode'=>'artist','artist'=>$idx[$i],'tag'=>'','torrent'=>'','group'=>'','episode'=>'')).'">'.$check['name'].'</a>' : $check['name'];
			} else {
				$name[] = 'Unknown';
			}
		}
		
		return $name;
	}
	
	function GetCategoryName($idx,$link=false) {
		if (is_array($idx) == false) {
			$category = $this->mDB->DBfetch($this->table['category'],array('title'),"where `idx`='$idx'");
			if (isset($category['title']) == true) {
				return $link == true ? '<a href="#">'.$category['title'].'</a>' : $category['title'];
			}
		} else {
			$category = array();
			for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
				if ($idx[$i]) $category[] = $this->GetCategoryName($idx[$i],$link);
			}
			return $category;
		}
		return '';
	}
	
	function GetTagName($idx,$link=false) {
		if (is_array($idx) == false) $idx = array($idx);
		$name = array();
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$check = $this->mDB->DBfetch($this->table['tag'],array('title'),"where `idx`='{$idx[$i]}'");
			if (isset($check['title']) == true) {
				$name[] = $link == true ? '<a href="'.$this->baseURL.GetQueryString(array('mode'=>'list','artist'=>'','tag'=>$idx[$i],'torrent'=>'','group'=>'','episode'=>'')).'">'.$check['title'].'</a>' : $check['title'];
			}
		}
		
		return $name;
	}
	
	function GetTorrentFile($path) {
		if (file_exists($path) == true) {
			$data = file_get_contents($path);
			$this->torrent->load($data);
			return $this->torrent;
		} else {
			return false;
		}
	}
	
	function GetInfoHashToSHA1($str) {
		$sha1 = '';
		for ($i=0,$loop=strlen($str);$i<$loop;$i++) {
			if ($str[$i] == '%') {
				$token = substr($str,$i+1,2);
				$i = $i+2;
			} else {
				$token = dechex(ord($str[$i]));
			}
			if (strlen($token) == 1) $token = '0'.$token;
			$sha1.= $token;
		}
		
		return strtoupper($sha1);
	}
	
	function GetPIDToSHA1($str) {
		if (strlen($str) == 20 && preg_match('/%/',$str) == false) return $str;
		
		$sha1 = '';
		for ($i=0,$loop=strlen($str);$i<$loop;$i++) {
			if ($str[$i] == '%') {
				$token = substr($str,$i+1,2);
				$i = $i+2;
			} else {
				$token = dechex(ord($str[$i]));
			}
			if (strlen($token) == 1) $token = '0'.$token;
			$sha1.= $token;
		}
		
		return $sha1;
	}
	
	function GetBEncode($var) {
		if (is_int($var)) {
			return 'i'.$var.'e';
		} elseif (is_array($var) == true) {
			if (count($var) == 0) {
				return 'de';
			} else {
				$assoc = false;
				foreach ($var as $key => $val) {
					if (!is_int($key)) {
						$assoc = true;
						break;
					}
				}

				if ($assoc) {
					ksort($var, SORT_REGULAR);
					$ret = 'd';
					foreach ($var as $key=>$val) {
						$ret.= $this->GetBEncode($key).$this->GetBEncode($val);
					}
					return $ret.'e';
				} else {
					$ret = 'l';
					foreach ($var as $val) {
						$ret.= $this->GetBEncode($val);
					}
					return $ret.'e';
				}
			}
		} else {
			return strlen($var).':'.$var;
		}
	}
	
	function GetTorrentFileInfo($path) {
		if (file_exists($path) == true) {
			$data = file_get_contents($path);
			$this->torrent->load($data);
			
			$info = new TorrentFileObject();
			$info->file = $this->torrent->getFiles();
			$info->tracker = $this->torrent->getTrackers();
			
			$info->filesize = 0;
			$temp = 0;
			$detail = array();
			for ($i=0, $loop=sizeof($info->file);$i<$loop;$i++) {
				$info->filesize+= $info->file[$i]->length;
				$temp+= $info->file[$i]->length;
				$detail[$i] = $info->file[$i]->name.'/'.$info->file[$i]->length;
			}
			$info->filedetail = implode("\n",$detail);

			$info->date = date('Y-m-d H:i:s',$this->torrent->getCreationDate());
			$info->comment = $this->torrent->getComment();
			$info->createBy = $this->torrent->getCreatedBy();
			$info->piece = $this->torrent->getPieces();
			$info->pieceLength = $this->torrent->getPieceLength();
			$info->hash = $this->torrent->getHash();

			return $info;
		} else {
			return false;
		}
	}
	
	function GetGroupInfo($group) {
		if (isset($group[0]) == true) {
			for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
				$group[$i] = $this->GetGroupInfo($group[$i]);
			}
		} else {
			if (file_exists($_ENV['path'].$this->groupImagePath.'/'.$group['idx'].'.small.thm') == true) {
				$group['thumbnail'] = array(
					'small'=>$_ENV['dir'].$this->groupImagePath.'/'.$group['idx'].'.small.thm',
					'middle'=>$_ENV['dir'].$this->groupImagePath.'/'.$group['idx'].'.middle.thm',
					'big'=>$_ENV['dir'].$this->groupImagePath.'/'.$group['idx'].'.big.thm'
				);
			} else {
				$group['thumbnail'] = array(
					'small'=>'','middle'=>'','big'=>''
				);
			}
			$group['artist'] = implode(' & ',$this->GetArtistName(explode(',',$group['artist']),true));
			$group['category'] = implode(' &gt; ',$this->GetCategoryName(array($group['category1'],$group['category2'],$group['category3']),true));
			$group['subartist'] = implode(', ',$this->GetArtistName(explode(',',$group['subartist']),true));
			$group['tag'] = implode(', ',$this->GetTagName(explode(',',$group['tag']),true));
			$group['intro'] = nl2br($group['intro']);
			$group['editlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'modify','group'=>$group['idx'],'torrent'=>'','episode'=>''));
			
			$group['daummovie'] = $this->mDB->DBfetch($this->table['daum_movie'],array('idx'),"where `groupno`='{$group['idx']}'");
			$group['daumtv'] = $this->mDB->DBfetch($this->table['daum_tv'],array('idx'),"where `groupno`='{$group['idx']}'");
			$group['link'] = $this->torrentURL.$this->GetQueryString(array('torrent'=>'','episode'=>'','mode'=>'view','group'=>$group['idx']));
			
			$group['mentlist'] = '<iframe src="'.$this->moduleDir.'/MentList.php?mode=group&repto='.$group['idx'].'" id="TrackerGroupMentList-'.$group['idx'].'" style="width:100%; height:62px;" scrolling="no" frameborder="0"></iframe>';
		}
		return $group;
	}
	
	function GetEpisodeInfo($episode) {
		if (!$episode) return null;
		if (isset($episode[0]) == true) {
			for ($i=0, $loop=sizeof($episode);$i<$loop;$i++) {
				$episode[$i] = $this->GetEpisodeInfo($episode[$i]);
			}
		} else {
			$episode['is_pack'] = $episode['is_pack'] == 'TRUE';
			$episode['episode'] = $episode['episode'] == '0' ? '' : $episode['episode'];
			if ($episode['is_pack'] == true) {
				$temp = explode('~',$episode['episode_title']);
				$episode['episode'] = array('start'=>$temp[0],'end'=>$temp[1]);
				$episode['episode_title'] = '';
			}
			$episode['editlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'modify','episode'=>$episode['idx'],'group'=>'','torrent'=>''));
			$episode['link'] = $this->torrentURL.$this->GetQueryString(array('torrent'=>'','group'=>'','mode'=>'view','episode'=>$episode['idx']));
		}
		return $episode;
	}
	
	function GetSearchFormInner($category) {
		$category = $this->mDB->DBfetch($this->table['category'],array('idx','search_layout'),"where `idx`='$category'");
		$layout = $this->mDB->DBfetch($this->table['layout'],array('preset'),"where `idx`='{$category['search_layout']}'");
		$taglist = $this->mDB->DBfetchs($this->table['tag'],array('idx','title'),"where `category1`='{$category['idx']}'",'sort,asc');
		for ($i=0, $loop=sizeof($taglist);$i<$loop;$i++) {
			$taglist[$i]['checked'] = in_array($taglist[$i]['idx'],explode(',',Request('tag')));
		}
		
		$mTemplet = new Templet($this->modulePath.'/templet/tracker/'.$layout['preset']);
		$mTemplet->assign('taglist',$taglist);
		$mTemplet->assign('subtitles',Request('subtitles'));
		$mTemplet->assign('resolution',Request('resolution'));
		$mTemplet->assign('codec',Request('codec'));
		$mTemplet->assign('source',Request('source'));
		$mTemplet->assign('format',Request('format'));
		return $mTemplet->GetTemplet();
	}
	
	function GetTorrentInfo($torrent,$isAll=false) {
		if (isset($torrent[0]) == true) {
			for ($i=0, $loop=sizeof($torrent);$i<$loop;$i++) {
				$torrent[$i] = $this->GetTorrentInfo($torrent[$i],$isAll);
			}
		} else {
			$torrent['filesize'] = GetFileSize($torrent['filesize']);
			$torrent['reg_date'] = GetTime('Y-m-d H:i:s',$torrent['reg_date']);
			$torrent['downloadlink'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$torrent['idx'];
			$torrent['torrentlink'] = $this->baseURL.GetQueryString(array('mode'=>'view','torrent'=>$torrent['idx']));
			$torrent['is_freeleech'] = $torrent['is_freeleech'] == 'TRUE';
			$torrent['is_halfleech'] = $torrent['is_halfleech'] == 'TRUE';
			$torrent['is_doubleupload'] = $torrent['is_doubleupload'] == 'TRUE';
			$torrent['is_exclusive'] = $torrent['is_exclusive'] == 'TRUE';
			$torrent['mediainfo'] = nl2br($torrent['mediainfo']);
			if (Request('torrent') != null && Request('torrent') == $torrent['idx']) $torrent['is_select'] = true;
			else $torrent['is_select'] = false;
			$torrent['editlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'modify','torrent'=>$torrent['idx'],'group'=>'','episode'=>''));
			
			if ($isAll == true) {
				$torrent['image'] = array();
				$image = $this->mDB->DBfetchs($this->table['file'],array('idx','type','filename','filepath'),"where `repto`='{$torrent['idx']}'");
				for ($i=0, $loop=sizeof($image);$i<$loop;$i++) {
					$image[$i]['thumbnail'] = $_ENV['dir'].$this->thumbnail.'/'.$image[$i]['idx'].'.thm';
					$image[$i]['filepath'] = $_ENV['dir'].$image[$i]['filepath'];
					if ($image[$i]['type'] == 'SCREENSHOT') {
						$torrent['screenshot'] = $image[$i];
					} elseif ($image[$i]['type'] == 'SNAPSHOT') {
						$torrent['snapshot'] = $image[$i];
					} else {
						$torrent['image'][] = $image[$i];
					}
				}
				$torrent['mentlist'] = '<iframe src="'.$this->moduleDir.'/MentList.php?mode=torrent&repto='.$torrent['idx'].'" id="TrackerTorrentMentList-'.$torrent['idx'].'" style="width:100%; height:62px;" scrolling="no" frameborder="0"></iframe>';
			}
		}
		return $torrent;
	}

	function GetQueryString($var=array(),$queryString='',$encode=true) {
		$queryString = $queryString ? $queryString : $this->baseQueryString;
		if (Request('keyword') == null) {
			$var['key'] = '';
			$var['keyword'] = '';
			$var['amp;mode'] = '';
		}

		return GetQueryString($var,$queryString,$encode);
	}
	
	// 헤더출력
	function PrintHeader() {
		if ($this->isHeaderIncluded == true) return;

		if ($_ENV['isHeaderIncluded'] == false) {
			GetDefaultHeader($this->setup['title']);
		}

		echo "\n".'<!-- Module Tracker Start -->'."\n";
		if ($this->isHeaderIncluded == false) {
			echo '<link rel="stylesheet" href="'.$this->moduleDir.'/css/default.css" type="text/css" />'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/default.js"></script>'."\n";
		}
		
		$this->isHeaderIncluded = true;
	}

	// 푸터출력
	function PrintFooter() {
		if ($this->isFooterIncluded == true) return;
		$this->isFooterIncluded = true;

		if ($this->bid) echo "\n".'</div>'."\n";
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
	
	function PrintTracker() {
		$this->PrintHeader();
		
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/tracker/style.css" type="text/css" title="style" />'."\n";
		echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/tracker/script.js"></script>'."\n";
		
		if ($this->mode != 'list' && CheckIncluded('wysiwyg') == false) {
			echo '<script type="text/javascript" src="'.$_ENV['dir'].'/module/wysiwyg/script/wysiwyg.js"></script>'."\n";
		}
		
		$mode = Request('mode') ? Request('mode') : 'list';
		
		switch ($mode) {
			case 'list' :
				$this->PrintList();
				break;
				
			case 'artist' :
				$this->PrintArtist();
				break;
				
			case 'artist_modify' :
				$this->PrintArtist();
				break;
				
			case 'view' :
				$this->PrintView();
				break;
				
			case 'write' :
				$this->PrintWrite();
				break;
				
			case 'modify' :
				$this->PrintWrite();
				break;
		}
		$this->PrintFooter();
	}
	
	function PrintSignIn() {
		$this->PrintHeader();
		
		if ($this->module['is_opensign'] != 'on') {
			$code = Request('code');
			$check = $this->mDB->DBfetch($this->table['invitecode'],array('code','status'),"where `code`='$code'");
			
			if (isset($check['code']) ==  false || $check['status'] != 'WAIT') {
				return $this->PrintError('초대장코드가 잘못되었거나, 접근할 수 있는 권한이 없습니다.');
			}
		}

		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/tracker/style.css" type="text/css" title="style" />'."\n";

		$type = Request('type');
		if ($type == null) {
			$formStart = '<form name="TrackerSignIn" action="'.$this->moduleDir.'/exec/Tracker.do.php" target="execFrame">'."\n";
			$formStart.= '<input type="hidden" name="action" value="signin" />'."\n";
			$formStart.= '<input type="hidden" name="code" value="'.$code.'" />'."\n";
			$formEnd = '</form>'."\n";
			$formEnd.= '<iframe name="execFrame" style="display:none;"></iframe>'."\n";
			$this->link['next'] = $_SERVER['REQUEST_URI'].'&type=new';
			$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/signin.tpl');
			$this->mTemplet->assign('formStart',$formStart);
			$this->mTemplet->assign('formEnd',$formEnd);
			$this->mTemplet->assign('link',$link);
			$this->PrintTemplet();

		} else {
			$this->mMember = new ModuleMember();
			$this->member = $this->mMember->GetMemberInfo();
			$this->mMember->PrintSignIn($this->module['signin_skin'],$this->module['member_group'],$this->modulePath.'/exec/SignIn.do.php');
		}
		
		$this->PrintFooter();
	}
	
	function PrintUserInfo($skin,$mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		$user = $this->GetUserInfo($mno);
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/userinfo/'.$skin.'/style.css" type="text/css" title="style" />'."\n";
		$this->mTemplet = new Templet($this->modulePath.'/templet/userinfo/'.$skin.'/userinfo.tpl');
		$this->mTemplet->assign('user',$user);
		$this->mTemplet->PrintTemplet();
	}
	
	function PrintList() {
		$keyword = Request('keyword');
		$category1 = Request('category1');
		$category2 = Request('category2');
		$category3 = Request('category3');
		$tag = Request('tag');
		
		$find = "where 1";
		if ($category1 != null || $tag != null || $keyword != null) {
			$find = "where 1";
			if ($category1) $find.= " and `category1` IN ($category1)";
			if ($tag) {
				$tag = explode(',',$tag);
				$find.= " and (";
				$tagFind = array();
				for ($i=0, $loop=sizeof($tag);$i<$loop;$i++) {
					$tagFind[] = "FIND_IN_SET({$tag[$i]},`tag`)";
				}
				$find.= implode(' and ',$tagFind).")";
			}

			if ($keyword) {
				$mKeyword = new Keyword($keyword);
				$find.= " and ".$mKeyword->GetFullTextKeyword(array('title','season_title','eng_title'));
			}
			
			echo "<div class='tahoma f11' style='word-break:break-all; color:green;'>Group Search : ".$find."</div>";
			
			$search = $this->mDB->DBfetchs($this->table['group'],array('idx'),$find);
			$idx = array();
			for ($i=0, $loop=sizeof($search);$i<$loop;$i++) {
				$idx[] = $search[$i]['idx'];
			}
			$find = "where `groupno` IN (".implode(',',$idx).")";
		}
		
		$torrentFind = '';
		$subtitles = Request('subtitles');
		$resolution = Request('resolution');
		$codec = Request('codec');
		$source = Request('source');
		$format = Request('format');
		if ($subtitles != null || $resolution != null || $codec != null || $source != null || $format != null) {
			if ($subtitles) $torrentFind.= " and `subtitles`='$subtitles'";
			if ($resolution) $torrentFind.= " and `resolution`='$resolution'";
			if ($codec) $torrentFind.= " and `codec`='$codec'";
			if ($source) $torrentFind.= " and `source`='$source'";
			if ($format) $torrentFind.= " and `format`='$format'";
			
			echo "<div class='tahoma f11' style='word-break:break-all; color:red;'>Torrent Search : ".$find.$torrentFind."</div>";
			
			$search = $this->mDB->DBfetchs($this->table['torrent'],array('groupno'),$find.$torrentFind);
			$idx = array();
			for ($i=0, $loop=sizeof($search);$i<$loop;$i++) {
				$idx[] = $search[$i]['groupno'];
			}
			$find = "where `groupno` IN (".implode(',',array_unique($idx)).")";
		}
		
		echo "<div class='tahoma f11' style='word-break:break-all; color:blue;'>List Search : ".$find."</div>";
		
		$listnum = $this->module['listnum'];
		$pagenum = $this->module['pagenum'];
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;

		$totalgroup = $this->mDB->DBcount($this->table['episode'],$find);
		$totalpage = ceil($totalgroup/$listnum) == 0 ? 1 : ceil($totalgroup/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$sort = Request('sort') ? Request('sort') : 'idx';
		$dir = Request('dir') ? Request('dir') : 'desc';
		if ($sort == 'idx' and $dir == 'desc') {
			$sort = 'loop';
			$dir = 'asc';
		}

		$orderer = $sort.','.$dir;
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = array();
		$episode = $this->GetEpisodeInfo($this->mDB->DBfetchs($this->table['episode'],array('idx','groupno','episode','episode_title','is_pack','year','date','snatch','seeder','leecher'),$find,$orderer,$limiter));
		for ($i=0, $loop=sizeof($episode);$i<$loop;$i++) {
			$episode[$i]['episodeno'] = $episode[$i]['idx'];
			unset($episode[$i]['idx']);
			$group = $this->mDB->DBfetch($this->table['group'],array('category1','title','eng_title','season','season_title','artist','subartist','nation','tag','field1','field2','field3'),"where `idx`='{$episode[$i]['groupno']}'");
			$data[$i] = array_merge($episode[$i],$group);
			$data[$i]['category1Name'] = $this->GetCategoryName($group['category1'],true);
			$data[$i]['titlelink'] = $this->baseURL.GetQueryString(array('mode'=>'view','group'=>$data[$i]['groupno']));
			$data[$i]['episodelink'] = $this->baseURL.GetQueryString(array('mode'=>'view','episode'=>$data[$i]['episodeno']));
			$data[$i]['thumbnail'] = file_exists($_ENV['path'].$this->groupImagePath.'/'.$data[$i]['groupno'].'.small.thm') == true ? $_ENV['dir'].$this->groupImagePath.'/'.$data[$i]['groupno'].'.small.thm' : '';
			$data[$i]['artist'] = implode(' & ',$this->GetArtistName(explode(',',$data[$i]['artist']),true));
			$data[$i]['subartist'] = implode(', ',$this->GetArtistName(explode(',',$data[$i]['subartist']),true));
			$data[$i]['tag'] = implode(', ',$this->GetTagName(explode(',',$data[$i]['tag']),true));
			$data[$i]['torrent'] = $this->GetTorrentInfo($this->mDB->DBfetchs($this->table['torrent'],array('idx','is_freeleech','is_halfleech','is_doubleupload','is_exclusive','subtitles','tag','edition','resolution','codec','source','format','release','file','filesize','snatch','seeder','leecher','reg_date'),"where `episodeno`='{$episode[$i]['episodeno']}'".$torrentFind));;
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
		
		$searchFormStart = '<form name="TrackerTorrentSearchForm" onsubmit="return TrackerTorrentSearch(this)" action="'.$this->baseURL.'">'."\n";
		$searchFormStart.= '<input type="hidden" name="category1" />'."\n";
		$searchFormStart.= '<input type="hidden" name="category2" />'."\n";
		$searchFormStart.= '<input type="hidden" name="tag" />'."\n";
		$searchFormEnd = '</form>'."\n";
		$categoryList1 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `parent`='0'",'sort,asc');
		for ($i=0, $loop=sizeof($categoryList1);$i<$loop;$i++) {
			$categoryList1[$i]['checked'] = in_array($categoryList1[$i]['idx'],explode(',',$category1));
		}
		if ($category1 != null && sizeof(explode(',',$category1)) == 1) {
			$searchFormInner = '<div id="TrackerTorrentSearchFormInner">'.$this->GetSearchFormInner($category1).'</div>';
		}

		$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/list.tpl');
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('totalgroup',number_format($totalgroup));
		$this->mTemplet->assign('totalpage',number_format($totalpage));
		
		$this->mTemplet->assign('keyword',$keyword);
		$this->mTemplet->assign('categoryList1',$categoryList1);
		$this->mTemplet->assign('searchFormStart',$searchFormStart);
		$this->mTemplet->assign('searchFormEnd',$searchFormEnd);
		$this->mTemplet->assign('searchFormInner',$searchFormInner);
		$this->mTemplet->assign('p',$p);
		$this->PrintTemplet();
	}
	
	function PrintArtist() {
		$mode = Request('mode');
		$artist = Request('artist');
		$artist = $this->mDB->DBfetch($this->table['artist'],'*',"where `idx`='$artist'");
		$category = $this->mDB->DBfetch($this->table['category'],array('artist_layout'),"where `idx`='{$artist['category1']}'");
		$layout = $this->mDB->DBfetch($this->table['layout'],array('preset'),"where `idx`='{$category['artist_layout']}'");
		
		$artist['editlink'] = $this->baseURL.$this->GetQueryString(array('mode'=>'artist_modify','artist'=>$artist['idx']));
		$artist['daummovie'] = $artist['daummovie'] == '0' ? '' : $artist['daummovie'];
		$artist['photo'] = file_exists($_ENV['path'].$this->artistThumbnail.'/'.$artist['idx'].'.thm') == true ? $_ENV['dir'].$this->artistThumbnail.'/'.$artist['idx'].'.thm' : '';
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/'.$layout['preset']);
		if ($mode == 'artist') {
			$artist['intro'] = nl2br($artist['intro']);
			$idx = array();
			$find = $artist['type'] == 'MAIN' ? "where FIND_IN_SET({$artist['idx']},`artist`)" : "where FIND_IN_SET({$artist['idx']},`subartist`)";
			$group = $this->mDB->DBfetchs($this->table['group'],array('idx'),$find);
			for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
				$idx[] = $group[$i]['idx'];
			}
			$idx = implode(',',$idx);

			$episode = array();
			$data = $this->GetEpisodeInfo($this->mDB->DBfetchs($this->table['episode'],'*',"where `groupno` IN ($idx)",'loop,asc'));
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$group = $this->mDB->DBfetch($this->table['group'],array('category1','title','eng_title','season','season_title','artist','subartist','nation','tag','field1','field2','field3'),"where `idx`='{$data[$i]['groupno']}'");
				$episode[$i] = array_merge($group,$data[$i]);
				$episode[$i]['category1Name'] = $this->GetCategoryName($group['category1'],true);
				$episode[$i]['titlelink'] = $this->baseURL.GetQueryString(array('mode'=>'view','group'=>$episode[$i]['groupno'],'artist'=>''));
				$episode[$i]['episodelink'] = $this->baseURL.GetQueryString(array('mode'=>'view','episode'=>$episode[$i]['episodeno'],'artist'=>''));
				$episode[$i]['thumbnail'] = file_exists($_ENV['path'].$this->groupImagePath.'/'.$episode[$i]['groupno'].'.small.thm') == true ? $_ENV['dir'].$this->groupImagePath.'/'.$episode[$i]['groupno'].'.small.thm' : '';
				$episode[$i]['artist'] = implode(' & ',$this->GetArtistName(explode(',',$episode[$i]['artist']),true));
				$episode[$i]['subartist'] = implode(', ',$this->GetArtistName(explode(',',$episode[$i]['subartist']),true));
				$episode[$i]['tag'] = implode(', ',$this->GetTagName(explode(',',$episode[$i]['tag']),true));
				$episode[$i]['torrent'] = $this->GetTorrentInfo($this->mDB->DBfetchs($this->table['torrent'],'*',"where `episodeno`='{$episode[$i]['idx']}'"));
			}

			$this->mTemplet->assign('episode',$episode);
		} else {
			$formStart = '<form name="TrackerPost" enctype="multipart/form-data" method="post" action="'.$this->moduleDir.'/exec/Tracker.do.php" target="execFrame">'."\n";
			$formStart.= '<input type="hidden" name="action" value="artist" />'."\n";
			$formStart.= '<input type="hidden" name="artist" value="'.$artist['idx'].'" />'."\n";
			$formEnd = '</form>'."\n";
			$formEnd.= '<iframe name="execFrame" style="display:none;"></iframe>'."\n";
			
			$this->mTemplet->assign('formStart',$formStart);
			$this->mTemplet->assign('formEnd',$formEnd);
		}
		
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('artist',$artist);
		$this->PrintTemplet();
	}
	
	function PrintView() {
		$torrent = Request('torrent');
		$episode = Request('episode');
		$group = Request('group');

		if ($torrent) {
			$temp = $this->mDB->DBfetch($this->table['torrent'],'*',"where `idx`='{$torrent}'");
			$episode = $this->GetEpisodeInfo($this->mDB->DBfetch($this->table['episode'],'*',"where `idx`='{$temp['episodeno']}'"));
			$group = $this->GetGroupInfo($this->mDB->DBfetch($this->table['group'],'*',"where `idx`='{$temp['groupno']}'"));
			$viewmode = 'torrent';
			if ($episode['episode'] || $episode['episode_title']) $episodemode = 'episode';
			else $episodemode = 'unique';
			
			$this->link['addtorrent'] = $this->baseURL.$this->GetQueryString(array('mode'=>'write','episode'=>$episode['idx']));
		} elseif ($episode) {
			$episode = $this->GetEpisodeInfo($this->mDB->DBfetch($this->table['episode'],'*',"where `idx`='{$episode}'"));
			$group = $this->GetGroupInfo($this->mDB->DBfetch($this->table['group'],'*',"where `idx`='{$episode['groupno']}'"));
			$viewmode = 'episode';
			if ($episode['episode'] || $episode['episode_title']) $episodemode = 'episode';
			else $episodemode = 'unique';
			
			$this->link['addtorrent'] = $this->baseURL.$this->GetQueryString(array('mode'=>'write','episode'=>$episode['idx']));
		} elseif ($group) {
			$group = $this->GetGroupInfo($this->mDB->DBfetch($this->table['group'],'*',"where `idx`='{$group}'"));
			$episode = $this->GetEpisodeInfo($this->mDB->DBfetchs($this->table['episode'],'*',"where `groupno`='{$group['idx']}'",'episode,desc'));
			if (sizeof($episode) == 1 && $episode[0]['episode'] == '' && $episode[0]['episode_title'] == '') {
				$episode = $episode[0];
				$viewmode = 'episode';
				$episodemode = 'unique';
				$this->link['addtorrent'] = $this->baseURL.$this->GetQueryString(array('mode'=>'write','episode'=>$episode['idx']));
			} else {
				$viewmode = 'group';
				$episodemode = 'episode';
			}
			$this->link['addepisode'] = $this->baseURL.$this->GetQueryString(array('mode'=>'write','group'=>$group['idx']));
			
		}
		
		$category = $this->mDB->DBfetch($this->table['category'],array('idx','view_layout'),"where `idx`='{$group['category1']}'");
		$layout = $this->mDB->DBfetch($this->table['layout'],array('preset','config'),"where `idx`='{$category['view_layout']}'");

		$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/'.$layout['preset']);

		if ($viewmode == 'episode' || $viewmode == 'torrent') {
			$torrent = $this->GetTorrentInfo($this->mDB->DBfetchs($this->table['torrent'],'*',"where `episodeno`='{$episode['idx']}'"),true);
			$this->mTemplet->assign('torrent',$torrent);
			$this->mTemplet->assign('episode',$episode);
		}
		
		if ($viewmode == 'group') {
			for ($i=0, $loop=sizeof($episode);$i<$loop;$i++) {
				$episode[$i]['torrent'] = $this->GetTorrentInfo($this->mDB->DBfetchs($this->table['torrent'],'*',"where `episodeno`='{$episode[$i]['idx']}'"));
			}
			$this->mTemplet->assign('episode',$episode);
		}

		$this->mTemplet->assign('viewmode',$viewmode);
		$this->mTemplet->assign('episodemode',$episodemode);
		$this->mTemplet->assign('group',$group);
		
		echo '<iframe name="downloadFrame" style="display:none;"></iframe>'."\n";
		
		$this->PrintTemplet();
	}
	
	function PrintMent() {
		$mode = Request('mode') ? Request('mode') : 'torrent';
		$repto = Request('repto');

		$this->PrintHeader();
		
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/tracker/style.css" type="text/css" title="style" />'."\n";
		echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/tracker/script.js"></script>'."\n";
		
		if ($mode == 'torrent') {
			$find = "where `repto`='$repto' and `mode`='torrent'";
		} elseif ($mode == 'group') {
			$find = "where `groupno`='$repto' and (`mode`='torrent' or `mode`='group')";
		}

		$listnum = 10;
		$pagenum = 5;
		$p = is_numeric(Request('p')) == true && Request('p') > 0 ? Request('p') : 1;

		$totalpost = $this->mDB->DBcount($this->table['ment'],$find);
		$totalpage = ceil($totalpost/$listnum) == 0 ? 1 : ceil($totalpost/$listnum);
		$p = $p > $totalpage ? $totalpage : $p;

		$orderer = 'idx,desc';
		$limiter = ($p-1)*$listnum.','.$listnum;

		$data = array();
		$data = $this->GetEpisodeInfo($this->mDB->DBfetchs($this->table['ment'],'*',$find,$orderer,$limiter));
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$data[$i]['member'] = $this->mMember->GetMemberInfo($data[$i]['mno']);
			$data[$i]['content'] = nl2br($data[$i]['content']);
			$data[$i]['reg_date'] = GetTime('Y-m-d H:i:s',$data[$i]['reg_date']);
			
			if ($mode == 'group' && $data[$i]['repto']) {
				$torrent = $this->GetTorrentInfo($this->mDB->DBfetch($this->table['torrent'],array('episodeno','subtitles','resolution','source','codec','format','edition','release'),"where `idx`='{$data[$i]['repto']}'"));
				$episode = $this->GetEpisodeInfo($this->mDB->DBfetch($this->table['episode'],array('episode','episode_title','is_pack'),"where `idx`='{$torrent['episodeno']}'"));
				$data[$i]['torrent'] = array_merge($torrent,$episode);
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

		$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/ment.tpl');
	
		$formStart = '<form name="TrackerPost" enctype="multipart/form-data" method="post" action="'.$this->moduleDir.'/exec/Tracker.do.php" target="execFrame" onsubmit="return TrackerCheckPostForm();">'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.$mode.'" />'."\n";
		$formStart.= '<input type="hidden" name="repto" value="'.$repto.'" />'."\n";
		$formStart.= '<input type="hidden" name="action" value="ment" />'."\n";
		
		$formEnd = '</form>'."\n";
		$formEnd.= '<iframe name="execFrame" style="display:none;"></iframe>'."\n";

		$this->mTemplet->assign('category',$category['idx']);
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('data',$data);
		$this->mTemplet->assign('page',$page);
		$this->mTemplet->assign('p',$p);
		$this->mTemplet->assign('pagenum',$pagenum);
		$this->mTemplet->assign('prevpage',$prevpage);
		$this->mTemplet->assign('nextpage',$nextpage);
		$this->mTemplet->assign('prevlist',$prevlist);
		$this->mTemplet->assign('nextlist',$nextlist);
		$this->mTemplet->assign('totalpost',number_format($totalpost));
		$this->mTemplet->assign('totalpage',number_format($totalpage));
		$this->PrintTemplet();
		
		echo '<script type="text/javascript">addEvent(window,"load",TrackerMentInnerFormHeight);</script>';
	}
	
	function PrintWrite() {
		$mode = Request('mode');
		$group = Request('group');
		$episode = Request('episode');
		$torrent = Request('torrent');
		
		$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/write.tpl');
		
		$addmode = false;
		$categoryList1 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `parent`='0'",'sort,asc');
		$categoryList2 = $categoryList3 = array();
		
		$categoryName1 = $categoryName2 = $categoryName3 = '';
		if ($mode == 'modify') {
			if ($group != null) {
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='$group'");
				if (isset($group['idx']) == false) $this->PrintError('등록되어 있는 그룹이 아닙니다.<br />Not Found Group Info.');

				$innerForm = '<iframe id="TrackerWriteInnerForm" src="'.$this->moduleDir.'/InnerForm.php?category='.$group['category1'].'&mode=modify&group='.$group['idx'].'" name="innerForm" src="about:blank;" style="width:100%; height:10px;" frameborder="0" scrolling="no"></iframe>';
			} elseif ($episode != null) {
				$episode = $this->mDB->DBfetch($this->table['episode'],'*',"where `idx`='$episode'");
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='{$episode['groupno']}'");
				
				$innerForm = '<iframe id="TrackerWriteInnerForm" src="'.$this->moduleDir.'/InnerForm.php?category='.$group['category1'].'&mode=modify&episode='.$episode['idx'].'" name="innerForm" src="about:blank;" style="width:100%; height:10px;" frameborder="0" scrolling="no"></iframe>';
			} elseif ($torrent != null) {
				$torrent = $this->mDB->DBfetch($this->table['torrent'],'*',"where `idx`='$torrent'");
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='{$torrent['groupno']}'");
				
				$innerForm = '<iframe id="TrackerWriteInnerForm" src="'.$this->moduleDir.'/InnerForm.php?category='.$group['category1'].'&mode=modify&torrent='.$torrent['idx'].'" name="innerForm" src="about:blank;" style="width:100%; height:10px;" frameborder="0" scrolling="no"></iframe>';
			}
		} else {
			if ($group != null) {
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='$group'");
				if (isset($group['idx']) == false) $this->PrintError('등록되어 있는 그룹이 아닙니다.<br />Not Found Group Info.');
				
				$addmode = true;
				if ($group['category1']) $categoryList2 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `parent`='{$group['category1']}'",'sort,asc');
				if ($group['category2']) $categoryList3 = $this->mDB->DBfetchs($this->table['category'],array('idx','title'),"where `parent`='{$group['category2']}'",'sort,asc');
				
				$categoryName1 = $this->GetCategoryName($group['category1']);
				$categoryName2 = $this->GetCategoryName($group['category2']);
				$categoryName3 = $this->GetCategoryName($group['category3']);
				
				$innerForm = '<iframe id="TrackerWriteInnerForm" name="innerForm" src="'.$this->moduleDir.'/InnerForm.php?category='.$group['category1'].'&mode=write&group='.$group['idx'].'" style="width:100%; height:10px;" frameborder="0" scrolling="no"></iframe>';
			} else {
				$innerForm = '<iframe id="TrackerWriteInnerForm" name="innerForm" src="about:blank;" style="width:100%; height:10px;" frameborder="0" scrolling="no"></iframe>';
			}
		}
		
		$formStart = '<form name="TrackerOuter" onsumbit="return false">'."\n";
		$formEnd = '</form>';
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('category1',$category1);
		$this->mTemplet->assign('category2',$category2);
		$this->mTemplet->assign('category3',$category3);
		$this->mTemplet->assign('categoryList1',$categoryList1);
		$this->mTemplet->assign('categoryList2',$categoryList2);
		$this->mTemplet->assign('categoryList3',$categoryList3);
		$this->mTemplet->assign('categoryName1',$categoryName1);
		$this->mTemplet->assign('categoryName2',$categoryName2);
		$this->mTemplet->assign('categoryName3',$categoryName3);
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('addmode',$addmode);
		$this->mTemplet->assign('announce',$this->announceURL.'?mid='.$this->GetMID());
		$this->mTemplet->assign('innerForm',$innerForm);
		
		$this->PrintTemplet();
	}
	
	function PrintWriteInner($category) {
		$mode = Request('mode') ? Request('mode') : 'write';
		$group = Request('group');
		$episode = Request('episode');
		$torrent = Request('torrent');

		$this->PrintHeader();
		
		echo '<link rel="stylesheet" href="'.$this->moduleDir.'/templet/tracker/style.css" type="text/css" title="style" />'."\n";
		echo '<script type="text/javascript" src="'.$this->moduleDir.'/templet/tracker/script.js"></script>'."\n";
		
		$category = $this->mDB->DBfetch($this->table['category'],array('idx','form_layout'),"where `idx`='$category'");
		$taglist = $this->mDB->DBfetchs($this->table['tag'],array('idx','title'),"where `category1`='{$category['idx']}'");
		$layout = $this->mDB->DBfetch($this->table['layout'],array('preset','config'),"where `idx`='{$category['form_layout']}'");

		$addmode = '';
		$this->mTemplet = new Templet($this->modulePath.'/templet/tracker/'.$layout['preset']);
		
		if ($mode == 'modify') {
			if ($group != null) {
				$mode = 'group';
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='$group'");
				if (isset($group['idx']) == false) $this->PrintError('등록되어 있는 그룹이 아닙니다.<br />Not Found Group Info.');
				$group['artist'] = $this->GetArtistName(explode(',',$group['artist']));
				$group['subartist'] = $this->GetArtistName(explode(',',$group['subartist']));
				$group['nation'] = explode(',',$group['nation']);
				$group['tag'] = explode(',',$group['tag']);
				$group['thumbnail'] = file_exists($_ENV['path'].$this->groupImagePath.'/'.$group['idx'].'.small.thm');
				$this->mTemplet->assign('group',$group);
				
				$modifyScript = '<script type="text/javascript">'."\n";
				for ($i=0, $loop=sizeof($group['artist']);$i<$loop;$i++) {
					if ($group['artist'][$i]) $modifyScript.= 'TrackerArtistItemAdd("'.$group['artist'][$i].'");';
				}
				for ($i=0, $loop=sizeof($group['subartist']);$i<$loop;$i++) {
					if ($group['subartist'][$i]) $modifyScript.= 'TrackerSubArtistItemAdd("'.$group['subartist'][$i].'");';
				}
				for ($i=0, $loop=sizeof($group['nation']);$i<$loop;$i++) {
					if ($group['nation'][$i]) $modifyScript.= 'TrackerNationItemAdd("'.$group['nation'][$i].'");';
				}
				$modifyScript.= '</script>';
				
				for ($i=0, $loop=sizeof($taglist);$i<$loop;$i++) {
					if (in_array($taglist[$i]['idx'],$group['tag']) == true) $taglist[$i]['checked'] = true;
					else $taglist[$i]['checked'] = false;
				}
			} elseif ($episode != null) {
				$mode = 'episode';
				$episode = $this->mDB->DBfetch($this->table['episode'],'*',"where `idx`='$episode'");
				$episode['date'] = $episode['date'] == '0000-00-00' ? '' : $episode['date'];
				$episode['episode'] = $episode['episode'] == '0' ? '' : $episode['episode'];
				$episode['is_pack'] = $episode['is_pack'] == 'TRUE';
				$episode['episode'] = $episode['is_pack'] == true ? '' : $episode['episode'];
				$this->mTemplet->assign('episode',$episode);
			} elseif ($torrent != null) {
				$mode = 'torrent';
				$torrent = $this->mDB->DBfetch($this->table['torrent'],'*',"where `idx`='$torrent'");
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='{$torrent['groupno']}'");
				$image = $this->mDB->DBfetchs($this->table['file'],array('idx','type','filename'),"where `repto`='{$torrent['idx']}'");
				for ($i=0, $loop=sizeof($image);$i<$loop;$i++) {
					$image[$i]['thumbnail'] = $_ENV['dir'].$this->thumbnail.'/'.$image[$i]['idx'].'.thm';
					$image[$i]['filepath'] = $_ENV['dir'].$image[$i]['filepath'];
					if ($image[$i]['type'] == 'SCREENSHOT') {
						$torrent['screenshot'] = $image[$i];
					} elseif ($image[$i]['type'] == 'SNAPSHOT') {
						$torrent['snapshot'] = $image[$i];
					} else {
						$torrent['image'][] = $image[$i];
					}
				}
				
				$episode = $this->mDB->DBfetch($this->table['episode'],'*',"where `idx`='{$torrent['episodeno']}'");
				$episode['date'] = $episode['date'] == '0000-00-00' ? '' : $episode['date'];
				$episode['episode'] = $episode['episode'] == '0' ? '' : $episode['episode'];
				$episode['is_pack'] = $episode['is_pack'] == 'TRUE';
				$episode['episode'] = $episode['is_pack'] == true ? '' : $episode['episode'];
				
				$this->mTemplet->assign('episode',$episode);
				$this->mTemplet->assign('torrent',$torrent);
				
				$modifyScript = '<script type="text/javascript">'."\n";
				$modifyScript.= 'TrackerEpisodeSearch('.$torrent['groupno'].');'."\n";
				$modifyScript.= 'AzUploaderComponent.load("repto='.$torrent['idx'].'");'."\n";
				$modifyScript.= '</script>';
			}
		} else {
			if ($group != null) {
				$group = $this->mDB->DBfetch($this->table['group'],'*',"where `idx`='$group'");
				if (isset($group['idx']) == false) $this->PrintError('등록되어 있는 그룹이 아닙니다.<br />Not Found Group Info.');
				
				$addmode = 'group';
				$modifyScript = '<script type="text/javascript">TrackerEpisodeSearch('.$group['idx'].');</script>';
			} else {
				$modifyScript = '';
			}
		}
		
		$formStart = '<form name="TrackerPost" enctype="multipart/form-data" method="post" action="'.$this->moduleDir.'/exec/Tracker.do.php" target="execFrame" onsubmit="return TrackerCheckPostForm();">'."\n";
		$formStart.= '<input type="hidden" name="mode" value="'.$mode.'" />'."\n";
		$formStart.= '<input type="hidden" name="action" value="post" />'."\n";
		$formStart.= '<input type="hidden" name="category1" value="'.$category['idx'].'" />'."\n";
		$formStart.= '<input type="hidden" name="category2" value="" />'."\n";
		$formStart.= '<input type="hidden" name="category3" value="" />'."\n";
		$formStart.= '<input type="hidden" name="torrent_tag">'."\n";
		$formStart.= '<input type="hidden" name="daummovie">'."\n";
		$formStart.= '<input type="hidden" name="daumtv">'."\n";
		$formStart.= '<input type="hidden" name="daumbook">'."\n";
		$formStart.= '<input type="hidden" name="groupno" value="'.(isset($group['idx']) == true ? $group['idx'] : '').'">'."\n";
		$formStart.= '<input type="hidden" name="episodeno" value="'.(isset($episode['idx']) == true ? $episode['idx'] : '').'">'."\n";
		$formStart.= '<input type="hidden" name="torrentno" value="'.(isset($torrent['idx']) == true ? $torrent['idx'] : '').'">'."\n";
		
		$formEnd = '</form>'."\n";
		$formEnd.= '<iframe name="execFrame" style="display:none;"></iframe>'."\n";
		
		$formEnd.= $modifyScript;

		$this->mTemplet->assign('category',$category['idx']);
		$this->mTemplet->assign('formStart',$formStart);
		$this->mTemplet->assign('formEnd',$formEnd);
		$this->mTemplet->assign('taglist',$taglist);
		$this->mTemplet->assign('mode',$mode);
		$this->mTemplet->assign('addmode',$addmode);
		$this->mTemplet->register_object('mTracker',$this,array('PrintUploader'));
		$this->PrintTemplet();
		
		echo '<script type="text/javascript">addEvent(window,"load",TrackerWriteInnerFormHeight);</script>';
	}
	
	function PrintUploader($var) {
		$mModule = new Module('uploader');
		if ($mModule->IsSetup() == true) {
			$use_uploader = true;
			$mUploader = new ModuleUploader();
			
			$mUploader->SetCaller('tracker',$this);
			$mUploader->SetUploadPath($this->moduleDir.'/exec/FileUpload.do.php?type='.strtoupper($var['type']));
			$mUploader->SetLoadPath($this->moduleDir.'/exec/FileLoad.do.php?type='.strtoupper($var['type']));
			$mUploader->SetType('gif,jpg,png,jpeg');
			$mUploader->SetCallback('TrackerWriteInnerFormHeight');
			$uploader = $mUploader->GetUploader($var['skin'],$var['id'],$var['form'],'');
		} else {
			$uploader = '';
		}

		return $uploader;
	}
	
	function CheckTorrent($hash) {
		return $this->mDB->DBcount($this->table['torrent'],"where `hash`='$hash'") == 1;
	}
	
	function CheckMember($mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		return $this->mDB->DBcount($this->table['user'],"where `mno`='$mno' and `status`='ACTIVE'") > 0;
	}
	
	function CheckUser($mid) {
		$check = $this->mDB->DBfetch($this->table['user'],array('status'),"where `mid`='$mid'");
		return isset($check['status']) == true ? $check['status'] : 'UNREGISTED';
	}
	
	function GetMID($mno='') {
		$mno = $mno ? $mno : $this->member['idx'];
		$check = $this->mDB->DBfetch($this->table['user'],array('mid'),"where `mno`='$mno'");
		return isset($check['mid']) == true ? $check['mid'] : sha1('empty');
	}
	
	function GetUserID($mno='',$mid='') {
		if ($mno) {
			$member = $this->mMember->GetMemberInfo($mno);
			return $member['user_id'];
		}
	}
	
	function GetUserInfo($mno) {
		$member = $this->mMember->GetMemberInfo($mno);
		$user = $this->mDB->DBfetch($this->table['user'],'*',"where `mno`='$mno'");
		$user['grade'] = $this->mDB->DBfetch($this->table['user_grade'],'*',"where `sort`='{$user['grade']}'");
		
		return array_merge($member,$user);
	}
	
	function GetTorrentPermission($mode,$value='') {
		return $this->CheckMember();
	}
	
	function GetAnnouncePeer($hash,$no_peer_id=false) {
		$find = "where `hash`='$hash' and `status`='ACTIVE'";
		$data = $this->mDB->DBfetchs($this->table['peer'],'*',$find);
		$peer = array();
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$peer[$i] = array('ip'=>$data[$i]['ip'],'port'=>$data[$i]['port']);
			if ($no_peer_id == false) $peer[$i]['peer id'] = $data[$i]['pid'];
		}
		return $peer;
	}
	
	function GetLeecherCount($hash) {
		return $this->mDB->DBcount($this->table['peer'],"where `hash`='$hash' and `status`='ACTIVE' and `left`>0");
	}
	
	function GetPeerCount($hash) {
		return $this->mDB->DBcount($this->table['peer'],"where `hash`='$hash' and `status`='ACTIVE'");
	}
	
	function GetSeederCount($hash) {
		return $this->mDB->DBcount($this->table['peer'],"where `hash`='$hash' and `status`='ACTIVE' and `left`=0");
	}
	
	function GetDownloadSize($hash) {
		$check = $this->mDB->DBfetch($this->table['peer'],array('SUM(download)'),"where `hash`='$hash'");
		return isset($check[0]) == true ? $check[0] : '0';
	}
	
	function UpdateTorrentPeer($hash) {
		$checkTime = GetGMT() - ($this->module['tracker_time']+10)*60;
		$torrent = $this->mDB->DBfetch($this->table['torrent'],array('episodeno'),"where `hash`='$hash'");
		$this->mDB->DBupdate($this->table['peer'],array('status'=>'INACTIVE'),'',"where `hash`='$hash' and `last_check_date`<$checkTime");
		$update = array('leecher'=>$this->GetLeecherCount($hash),'seeder'=>$this->GetSeederCount($hash),'downloadsize'=>$this->GetDownloadSize($hash));
		$this->mDB->DBupdate($this->table['torrent'],$update,'',"where `hash`='$hash'");
		
		$episode = $this->mDB->DBfetch($this->table['torrent'],array('SUM(seeder)','SUM(leecher)'),"where `episodeno`='{$torrent['episodeno']}'");
		$this->mDB->DBupdate($this->table['episode'],array('seeder'=>$episode[0],'leecher'=>$episode[1]),'',"where `idx`='{$torrent['episodeno']}'");
	}
	
	function UpdatePeer($hash,$mid,$pid,$ip,$port,$upload,$download,$left,$status) {
		$pid = explode('-',$pid);
		$pid[2] = $this->GetPIDToSHA1($pid[2]);
		$pid = implode('-',$pid);
		$torrent = $this->mDB->DBfetch($this->table['torrent'],array('idx','is_freeleech','is_halfleech','is_doubleupload'),"where `hash`='$hash'");
		$check = $this->mDB->DBfetch($this->table['peer'],array('mno','upload','download','status'),"where `hash`='$hash' and `mid`='$mid' and `pid`='$pid'");
		if (isset($check['status']) == true) {
			$plusUpload = $upload - $check['upload'];
			$plusDownload = $download - $check['download'];
			
			if ($torrent['is_freeleech'] == 'TRUE') $plusDownload = '0';
			elseif ($torrent['is_halfleech'] == 'TRUE') $plusDownload = floor($plusDownload/2);
			if ($torrent['is_doubleupload'] == 'TRUE') $plusUpload = $plusUpload*2;
			
			$this->mDB->DBupdate($this->table['user'],'',array('upload'=>'`upload`+'.$plusUpload,'download'=>'`download`+'.$plusDownload),"where `mid`='$mid'");
			$this->mDB->DBupdate($this->table['peer'],array('upload'=>$upload,'download'=>$download,'left'=>$left,'last_check_date'=>GetGMT(),'status'=>$status),'',"where `hash`='$hash' and `mid`='$mid' and `pid`='$pid'");
			
			$snatch = $this->mDB->DBfetch($this->table['snatch'],array('idx'),"where `torrentno`='{$torrent['idx']}' and `mno`='{$check['mno']}'");
			if (isset($snatch['idx']) == true) {
				$this->mDB->DBupdate($this->table['snatch'],'',array('upload'=>'`upload`+'.$plusUpload,'download'=>'`download`+'.$plusDownload),"where `idx`='{$snatch['idx']}'");
			} else {
				$this->mDB->DBinsert($this->table['snatch'],array('torrentno'=>$torrent['idx'],'mno'=>$check['mno'],'upload'=>$upload,'download'=>$download,'reg_date'=>GetGMT()));
			}
		} else {
			$user = $this->mDB->DBfetch($this->table['user'],array('mno'),"where `mid`='$mid'");
			$this->mDB->DBinsert($this->table['peer'],array('hash'=>$hash,'mid'=>$mid,'mno'=>$user['mno'],'pid'=>$pid,'ip'=>$ip,'port'=>$port,'client'=>$_SERVER['HTTP_USER_AGENT'],'upload'=>$upload,'download'=>$download,'left'=>$left,'start_date'=>GetGMT(),'last_check_date'=>GetGMT(),'status'=>$status));
		}
		$this->UpdateTorrentPeer($hash);
	}
}
?>