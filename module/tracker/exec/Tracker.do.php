<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$action = Request('action');

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mTracker = new ModuleTracker();

if ($action == 'signin') {
	$user_id = strlen(Request('user_id')) > 0 ? strtolower(Request('user_id')) : Alertbox('아이디를 입력하여 주십시오.');
	$password = strlen(Request('password')) > 0 ? md5(strtolower(Request('password'))) : Alertbox('패스워드를 입력하여 주십시오.');
	$autologin = Request('autologin') ? true : false;

	if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='$user_id' and `is_leave`='FALSE'") == 0) Alertbox('아이디를 찾을 수 없습니다.');
	if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='$user_id' and `password`='$password'") == 0) Alertbox('패스워드가 일치하지 않습니다.');
	
	if ($mTracker->GetConfig('is_opensign') != 'on') {
		$code = Request('code');
		$check = $mDB->DBfetch($mTracker->table['invitecode'],array('code','status'),"where `code`='$code'");
		
		if (isset($check['code']) ==  false || $check['status'] != 'WAIT') {
			Alertbox('초대장코드가 잘못되었거나, 접근할 수 있는 권한이 없습니다.');
		}
	}

	$myinfo = $mDB->DBfetch($_ENV['table']['member'],'*',"where `user_id`='$user_id' and `password`='$password'");
	REQUIRE_ONCE './SignIn.do.php';
	
	$mMember->Login($myinfo['idx'],$autologin);
	Alertbox('성공적으로 회원가입이 되었습니다.\\nSign in complete.',3,'/','parent');
}

if ($action == 'post') {
	$type = Request('type');
	$mode = Request('mode');
	
	if ($mode == 'write') {
		$mKeyword = new Keyword();

		$groupno = Request('groupno');
		$category1 = Request('category1');
		$category2 = Request('category2');
		$category3 = Request('category3');
		
		$daummovie = Request('daummovie');
		$daumtv = Request('daumtv');

		if (!$groupno) {
			if ($daummovie) {
				$daum = $mDB->DBfetch($mTracker->table['daum_movie'],'*',"where `idx`='$daummovie'");
				if (isset($daum['groupno']) == true && $mDB->DBcount($mTracker->table['group'],"where `idx`='{$daum['groupno']}' and `title`='{$daum['title']}'") > 0) {
					$groupno = $daum['groupno'];
				} elseif (isset($daum['idx']) == false) {
					$daummovie = '';
				}
			} elseif ($daumtv) {
				$daum = $mDB->DBfetch($mTracker->table['daum_tv'],'*',"where `idx`='$daumtv'");
				if (isset($daum['groupno']) == true && $mDB->DBcount($mTracker->table['group'],"where `idx`='{$daum['groupno']}' and `title`='{$daum['title']}'") > 0) {
					$groupno = $daum['groupno'];
				} elseif (isset($daum['idx']) == false) {
					$daumtv = '';
				}
			}
		}
		
		$group = array();
		if (!$groupno) {
			if (!$daummovie && !$daumtv) {
				$group['title'] = Request('title') ? Request('title') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(title)');
				$group['eng_title'] = Request('eng_title') ? Request('eng_title') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(eng_title)');
				$group['artist'] = Request('artist') ? $mTracker->GetArtistIDX(Request('artist'),$category1) : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(artist)');
				$group['subartist'] = $mTracker->GetSubArtistIDX(Request('subartist'),$category1);
				$group['year'] = Request('year') ? Request('year') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(year)');
				$group['date'] = Request('date');
				$group['nation'] = Request('nation');
				$group['season'] = Request('season') ? Request('season') : '0';
				$group['season_title'] = Request('season_title');
			} else {
				$group['title'] = $daum['title'];
				$group['eng_title'] = $daum['eng_title'] ? $daum['eng_title'] : Request('eng_title');
				$group['artist'] = $mTracker->GetArtistIDX($daum['artist'],$category1);
				$group['subartist'] = $mTracker->GetSubArtistIDX($daum['subartist'],$category1);
				$group['year'] = $daum['year'] ? $daum['year'] : Request('year');
				$group['date'] = $daum['date'] ? $daum['date'] : Request('date');
				$group['nation'] = $daum['nation'] ? $daum['nation'] : Request('nation');
				$group['season'] = $daum['season'] ? $daum['season'] : (Request('season') ? Request('season') : '0');
				$group['season_title'] = Request('season_title');
			}
			
			$group['category1'] = $category1;
			$group['category2'] = $category2;
			$group['category3'] = $category3;
			$group['titlekey1'] = $mKeyword->GetUTF8Code($group['title']);
			$group['titlekey2'] = $mKeyword->GetEngCode($group['titlekey1']);
			$group['titlekey3'] = $mKeyword->GetUTF8Code($group['eng_title']);
			$group['intro'] = Request('intro') ? Request('intro') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(intro)');
			$group['tag'] = sizeof(Request('tag')) > 0 ? implode(',',Request('tag')) : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(tag)');
			$group['field1'] = Request('field1');
			$group['field2'] = Request('field2');
			$group['field3'] = Request('field3');
		} else {
			$group = $mDB->DBfetch($mTracker->table['group'],'*',"where `idx`='$groupno'");
		}
		
		$torrent = array();
		
		$seedfile = $mTracker->GetTorrentFileInfo($_FILES['torrent']['tmp_name']);
		if ($seedfile == false) Alertbox('토렌트 파일을 업로드 하여 주십시오.\\nPlease select torrent file.');
		
		$torrent['hash'] = $seedfile->hash;
		$torrent['file'] = sizeof($seedfile->file);
		$torrent['filesize'] = $seedfile->filesize;
		$torrent['filedetail'] = $seedfile->filedetail;
		$torrent['subtitles'] = Request('subtitles');
		$torrent['edition'] = Request('edition');
		$torrent['resolution'] = Request('resolution');
		$torrent['codec'] = Request('codec');
		$torrent['source'] = Request('source');
		$torrent['format'] = Request('format');
		$torrent['release'] = Request('release');
		$torrent['intro'] = Request('torrent_intro');
		$torrent['mediainfo'] = Request('mediainfo');
		$torrent['reg_date'] = GetGMT();

		if (!$groupno) {
			$groupno = $mDB->DBinsert($mTracker->table['group'],$group);
		}
		
		if (Request('is_pack') == 'true') {
			$episode = Request('episode_title') ? Request('episode_title') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(range of episode)');
			$episode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `groupno`='$groupno' and `episode_title`='$episode'");
		} else {
			$episode = Request('episode') ? Request('episode') : '0';
			if ($episode != '0') {
				$episode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `groupno`='$groupno' and `episode`='$episode'");
			} else {
				$episode_title = Request('episode_title');
				$episode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `groupno`='$groupno' and `episode_title`='$episode_title'");
			}
		}
		
		if (isset($episode['idx']) == false) {
			$episode = array();
			$episode['groupno'] = $groupno;
			$episode['category1'] = $category1;
			$episode['category2'] = $category2;
			$episode['category3'] = $category3;
			$episode['episode'] = Request('episode');
			$episode['episode_title'] = Request('episode_title');
			$episode['is_pack'] = Request('is_pack') == 'true' ? 'TRUE' : 'FALSE';
			$episode['year'] = Request('episode_year') ? Request('episode_year') : $group['year'];
			$episode['date'] = Request('episode_date') ? Request('episode_date') : $group['date'];
			$episode['intro'] = Request('episode_intro');
			
			$episodeno = $mDB->DBinsert($mTracker->table['episode'],$episode);
		} else {
			$episode['year'] = Request('episode_year') ? Request('episode_year') : $group['year'];
			$episode['date'] = Request('episode_date') ? Request('episode_date') : $group['date'];
			$episode['intro'] = Request('episode_intro');
			
			$mDB->DBupdate($mTracker->table['episode'],$episode,'',"where `idx`='{$episode['idx']}'");
			$episodeno = $episode['idx'];
		}

		$torrent['groupno'] = $groupno;
		$torrent['episodeno'] = $episodeno;
		$torrent['mno'] = $member['idx'];
		//$torrent['tag'] = $mTracker->GetTorrentTag();
		
		$idx = $mDB->DBinsert($mTracker->table['torrent'],$torrent);
		$mDB->DBupdate($mTracker->table['episode'],array('loop'=>$torrent['reg_date']*-1),'',"where `idx`='$episodeno'");
		
		if ($daummovie) $mDB->DBupdate($mTracker->table['daum_movie'],array('groupno'=>$groupno),'',"where `idx`='$daummovie'");
		if ($daumtv) $mDB->DBupdate($mTracker->table['daum_tv'],array('groupno'=>$groupno),'',"where `idx`='$daumtv'");
		
		@move_uploaded_file($_FILES['torrent']['tmp_name'],$_ENV['path'].$mTracker->torrentPath.'/'.$idx.'.torrent');
		
		if (isset($_FILES['screenshot']['tmp_name']) == true && GetFileType($_FILES['screenshot']['name'],$_FILES['screenshot']['tmp_name']) == 'IMG') {
			$filepath = $mTracker->imagePath.'/'.md5_file($_FILES['screenshot']['tmp_name']).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($_FILES['screenshot']['name']);

			if (CreateDirectory($_ENV['path'].$mTracker->imagePath) == true) {
				$fidx = $mDB->DBinsert($mTracker->table['file'],array('type'=>'SCREENSHOT','repto'=>$idx,'filename'=>$_FILES['screenshot']['name'],'filepath'=>$filepath,'filesize'=>filesize($_FILES['screenshot']['tmp_name']),'filetype'=>'IMG','reg_date'=>GetGMT()));
				@move_uploaded_file($_FILES['screenshot']['tmp_name'],$_ENV['path'].$filepath);
				GetThumbnail($_ENV['path'].$filepath,$_ENV['path'].$mTracker->thumbnail.'/'.$fidx.'.thm',150,120,false);
			}
		}
		
		if (isset($_FILES['snapshot']['tmp_name']) == true && GetFileType($_FILES['snapshot']['name'],$_FILES['snapshot']['tmp_name']) == 'IMG') {
			$filepath = $mTracker->imagePath.'/'.md5_file($_FILES['snapshot']['tmp_name']).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($_FILES['snapshot']['name']);

			if (CreateDirectory($_ENV['path'].$mTracker->imagePath) == true) {
				$fidx = $mDB->DBinsert($mTracker->table['file'],array('type'=>'SNAPSHOT','repto'=>$idx,'filename'=>$_FILES['snapshot']['name'],'filepath'=>$filepath,'filesize'=>filesize($_FILES['snapshot']['tmp_name']),'filetype'=>'IMG','reg_date'=>GetGMT()));
				@move_uploaded_file($_FILES['snapshot']['tmp_name'],$_ENV['path'].$filepath);
				GetThumbnail($_ENV['path'].$filepath,$_ENV['path'].$mTracker->thumbnail.'/'.$fidx.'.thm',150,120,false);
			}
		}
		
		if (isset($_FILES['group_image']['tmp_name']) == true && $_FILES['group_image']['tmp_name']) {
			if (CreateDirectory($_ENV['path'].$mTracker->groupImagePath) == true) {
				GetThumbnail($_FILES['group_image']['tmp_name'],$_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.small.thm','100','200');
				GetThumbnail($_FILES['group_image']['tmp_name'],$_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.middle.thm','300','0');
				GetThumbnail($_FILES['group_image']['tmp_name'],$_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.big.thm','500','0',true);
			}
		}
		$file = Request('file');
		if ($file != null) {
			for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
				$temp = explode('|',$file[$i]);
				$fidx = $temp[0];
	
				if (sizeof($temp) == 1) {
					$fileData = $mDB->DBfetch($mTracker->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
					@unlink($_ENV['path'].$fileData['filepath']);
					if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mTracker->thumbnail.'/'.$fidx.'.thm');
					$mDB->DBdelete($mTracker->table['file'],"where `idx`='$fidx'");
				} else {
					$mDB->DBupdate($mTracker->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
				}
			}
		}
		
		Alertbox('성공적으로 등록하였습니다.\\nUploaded Complete.',3,$mTracker->GetConfig('torrentURL').GetQueryString(array('mode'=>'view','group'=>$groupno),'',false),'parent.parent');
	}
	
	if ($mode == 'group') {
		$groupno = Request('groupno');
		$group = $mDB->DBfetch($mTracker->table['group'],array('category1','category2','category3'),"where `idx`='$groupno'");
		$category1 = $group['category1'];
		$category2 = $group['category2'];
		$category3 = $group['category3'];
		
		$mKeyword = new Keyword();
		
		$group['intro'] = Request('intro') ? Request('intro') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(intro)');
		$group['tag'] = sizeof(Request('tag')) > 0 ? implode(',',Request('tag')) : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(tag)');
		$group['field1'] = Request('field1');
		$group['field2'] = Request('field2');
		$group['field3'] = Request('field3');
		$group['title'] = Request('title') ? Request('title') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(title)');
		$group['eng_title'] = Request('eng_title');
		$group['artist'] = Request('artist') ? $mTracker->GetArtistIDX(Request('artist'),$category1) : '';
		$group['subartist'] = $mTracker->GetSubArtistIDX(Request('subartist'),$category1);
		$group['year'] = Request('year') ? Request('year') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(year)');
		$group['date'] = Request('date');
		$group['nation'] = Request('nation');
		$group['season'] = Request('season') ? Request('season') : '0';
		$group['season_title'] = Request('season_title');
		$group['titlekey1'] = $mKeyword->GetUTF8Code($group['title']);
		$group['titlekey2'] = $mKeyword->GetEngCode($group['titlekey1']);
		$group['titlekey3'] = $mKeyword->GetUTF8Code($group['eng_title']);
		
		if (Request('delete_group_image') == 'true') {
			@unlink($_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.small.thm');
			@unlink($_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.middle.thm');
			@unlink($_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.big.thm');
		}
		
		if (isset($_FILES['group_image']['tmp_name']) == true && $_FILES['group_image']['tmp_name']) {
			if (CreateDirectory($_ENV['path'].$mTracker->groupImagePath) == true) {
				GetThumbnail($_FILES['group_image']['tmp_name'],$_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.small.thm','100','200');
				GetThumbnail($_FILES['group_image']['tmp_name'],$_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.middle.thm','300','0');
				GetThumbnail($_FILES['group_image']['tmp_name'],$_ENV['path'].$mTracker->groupImagePath.'/'.$groupno.'.big.thm','500','0',true);
			}
		}
		
		$mDB->DBupdate($mTracker->table['group'],$group,'',"where `idx`='$groupno'");

		Alertbox('성공적으로 수정하였습니다.\\nUpdated Complete.',3,$mTracker->GetConfig('torrentURL').GetQueryString(array('mode'=>'view','group'=>$groupno),'',false),'parent.parent');
	}
	
	if ($mode == 'episode') {
		$episodeno = Request('episodeno');
		$episode = $mDB->DBfetch($mTracker->table['episode'],array('groupno','category1','category2','category3'),"where `idx`='$episodeno'");
		$group = $mDB->DBfetch($mTracker->table['group'],array('year','date'),"where `idx`='{$episode['groupno']}'");
		
		$episode['episode'] = Request('is_pack') == 'true' ? Request('episode_title') : Request('episode');
		$episode['episode_title'] = Request('episode_title');
		$episode['is_pack'] = Request('is_pack') == 'true' ? 'TRUE' : 'FALSE';
		$episode['year'] = Request('episode_year') ? Request('episode_year') : $group['year'];
		$episode['date'] = Request('episode_date') ? Request('episode_date') : $group['date'];
		$episode['intro'] = Request('episode_intro');
		
		$mDB->DBupdate($mTracker->table['episode'],$episode,'',"where `idx`='$episodeno'");
		
		Alertbox('성공적으로 수정하였습니다.\\nUpdated Complete.',3,$mTracker->GetConfig('torrentURL').GetQueryString(array('mode'=>'view','episode'=>$episodeno),'',false),'parent.parent');
	}
	
	if ($mode == 'torrent') {
		$torrentno = Request('torrentno');
		$torrent = $mDB->DBfetch($mTracker->table['torrent'],'*',"where `idx`='$torrentno'");
		$oEpisode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `idx`='{$torrent['episodeno']}'");
		$group = $mDB->DBfetch($mTracker->table['group'],'*',"where `idx`='{$torrent['groupno']}'");
		
		if (Request('is_pack') == 'true') {
			$episode = Request('episode_title') ? Request('episode_title') : Alertbox('필수항목을 모두 입력하여 주세요.\\nPlease complete all required fields.(range of episode)');
			$episode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `groupno`='$groupno' and `episode_title`='$episode'");
		} else {
			$episode = Request('episode') ? Request('episode') : '0';
			if ($episode != '0') {
				$episode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `groupno`='$groupno' and `episode`='$episode'");
			} else {
				$episode_title = Request('episode_title');
				$episode = $mDB->DBfetch($mTracker->table['episode'],'*',"where `groupno`='$groupno' and `episode_title`='$episode_title'");
			}
		}
		if (isset($episode['idx']) == false) {
			$episode = array();
			$episode['loop'] = GetGMT()*-1;
			$episode['groupno'] = $torrent['gruopno'];
			$episode['category1'] = $group['category1'];
			$episode['category2'] = $group['category2'];
			$episode['category3'] = $group['category3'];
			$episode['episode'] = Request('episode') ? Request('episode') : '0';
			$episode['episode_title'] = Request('episode_title');
			$episode['is_pack'] = Request('is_pack') == 'true' ? 'TRUE' : 'FALSE';
			$episode['year'] = Request('episode_year') ? Request('episode_year') : $group['year'];
			$episode['date'] = Request('episode_date') ? Request('episode_date') : $group['date'];
			$episode['intro'] = Request('episode_intro');

			$episodeno = $mDB->DBinsert($mTracker->table['episode'],$episode);
		} else {
			$episode['episode'] = Request('episode');
			$episode['episode_title'] = Request('episode_title');
			$episode['is_pack'] = Request('is_pack') == 'true' ? 'TRUE' : 'FALSE';
			$episode['year'] = Request('episode_year') ? Request('episode_year') : $group['year'];
			$episode['date'] = Request('episode_date') ? Request('episode_date') : $group['date'];
			$episode['intro'] = Request('episode_intro');
			
			$mDB->DBupdate($mTracker->table['episode'],$episode,'',"where `idx`='{$oEpisode['idx']}'");
			$episodeno = $oEpisode['idx'];
		}

		$torrent['episodeno'] = $episodeno;
		$torrent['subtitles'] = Request('subtitles');
		$torrent['edition'] = Request('edition');
		$torrent['resolution'] = Request('resolution');
		$torrent['codec'] = Request('codec');
		$torrent['source'] = Request('source');
		$torrent['format'] = Request('format');
		$torrent['release'] = Request('release');
		$torrent['intro'] = Request('torrent_intro');
		$torrent['mediainfo'] = Request('mediainfo');
		//$torrent['tag'] = $mTracker->GetTorrentTag();
		
		$mDB->DBupdate($mTracker->table['torrent'],$torrent,'',"where `idx`='{$torrent['idx']}'");
		
		if (isset($_FILES['screenshot']['tmp_name']) == true && GetFileType($_FILES['screenshot']['name'],$_FILES['screenshot']['tmp_name']) == 'IMG') {
			$filepath = $mTracker->imagePath.'/'.md5_file($_FILES['screenshot']['tmp_name']).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($_FILES['screenshot']['name']);

			if (CreateDirectory($_ENV['path'].$mTracker->imagePath) == true) {
				$image = $mDB->DBfetch($this->table['file'],array('idx','filepath'),"where `type`='SCREENSHOT' and `repto`='{$torrent['idx']}'");
				if (isset($image['idx']) == true) {
					@unlink($mTracker->imagePath.$image['filepath']);
					@unlink($mTracker->thumbnail.'/'.$image['idx'].'.thm');
					$fidx = $image['idx'];
				} else {
					$fidx = $mDB->DBinsert($mTracker->table['file'],array('type'=>'SCREENSHOT','repto'=>$torrent['idx'],'filename'=>$_FILES['screenshot']['name'],'filepath'=>$filepath,'filesize'=>filesize($_FILES['screenshot']['tmp_name']),'filetype'=>'IMG','reg_date'=>GetGMT()));
				}
				@move_uploaded_file($_FILES['screenshot']['tmp_name'],$_ENV['path'].$filepath);
				GetThumbnail($_ENV['path'].$filepath,$_ENV['path'].$mTracker->thumbnail.'/'.$fidx.'.thm',150,120,false);
			}
		}
		
		if (isset($_FILES['snapshot']['tmp_name']) == true && GetFileType($_FILES['snapshot']['name'],$_FILES['snapshot']['tmp_name']) == 'IMG') {
			$filepath = $mTracker->imagePath.'/'.md5_file($_FILES['snapshot']['tmp_name']).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($_FILES['snapshot']['name']);

			if (CreateDirectory($_ENV['path'].$mTracker->imagePath) == true) {
				$image = $mDB->DBfetch($this->table['file'],array('idx','filepath'),"where `type`='SNAPSHOT' and `repto`='{$torrent['idx']}'");
				if (isset($image['idx']) == true) {
					@unlink($mTracker->imagePath.$image['filepath']);
					@unlink($mTracker->thumbnail.'/'.$image['idx'].'.thm');
					$fidx = $image['idx'];
				} else {
					$fidx = $mDB->DBinsert($mTracker->table['file'],array('type'=>'SNAPSHOT','repto'=>$torrent['idx'],'filename'=>$_FILES['snapshot']['name'],'filepath'=>$filepath,'filesize'=>filesize($_FILES['snapshot']['tmp_name']),'filetype'=>'IMG','reg_date'=>GetGMT()));
				}
				
				@move_uploaded_file($_FILES['snapshot']['tmp_name'],$_ENV['path'].$filepath);
				GetThumbnail($_ENV['path'].$filepath,$_ENV['path'].$mTracker->thumbnail.'/'.$fidx.'.thm',150,120,false);
			}
		}

		$file = Request('file');
		if ($file != null) {
			for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
				$temp = explode('|',$file[$i]);
				$fidx = $temp[0];
	
				if (sizeof($temp) == 1) {
					$fileData = $mDB->DBfetch($mTracker->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
					@unlink($_ENV['path'].$fileData['filepath']);
					if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mTracker->thumbnail.'/'.$fidx.'.thm');
					$mDB->DBdelete($mTracker->table['file'],"where `idx`='$fidx'");
				}
			}
		}
		
		if ($mDB->DBcount($mTracker->table['episode'],"where `episodeno`='{$oEpisode['idx']}'") == 0) {
			$mDB->DBdelete($mTracker->table['episode'],"where `idx`='{$oEpisode['idx']}'");
		}
		
		Alertbox('성공적으로 수정하였습니다.\\nEdit Complete.',3,$mTracker->GetConfig('torrentURL').GetQueryString(array('mode'=>'view','torrent'=>$torrent['idx']),'',false),'parent.parent');
	}
}

if ($action == 'artist') {
	$artist = Request('artist');
	
	$insert = array();
	$insert['name'] = Request('name');
	$insert['eng_name'] = Request('eng_name');
	$insert['date'] = Request('date') ? Request('date') : '0000-00-00';
	$insert['gender'] = Request('gender');
	$insert['nation'] = Request('nation');
	$insert['intro'] = Request('intro');
	$insert['daummovie'] = Request('daummovie');
	
	$mDB->DBupdate($mTracker->table['artist'],$insert,'',"where `idx`='$artist'");
	
	if (Request('delete_photo') == 'true') {
		@unlink($_ENV['path'].$mTracker->artistThumbnail.'/'.$artist.'.thm');
	}
	
	if (isset($_FILES['photo']['tmp_name']) == true && GetFileType($_FILES['photo']['name'],$_FILES['photo']['tmp_name']) == 'IMG') {
		if (CreateDirectory($_ENV['path'].$mTracker->artistThumbnail) == true) {
			GetThumbnail($_FILES['photo']['tmp_name'],$_ENV['path'].$mTracker->artistThumbnail.'/'.$artist.'.thm',300,0,false);
		}
	}
	
	Alertbox('성공적으로 수정하였습니다.\\nEdit Complete.',3,$mTracker->GetConfig('torrentURL').GetQueryString(array('mode'=>'artist','artist'=>$artist),'',false),'parent');
}

if ($action == 'ment') {
	$mode = Request('mode');
	$repto = Request('repto');
	$vote = Request('vote') ? Request('vote') : '-1';
	$content = Request('content');
	
	$insert = array();
	$insert['mode'] = strtoupper($mode);
	$insert['mno'] = $member['idx'];
	$insert['content'] = $content;
	$insert['vote'] = $vote;
	$insert['ip'] = $_SERVER['REMOTE_ADDR'];
	$insert['reg_date'] = GetGMT();
	
	if ($mode == 'torrent') {
		$torrent = $mDB->DBfetch($mTracker->table['torrent'],array('groupno'),"where `idx`='$repto'");
		$insert['groupno'] = $torrent['groupno'];
		$insert['repto'] = $repto;
	} elseif ($mode == 'group') {
		$insert['groupno'] = $repto;
	}
	$mDB->DBinsert($mTracker->table['ment'],$insert);
	if ($vote != '-1') {
		if ($mode == 'torrent' || $mode == 'group') $mDB->DBupdate($mTracker->table['group'],'',array('vote_point'=>'`vote_point`+'.$vote,'vote_user'=>'`vote_user`+1'),"where `idx`='{$insert['groupno']}'");
	}
	
	Alertbox('성공적으로 등록하였습니다.\\nRegisted Complete.',3,$mTracker->moduleDir.'/MentList.php'.GetQueryString(array('mode'=>$mode,'repto'=>$repto,'p'=>'1'),'',false),'parent');
}
print_r($_REQUEST);
?>