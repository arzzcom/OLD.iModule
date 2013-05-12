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
$filepath = '/userfile/database/'.$tno.'/'.md5_file($temppath).'.'.time().'.'.rand(100000,999999);

if (CreateDirectory($_ENV['path'].'/userfile/database/'.$tno) == true) {
	@move_uploaded_file($temppath,$_ENV['path'].$filepath);
	$idx = $mDB->DBinsert($mDatabase->table['file'],array('type'=>$type,'tno'=>$tno,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg));

	echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
	if ($filetype == 'IMG' && CreateDirectory($_ENV['path'].'/userfile/database/thumbneil') == true) {
		GetThumbneil($_ENV['path'].$filepath,$_ENV['path'].'/userfile/database/thumbneil/'.$idx.'.thm',100,75,false);
		echo '|'.$_ENV['dir'].'/userfile/database/thumbneil/'.$idx.'.thm';
	}
} else {
	echo 'FALSE';
}
?>