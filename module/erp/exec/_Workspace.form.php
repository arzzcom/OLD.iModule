<?php
REQUIRE_ONCE '../../../config/default.conf.php';

header('Content-type: text/xml; charset="UTF-8"',true);
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

if ($action == 'attend') {
	if ($get == 'worker') {
		$idx = Request('idx');
		$workerspace = $mDB->DBfetch($mErp->table['workerspace'],'*',"where `idx`=$idx");
		$workerspace['pno'] = isset($workerspace['pno']) == true ? $workerspace['pno'] : '0';
		$data = $mDB->DBfetch($mErp->table['worker'],'*',"where `idx`={$workerspace['pno']}");

		$form[] = '<idx>'.$idx.'</idx>';
		$form[] = '<name>'.GetString($data['name'],'xml').'</name>';
		$form[] = '<grade>'.GetString(array_shift(explode('||',$data['grade'])),'xml').'</grade>';
		$form[] = '<grade_handwrite>'.GetString(array_pop(explode('||',$data['grade'])),'xml').'</grade_handwrite>';
		$form[] = '<jumin>'.$data['jumin'].'</jumin>';
		$form[] = '<contract_date>'.($data['contract_date'] != '1970-01-01' ? $data['contract_date'] : '').'</contract_date>';
		$form[] = '<enter_date>'.($data['enter_date'] != '1970-01-01' ? $data['enter_date'] : '').'</enter_date>';
		$form[] = '<retire_date>'.($data['retire_date'] != '1970-01-01' ? $data['retire_date'] : '').'</retire_date>';
		$form[] = '<workstart_date>'.$workerspace['workstart_date'].'</workstart_date>';
		$form[] = '<zipcode>'.$data['zipcode'].'</zipcode>';
		$form[] = '<address1>'.GetString(array_shift(explode('||',$data['address'])),'xml').'</address1>';
		$form[] = '<address2>'.GetString(array_pop(explode('||',$data['address'])),'xml').'</address2>';
		$form[] = '<pay_type>'.$data['pay_type'].'</pay_type>';
		$form[] = '<work_type>'.$data['work_type'].'</work_type>';
		$form[] = '<payment>'.number_format($data['payment']).'</payment>';
		$form[] = '<account_name>'.GetString(array_shift(explode('||',$data['account'])),'xml').'</account_name>';
		$form[] = '<account_bank>'.GetString(implode('',array_slice(explode('||',$data['account']),1,1)),'xml').'</account_bank>';
		$form[] = '<account_number>'.GetString(array_pop(explode('||',$data['account'])),'xml').'</account_number>';
		$form[] = '<telephone>'.$data['telephone'].'</telephone>';
		$form[] = '<cellphone>'.$data['cellphone'].'</cellphone>';
		$form[] = '<photo>'.(file_exists($_ENV['path'].'/userfile/erp/worker/'.$data['idx'].'.jpg') == true ? $_ENV['dir'].'/userfile/erp/worker/'.$data['idx'].'.jpg' : '').'</photo>';
	}

}

echo '<message success="'.(sizeof($form) > 0 ? 'true' : 'false').'"><form>';
echo implode('',$form);
echo '</form></message>';
?>