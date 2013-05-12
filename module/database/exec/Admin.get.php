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

$list = array();

$mDatabase = new ModuleDatabase();

if ($action == 'list') {
	$find = '';
	$data = $mDB->DBfetchs($mDatabase->table['table'],'*',$find);
	$total = $mDB->DBfetchs($mDatabase->table['table'],'*',$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$filesize = $mDB->DBfetch($mDatabase->table['file'],array('SUM(filesize)'),"where `tno`={$data[$i]['idx']}");
		$filesize = $filesize[0];
		$list[$i] = '{';
		$list[$i].= '"group":"group",';
		$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
		$list[$i].= '"name":"'.$data[$i]['name'].'",';
		$list[$i].= '"info":"'.GetString($data[$i]['info'],'ext').'",';
		$list[$i].= '"record":"'.$mDB->DBcount($data[$i]['name']).'",';
		$list[$i].= '"dbsize":"'.$mDB->DBsize($data[$i]['name']).'",';
		$list[$i].= '"filesize":"'.$filesize.'"';
		$list[$i].= '}';
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

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>