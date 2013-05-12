<?php
REQUIRE_ONCE '../../../config/default.conf.php';
header('Content-Encoding: none');
header('Content-Type: text/plain;charset=utf-8');


$mDB = &DB::instance();
$mTracker = new ModuleTracker();

$mid = Request('mid');
$args = $_SERVER['argv'][0];

//$mDB->DBinsert('test',array('content'=>serialize($_SERVER)));

$pid = array_shift(explode('&',array_pop(explode('&peer_id=',$args))));
$info_hash = array_shift(explode('&',array_pop(explode('info_hash=',$args))));

$hash = Request('hash') ? Request('hash') : $mTracker->GetInfoHashToSHA1($info_hash);
$status = Request('event') == 'stopped' ? 'INACTIVE' : 'ACTIVE';

if ($mTracker->CheckUser($mid) != 'ACTIVE') {
	echo $mTracker->GetBEncode(array('failure reason'=>'Unregisted User. ('.$mTracker->CheckUser($mid).')'));
} elseif ($mDB->DBcount($mTracker->table['torrent'],"where `hash`='$hash'") == false) {
	echo $mTracker->GetBEncode(array('failure reason'=>'Unregisted Torrent. ('.$idx.'-'.$hash.')'));
} else {
	$mTracker->UpdatePeer($hash,$mid,$pid,$_SERVER['REMOTE_ADDR'],Request('port'),Request('uploaded'),Request('downloaded'),Request('left'),$status);
	$peers = $mTracker->GetAnnouncePeer($hash,(Request('compact') == '1' || Request('no_peer_id') == '1'));
	$mDB->DBinsert('test',array('content'=>$mTracker->GetBEncode(array('interval'=>intval(60*10),'min_request_interval'=>intval(60*5),'complete'=>intval($torrent['seeder']),'incomplete'=>intval($torrent['leecher']),'peers'=>$peers))));
	$torrent = $mDB->DBfetch($mTracker->table['torrent'],array('seeder','leecher'),"where `hash`='$hash'");
	exit($mTracker->GetBEncode(array('interval'=>intval(60*10),'min_request_interval'=>intval(60*5),'complete'=>intval($torrent['seeder']),'incomplete'=>intval($torrent['leecher']),'peers'=>$peers)));
}
?>