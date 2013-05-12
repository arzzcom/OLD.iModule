<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mKin = new ModuleKin();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = $mKin->userfile.'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);

if (CreateDirectory($_ENV['path'].$mKin->userfile) == true) {
	if ($temppath) {
		@move_uploaded_file($temppath,$_ENV['path'].$filepath);
		$idx = $mDB->DBinsert($mKin->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>GetGMT()));

		echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
		if ($filetype == 'IMG' && CreateDirectory($_ENV['path'].$mKin->thumbnail) == true) {
			GetThumbnail($_ENV['path'].$filepath,$_ENV['path'].$mKin->thumbnail.'/'.$idx.'.thm',150,120,false);
			echo '|'.$_ENV['dir'].$mKin->thumbnail.'/'.$idx.'.thm';
		}
	}
} else {
	echo 'FALSE';
}
?>