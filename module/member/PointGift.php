<?php
REQUIRE_ONCE '../../config/default.conf.php';

$mMember = new ModuleMember();

if ($member['idx'] == '0') Alertbox('회원만 포인트를 선물할 수 있습니다.',2);
GetDefaultHeader('포인트 선물하기','');

$mMember->PrintPointGift();
?>