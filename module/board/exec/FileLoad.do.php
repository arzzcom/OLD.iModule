<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mBoard = new ModuleBoard();

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$repto = Request('repto');
$autosave = Request('autosave');

$result = array();
$result['success'] = false;

$files = array();
if ($repto != null && $autosave == null) {
	$result['success'] = true;
	$data = $mDB->DBfetchs($mBoard->table['file'],'*',"where `type`='$type' and `repto`=$repto and `wysiwyg`='$wysiwyg'",'idx,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$files[$i] = array();
		$files[$i]['idx'] = $data[$i]['idx'];
		$files[$i]['type'] = $data[$i]['filetype'];
		$files[$i]['name'] = $data[$i]['filename'];
		$files[$i]['size'] = $data[$i]['filesize'];
		
		if ($data[$i]['filetype'] == 'IMG') {
			$files[$i]['thumbnail'] = $_ENV['userfileDir'].$mBoard->thumbnail.'/'.$data[$i]['idx'].'.thm';
		}
	}
} elseif ($autosave != null) {
	$result['success'] = true;
	/*$data = $mDB->DBfetch($mBoard->table['autosave'],array('data','ip'),"where `tid`='$autosave'");
	if (isset($data['data']) == true && $data['ip'] == $_SERVER['REMOTE_ADDR']) {
		$data = unserialize(base64_decode($data['data']));
		$file = split(',',$data['file']);

		for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
			$temp = explode('|',$file[$i]);
			$fidx = $temp[0];
			$fileData = $mDB->DBfetch($mBoard->table['file'],'*',"where `idx`=$fidx");
			echo '<file>';
			echo '<name>'.GetString($fileData['filename'],'xml').'</name>';
			echo '<size>'.$fileData['filesize'].'</size>';
			echo '<server>'.GetString($file[$i],'xml').'</server>';
			echo '</file>';
		}
	}
	*/
}

$result['files'] = $files;
exit(json_encode($result));
?>