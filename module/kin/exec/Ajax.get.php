<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset=UTF-8', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$action = Request('action');
$mDB = &DB::instance();
$mKin = new ModuleKin();

$returnXML = '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';

if ($action == 'autosave') {
	$mode = Request('mode');
	$tid = Request('tid');

	if ($mode == 'get') {
		$data = $mDB->DBfetch($mKin->table['autosave'],array('tid','ip','data'),"where `tid`='$tid'");

		if (isset($data['tid']) == true && $data['ip'] == $_SERVER['REMOTE_ADDR']) {
			$data = GetUnserialize($data['data']);

			foreach ($data as $field=>$value) {
				$returnXML.= '<item field="'.$field.'" value="'.GetString($value,'xml').'" />';
			}
		}
	} else {
		$data = $mDB->DBfetch($mKin->table['autosave'],array('tid','ip','data'),"where `tid`='$tid'");
		if (isset($data['tid']) == true && $data['ip'] == $_SERVER['REMOTE_ADDR']) {
			$data = unserialize(base64_decode($data['data']));
			$file = split(',',$data['file']);

			for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
				$temp = explode('|',$file[$i]);
				$fidx = $temp[0];
				$fileData = $mDB->DBfetch($mKin->table['file'],array('filepath','filetype','repto'),"where `idx`=$fidx");
				if ($fileData['repto'] == '0') {
					@unlink($_ENV['path'].$fileData['filepath']);
					if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].$mKin->thumbneil.'/'.$fidx.'.thm');
					$mDB->DBdelete($mKin->table['file'],"where `idx`=$fidx");
				}
			}

			$mDB->DBdelete($mKin->table['autosave'],"where `tid`='$tid'");
		}
	}
}

if ($action == 'category') {
	$idx = Request('idx');
	$data = $mDB->DBfetchs($mKin->table['category'],array('idx','category'),"where `parent`='$idx'");
	
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$returnXML.= '<item idx="'.$data[$i]['idx'].'" category="'.GetString($data[$i]['category'],'xml').'" />';
	}
}

$returnXML.= '</Ajax>';
echo $returnXML;
?>