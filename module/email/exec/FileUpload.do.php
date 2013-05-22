<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mEmail = new ModuleEmail();

$file = $_FILES['Filedata'];
$check = @getimagesize($file['tmp_name']);

$repto = Request('repto') ? Request('repto') : '0';
$type = Request('type');
$filename = $file['name'];
$temppath = $file['tmp_name'];
$filesize = filesize($temppath);
$filetype = GetFileType($filename,$temppath);
$filepath = '/attach/'.md5_file($temppath).'.'.time().'.'.rand(100000,999999);

if (CreateDirectory($_ENV['userfilePath'].$mEmail->userfile.'/attach') == true) {
	@move_uploaded_file($temppath,$_ENV['userfilePath'].$mEmail->userfile.$filepath);
	$idx = $mDB->DBinsert($mEmail->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'repto'=>$repto));

	echo $idx.'|'.$filetype.'|'.$filename.'|'.$filesize;
	
	if (CreateDirectory($_ENV['userfilePath'].$mEmail->thumbnail) == true) {
		GetThumbnail($_ENV['userfilePath'].$mEmail->userfile.$filepath,$_ENV['userfilePath'].$mEmail->thumbnail.'/'.$idx.'.thm',100,75,false);
		echo '|'.$_ENV['userfileDir'].$mEmail->thumbnail.'/'.$idx.'.thm';
	}
} else {
	echo 'FALSE';
}
?>