<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mOneroom = new ModuleOneroom();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);

$repto = Request('repto') ? Request('repto') : '0';
$type = Request('type');
$wysiwyg = Request('wysiwyg');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = '/'.$type.'/'.md5_file($temppath).'.'.time().'.'.rand(100000,999999);

if (CreateDirectory($_ENV['userfilePath'].$mOneroom->userfile.'/'.$type) == true) {
	@move_uploaded_file($temppath,$_ENV['userfilePath'].$mOneroom->userfile.$filepath);
	$idx = $mDB->DBinsert($mOneroom->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'repto'=>$repto));

	echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
	
	if (CreateDirectory($_ENV['userfilePath'].$mOneroom->thumbnail) == true) {
		if ($type == 'attach') GetThumbnail($_ENV['userfilePath'].$mOneroom->userfile.$filepath,$_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$idx.'.thm',200,150,false);
		else GetThumbnail($_ENV['userfilePath'].$mOneroom->userfile.$filepath,$_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$idx.'.thm',100,75,false);
		echo '|'.$_ENV['userfileDir'].$mOneroom->thumbnail.'/'.$idx.'.thm';
	}
} else {
	echo 'FALSE';
}
?>