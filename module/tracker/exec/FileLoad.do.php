<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset="UTF-8"', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$mDB = &DB::instance();
$mTracker = new ModuleTracker();

$type = Request('type');
$repto = Request('repto');

echo '<?xml version="1.0" encoding="UTF-8"?><list>';

$data = $mDB->DBfetchs($mTracker->table['file'],'*',"where `type`='$type' and `repto`=$repto",'idx,asc');
for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
	echo '<file>';
	echo '<name>'.GetString($data[$i]['filename'],'xml').'</name>';
	echo '<size>'.$data[$i]['filesize'].'</size>';
	echo '<server>'.GetString($data[$i]['idx'].'|'.$data[$i]['filetype'].'|'.$data[$i]['filename'].'|'.$data[$i]['filesize'].($data[$i]['filetype'] == 'IMG' ? '|'.$_ENV['dir'].$mTracker->thumbnail.'/'.$data[$i]['idx'].'.thm' : ''),'xml').'</server>';
	echo '</file>';
}

echo '</list>';
?>