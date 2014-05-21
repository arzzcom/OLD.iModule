<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mRelease = new ModuleRelease();

$idx = Request('idx');
$version = Request('version');
if ($idx != null) {
	$post = $mDB->DBfetch($mRelease->table['post'],'*',"where `idx`='$idx'");
	$mRelease = new ModuleRelease($post['rid']);
	
	if ($version == 'lastest') {
		$version = $mDB->DBfetch($mRelease->table['version'],'*',"where `repto`='$idx'",'idx,desc','0,1');
	} else {
		$version = $mDB->DBfetch($mRelease->table['version'],'*',"where `idx`='$version'",'idx,desc','0,1');
	}
	
	if (isset($version['idx']) == false) Alertbox('버전을 찾을 수 없습니다.');
	
	if ($post['price'] > 0) {
		if ($mMember->IsLogged() == false) {
			Alertbox('해당 프로그램은 유료프로그램으로 먼저 로그인을 하셔야 합니다.');
		}
		
		if ($mRelease->CheckPayment($idx) == false) {
			if ($member['point'] < $post['price']) {
				Alertbox('해당 프로그램을 구매하기 위한 포인트가 부족합니다.');
			} else {
				$mMember->SendPoint($member['idx'],$post['price']*-1,'프로그램 구입 ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$idx,'release');
				$mMember->SendPoint($member['idx'],round($post['price']/100*(100-$mRelease->setup['tax_point'])),'프로그램 판매 (세금 '.$mRelease->setup['tax_point'].'%제외) ('.GetCutString($post['title'],20).')','/module/release/release.php?rid='.$post['rid'].'&mode=view&idx='.$idx,'release');
			}
		}
	}
	
	if ($mMember->IsLogged() == true && $mRelease->CheckPayment($idx) == false) {
		$mDB->DBinsert($mRelease->table['payment'],array('rid'=>$post['rid'],'repto'=>$post['idx'],'mno'=>$member['idx'],'price'=>$post['price'],'reg_date'=>GetGMT()));
	}
	
	$mDB->DBupdate($mRelease->table['post'],'',array('download'=>'`download`+1'),"where `idx`='$idx'");
	$mDB->DBupdate($mRelease->table['version'],'',array('download'=>'`download`+1'),"where `idx`='{$version['idx']}'");
	
	GetFileDownload($_ENV['userfilePath'].$mRelease->file.$version['file'],$version['filename'],$version['filesize']);
}
?>