<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

$mEmail = new ModuleEmail();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'log') {
	if ($do == 'delete') {
		$mode = Request('mode');
		$idx = Request('idx');
		
		if ($mode == 'group') {
			$data = $mDB->DBfetchs($mEmail->table['email'],'*',"where `idx` IN ($idx)");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$file = $mDB->DBfetchs($mEmail->table['file'],'*',"where `repto`='{$data[$i]['idx']}'");
				for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
					@unlink($_ENV['userfilePath'].$mEmail->userfile.$file[$j]['filepath']);
				}
				$mDB->DBdelete($mEmail->table['file'],"where `repto`='{$data[$i]['idx']}'");
				$mDB->DBdelete($mEmail->table['send'],"where `repto`='{$data[$i]['idx']}'");
			}
			$mDB->DBdelete($mEmail->table['email'],"where `idx` IN ($idx)");
		} elseif ($mode == 'each') {
			$data = $mDB->DBfetchs($mEmail->table['send'],'*',"where `idx` IN ($idx)");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBdelete($mEmail->table['send'],"where `idx`='{$data[$i]['idx']}'");
				if ($mDB->DBcount($mEmail->table['send'],"where `repto`='{$data[$i]['repto']}'") == 0) {
					$file = $mDB->DBfetchs($mEmail->table['file'],'*',"where `repto`='{$data[$i]['repto']}'");
					for ($j=0, $loopj=sizeof($file);$j<$loopj;$j++) {
						@unlink($_ENV['userfilePath'].$mEmail->userfile.$file[$j]['filepath']);
					}
					$mDB->DBdelete($mEmail->table['file'],"where `repto`='{$data[$i]['repto']}'");
				}
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'send') {
	if ($do == 'receiver_add') {
		$idx = Request('idx');
		$keyword = Request('keyword');
		$group = Request('group');
		$mode = Request('mode');
		$key = Request('key');
		
		$insertCount = 0;
		if ($mode == 'select') {
			$data = $mDB->DBfetchs($_ENV['table']['member'],array('email','name'),"where `idx` IN ($idx)");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['email'] && $mDB->DBcount($mEmail->table['temp'],"where `key`='$key' and `email`='{$data[$i]['email']}'") == 0) {
					$mDB->DBinsert($mEmail->table['temp'],array('key'=>$key,'email'=>$data[$i]['email'],'name'=>$data[$i]['name']));
					$insertCount++;
				}
			}
			
			$return['success'] = true;
			$return['count'] = $insertCount;
		} elseif ($mode == 'store') {
			$find = "where `is_leave`='FALSE'";
			if ($group) $find.= " and `group`='$group'";
			if ($keyword) $find.= " and (`user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%' or `email` like '%$keyword%')";
			$data = $mDB->DBfetchs($_ENV['table']['member'],array('email','name'),$find);
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['email'] && $mDB->DBcount($mEmail->table['temp'],"where `key`='$key' and `email`='{$data[$i]['email']}'") == 0) {
					$mDB->DBinsert($mEmail->table['temp'],array('key'=>$key,'email'=>$data[$i]['email'],'name'=>$data[$i]['name']));
					$insertCount++;
				}
			}
			
			$return['success'] = true;
			$return['count'] = $insertCount;
		} elseif ($mode == 'direct') {
			$name = Request('name');
			$email = CheckEmail(Request('email')) == true ? Request('email') : $errors['email'] = '이메일 주소가 잘못되었습니다.';
			
			if (sizeof($errors) == 0) {
				if ($mDB->DBcount($mEmail->table['temp'],"where `key`='$key' and `email`='$email'") == 0) {
					$mDB->DBinsert($mEmail->table['temp'],array('key'=>$key,'email'=>$email,'name'=>$name));
				} else {
					$errors['email'] = '이미 받는사람에 추가되어 있는 이메일주소입니다.';
				}
			}
			
			if (sizeof($errors) == 0) {
				$return['success'] = true;
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'receiver_remove') {
		$key = Request('key');
		$email = explode(',',Request('email'));
		for ($i=0, $loop=sizeof($email);$i<$loop;$i++) {
			$mDB->DBdelete($mEmail->table['temp'],"where `key`='$key' and `email`='{$email[$i]}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'receiver_reset') {
		$key = Request('key');
		$mDB->DBtruncate($mEmail->table['temp']);
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'save') {
		$key = Request('key');
		$name = Request('name');
		$email = Request('email');
		$subject = Request('subject');
		$body = Request('body');
		$body = str_replace('http://'.$_SERVER['HTTP_HOST'].$_ENV['dir'],$_ENV['dir'],$body);
		$body = str_replace($_ENV['dir'],'http://'.$_SERVER['HTTP_HOST'].$_ENV['dir'],$body);
		$isSMTP = Request('is_smtp') == 'on';
	
		$body = '<style type="text/css">.smartOutput {font-size:12px; line-height:1.6; font-family:AppleGothic, "malgun gothic", dotum;}.smartOutput p {line-height:1.6;}.smartOutput p SPAN {line-height:1.6;}.smartOutput p DIV {line-height:1.6;}.smartOutput blockquote.q1,.smartOutput blockquote.q2,.smartOutput blockquote.q3,.smartOutput blockquote.q4,.smartOutput blockquote.q5,.smartOutput blockquote.q6,.smartOutput blockquote.q7{ padding:10px; margin-left:15px; margin-right:15px;}.smartOutput blockquote.q1{ padding:0 10px; border-left:2px solid #ccc;}.smartOutput blockquote.q2{ padding:0 10px; background:url(../module/wysiwyg/images/bg_qmark.gif) no-repeat;}.smartOutput blockquote.q3{ border:1px solid #d9d9d9;}.smartOutput blockquote.q4{ border:1px solid #d9d9d9; background:#fbfbfb;}.smartOutput blockquote.q5{ border:2px solid #707070;}.smartOutput blockquote.q6{ border:1px dashed #707070;}.smartOutput blockquote.q7{ border:1px dashed #707070; background:#fbfbfb;}.smartOutput sup{ font:10px Tahoma;}.smartOutput sub{ font:10px Tahoma;}.smartOutput table td{ padding:4px;}.smartOutput .movie {border:1px dashed #CCCCCC; background:url(../images/common/wysiwyg_movie.png) no-repeat 50% 50%;}</style><div class="smartOutput">'.$body.'</div>';

		$idx = $mDB->DBinsert($mEmail->table['email'],array('subject'=>$subject,'body'=>$body));

		$attach = Request('EmailFormUploader-files');
		if ($attach != null) {
			for ($i=0, $loop=sizeof($attach);$i<$loop;$i++) {
				$temp = explode('|',$attach[$i]);
				$fidx = $temp[0];
	
				if (sizeof($temp) == 1) {
					$fileData = $mDB->DBfetch($mEmail->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
					@unlink($_ENV['userfilePath'].$mEmail->userfile.$fileData['filepath']);
					if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mEmail->thumbnail.'/'.$fidx.'.thm');
					$mDB->DBdelete($mEmail->table['file'],"where `idx`='$fidx'");
				} else {
					$mDB->DBupdate($mEmail->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
				}
			}
		}
		
		$count = 0;
		$receiver = $mDB->DBfetchs($mEmail->table['temp'],'*',"where `key`='$key'");
		for ($i=0, $loop=sizeof($receiver);$i<$loop;$i++) {
			$insert = array();
			$insert['from'] = serialize(array($name,$email));
			$insert['to'] = serialize(array($receiver[$i]['name'],$receiver[$i]['email']));
			$insert['repto'] = $idx;
			$insert['result'] = 'WAIT';
			$mDB->DBinsert($mEmail->table['send'],$insert);
			$count++;
		}
		
		$mDB->DBdelete($mEmail->table['temp'],"where `key`='$key'");
		
		$return['success'] = true;
		$return['idx'] = $idx;
		$return['count'] = $count;
		exit(json_encode($return));
	}
	
	if ($do == 'send') {
		$is_smtp = Request('is_smtp');
		$repto = Request('repto');

		GetDefaultHeader('메일발송');

		$mail = $mDB->DBfetch($mEmail->table['email'],array('subject','body'),"where `idx`='$repto'");

		$total = Request('total') ? Request('total') : $mDB->DBcount($mEmail->table['send'],"where `repto`='$repto'");
		$wait = Request('wait') ? Request('wait') : $mDB->DBcount($mEmail->table['send'],"where `repto`='$repto' and `result`='WAIT'");

		echo '<script type="text/javascript">parent.ShowProgress('.($total-$wait).','.$total.');</script>';
		
		$data = $mDB->DBfetchs($mEmail->table['send'],'*',"where `repto`='$repto' and `result`='WAIT'",'idx,asc','0,1');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$from = unserialize($data[$i]['from']);
			$to = unserialize($data[$i]['to']);
			$mEmail = new ModuleEmail($is_smtp == 'true');
			$mEmail->SetFrom($from[1],$from[0]);
			$mEmail->SetContent($mail['subject'],$mail['body'],true);
			$mEmail->AddTo($to[1],$to[0]);
			$mEmail->SendEmail($data[$i]['idx']);
			$wait--;
		}
		
		if ($wait > 0) {
			Redirect($mEmail->moduleDir.'/exec/Admin.do.php?action=send&do=send&repto='.$repto.'&total='.$total.'&wait='.$wait.'&is_smtp='.$is_smtp);
		} else {
			echo '<script type="text/javascript">parent.ShowProgress('.($total-$wait).','.$total.');</script>';
		}
		GetDefaultFooter();
	}
}

if ($action == 'file') {
	if ($do == 'retrench') {
		GetDefaultHeader('첨부파일 정리중');

		$fileLimit = Request('fileLimit') ? Request('fileLimit') : 0;

		$files = scandir($_ENV['userfilePath'].$mEmail->userfile.'/attach',0);

		if ($fileLimit == 0) {
			$totalFile = sizeof($files);
			$deleteFile = 0;
		} else {
			$totalFile = Request('totalFile');
			$deleteFile = Request('deleteFile');
		}
		
		for ($i=$fileLimit-$deleteFile;$i<$fileLimit+100;$i++) {
			if (isset($files[$i]) == false) {
				break;
			} elseif (is_dir($_ENV['userfilePath'].$mEmail->userfile.'/attach/'.$files[$i]) == false) {
				if ($mDB->DBcount($mEmail->table['file'],"where `filepath`='/attach/{$files[$i]}'") == 0) {
					$deleteFile++;
					@unlink($_ENV['userfilePath'].$mEmail->userfile.'/attach/'.$files[$i]) or $deleteFile--;
				}
			}
		}

		if ($totalFile > $fileLimit+100) {
			echo '<script type="text/javascript">top.RetrenchProgressControl('.($fileLimit+100).','.$totalFile.','.$deleteFile.');</script>';
			Redirect($_SERVER['PHP_SELF'].GetQueryString(array('fileLimit'=>$fileLimit+100,'totalFile'=>$totalFile,'deleteFile'=>$deleteFile),'',false));
		} else {
			echo '<script type="text/javascript">top.RetrenchProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		}

		GetDefaultFooter();
	}

	if ($do == 'removetemp') {
		GetDefaultHeader('첨부파일 정리중');
		
		$fileLimit = Request('fileLimit') ? Request('fileLimit') : 0;
		$deleteFile = Request('deleteFile') ? Request('deleteFile') : 0;
		if ($fileLimit == 0) {
			$totalFile = $mDB->DBcount($mEmail->table['file'],"where `repto`=0");
		} else {
			$totalFile = Request('totalFile');
		}
		
		$data = $mDB->DBfetchs($mEmail->table['file'],array('idx','filepath'),"where `repto`=0",'idx,asc',($fileLimit-$deleteFile).',1000');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$deleteFile++;
			@unlink($_ENV['userfilePath'].$mEmail->userfile.$data[$i]['filepath']);
			@unlink($_ENV['userfilePath'].$mEmail->thumbnail.'/'.$data[$i]['idx'].'.thm');
			$mDB->DBdelete($mEmail->table['file'],"where `idx`='{$data[$i]['idx']}'");
		}
		
		if ($totalFile > $fileLimit + 1000) {
			echo '<script type="text/javascript">top.TempProgressControl('.($fileLimit+1000).','.$totalFile.','.$deleteFile.');</script>';
			Redirect($_SERVER['PHP_SELF'].GetQueryString(array('fileLimit'=>$fileLimit+1000,'deleteFile'=>$deleteFile,'totalFile'=>$totalFile),'',false));
		} else {
			echo '<script type="text/javascript">top.TempProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		}
		
		GetDefaultFooter();
	}
}

if ($action == 'cancel') {
	echo '<script>parent.ShowProgress(-1);</script>';
}
?>