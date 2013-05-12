<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$action = Request('action');
$data = $GLOBALS['HTTP_RAW_POST_DATA'];

if ($action == 'new') {
	$filepath = '/temp/TakePhoto.'.time().'.'.rand(10000,99999).'.jpg';
	$file = @fopen($_ENV['path'].$filepath,'w') or die("FAIL");
	@fwrite($file,$data);
	@fclose($file);

	echo $_ENV['path'].$filepath.'|'.$_ENV['dir'].$filepath;
}
?>