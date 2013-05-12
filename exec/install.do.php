<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');

if ($action == 'update') {
	if ($member['type'] != 'ADMINISTRATOR') {
		Alertbox('최고관리자만 가능합니다.');
	}
	
	$XMLData = file_get_contents($_ENV['path'].'/index.xml');
	$XML = new SimpleXMLElement($XMLData);
	
	$table = $XML->database->table;
	for ($i=0, $loop=sizeof($table);$i<$loop;$i++) {
		$tablename = str_replace('{code}',$_ENV['code'],(string)($table[$i]->attributes()->name));

		$field = $table[$i]->field;
		$fields = array();
		for ($j=0, $loopj=sizeof($field);$j<$loopj;$j++) {
			$fields[$j] = array('name'=>(string)($field[$j]->attributes()->name),'type'=>(string)($field[$j]->attributes()->type),'length'=>(string)($field[$j]->attributes()->length),'default'=>(string)($field[$j]->attributes()->default),'comment'=>(string)($field[$j]));
		}
		
		$index = $table[$i]->index;
		$indexes = array();
		for ($j=0, $loopj=sizeof($index);$j<$loopj;$j++) {
			$indexes[$j] = array('name'=>(string)($index[$j]->attributes()->name),'type'=>(string)($index[$j]->attributes()->type),'comment'=>(string)($index[$j]));
		}
		
		if ($mDB->DBFind($tablename) == true) {
			if ($mDB->DBcompare($tablename,$fields,$indexes) == false) {
				if ($mDB->DBFind($tablename.'(NEW)') == true) {
					$mDB->DBremove($tablename.'(NEW)');
				}
							
				if ($mDB->DBcreate($tablename.'(NEW)',$fields,$indexes) == true) {
					$data = $mDB->DBfetchs($tablename,'*');
					for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
						$insert = array();
						for ($k=0, $loopk=sizeof($fields);$k<$loopk;$k++) {
							if (isset($data[$j][$fields[$k]['name']]) == true) $insert[$fields[$k]['name']] = $data[$j][$fields[$k]['name']];
						}
						
						$mDB->DBinsert($tablename.'(NEW)',$insert);
					}
					
					$mDB->DBname($tablename,$tablename.'(BK'.date('YmdHis').')');
					$mDB->DBname($tablename.'(NEW)',$tablename);
				}
			}
		} else {
			if ($mDB->DBcreate($tablename,$fields,$indexes) == true) {
				$data = isset($table[$i]->data) == true ? $table[$i]->data : array();
				
				for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
					$insert = array_pop(array_values((array)($data[$j]->attributes())));
					$mDB->DBinsert($tablename,$insert);
				}
			}
		}
	}

	Alertbox('업데이트를 완료하였습니다.',3,'reload','parent');
}
