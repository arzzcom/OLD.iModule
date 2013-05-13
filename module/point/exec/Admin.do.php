<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mPoint = new ModulePoint();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	$return['message'] = '관리권한이 없습니다.';
	
	exit(json_encode($return));
}

if ($action == 'payment') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		$insert['type'] = Request('type');
		
		switch ($insert['type']) {
			case 'BANKING' :
				$insert['value'] = Request('banking1').' '.Request('banking2').' ('.Request('banking3').')';
				break;
		}
		$insert['is_use'] = Request('is_use') == 'on' ? 'TRUE' : 'FALSE';
		$insert['min_point'] = Request('min_point');
		$insert['max_point'] = Request('max_point');
		
		if ($do == 'add') {
			if (sizeof($errors) == 0) {
				$mDB->DBinsert($mPoint->table['payment'],$insert);
				$return['success'] = true;
				SaveAdminLog('point','['.$insert['type'].']결제방식을 추가하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		} else {
			$idx = Request('idx');

			if (sizeof($errors) == 0) {
				$mDB->DBupdate($mPoint->table['payment'],$insert,'',"where `idx`='$idx'");
				$return['success'] = true;
				SaveAdminLog('board','[#'.$idx.'] 결제방식을 수정하였습니다.');
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		}

		exit(json_encode($return));
	}
	
	if ($do == 'activemode') {
		$idx = Request('idx');
		$value = Request('value');
		$mDB->DBupdate($mPoint->table['payment'],array('is_use'=>$value),'',"where `idx` IN ($idx)");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = Request('idx');
		$mDB->DBdelete($mPoint->table['payment'],"where `idx` IN ($idx)");
		$mDB->DBdelete($mPoint->table['buy'],"where `payment` IN ($idx)");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}
?>