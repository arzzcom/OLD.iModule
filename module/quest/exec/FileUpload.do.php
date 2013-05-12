<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mBoard = new ModuleBoard();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = $mBoard->userfile.'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);

if (CreateDirectory($_ENV['path'].$mBoard->userfile) == true) {
	if ($temppath) {
		@move_uploaded_file($temppath,$_ENV['path'].$filepath);
		$idx = $mDB->DBinsert($mBoard->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>GetGMT()));

		echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
		if ($filetype == 'IMG' && CreateDirectory($_ENV['path'].$mBoard->thumbneil) == true) {
			GetThumbneil($_ENV['path'].$filepath,$_ENV['path'].$mBoard->thumbneil.'/'.$idx.'.thm',150,120,false);
			echo '|'.$_ENV['dir'].$mBoard->thumbneil.'/'.$idx.'.thm';
		}
	}
} else {
	echo 'FALSE';
}
?>