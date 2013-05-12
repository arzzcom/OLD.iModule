<?php
$mDB = &DB::instance();
$mTracker = new ModuleTracker();

if ($myinfo['idx'] > 0) {
	if ($mDB->DBcount($mTracker->table['user'],"where `mno`='{$myinfo['idx']}'") == 0) {
		$code = Request('code');
		$check = $mDB->DBfetch($mTracker->table['invitecode'],array('frommno','status'),"where `code`='$code'");
		
		if ($check['status'] == 'WAIT') {
			$mDB->DBupdate($mTracker->table['invitecode'],array('status'=>'USED','tomno'=>$myinfo['idx'],'use_date'=>GetGMT()),'',"where `code`='$code'");
			$mDB->DBinsert($mTracker->table['user'],array('mno'=>$myinfo['idx'],'mid'=>sha1($myinfo['user_id'].GetGMT()),'status'=>'ACTIVE','reg_ip'=>$_SERVER['REMOTE_ADDR']));
		}
	}
}
?>