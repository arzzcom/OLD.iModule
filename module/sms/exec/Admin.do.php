<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');
$mSMS = new ModuleSMS();

if ($action == 'membersend') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('receiver');
	$content = Request('content');

	$data = $mDB->DBfetchs($_ENV['table']['member'],array('cellphone'),"where `idx` IN ($idx)");
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		if ($data[$i]['cellphone']) {
			if ($mSMS->SendSMS(array_shift(explode('||',$data[$i]['cellphone'])),$content,(isset($member['cellphone']['cellphone']) == true && $member['cellphone']['cellphone'] ? $member['cellphone']['cellphone'] : ''),false) == true) {
				SaveAdminLog('sms','['.array_shift(explode('||',$data[$i]['cellphone']).'] 에게 SMS를 발송하였습니다.');
			}
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '<errors>';
	echo '<field><id></id></field>';
	echo '</errors>';
	echo '</message>';
}

if ($action == 'send') {
	header('Content-type: text/html; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$to = explode("\r\n",Request('to'));
	$from = Request('from');
	$content = Request('content');

	if ($to[0] == 'ALL') {
		$total = $mDB->DBcount($_ENV['table']['member'],"where `is_leave`='FALSE' and `cellphone`!='||' and `cellphone`!=''");
		$total+= sizeof($to)-1;
	} else {
		$total = sizeof($to);
	}

	$looper = 0;
	for ($i=0, $loop=sizeof($to);$i<$loop;$i++) {
		if ($to[$i] == 'ALL') {
			$list = $mDB->DBfetchs($_ENV['table']['member'],array('cellphone'),"where `is_leave`='FALSE' and `cellphone`!='||' and `cellphone`!=''");
			for ($j=0, $loopj=sizeof($list);$j<$loopj;$j++) {
				$mSMS->SendSMS(array_shift(explode('||',$list[$j]['cellphone'])),$content,$from,false);
				echo '<script>parent.ShowProgress('.++$looper.','.$total.');</script>';
				flush();
				sleep(0.1);
			}
		} else {
			$mSMS->SendSMS($to[$i],$content,$from,false);
			echo '<script>parent.ShowProgress('.++$looper.','.$total.');</script>';
			flush();
			sleep(0.1);
		}

		if ($looper % 50) sleep(1);
	}
}

if ($action == 'cancel') {
	echo '<script>parent.ShowProgress(-1);</script>';
}
?>