<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mBoard = new ModuleBoard();

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
	
	if (CreateDirectory($_ENV['userfilePath'].$mBoard->userfile.'/attach/'.date('Ym')) == true) {
		if ($temppath) {
			@move_uploaded_file($temppath,$_ENV['userfilePath'].$mBoard->userfile.$filepath);
			$idx = $mDB->DBinsert($mBoard->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>GetGMT()));
	
			echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
			if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$mBoard->thumbnail) == true) {
				GetThumbnail($_ENV['userfilePath'].$mBoard->userfile.$filepath,$_ENV['userfilePath'].$mBoard->thumbnail.'/'.$idx.'.thm',150,120,false);
				echo '|'.$_ENV['userfileDir'].$mBoard->thumbnail.'/'.$idx.'.thm';
			}
		}
	} else {
		echo 'FALSE';
	}
} else {
	echo 'FALSE';
}
?>