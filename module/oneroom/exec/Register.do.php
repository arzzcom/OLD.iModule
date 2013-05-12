<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$bid = Request('bid');
$action = Request('action');
$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mOneroom = new ModuleOneroom();

if ($action == 'register') {
	if ($mMember->IsLogged() == false) Alertbox('먼저 로그인을 하여 주십시오.');
	$type = Request('type');
	if (Request('agree') == null) Alertbox('약관에 동의하여 주십시오.');
	if ($mDB->DBcount($mOneroom->table['agent'],"where `mno`={$member['idx']}") > 0) Alertbox('이미 회원님께서는 중개업소를 등록하셨습니다.');
	if ($mDB->DBcount($mOneroom->table['dealer'],"where `mno`={$member['idx']}") > 0) Alertbox('이미 회원님께서는 중개담당자로 등록하셨습니다.');
		
	if ($type == 'agent') {
		$insert = array();
		$insert['mno'] = $member['idx'];
		$insert['title'] = Request('title') ? Request('title') : Alertbox('중개업소이름을 입력하여 주십시오.');
		$insert['register_number'] = $mOneroom->CheckRegisterNumber(Request('register_number1').'-'.Request('register_number2').'-'.Request('register_number3')) == true ? Request('register_number1').'-'.Request('register_number2').'-'.Request('register_number3') : Alertbox('사업자 등록번호가 올바르지 않습니다.');
		$insert['homepage'] = Request('homepage') != null && preg_match('/^http:\/\//i',Request('homepage')) == false ? 'http://'.Request('homepage') : Request('homepage');
		
		if ($mDB->DBcount($mOneroom->table['agent'],"where `register_number`={$insert['register_number']}") > 0) Alertbox('이미 등록된 사업자등록번호입니다.');

		$idx = $mDB->DBinsert($mOneroom->table['agent'],$insert);
		$mDB->DBinsert($mOneroom->table['dealer'],array('mno'=>$member['idx'],'agent'=>$idx,'status'=>'ACTIVE'));
		
		Alertbox('중개업소등록이 완료되었습니다.',3,str_replace('type=agent','type=complete',$_SERVER['HTTP_REFERER']),'parent');
	} else {
		$register_number = $mOneroom->CheckRegisterNumber(Request('register_number1').'-'.Request('register_number2').'-'.Request('register_number3')) == true ? Request('register_number1').'-'.Request('register_number2').'-'.Request('register_number3') : Alertbox('사업자 등록번호가 올바르지 않습니다.');
		$check = $mDB->DBfetch($mOneroom->table['agent'],array('idx','title'),"where `register_number`='$register_number'");
		
		if (isset($check['idx']) == false) Alertbox('해당 사업자등록번호를 찾을 수 없습니다.');
		
		$insert = array();
		$insert['mno'] = $member['idx'];
		$insert['agent'] = $check['idx'];
		$insert['status'] = 'WAIT';
		
		$mDB->DBinsert($mOneroom->table['dealer'],$insert);
		
		Alertbox('['.$check['title'].']의 중개담당자로 등록되었습니다.\\n중개업소 관리자의 승인이후 매물을 등록할 수 있습니다.',3,str_replace('type=agent','type=complete',$_SERVER['HTTP_REFERER']),'parent');
	}
}