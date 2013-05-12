<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset="UTF-8"', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$mDB = &DB::instance();
$mKin = new ModuleKin();

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$repto = Request('repto');
$autosave = Request('autosave');

echo '<?xml version="1.0" encoding="UTF-8"?><list>';
if ($repto != null && $autosave == null) {
	$data = $mDB->DBfetchs($mKin->table['file'],'*',"where `type`='$type' and `repto`=$repto and `wysiwyg`='$wysiwyg'",'idx,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		echo '<file>';
		echo '<name>'.GetString($data[$i]['filename'],'xml').'</name>';
		echo '<size>'.$data[$i]['filesize'].'</size>';
		echo '<server>'.GetString($data[$i]['idx'].'|'.$data[$i]['filetype'].'|'.$data[$i]['filename'].'|'.$data[$i]['filesize'].($data[$i]['filetype'] == 'IMG' ? '|'.$_ENV['dir'].$mKin->thumbneil.'/'.$data[$i]['idx'].'.thm' : ''),'xml').'</server>';
		echo '</file>';
	}
} elseif ($autosave != null) {
	$data = $mDB->DBfetch($mKin->table['autosave'],array('data','ip'),"where `tid`='$autosave'");
	if (isset($data['data']) == true && $data['ip'] == $_SERVER['REMOTE_ADDR']) {
		$data = unserialize(base64_decode($data['data']));
		$file = split(',',$data['file']);

		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$temp = explode('|',$file[$i]);
			$fidx = $temp[0];
			$fileData = $mDB->DBfetch($mKin->table['file'],'*',"where `idx`=$fidx");
			echo '<file>';
			echo '<name>'.GetString($fileData['filename'],'xml').'</name>';
			echo '<size>'.$fileData['filesize'].'</size>';
			echo '<server>'.GetString($file[$i],'xml').'</server>';
			echo '</file>';
		}
	}
}

echo '</list>';
?>