<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mEmail = new ModuleEmail();

$idx = Request('idx');
if ($idx != null) {
	$data = $mDB->DBfetch($mEmail->table['file'],array('repto','filename','filepath','filetype','filesize'),"where `idx`='$idx'");
	$mDB->DBupdate($mEmail->table['file'],'',array('hit'=>'`hit`+1'),"where `idx`=$idx");

	if (file_exists($_ENV['userfilePath'].$mEmail->userfile.$data['filepath']) == true) {
		header("Cache-control: private");

		if(preg_match('/IE/',$_ENV['browser']) == true || preg_match('/OP/',$_ENV['browser']) == true) {
			header("Content-type:application/octet-stream");
			header("Content-Length:".$data['filesize']);
			header("Content-Disposition:attachment;filename=".iconv('UTF-8','CP949//IGNORE',str_replace(' ','_',$data['filename'])));
			header("Content-Transfer-Encoding:binary");
			header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
			header("Pragma:no-cache");
			header("Expires:0");
			header("Connection:close");
		} else {
			header("Content-type:".GetFileMime($data['filename']));
			header("Content-Length:".$data['filesize']);
			header("Content-Disposition:attachment; filename=".str_replace(' ','_',$data['filename']));
			header("Content-Description:PHP3 Generated Data");
			header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/inc/blank.php");
			header("Pragma: no-cache");
			header("Expires: 0");
			header("Connection:close");
		}

		$fp = fopen($_ENV['userfilePath'].$mEmail->userfile.$data['filepath'],'r');
		while(!feof($fp)) {
			echo fread($fp,1024*1024);
			flush();
		}
		fclose($fp);
	}
}
?>