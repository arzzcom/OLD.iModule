<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mEmail = new ModuleEmail();
$idx = Request('idx');

$mDB->DBupdate($mEmail->table['send'],array('read_time'=>GetGMT()),'',"where `idx`='$idx'");

$check = getimagesize($_ENV['path'].'/module/email/images/t.gif');
Header("Content-type: $check[mime]");

readfile($_ENV['path'].'/module/email/images/t.gif');
?>