<?php
REQUIRE_ONCE '../../config/default.conf.php';

$mMember = new ModuleMember();
$member = $mMember->GetMemberInfo();
$mno = Request('mno');
$tomember = $mMember->GetMemberInfo($mno);

if ($member['idx'] == '0') Alertbox('회원만 메세지를 보낼 수 있습니다.',2);
GetDefaultHeader($tomember['nickname'].'('.$tomember['user_id'].')님과의 메세지','');

$mMember->PrintMessageView();
?>