<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mTracker = new ModuleTracker();
$member = &Member::instance()->GetMemberInfo();

if ($mTracker->GetTorrentPermission('download',$idx) == false) Alertbox('권한이 없습니다.');

$idx = Request('idx');
$mid = $mTracker->GetMID();
$post = $mDB->DBfetch($mTracker->table['torrent'],array('idx','groupno','episodeno','edition','resolution','source','release'),"where `idx`='$idx'");
$group = $mDB->DBfetch($mTracker->table['group'],array('title'),"where `idx`='{$post['groupno']}'");
$episode = $mDB->DBfetch($mTracker->table['episode'],array('episode'),"where `idx`='{$post['episodeno']}'");

$filename = $group['title'];
$filename.= $group['eng_title'] ? '('.$group['eng_title'].')' : '';
$filename.= $episode['episode'] ? '.'.$episode['episode'] : '';
$filename.= $post['edition'] ? '.'.$post['edition'] : '';
$filename.= $post['resolution'] ? '.'.$post['resolution'] : '';
$filename.= $post['source'] ? '.'.$post['source'] : '';
$filename.= $post['release'] ? '.'.$post['release'] : '';
$filename = str_replace(array(' ','/'),'.',$filename.'.torrent');


if (file_exists($_ENV['path'].$mTracker->torrentPath.'/'.$idx.'.torrent') == true) {
	$file = $mTracker->GetTorrentFile($_ENV['path'].$mTracker->torrentPath.'/'.$idx.'.torrent');
	$file->setTrackers(array('http://'.$_SERVER['HTTP_HOST'].$mTracker->moduleDir.'/exec/Announce.php?mid='.$mid));
	$file->setPrivate(1);
	
	$torrent = $file->bencode();
	header('Content-Encoding: none'); 
	header("Cache-control: private");
	header("Content-type:application/octet-stream");
	header("Content-Length:".strlen($torrent));

	if(ereg('IE',$_ENV['browser']) == true || ereg('OP',$_ENV['browser']) == true) {
		header("Content-Disposition:attachment;filename=".iconv('UTF-8','CP949//IGNORE',$filename));
		header("Content-Transfer-Encoding:binary");
		header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
		header("Pragma:no-cache");
		header("Expires:0");
		header("Connection:close");
	} else {
		header("Content-Disposition:attachment; filename=".$filename);
		header("Content-Description:PHP3 Generated Data");
		header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
		header("Pragma: no-cache");
		header("Expires: 0");
		header("Connection:close");
	}

	echo $torrent;
	
	$mDB->DBupdate($mTracker->table['episode'],'',array('snatch'=>'`snatch`+1'),"where `idx`='{$post['episodeno']}'");
	$mDB->DBupdate($mTracker->table['torrent'],'',array('snatch'=>'`snatch`+1'),"where `idx`='$idx'");
	$check = $mDB->DBfetch($mTracker->table['snatch'],array('idx'),"where `torrentno`='$idx' and `mno`='{$member['idx']}'");
	if (isset($check['idx']) == true) {
		$mDB->DBupdate($mTracker->table['snatch'],array('reg_date'=>GetGMT()),'',"where `idx`='{$check['idx']}'");
	} else {
		$mDB->DBinsert($mTracker->table['snatch'],array('torrentno'=>$torrent['idx'],'mno'=>$member['mno'],'upload'=>0,'download'=>0,'reg_date'=>GetGMT()));
	}
} else {
	Alertbox('잘못된 접근입니다.');
}
?>
