<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mDatabase = new ModuleDatabase();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);

$tno = Request('tno');
$type = Request('type');
$wysiwyg = Request('wysiwyg');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = '/attach/'.$tno.'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);

if (CreateDirectory($_ENV['userfilePath'].$mDatabase->userfile.'/attach/'.$tno) == true) {
	@move_uploaded_file($temppath,$_ENV['userfilePath'].$mDatabase->userfile.$filepath);
	@chmod(707,$_ENV['userfilePath'].$mDatabase->userfile.$filepath);
	$idx = $mDB->DBinsert($mDatabase->table['file'],array('type'=>$type,'tno'=>$tno,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg));

	echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
	if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$mDatabase->thumbnail) == true) {
		GetThumbnail($_ENV['userfilePath'].$mDatabase->userfile.$filepath,$_ENV['userfilePath'].$mDatabase->thumbnail.'/'.$idx.'.thm',100,75,false);
		echo '|'.$_ENV['userfileDir'].$mDatabase->thumbnail.'/'.$idx.'.thm';
	}
} else {
	echo 'FALSE';
}
?>