<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset="UTF-8"', true);
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action == Request('action');
$get = Request('get');
$form = array();

$mErp = new ModuleErp();

if ($mErp->IsAdmin() == false) {
	echo '<message success="false"><form>';
	echo '<errormsg>관리권한이 없습니다.</errormsg>';
	echo '</form></message>';
	exit;
}

if ($action == 'workspace') {
	$data = $mDB->DBfetch($mErp->table['workspace'],'*',"where `idx`=$get");

	if ($data['workspace_master']) {
		$members = $mDB->DBfetchs($_ENV['table']['member'],array('idx','name'),"where `idx` IN ({$data['workspace_master']})",'name,asc');
		$workspace_master = array();
		$workspace_master_view = array();
		for ($i=0, $loopj=sizeof($members);$i<$loopj;$i++) {
			$workspace_master[] = $members[$i]['idx'].':'.$members[$i]['name'];
			$workspace_master_view[] = $members[$i]['name'];
		}
		$workspace_master = implode('@',$workspace_master);
		$workspace_master_view = implode(',',$workspace_master_view);
	} else {
		$workspace_master = $workspace_master_view = '';
	}
	$form[] = '<idx>'.$data['idx'].'</idx>';
	$form[] = '<title>'.GetString($data['title'],'xml').'</title>';
	$form[] = '<orderer>'.GetString($data['orderer'],'xml').'</orderer>';
	$form[] = '<contract_date>'.($data['contract_date'] != '1970-01-01' ? $data['contract_date'] : '').'</contract_date>';
	$form[] = '<workstart_date>'.($data['workstart_date'] != '1970-01-01' ? $data['workstart_date'] : '').'</workstart_date>';
	$form[] = '<workend_date>'.($data['workend_date'] != '1970-01-01' ? $data['workend_date'] : '').'</workend_date>';
	$form[] = '<contract_money>'.$data['contract_money'].'</contract_money>';
	$form[] = '<area>'.$data['area'].'</area>';
	$form[] = '<totalarea>'.$data['totalarea'].'</totalarea>';
	$form[] = '<size>'.GetString($data['size'],'xml').'</size>';
	$form[] = '<structure>'.GetString($data['structure'],'xml').'</structure>';
	$form[] = '<buildarea>'.$data['buildarea'].'</buildarea>';
	$form[] = '<buildpercent>'.$data['buildpercent'].'</buildpercent>';
	$form[] = '<buildingcoverage>'.$data['buildingcoverage'].'</buildingcoverage>';
	$form[] = '<purpose>'.GetString($data['purpose'],'xml').'</purpose>';
	$form[] = '<zone>'.GetString($data['zone'],'xml').'</zone>';
	$form[] = '<workspace_zipcode>'.$data['workspace_zipcode'].'</workspace_zipcode>';
	$form[] = '<workspace_address1>'.array_shift(explode('||',$data['workspace_address'])).'</workspace_address1>';
	$form[] = '<workspace_address2>'.array_pop(explode('||',$data['workspace_address'])).'</workspace_address2>';
	$form[] = '<workspace_telephone>'.$data['workspace_telephone'].'</workspace_telephone>';
	$form[] = '<workspace_master>'.$workspace_master.'</workspace_master>';
	$form[] = '<workspace_master_view>'.$workspace_master_view.'</workspace_master_view>';
	$form[] = '<architects>'.GetString($data['architects'],'xml').'</architects>';

}

echo '<message success="'.(sizeof($form) > 0 ? 'true' : 'false').'"><form>';
echo implode('',$form);
echo '</form></message>';
?>