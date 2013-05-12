<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$action = Request('action');
$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mCoupon = new ModuleCoupon();

// 구매
if ($action == 'buy') {
	if ($mMember->IsLogged() == false) Alertbox('먼저 로그인을 하여주십시오.');

	$code = Request('code');
	$data = $mDB->DBfetch($mCoupon->table['item'],'*',"where `code`='$code'");

	if (isset($data['idx']) == false) Alertbox('해당 쿠폰을 찾을 수 없습니다.');

	if ($data['ea'] > 0) {
		if ($mMember->SendPoint($member['idx'],$data['point']*-1,$data['title'].' 구매') == true) {
			$mDB->DBupdate($mCoupon->table['item'],'',array('ea'=>'`ea`-1'),"where `code`='$code'");
			$mDB->DBinsert($mCoupon->table['user'],array('code'=>$data['code'],'mno'=>$member['idx'],'buy_date'=>GetGMT(),'expire_date'=>($data['expire'] > 0 ? GetGMT()+60*60*24*$data['expire'] : 0)));
			Alertbox('쿠폰을 성공적으로 구매하였습니다.',3,'reload','parent');
		} else {
			Alertbox('포인트가 부족합니다.');
		}
	} else {
		Alertbox('매진된 쿠폰입니다.');
	}
}
?>