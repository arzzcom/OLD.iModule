<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$action = Request('action');

if ($action == 'login') {
	$user_id = strlen(Request('user_id')) > 0 ? strtolower(Request('user_id')) : Alertbox('아이디를 입력하여 주십시오.');
	$password = strlen(Request('password')) > 0 ? md5(strtolower(Request('password'))) : Alertbox('패스워드를 입력하여 주십시오.');
	$autologin = Request('autologin') ? true : false;

	if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='$user_id' and `is_leave`='FALSE'") == 0) Alertbox('아이디를 찾을 수 없습니다.');
	if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='$user_id' and `password`='$password'") == 0) Alertbox('패스워드가 일치하지 않습니다.');

	$check = $mDB->DBfetch($_ENV['table']['member'],array('idx','is_active'),"where `user_id`='$user_id' and `password`='$password'");

	if ($check['is_active'] == 'FALSE') Alertbox('해당 회원계정은 현재 비활성화중입니다.\\n관리자의 승인이후 로그인할 수 있습니다.');
	$mMember->Login($check['idx'],$autologin);

	$target = Request('RefreshTarget') ? Request('RefreshTarget') : 'parent';
	REQUIRE_ONCE '../inc/header.inc.php';
	Redirect('reload',$target);
	REQUIRE_ONCE '../inc/footer.inc.php';
}

if ($action == 'logout') {
	if ($mMember->IsLogged() == true) {
		$member = $mMember->GetMemberInfo();
		$mMember->Logout();
		Alertbox($member['name'].'님 성공적으로 로그아웃 되셨습니다.','4');

		$target = Request('RefreshTarget') ? Request('RefreshTarget') : 'parent';
		Redirect('reload',$target);
	} else {
		Alertbox('로그인상태가 아닙니다.');
	}
}

if ($action == 'signin') {
	$mMember = new ModuleMember();
	$mMember->MemberSignIn();
}

if ($action == 'myinfo') {
	$mMember = new ModuleMember();
	$mMember->MemberMyInfo();
}

if ($action == 'leave') {
	$mMember = new ModuleMember();
	$mMember->MemberLeave();
}

if ($action == 'pointgift') {
	if ($mMember->IsLogged() == false) Alertbox('먼저 로그인을 하여주십시오.','2');

	$member = $mMember->GetMemberInfo();
	if (GetPermission($mMember->module['permission_pointgift']) == false) Alertbox('포인트를 선물할 권한이 없습니다.');
	$user_id = Request('user_id') ? Request('user_id') : Alertbox('선물받으실 분 아이디를 입력하여 주십시오.');
	$point = preg_match('/^[1-9]{1}[0-9]+$/',Request('point')) == true ? Request('point') : Alertbox('선물할 포인트를 숫자로만 입력하여 주십시오.');
	$message = Request('message') ? Request('message') : '포인트를 선물해드립니다.';
	$is_secret = Request('is_secret') == 'true';

	if ($member['point'] < $point) Alertbox('포인트가 부족합니다.');

	$giftPoint = floor($point/100*90);

	$check = $mDB->DBfetch($_ENV['table']['member'],array('idx'),"where `user_id`='$user_id'");
	if (isset($check['idx']) == false) Alertbox('회원을 찾을 수 없습니다.');

	$mMember->SendPoint($member['idx'],$point*-1,$user_id.'님께 포인트를 선물');
	$mMember->SendPoint($check['idx'],$giftPoint,($is_secret == true ? '익명의 유저' : $member['nickname'].'님').'에게 포인트를 선물받음');

	if ($is_secret == true) {
		$message = array('module'=>'member','mno'=>'0','type'=>'pointgift','name'=>'익명의 유저','message'=>nl2br($message));
	} else {
		$message = array('module'=>'member','mno'=>$member['idx'],'type'=>'pointgift','name'=>$member['nickname'],'message'=>nl2br($message));
	}
	$mMember->SendMessage($check['idx'],$message);

	Alertbox('포인트를 성공적으로 선물하였습니다.','2','','parent');
}
?>