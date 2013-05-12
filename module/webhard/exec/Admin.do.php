<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action == Request('action');
$do = Request('do');

$mWebHard = new ModuleWebHard();

if ($action == 'folder') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$dir = Request('dir');
	$Error = array();

	$insert['filename'] = Request('name') ? Request('name') : $Error['name'] = '폴더명을 입력하여 주십시오.';
	$insert['type'] = 'DIR';
	$insert['reg_date'] = $insert['modify_date'] = GetGMT();
	$insert['dir'] = Request('dir');

	if (sizeof($Error) == 0) $mDB->DBinsert($mWebHard->table['file'],$insert);

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

	if (sizeof($Error) > 0) {
		echo '<errors>';
		foreach ($Error as $id=>$msg) {
			echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
		}
		echo '</errors>';
	} else {
		echo '<errors>';
		echo '<field><id>'.$idx.'</id></field>';
		echo '</errors>';
	}

	echo '</message>';
}
?>