<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mRelease = new ModuleRelease();

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$repto = Request('repto');

$result = array();
$result['success'] = false;

$files = array();
if ($repto != null) {
	$result['success'] = true;
	$data = $mDB->DBfetchs($mRelease->table['file'],'*',"where `type`='$type' and `repto`=$repto and `wysiwyg`='$wysiwyg'",'idx,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$files[$i] = array();
		$files[$i]['idx'] = $data[$i]['idx'];
		$files[$i]['type'] = $data[$i]['filetype'];
		$files[$i]['name'] = $data[$i]['filename'];
		$files[$i]['size'] = $data[$i]['filesize'];
		
		if ($data[$i]['filetype'] == 'IMG') {
			$files[$i]['thumbnail'] = $_ENV['userfileDir'].$mRelease->thumbnail.'/'.$data[$i]['idx'].'.thm';
		}
	}
}

$result['files'] = $files;
exit(json_encode($result));
?>