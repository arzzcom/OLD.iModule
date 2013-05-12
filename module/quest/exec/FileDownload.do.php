<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mBoard = new ModuleBoard();

$idx = Request('idx');
if ($idx != null) {
	$data = $mDB->DBfetch($mBoard->table['file'],array('repto','filename','filepath','filetype','filesize'),"where `idx`=$idx");
	$mDB->DBupdate($mBoard->table['file'],'',array('hit'=>'`hit`+1'),"where `idx`=$idx");

	if (file_exists($_ENV['path'].$data['filepath']) == true) {
		header("Cache-control: private");

		if(ereg('IE',$_ENV['browser']) == true || ereg('OP',$_ENV['browser']) == true) {
			Header("Content-type:application/octet-stream");
			Header("Content-Length:".$data['filesize']);
			Header("Content-Disposition:attachment;filename=".iconv('UTF-8','CP949//IGNORE',str_replace(' ','_',$data['filename'])));
			Header("Content-Transfer-Encoding:binary");
			header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
			Header("Pragma:no-cache");
			Header("Expires:0");
			Header("Connection:close");
		} else {
			Header("Content-type:".GetFileMime($data['filename']));
			Header("Content-Length:".$data['filesize']);
			Header("Content-Disposition:attachment; filename=".str_replace(' ','_',$data['filename']));
			Header("Content-Description:PHP3 Generated Data");
			header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
			Header("Pragma: no-cache");
			Header("Expires: 0");
			Header("Connection:close");
		}

		$fp = fopen($_ENV['path'].$data['filepath'],'rb');
		while(!feof($fp)) {
			echo fread($fp,1024*1024);
			sleep(1);
			flush();
		}
		fclose($fp);
	}
}
?>