<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset=UTF-8', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$bid = Request('bid');
$action = Request('action');
$mDB = &DB::instance();
$mBoard = new ModuleBoard($bid);

$returnXML = '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';

if ($action == 'autosave') {
	$mode = Request('mode');
	$tid = Request('tid');

	if ($mode == 'get') {
		$data = $mDB->DBfetch($mBoard->table['autosave'],array('tid','ip','data'),"where `tid`='$tid'");

		if (isset($data['tid']) == true && $data['ip'] == $_SERVER['REMOTE_ADDR']) {
			$data = unserialize(base64_decode($data['data']));

			foreach ($data as $field=>$value) {
				$returnXML.= '<item field="'.$field.'" value="'.GetString($value,'xml').'" />';
			}
		}
	} else {
		$data = $mDB->DBfetch($mBoard->table['autosave'],array('tid','ip','data'),"where `tid`='$tid'");
		if (isset($data['tid']) == true && $data['ip'] == $_SERVER['REMOTE_ADDR']) {
			$data = unserialize(base64_decode($data['data']));
			$file = split(',',$data['file']);

			for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
				$temp = explode('|',$file[$i]);
				$fidx = $temp[0];
				$fileData = $mDB->DBfetch($mBoard->table['file'],array('filepath','filetype','repto'),"where `idx`=$fidx");
				if ($fileData['repto'] == '0') {
					@unlink($_ENV['path'].$fileData['filepath']);
					if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mBoard->thumbneil.'/'.$fidx.'.thm');
					$mDB->DBdelete($mBoard->table['file'],"where `idx`=$fidx");
				}
			}

			$mDB->DBdelete($mBoard->table['autosave'],"where `tid`='$tid'");
		}
	}
}

$returnXML.= '</Ajax>';
echo $returnXML;
?>