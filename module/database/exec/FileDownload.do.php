<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$action = Request('action');

$mDatabase = new ModuleDatabase();
$tno = Request('tno');
$idx = Request('idx') ? Request('idx') : 0;
$field = Request('field');

$file = $mDB->DBfetch($mDatabase->table['file'],array('idx','filename','filepath','filesize'),"where `idx`=$idx");

if (isset($file['idx']) == true) {
	$mDB->DBupdate($mDatabase->table['file'],'',array('hit'=>'`hit`+1'),"where `idx`=$idx");
	GetFileDownload($_ENV['userfilePath'].$mDatabase->userfile.$file['filepath'],$file['filename'],$file['filesize']);
}
?>