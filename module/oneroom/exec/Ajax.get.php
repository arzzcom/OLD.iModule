<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset=UTF-8', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$bid = Request('bid');
$action = Request('action');
$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();
$mOneroom = new ModuleOneroom();

$returnXML = '<?xml version="1.0" encoding="UTF-8" ?><Ajax>';

if ($action == 'check_register_number') {
	$type = Request('type');
	$register_number = Request('register_number');
	
	if ($mOneroom->CheckRegisterNumber($register_number) == false) {
		$returnXML.= '<result result="FALSE" msg="사업자 등록번호가 잘못 입력되었습니다." />';
	} else {
		if ($type == 'agent') {
			if ($mDB->DBcount($mOneroom->table['agent'],"where `register_number`='$register_number'") == 0) {
				$returnXML.= '<result result="TRUE" msg="사용가능한 사업자등록번호입니다." />';
			} else {
				$returnXML.= '<result result="FALSE" msg="이미 등록된 사업자등록번호입니다." />';
			}
		} else {
			$check = $mDB->DBfetch($mOneroom->table['agent'],array('title'),"where `register_number`='$register_number'");
			if (isset($check['title']) == true) {
				$returnXML.= '<result result="TRUE" msg="['.$check['title'].']의 중개담당자로 등록합니다." />';
			} else {
				$returnXML.= '<result result="FALSE" msg="사업자등록번호를 찾을 수 없습니다. 등록할 중개업소의 사업자등록번호를 확인하여 주십시오." />';
			}
		}
	}
}

if ($action == 'region') {
	$parent = Request('parent');
	
	$data = $mDB->DBfetchs($mOneroom->table['region'],array('idx','title'),"where `parent`='$parent'",'sort,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$returnXML.= '<item idx="'.$data[$i]['idx'].'" title="'.GetString($data[$i]['title'],'xml').'" />';
	}
}

if ($action == 'university') {
	$parent = Request('parent');
	
	$data = $mDB->DBfetchs($mOneroom->table['university'],array('idx','title'),"where `parent`='$parent'",'sort,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$itemnum = $mDB->DBcount($mOneroom->table['item'],"where `university`='{$data[$i]['idx']}'");
		$returnXML.= '<item idx="'.$data[$i]['idx'].'" title="'.GetString($data[$i]['title'],'xml').'" itemnum="'.$itemnum.'" />';
	}
}

if ($action == 'subway') {
	$parent = Request('parent');
	
	$data = $mDB->DBfetchs($mOneroom->table['subway'],array('idx','title'),"where `parent`='$parent'",'sort,asc');
	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$itemnum = $mDB->DBcount($mOneroom->table['item'],"where `subway`='{$data[$i]['idx']}'");
		$returnXML.= '<item idx="'.$data[$i]['idx'].'" title="'.GetString($data[$i]['title'],'xml').'" itemnum="'.$itemnum.'" />';
	}
}

$returnXML .= '</Ajax>';

echo $returnXML;
?>