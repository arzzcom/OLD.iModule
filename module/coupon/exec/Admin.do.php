<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mCoupon = new ModuleCoupon();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($action == 'category') {
	if ($do == 'add') {
		$insert = array();
		$insert['category'] = Request('category');
		
		if ($mDB->DBcount($mCoupon->table['category'],"where `category`='{$insert['category']}'") > 0) {
			$errors['category'] = '카테고리명이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($mCoupon->table['category']);
			$idx = $mDB->DBinsert($mCoupon->table['category'],$insert);
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['category'] = $insert['category'];
			$return['sort'] = $insert['sort'];
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'modify') {
		$data = json_decode(Request('data'),true);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$idx = $data[$i]['idx'];
			unset($data[$i]['idx']);
			$mDB->DBupdate($mCoupon->table['category'],$data[$i],'',"where `idx`='$idx'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));

		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mCoupon->table['category'],"where `idx`='{$idx[$i]}'");
			
			$item = $mDB->DBfetchs($mCoupon->table['item'],array('idx'),"where `category`='{$idx[$i]}'");
			for ($j=0, $loopj=sizeof($item);$j<$loopj;$j++) {
				$mDB->DBdelete($mCoupon->table['item'],"where `idx`='{$item[$j]['idx']}'");
				@unlink($_ENV['userfilePath'].$mCoupon->userfile.'/'.$item[$j]['idx'].'.gif');
			}
		}
		
		$category = $mDB->DBfetchs($mCoupon->table['category'],array('idx','sort'),'','sort,asc');
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$mDB->DBupdate($mCoupon->table['category'],array('sort'=>$i),'',"where `idx`='{$category[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'item') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		$insert['category'] = Request('category');
		$insert['title'] = Request('title');
		$insert['code'] = Request('code');
		$insert['infor'] = Request('infor');
		$insert['content'] = Request('content');
		$insert['point'] = Request('point');
		$insert['ea'] = Request('ea');
		$insert['expire'] = Request('expire');
		$insert['is_new'] = Request('is_new') == 'on' ? 'TRUE' : 'FALSE';
		$insert['is_vote'] = Request('is_vote') == 'on' ? 'TRUE' : 'FALSE';
		$insert['is_gift'] = Request('is_gift') == 'on' ? 'TRUE' : 'FALSE';
		
		if ($do == 'add') {
			if ($mDB->DBcount($mCoupon->table['item'],"where `code`='{$insert['code']}'") > 0) {
				$errors['code'] = '쿠폰코드가 중복됩니다.';
			}
		} else {
			$idx = Request('idx');
			if ($mDB->DBcount($mCoupon->table['item'],"where `code`='{$insert['code']}' and `idx`!='$idx'") > 0) {
				$errors['code'] = '쿠폰코드가 중복됩니다.';
			}
		}
		
		if (isset($_FILES['file']['tmp_name']) == true && $_FILES['file']['tmp_name']) {
			$filetype = getimagesize($_FILES['file']['tmp_name']);
			if ($filetype[2] != 1) {
				$errors['file'] = '쿠폰이미지는 GIF파일만 업로드 가능합니다.';
			}
		}
		
		if (sizeof($errors) == 0) {
			if ($do == 'add') {
				$idx = $mDB->DBinsert($mCoupon->table['item'],$insert);
				@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['userfilePath'].$mCoupon->userfile.'/'.$idx.'.gif');
				@chmod($_ENV['userfilePath'].$mCoupon->userfile.'/'.$idx.'.gif',0707);
			} else {
				if (isset($_FILES['file']['tmp_name']) == true && $_FILES['file']['tmp_name']) {
					@unlink($_ENV['userfilePath'].$mCoupon->userfile.$idx.'.gif');
					@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['userfilePath'].$mCoupon->userfile.'/'.$idx.'.gif');
					@chmod($_ENV['userfilePath'].$mCoupon->userfile.'/'.$idx.'.gif',0707);
				}
				
				$data = $mDB->DBfetch($mCoupon->table['item'],array('code'),"where `idx`='$idx'");
				if ($data['code'] != $insert['code']) {
					$mDB->DBupdate($mCoupon->table['user'],array('code'=>$insert['code']),'',"where `code`='{$data['code']}'");
				}
				$mDB->DBupdate($mCoupon->table['item'],$insert,'',"where `idx`='$idx'");
			}
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$data = $mDB->DBfetch($mCoupon->table['item'],'*',"where `idx`='{$idx[$i]}'");
			$mDB->DBdelete($mCoupon->table['item'],"where `idx`='{$data['idx']}'");
			$mDB->DBdelete($mCoupon->table['user'],"where `code`='{$data['code']}'");
			
			@unlink($_ENV['userfilePath'].$mCoupon->userfile.'/'.$data['idx'].'.gif');
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}
?>