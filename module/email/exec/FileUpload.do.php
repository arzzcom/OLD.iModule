<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mEmail = new ModuleEmail();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);
$type = Request('type');
$wysiwyg = Request('wysiwyg');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = '/userfile/email/'.md5_file($temppath).'.'.time().'.'.rand(100000,999999);

if ($filetype == 'IMG' && CreateDirectory($_ENV['path'].'/userfile/email') == true) {
	@move_uploaded_file($temppath,$_ENV['path'].$filepath);
	$idx = $mDB->DBinsert($mEmail->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg));

	echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;

	if ($filetype == 'IMG' && CreateDirectory($_ENV['path'].'/userfile/email/thumbneil') == true) {
		GetThumbneil($_ENV['path'].$filepath,$_ENV['path'].'/userfile/email/thumbneil/'.$idx.'.thm',100,75,false);
		echo '|'.$_ENV['dir'].'/userfile/email/thumbneil/'.$idx.'.thm';
	}
} else {
	echo 'FALSE';
}
?>