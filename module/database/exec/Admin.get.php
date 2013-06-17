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
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$lists = array();
$return = array();

$mDatabase = new ModuleDatabase();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'user') {
	if ($get == 'list') {
		$find = '';
		$total = $mDB->DBcount($mDatabase->table['table'],$find);
		$lists = $mDB->DBfetchs($mDatabase->table['table'],'*',$find);
	
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			if ($mDB->DBfind($lists[$i]['name'],$lists[$i]['database']) == true) {
				$lists[$i]['dbsize'] = $mDB->DBsize($lists[$i]['name'],$lists[$i]['database']);
				$lists[$i]['filesize'] = array_pop($mDB->DBfetch($mDatabase->table['file'],array('SUM(filesize)'),"where `tno`={$lists[$i]['idx']}"));
				$lists[$i]['record'] = $mDB->DBcount($lists[$i]['name'],'',$lists[$i]['database']);
			}
		}
	}
	
	if ($get == 'field') {
		$idx = Request('idx');
		$data = $mDB->DBfetch($mDatabase->table['table'],array('field'),"where `idx`='$idx'");
		
		$return['success'] = true;
		$return['field'] = unserialize($data['field']);
		exit(json_encode($return));
	}
	
	if ($get == 'info') {
		$idx = Request('idx');
		$data = $mDB->DBfetch($mDatabase->table['table'],array('name','info','database'),"where `idx`='$idx'");
		
		$return['success'] = true;
		$return['data'] = $data;
		exit(json_encode($return));
	}
	
	if ($get == 'fieldlist') {
		$idx = Request('idx');
		
		if ($idx == '0') {
			$lists[] = array('name'=>'idx','info'=>'고유값','type'=>'INT','length'=>'11','option'=>'AUTO_INCREMENT','index'=>'PRIMARY');
		} else {
			$data = $mDB->DBfetch($mDatabase->table['table'],array('field'),"where `idx`='$idx'");
			$lists = unserialize($data['field']);
		}
	}
	
	if ($get == 'record') {
		$mode = Request('mode');
		
		if ($mode == 'list') {
			$idx = Request('idx');
			$table = $mDB->DBfetch($mDatabase->table['table'],'*',"where `idx`='$idx'");
			
			$fileField = array();
			$htmlField = array();
			$field = unserialize($table['field']);
			for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
				if ($field[$i]['type'] == 'FILE') {
					$fileField[] = $field[$i]['name'];
				}
				if ($field[$i]['type'] == 'HTML') {
					$htmlField[] = $field[$i]['name'];
				}
			}
			
			$find = '';
			$total = $mDB->DBcount($table['name'],$find,$table['database']);
			$lists = $mDB->DBfetchs($table['name'],'*',$find,$orderer,$limiter,$table['database']);
			for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
				foreach ($fileField as $value) {
					if ($lists[$i][$value] != '0') {
						$file = $mDB->DBfetch($mDatabase->table['file'],array('filename','filesize','hit'),"where `idx`='{$lists[$i][$value]}'");
						$lists[$i][$value] = $file['filename'].' <span style="color:#888888;">('.GetFileSize($file['filesize']).', <span style="color:#EF5600;">'.number_format($file['hit']).'</span>Hits)</span>';
					} else {
						$lists[$i][$value] = '';
					}
				}
				foreach ($htmlField as $value) {
					$lists[$i][$value] = str_replace('{$moduleHost}','http://'.$_SERVER['HTTP_HOST'],$lists[$i][$value]);
					$lists[$i][$value] = str_replace('{$moduleDir}',$mDatabase->moduleDir,$lists[$i][$value]);
				}
			}
		}
		
		if ($mode == 'data') {
			$tno = Request('tno');
			$primary = Request('primary');
			$idx = Request('idx');
			
			$table = $mDB->DBfetch($mDatabase->table['table'],'*',"where `idx`='$tno'");
			$data = $mDB->DBfetch($table['name'],'*',"where `$primary`='$idx'",'','',$table['database']);
			
			$field = unserialize($table['field']);
			for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
				if ($field[$i]['type'] == 'HTML') {
					$data[$field[$i]['name']] = str_replace('{$moduleHost}','http://'.$_SERVER['HTTP_HOST'],$data[$field[$i]['name']]);
					$data[$field[$i]['name']] = str_replace('{$moduleDir}',$mDatabase->moduleDir,$data[$field[$i]['name']]);
				} elseif ($field[$i]['type'] == 'FILE') {
					if ($data[$field[$i]['name']] == '0') {
						$data[$field[$i]['name']] = '';
					} else {
						$file = $mDB->DBfetch($mDatabase->table['file'],array('filename','filesize'),"where `idx`='{$data[$field[$i]['name']]}'");
						$data[$field[$i]['name']] = $file['filename'].' ('.GetFileSize($file['filesize']).')';
					}
				}
			}
			$return['success'] = true;
			$return['data'] = $data;
			exit(json_encode($return));
		}
	}
}

if ($action == 'field') {
	$idx = Request('idx');
	$data = $mDB->DBfetch($mDatabase->table['table'],array('field'),"where `idx`=$idx");
	$field = isset($data['field']) == true ? unserialize($data['field']) : array();

	for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
		$field[$i]['newname'] = $field[$i]['name'];
		$field[$i]['sort'] = $i;
	}
	$list = GetArrayToExtData($field);
}

if ($action == 'item') {
	if ($get == 'list') {
		$idx = Request('idx');
		$key = Request('key');
		$keyword = Request('keyword');
		$table = $mDB->DBfetch($mDatabase->table['table'],array('name','field'),"where `idx`=$idx");

		$fields = unserialize($table['field']);
		$find = '';
		if ($key && $keyword) {
			$find = "where `$key` like '%$keyword%'";
		}
		$data = $mDB->DBfetchs($table['name'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($table['name'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = array();
			foreach ($fields as $key=>$field) {
				if ($field['type'] == 'FILE') {
					if ($data[$i][$field['name']]) {
						$file = $mDB->DBfetch($mDatabase->table['file'],array('idx','filename','filepath','filesize','hit'),"where `idx`={$data[$i][$field['name']]}");
						$list[$i][] = '"'.$field['name'].'":"'.$file['idx'].'|'.GetString($file['filename'],'ext').'|'.$file['filepath'].'|'.$file['filesize'].'|'.$file['hit'].'"';
					}
				} elseif ($field['type'] == 'TEXT' || $field['type'] == 'HTML') {
					$list[$i][] = '"'.$field['name'].'":"'.GetCutString(GetString(strip_tags($data[$i][$field['name']]),'extreplace'),50).'"';
				} else {
					$list[$i][] = '"'.$field['name'].'":"'.GetString(strip_tags($data[$i][$field['name']]),'extreplace').'"';
				}
			}
			$list[$i] = '{'.implode(',',$list[$i]).'}';
		}
	}

	if ($get == 'data') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$tno = Request('tno');
		$idx = Request('idx');

		$table = $mDB->DBfetch($mDatabase->table['table'],array('name','field'),"where `idx`=$tno");
		$data = $mDB->DBfetch($table['name'],'*',"where `idx`=$idx");

		$field = unserialize($table['field']);

		$item = array();
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if ($field[$i]['type'] == 'HTML') {
				$item[$field[$i]['name']] = str_replace('{$moduleDir}',$mDatabase->moduleDir,$data[$field[$i]['name']]);
			} elseif ($field[$i]['type'] == 'FILE') {
				if ($data[$field[$i]['name']] && $data[$field[$i]['name']] != '0') {
					$file = $mDB->DBfetch($mDatabase->table['file'],array('filename','filesize'),"where `idx`={$data[$field[$i]['name']]}");
					$item[$field[$i]['name']] = $file['filename'].' ('.GetFileSize($file['filesize']).') 파일이 등록되어 있습니다.';
				}
			} else {
				$item[$field[$i]['name']] = $data[$field[$i]['name']];
			}
		}

		echo GetArrayToExtXML($item,true);
		exit;
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>