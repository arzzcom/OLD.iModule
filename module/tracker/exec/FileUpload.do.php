<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mTracker = new ModuleTracker();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = $mTracker->imagePath.'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);

if (CreateDirectory($_ENV['path'].$mTracker->imagePath) == true) {
	if ($temppath) {
		@move_uploaded_file($temppath,$_ENV['path'].$filepath);
		$idx = $mDB->DBinsert($mTracker->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'reg_date'=>GetGMT()));

		echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
		if ($filetype == 'IMG' && CreateDirectory($_ENV['path'].$mTracker->thumbnail) == true) {
			GetThumbnail($_ENV['path'].$filepath,$_ENV['path'].$mTracker->thumbnail.'/'.$idx.'.thm',150,120,false);
			echo '|'.$_ENV['dir'].$mTracker->thumbnail.'/'.$idx.'.thm';
		}
	}
} else {
	echo 'FALSE';
}
?>