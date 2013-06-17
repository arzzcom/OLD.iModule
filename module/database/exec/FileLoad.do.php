<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset="UTF-8"', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$mDB = &DB::instance();
$mDatabase = new ModuleDatabase();

$type = Request('type');
$wysiwyg = Request('wysiwyg');
$repto = Request('repto');

echo '<?xml version="1.0" encoding="UTF-8"?><list>';
if ($repto != null) {
	$data = $mDB->DBfetchs($mDatabase->table['file'],'*',"where `type`='$type' and `repto`=$repto and `wysiwyg`='$wysiwyg'",'idx,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		echo '<file>';
		echo '<name>'.GetString($data[$i]['filename'],'xml').'</name>';
		echo '<size>'.$data[$i]['filesize'].'</size>';
		echo '<server>'.GetString($data[$i]['idx'].'|'.$data[$i]['filetype'].'|'.$data[$i]['filename'].'|'.$data[$i]['filesize'].($data[$i]['filetype'] == 'IMG' ? '|'.$_ENV['userfileDir'].$mDatabase->thumbnail.'/'.$data[$i]['idx'].'.thm' : ''),'xml').'</server>';
		echo '</file>';
	}
}
echo '</list>';
?>