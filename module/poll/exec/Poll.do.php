<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mPoll = new ModulePoll();
$member = $mMember->GetMemberInfo();

$action = Request('action');

if ($action == 'vote') {
	$repto = Request('repto');
	$redirect = Request('redirect');
	$ip = $_SERVER['REMOTE_ADDR'];
	$mno = $member['idx'];
	$vote = Request('vote');
	
	$data = $mDB->DBfetch($mPoll->table['poll'],array('end_date','point'),"where `idx`='$repto'");
	
	if ($data['end_date'] < GetGMT()) {
		Alertbox('투표기간이 종료되었습니다.');
	}
	
	if ($mPoll->GetPermission($repto,'vote') == true) {
		if ($mPoll->GetVoted($repto) == true) {
			Alertbox('이미 투표하셨습니다.');
		}
		
		if ((is_array($vote) == true && sizeof($vote) == 0) || $vote == null) {
			Alertbox('투표항목을 선택하여 주십시오.');
		}
		
		$vote = is_array($vote) == true ? implode(',',$vote) : $vote;
		
		$mDB->DBinsert($mPoll->table['voter'],array('repto'=>$repto,'mno'=>$mno,'ip'=>$ip,'vote'=>$vote,'reg_date'=>GetGMT()));
		$mDB->DBupdate($mPoll->table['poll'],'',array('voter'=>'`voter`+1'),"where `idx`='$repto'");
		$mDB->DBupdate($mPoll->table['item'],'',array('voter'=>'`voter`+1'),"where `idx` IN ($idx) and `repto`='$repto'");
		
		$msg = '투표를 하였습니다.';
		if ($data['point'] > 0) {
			$msg.= '\\n투표감사선물로 '.number_format($data['point']).'포인트가 적립되었습니다.';
		}
		Alertbox($msg);
	} else {
		Alertbox('투표할 권한이 없습니다.');
	}
}
?>