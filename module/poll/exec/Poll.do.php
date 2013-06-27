<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$pid = Request('pid');
if ($pid) {
	$mPoll = new ModulePoll($pid);
} else {
	$mPoll = new ModulePoll();
}
$member = $mMember->GetMemberInfo();

$action = Request('action');

if ($action == 'vote') {
	$repto = Request('repto');
	$redirect = Request('redirect');
	$ip = $_SERVER['REMOTE_ADDR'];
	$mno = $member['idx'];
	$vote = Request('vote');
	
	$data = $mDB->DBfetch($mPoll->table['post'],array('pid','end_date'),"where `idx`='$repto'");
	$mPoll = new ModulePoll($data['pid']);
	$poll = $mDB->DBfetch($mPoll->table['setup'],array('vote_point'),"where `pid`='{$data['pid']}'");
	
	if ($data['end_date'] < GetGMT()) {
		Alertbox('투표기간이 종료되었습니다.');
	}

	if ($mPoll->GetPermission('vote') == true) {
		if ($mPoll->GetVoted($repto) == true) {
			Alertbox('이미 투표하셨습니다.');
		}
		
		if ((is_array($vote) == true && sizeof($vote) == 0) || $vote == null) {
			Alertbox('투표항목을 선택하여 주십시오.');
		}
		
		$vote = is_array($vote) == true ? implode(',',$vote) : $vote;
		
		$mDB->DBinsert($mPoll->table['voter'],array('repto'=>$repto,'mno'=>$mno,'ip'=>$ip,'vote'=>$vote,'reg_date'=>GetGMT()));
		$mDB->DBupdate($mPoll->table['post'],'',array('voter'=>'`voter`+1'),"where `idx`='$repto'");
		$mDB->DBupdate($mPoll->table['item'],'',array('voter'=>'`voter`+1'),"where `idx` IN ($vote) and `repto`='$repto'");
		
		$msg = '투표를 하였습니다.';
		if ($poll['vote_point'] > 0) {
			$msg.= '\\n투표감사선물로 '.number_format($poll['vote_point']).'포인트가 적립되었습니다.';
		}
		Alertbox($msg,3,$redirect,'parent');
	} else {
		Alertbox('투표할 권한이 없습니다.');
	}
}

if ($action == 'ment') {
	$insert = array();
	$pid = Request('pid');
	$repto = Request('repto');
	
	$poll = $mDB->DBfetch($mPoll->table['post'],array('pid'),"where `idx`='$repto'");
	
	if (isset($poll['pid']) == false || $poll['pid'] != $pid) Alertbox('잘못된 접근입니다.');
	
	if ($mPoll->GetPermission('ment') == false) Alertbox('댓글을 작성할 권한이 없습니다.');
	
	$insert['repto'] = Request('repto');
	$insert['content'] = Request('content') ? Request('content') : Alertbox('댓글내용을 입력하여 주십시오.');
	if ($mMember->IsLogged() == false) {
		$insert['name'] = Request('name') ? Request('name') : Alertbox('이름을 입력하여 주십시오.');
		$insert['password'] = Request('password') ? md5(Request('password')) : Alertbox('패스워드를 입력하여 주십시오.');
		$insert['mno'] = 0;
	} else {
		$insert['mno'] = $member['idx'];
	}
	$insert['ip'] = $_SERVER['REMOTE_ADDR'];
	$insert['reg_date'] = GetGMT();
	
	$mDB->DBinsert($mPoll->table['ment'],$insert);
	$mDB->DBupdate($mPoll->table['post'],array('last_ment'=>$insert['reg_date']),array('ment'=>'`ment`+1'),"where `idx`='$repto'");
	
	if ($mMember->IsLogged() == true && $mPoll->setup['ment_point'] > 0) {
		$mMember->SendPoint($member['idx'],$mPoll->setup['ment_point'],'설문댓글 작성 ('.GetCutString(GetRemoveEnterTab($insert['content'],' '),20).')','/module/poll/poll.php?pid='.$pid.'&mode=view&idx='.$repto,'poll');
		$mMember->SendExp($member['idx'],5);
	}
	
	Alertbox('성공적으로 등록하였습니다.',3,'reload','parent');
}

if ($action == 'delete') {
	$idx = Request('idx');
	$mode = Request('mode');
	
	if ($mode == 'ment') {
		$data = $mDB->DBfetch($mPoll->table['ment'],array('mno','password','repto'),"where `idx`='$idx'");
		$post = $mDB->DBfetch($mPoll->table['post'],array('pid'),"where `idx`='{$data['repto']}'");
		$mPoll = new ModulePoll($post['pid']);

		if ($mPoll->GetPermission('delete') == false) {
			if ($data['mno'] == '0') {
				if (md5(Request('password')) != $data['password']) Alertbox('패스워드가 일치하지 않습니다.');
			} elseif ($data['mno'] != $member['idx']) {
				Alertbox('댓글을 삭제할 권한이 없습니다.');
			}
		}

		$mDB->DBdelete($mPoll->table['ment'],"where `idx`='$idx'");

		$last_ment = $mDB->DBfetch($mPoll->table['ment'],array('reg_date'),"where `repto`='{$data['repto']}'",'reg_date,desc','0,1');
		$last_ment = isset($last_ment['reg_date']) ==  true ? $last_ment['reg_date'] : 0;

		$mDB->DBupdate($mPoll->table['post'],array('last_ment'=>$last_ment),array('ment'=>'`ment`-1'),"where `idx`='{$data['repto']}'");

		$path = explode('?',$_SERVER['HTTP_REFERER']);
		$url = $path[0];
		$query = $mPoll->GetQueryString(array('mode'=>'view','idx'=>$data['repto']),$path[1],false);
		$returnURL = $url.$query;

		if ($data['mno'] != 0) $mMember->SendPoint($data['mno'],$mPoll->setup['ment_point']*-1,'댓글 삭제 ('.GetCutString($data['search'],20).')','/module/poll/poll.php?pid='.$post['pid'].'&mode=view&idx='.$data['repto'],'poll',true);
		Alertbox('성공적으로 삭제하였습니다.',3,$returnURL,'parent');
	}
}
?>