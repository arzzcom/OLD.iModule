<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$get = Request('get');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$callbackStart = Request('callback') ? Request('callback').'(' : '';
$callbackEnd = Request('callback') ? ');' : '';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$lists = array();
$return = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

$mEmail = new ModuleEmail();

if ($action == 'member') {
	$group = Request('group');
	$keyword = Request('keyword');
	
	$find = "where `is_leave`='FALSE'";
	if ($group) $find.= " and `group`='$group'";
	if ($keyword) $find.= " and (`user_id`='$keyword' or `name` like '%$keyword%' or `nickname` like '%$keyword%' or `email` like '%$keyword%')";
	
	$total = $mDB->DBcount($_ENV['table']['member'],$find);
	$lists = $mDB->DBfetchs($_ENV['table']['member'],array('idx','group','user_id','name','nickname','email'),$find,$orderer,$limiter);
}

if ($action == 'receiver') {
	$key = Request('key');
	$keyword = Request('keyword');
	$find = "where `key`='$key'";
	if ($keyword) $find = " and `email` like '%$keyword%' or `name` like '%$keyword%'";
	
	$total = $mDB->DBcount($mEmail->table['temp'],$find);
	$lists = $mDB->DBfetchs($mEmail->table['temp'],'*',$find,$orderer,$limiter);
}

if ($action == 'list') {
	$type = Request('type');
	
	if ($type == 'group') {
		$find = '';
		$total = $mDB->DBcount($mEmail->table['email'],$find);
		$lists = $mDB->DBfetchs($mEmail->table['email'],array('idx','subject'),$find,'idx,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			
			$sendmail = $mDB->DBfetch($mEmail->table['send'],'*',"where `repto`='{$lists[$i]['idx']}'",'idx,asc','0,1');
			$readmail = $mDB->DBfetch($mEmail->table['send'],'*',"where `repto`='{$lists[$i]['idx']}'",'read_date,desc','0,1');
			$from = unserialize($sendmail['from']);
			$to = unserialize($sendmail['to']);
			$lists[$i]['from'] = $from[0].' &lt;'.$from[1].'&gt;';
			$lists[$i]['to'] = $to[0].' &lt;'.$to[1].'&gt;';
			$lists[$i]['success'] = $mDB->DBcount($mEmail->table['send'],"where `repto`='{$lists[$i]['idx']}' and `result`='TRUE'");
			$lists[$i]['fail'] = $mDB->DBcount($mEmail->table['send'],"where `repto`='{$lists[$i]['idx']}' and `result`='FALSE'");
			$lists[$i]['wait'] = $mDB->DBcount($mEmail->table['send'],"where `repto`='{$lists[$i]['idx']}' and `result`='WAIT'");
			$lists[$i]['read'] = $mDB->DBcount($mEmail->table['send'],"where `repto`='{$lists[$i]['idx']}' and `read_date`>0");
			$lists[$i]['send_date'] = GetTime('Y.m.d H:i:s',$sendmail['send_date']);
			$lists[$i]['read_date'] = $readmail['read_date'] > 0 ? GetTime('Y.m.d H:i:s',$readmail['read_date']) : '';
		}
	} elseif ($type == 'each') {
		$find = '';
		$total = $mDB->DBcount($mEmail->table['send'],$find);
		$lists = $mDB->DBfetchs($mEmail->table['send'],'*',$find,'idx,desc',$limiter);
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$mail = $mDB->DBfetch($mEmail->table['email'],array('idx','subject'),"where `idx`='{$lists[$i]['repto']}'");
			$from = unserialize($lists[$i]['from']);
			$to = unserialize($lists[$i]['to']);
			$lists[$i]['from'] = $from[0].' &lt;'.$from[1].'&gt;';
			$lists[$i]['to'] = $to[0].' &lt;'.$to[1].'&gt;';
			
			$lists[$i]['subject'] = $mail['subject'];
			
			$lists[$i]['success'] = $lists[$i]['fail'] = $lists[$i]['wait'] = $lists[$i]['read'] = 0;
			if ($lists[$i]['result'] == 'WAIT') $lists[$i]['wait'] = 1;
			elseif ($lists[$i]['result'] == 'TRUE') $lists[$i]['success'] = 1;
			elseif ($lists[$i]['result'] == 'FALSE') $lists[$i]['false'] = 1;
			if ($lists[$i]['read_date'] > 0) $lists[$i]['read'] = 1;
			
			$lists[$i]['send_date'] = GetTime('Y.m.d H:i:s',$lists[$i]['send_date']);
			$lists[$i]['read_date'] = $lists[$i]['read_date'] > 0 ? GetTime('Y.m.d H:i:s',$lists[$i]['read_date']) : '';
		}
	}
}

if ($action == 'email') {
	$mode = Request('mode');
	$idx = Request('idx');
	
	if ($mode == 'group') {
		$mail = $mDB->DBfetch($mEmail->table['email'],array('body'),"where `idx`='$idx'");
		$body = $mail['body'];
	} else {
		$data = $mDB->DBfetch($mEmail->table['send'],array('to','repto'),"where `idx`='$idx'");
		$mail = $mDB->DBfetch($mEmail->table['email'],array('body'),"where `idx`='{$data['repto']}'");
		$to = unserialize($data['to']);
		$body = str_replace('{name}',$to[0],$mail['body']);
	}
	
	$return['success'] = true;
	$return['body'] = $body;
	exit(json_encode($return));
}

if ($action == 'file') {
	$get = Request('get');
	
	if ($get == 'totalsize') {
		$data = $mDB->DBfetch($mEmail->table['file'],array('SUM(filesize)'));
		$return['success'] = true;
		$return['totalsize'] = isset($data[0]) == true ? $data[0] : 0;
		exit(json_encode($return));
	} else {
		$keyword = Request('keyword');
		if ($get == 'register') $find = "where `repto`!=0";
		elseif ($get == 'temp') $find = "where `repto`=0";
		elseif ($get == 'image') $find = "where `filetype`='IMG'";
	
		if ($keyword) $find.= " and `filename` like '%$keyword%'";
		$boardInfo = array();
		$total = $mDB->DBcount($mEmail->table['file'],$find);
		$lists = $mDB->DBfetchs($mEmail->table['file'],'*',$find,$orderer,$limiter);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$post = $ment = array();
			if ($lists[$i]['repto'] != 0) {
				$email = $mDB->DBfetch($mEmail->table['email'],array('subject'),"where `idx`='{$lists[$i]['repto']}'");
			}
				
			if (isset($email['subject']) == true) $lists[$i]['subject'] = $email['subject'];
			else $lists[$i]['subject'] = '';
			
			if ($lists[$i]['filetype'] == 'IMG' && $get == 'image') {
				if (file_exists($_ENV['userfilePath'].$mEmail->thumbnail.'/'.$lists[$i]['idx'].'.thm') == true) {
					$lists[$i]['image'] = $_ENV['userfileDir'].$mEmail->thumbnail.'/'.$lists[$i]['idx'].'.thm';
				} else {
					if (GetThumbnail($_ENV['userfilePath'].$mEmail->userfile.$lists[$i]['filepath'],$_ENV['userfilePath'].$mEmail->thumbnail.'/'.$lists[$i]['idx'].'.thm',100,75,false) == true) {
						$lists[$i]['image'] = $_ENV['userfileDir'].$mEmail->thumbnail.'/'.$lists[$i]['idx'].'.thm';
					} else {
						$lists[$i]['image'] = $_ENV['dir'].'/module/board/images/admin/noimage.gif';
					}
				}
				
			} else {
				$lists[$i]['image'] = '';
			}
			
			$lists[$i]['filepath'] = $_ENV['userfilePath'].$mEmail->userfile.$lists[$i]['filepath'];
		}
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>