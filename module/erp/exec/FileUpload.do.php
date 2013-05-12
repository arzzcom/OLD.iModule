<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mErp = new ModuleErp();

$action = Request('action');

$file = $_FILES['Filedata'];
$filetemp = $file['tmp_name'];
$filename = $file['name'];
$filesize = filesize($filetemp);

if ($action == 'workspace') {
	$wno = Request('wno');
	$filepath = '/userfile/erp/workspace/'.$wno;

	$check = @getimagesize($filetemp);

	if (in_array($check[2],array(1,2,3)) == true) {
		if (CreateDirectory($_ENV['path'].$filepath)) {
			$filepath.= '/'.md5_file($filetemp).'.'.GetFileExec($filename);
			@move_uploaded_file($filetemp,$_ENV['path'].$filepath);

			$idx = $mDB->DBinsert($mErp->table['workspace_image'],array('wno'=>$wno,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'date'=>GetTime('Y-m'),'reg_date'=>GetGMT()));

			$thumbpath = $_ENV['path'].'/userfile/erp/workspace/thumbnail';

			if (CreateDirectory($thumbpath) == true) GetThumbnail($_ENV['path'].$filepath,$thumbpath.'/'.$idx.'.thm',100,75);

			echo 'SUCCESS';
		} else {
			echo 'FAIL';
		}
	} else {
		echo 'FAIL';
	}
}
?>