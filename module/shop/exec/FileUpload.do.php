<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$action = Request('action');

$file = $_FILES['Filedata'];
$filetemp = $file['tmp_name'];
$filename = $file['name'];
$filesize = filesize($filetemp);

$mShop = new ModuleShop();

if ($action == 'item') {
	$filepath = '/userfile/shop/item/detail';
	$check = @getimagesize($filetemp);

	if (in_array($check[2],array(1,2,3)) == true) {
		if (CreateDirectory($_ENV['path'].$filepath) == true) {
			$filepath.= '/'.md5_file($filetemp).'.'.GetFileExec($filename);
			@move_uploaded_file($filetemp,$_ENV['path'].$filepath);

			$idx = $mDB->DBinsert($mShop->table['file'],array('filepath'=>$filepath,'filesize'=>$filesize));

			echo $idx.'|'.$filepath.'|'.$filesize;
		} else {
			echo 'FAIL';
		}
	} else {
		echo 'FAIL';
	}
}
?>