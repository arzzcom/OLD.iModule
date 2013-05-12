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
		if ($data[$i]['cellphone']) $mSMS->SendSMS(array_shift(explode('||',$data[$i]['cellphone'])),$content,($member['cellphone'] ? array_shift(explode('||',$member['cellphone'])) : ''),false);
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
	$fromName = Request('fromName');
	$fromEmail = Request('fromEmail');
	$subject = stripslashes(Request('subject'));
	$content = stripslashes(Request('content'));
	$content = str_replace('http://'.$_SERVER['HTTP_HOST'].$_ENV['dir'],$_ENV['dir'],$content);
	$content = str_replace($_ENV['dir'],'http://'.$_SERVER['HTTP_HOST'].$_ENV['dir'],$content);
	$isSMTP = Request('is_smtp') == 'on';

	$content = '<style type="text/css">.smartOutput {font-size:12px; line-height:1.6; font-family:AppleGothic, "malgun gothic", dotum;}.smartOutput p {line-height:1.6;}.smartOutput p SPAN {line-height:1.6;}.smartOutput p DIV {line-height:1.6;}.smartOutput blockquote.q1,.smartOutput blockquote.q2,.smartOutput blockquote.q3,.smartOutput blockquote.q4,.smartOutput blockquote.q5,.smartOutput blockquote.q6,.smartOutput blockquote.q7{ padding:10px; margin-left:15px; margin-right:15px;}.smartOutput blockquote.q1{ padding:0 10px; border-left:2px solid #ccc;}.smartOutput blockquote.q2{ padding:0 10px; background:url(../module/wysiwyg/images/bg_qmark.gif) no-repeat;}.smartOutput blockquote.q3{ border:1px solid #d9d9d9;}.smartOutput blockquote.q4{ border:1px solid #d9d9d9; background:#fbfbfb;}.smartOutput blockquote.q5{ border:2px solid #707070;}.smartOutput blockquote.q6{ border:1px dashed #707070;}.smartOutput blockquote.q7{ border:1px dashed #707070; background:#fbfbfb;}.smartOutput sup{ font:10px Tahoma;}.smartOutput sub{ font:10px Tahoma;}.smartOutput table td{ padding:4px;}.smartOutput .movie {border:1px dashed #CCCCCC; background:url(../images/common/wysiwyg_movie.png) no-repeat 50% 50%;}</style><div class="smartOutput">'.$content.'</div>';

	if ($to[0] == 'ALL') {
		$total = $mDB->DBcount($_ENV['table']['member'],"where `is_leave`='FALSE' and `email`!=''");
		$total+= sizeof($to)-1;
	} else {
		$total = sizeof($to);
	}

	$looper = 0;
	for ($i=0, $loop=sizeof($to);$i<$loop;$i++) {
		if ($to[$i] == 'ALL') {
			$list = $mDB->DBfetchs($_ENV['table']['member'],array('name','email'),"where `is_leave`='FALSE' and `email`!=''");
			for ($j=0, $loopj=sizeof($list);$j<$loopj;$j++) {
				$mEmail = new ModuleEmail($isSMTP);
				$mEmail->SetFrom($fromEmail,$fromName);
				$mEmail->SetContent($subject,$content,true);
				$mEmail->AddTo($list[$j]['email'],$list[$j]['name']);
				$mEmail->SendEmail();
				echo '<script>parent.ShowProgress('.++$looper.','.$total.');</script>';
				flush();
			}
		} else {
			$mEmail = new ModuleEmail($isSMTP);
			$mEmail->SetFrom($fromEmail,$fromName);
			$mEmail->SetContent($subject,$content,true);
			$check = $mDB->DBfetch($_ENV['table']['member'],array('name'),"where `email`='{$to[$i]}'");
			$mEmail->AddTo($to[$i],isset($check['name']) == true ? $check['name'] : null);
			$mEmail->SendEmail();
			echo '<script>parent.ShowProgress('.++$looper.','.$total.');</script>';
			flush();
		}

		if ($looper % 50) sleep(1);
	}
}

if ($action == 'cancel') {
	echo '<script>parent.ShowProgress(-1);</script>';
}
?>