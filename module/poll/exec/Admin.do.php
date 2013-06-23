<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mPoll = new ModulePoll();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'poll') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		$insert['title'] = Request('title');
		$insert['skin'] = Request('skin');
		$insert['width'] = Request('width') && preg_match('/^[^0]+[0-9]+(%)?$/',Request('width')) == true ? Request('width') : $errors['width'] = '설문조사가로크기를 정확하게 입력하여 주십시오.';
		
		$insert['listnum'] = Request('listnum');
		$insert['pagenum'] = Request('pagenum');
		$insert['use_ment'] = Request('use_ment') == 'on' ? 'TRUE' : 'FALSE';
		$insert['post_point'] = Request('post_point');
		$insert['ment_point'] = Request('ment_point');
		$insert['vote_point'] = Request('vote_point');
		$insert['permission'] = serialize(array('list'=>Request('permission_list'),'view'=>Request('permission_view'),'post'=>Request('permission_post'),'ment'=>Request('permission_ment'),'modify'=>Request('permission_modify'),'delete'=>Request('permission_delete'),'vote'=>Request('permission_vote'),'result'=>Request('permission_result')));

		if ($do == 'add') {
			$insert['pid'] = Request('pid') && preg_match('/^[a-z0-9_]+$/i',Request('pid')) == true ? Request('pid') : $errors['pid'] = '설문조사ID를 영문,숫자,_(언더바)를 이용하여 입력하여 주십시오.';

			if ($mDB->DBcount($mPoll->table['setup'],"where `pid`='{$insert['pid']}'") > 0) $errors['pid'] = '이미 사용중인 설문조사 ID입니다.';

			if (sizeof($errors) == 0) {
				$mDB->DBinsert($mPoll->table['setup'],$insert);
				$return['success'] = true;
				SaveAdminLog('poll','['.$insert['pid'].'] 설문조사을 추가하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		} else {
			$pid = Request('pid');

			if (sizeof($errors) == 0) {
				$mDB->DBupdate($mPoll->table['setup'],$insert,'',"where `pid`='$pid'");
				$return['success'] = true;
				SaveAdminLog('poll','['.$pid.'] 설문조사을 수정하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		}

		exit(json_encode($return));
	}
	
	if ($do == 'modify_all') {
		$pid = explode(',',Request('pid'));

		$insert = array();
		if (Request('is_skin') == 'on') $insert['skin'] = Request('skin');
		if (Request('is_width') == 'on') $insert['width'] = Request('width') && preg_match('/^[^0]+[0-9]+(%)?$/',Request('width')) == true ? Request('width') : $errors['width'] = '설문조사가로크기를 정확하게 입력하여 주십시오.';
		
		if (Request('is_listnum') == 'on') $insert['listnum'] = Request('listnum');
		if (Request('is_pagenum') == 'on') $insert['pagenum'] = Request('pagenum');
		if (Request('is_use_ment') == 'on') $insert['use_ment'] = Request('use_ment') == 'on' ? 'TRUE' : 'FALSE';
		
		if (Request('is_post_point') == 'on') $insert['post_point'] = Request('post_point');
		if (Request('is_ment_point') == 'on') $insert['ment_point'] = Request('ment_point');
		if (Request('is_vote_point') == 'on') $insert['vote_point'] = Request('vote_point');
		
		if (sizeof($errors) == 0) {
			for ($i=0, $loop=sizeof($pid);$i<$loop;$i++) {
				$poll = $mDB->DBfetch($mPoll->table['setup'],array('permission'),"where `pid`='{$pid[$i]}'");
				
				if ($poll['permission'] && is_array(unserialize($poll['permission'])) == true) {
					$insert['permission'] = unserialize($poll['permission']);
				} else {
					$insert['permission'] = array('list'=>'true','post'=>'true','view'=>'true','ment'=>'true','vote'=>'true','result'=>'true','modify'=>'{$member.type} == \'ADMINISTRATOR\'','delete'=>'{$member.type} == \'ADMINISTRATOR\'');
				}
				foreach ($insert['permission'] as $key=>$value) {
					if (Request('is_permission_'.$key) == 'on') $insert['permission'][$key] = Request('permission_'.$key);
				}
				$insert['permission'] = serialize($insert['permission']);
				
				$mDB->DBupdate($mPoll->table['setup'],$insert,'',"where `pid`='{$pid[$i]}'");
			}
			
			$return['success'] = true;
			SaveAdminLog('poll','['.$pid.'] 설문조사을 수정하였습니다.');
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$pid = explode(',',Request('pid'));

		for ($i=0, $loop=sizeof($pid);$i<$loop;$i++) {
			$mDB->DBdelete($mPoll->table['setup'],"where `pid`='{$pid[$i]}'");
			$post = $mDB->DBfetchs($mPoll->table['post'],array('idx'),"where `pid`='{$pid[$i]}'");
			for ($j=0, $loopj=sizeof($post);$j<$loopj;$j++) {
				$mDB->DBdelete($mPoll->table['post'],"where `idx`={$post[$j]['idx']}");
				$mDB->DBdelete($mPoll->table['item'],"where `repto`={$post[$j]['idx']}");
				$mDB->DBdelete($mPoll->table['voter'],"where `repto`={$post[$j]['idx']}");
				@unlink($_ENV['userfilePath'].$mPoll->userfile.'/'.$post[$j]['idx'].'.file');
				@unlink($_ENV['userfilePath'].$mPoll->thumbnail.'/'.$post[$j]['idx'].'.thm');
			}

			$mDB->DBdelete($mPoll->table['ment'],"where `pid`='{$pid[$i]}'");

			SaveAdminLog('poll','['.$pid[$i].'] 설문조사을 삭제하였습니다.');
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}


if ($action == 'post') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		$insert['pid'] = Request('pid');
		$insert['title'] = Request('title');
		$insert['content'] = Request('content');
		$insert['reg_date'] = GetGMT();
		$insert['end_date'] = GetGMT(Request('end_date').' 23:59:59');
		$insert['vote_type'] = Request('is_multi') == 'on' ? 'MULTI' : 'SINGLE';
		
		if ($insert['reg_date'] > $insert['end_date']) $errors['end_date'] = '설문시작일보다 종료일이 이전입니다.';
		
		$item = json_decode(Request('item'),true);
		if (sizeof($item) < 2) $errors['item'] = '설문조사 항목은 2개 이상이어야 합니다.';
		
		if (isset($_FILES['image']['tmp_name']) == true) {
			$check = getimagesize($_FILES['image']['tmp_name']);
			if (in_array($check[2],array('1','2','3')) == false) {
				$errors['image'] = '이미지파일은 GIF,JPG,PNG파일만 가능합니다.';
			}
		}
			
		if (sizeof($errors) == 0) {
			if ($do == 'add') {
				$insert['mno'] = $member['idx'];
				$insert['ip'] = $_SERVER['REMOTE_ADDR'];
				$idx = $mDB->DBinsert($mPoll->table['post'],$insert);
			} else {
				
			}
			
			$itemIDX = array();
			for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
				if ($item[$i]['idx'] == '-1') {
					$itemIDX[] = $mDB->DBinsert($mPoll->table['item'],array('repto'=>$idx,'title'=>$item[$i]['title'],'sort'=>$item[$i]['sort']));
				} else {
					$mDB->DBupdate($mPoll->table['item'],array('title'=>$item[$i]['title'],'sort'=>$item[$i]['sort']),'',"where `idx`='{$item[$i]['idx']}'");
					$itemIDX[] = $item[$i]['idx'];
				}
			}
			
			$item = $mDB->DBfetchs($mPoll->table['item'],array('idx'),"where `repto`='$idx'");
			for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
				if (in_array($item[$i]['idx'],$itemIDX) == false) {
					$mDB->DBdelete($mPoll->table['item'],"where `idx`='{$item[$i]['idx']}'");
				}
			}
			
			if (Request('image_delete') == 'on' || isset($_FILES['image']['tmp_name']) == true) {
				@unlink($mPoll->userfile.'/'.$idx.'.file');
			}
			
			if (isset($_FILES['image']['tmp_name']) == true) {
				@move_uploaded_file($_FILES['image']['tmp_name'],$_ENV['userfilePath'].$mPoll->userfile.'/'.$idx.'.file');
				GetThumbnail($_ENV['userfilePath'].$mPoll->userfile.'/'.$idx.'.file',$_ENV['userfilePath'].$mPoll->thumbnail.'/'.$idx.'.thm',200,0);
			}
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
}
?>