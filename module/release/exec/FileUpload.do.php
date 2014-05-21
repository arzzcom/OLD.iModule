<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mRelease = new ModuleRelease();

if (isset($_FILES['Filedata']) == true) {
	$file = $_FILES['Filedata'];
	$check = @getimagesize($file['tmp_name']);
	
	$type = Request('type');
	$wysiwyg = Request('wysiwyg');
	$filename = $file['name'];
	$temppath = $file['tmp_name'];
	$filesize = filesize($temppath);
	$filetype = GetFileType($filename,$temppath);
	$filepath = '/attach/'.date('Ym').'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);
	
	if (CreateDirectory($_ENV['userfilePath'].$mRelease->userfile.'/attach/'.date('Ym')) == true) {
		if ($temppath) {
			@move_uploaded_file($temppath,$_ENV['userfilePath'].$mRelease->userfile.$filepath);
			$idx = $mDB->DBinsert($mRelease->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>GetGMT()));
	
			echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
			if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$mRelease->thumbnail) == true) {
				GetThumbnail($_ENV['userfilePath'].$mRelease->userfile.$filepath,$_ENV['userfilePath'].$mRelease->thumbnail.'/'.$idx.'.thm',150,120,false);
				echo '|'.$_ENV['userfileDir'].$mRelease->thumbnail.'/'.$idx.'.thm';
			}
		}
	} else {
		echo 'FALSE';
	}
} else {
	echo 'FALSE';
}
?>