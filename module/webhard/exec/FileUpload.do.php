<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$action = Request('action');

$file = $_FILES['Filedata'];
$filetemp = $file['tmp_name'];
$filename = $file['name'];
$filesize = filesize($filetemp);

$mWebHard = new ModuleWebHard();

$dir = urldecode(Request('dir'));
$filepath = '/userfile/webhard';
$check = @getimagesize($filetemp);

if ($file) {
	if (CreateDirectory($_ENV['path'].$filepath) == true) {
		$filepath.= '/'.md5_file($filetemp).'.'.rand(10000,99999).'.'.GetFileExec($filename);
		@move_uploaded_file($filetemp,$_ENV['path'].$filepath);

		$idx = $mDB->DBinsert($mWebHard->table['file'],array('filename'=>$filename,'type'=>'FILE','dir'=>$dir,'filepath'=>$filepath,'filesize'=>$filesize,'reg_date'=>GetGMT(),'modify_date'=>GetGMT()));

		$mWebHard->DirFileSize($dir,$filesize);

		echo 'SUCCESS';
	} else {
		echo 'FAIL';
	}
}
?>