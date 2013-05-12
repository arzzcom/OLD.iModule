<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');
$mDatabase = new ModuleDatabase();

if ($action == 'table') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add') {
		$Error = array();
		$field = GetExtData('field');
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			unset($field[$i]['newname']);
		}
		$insert['name'] = preg_match('/^[[:alnum:]_]+$/i',Request('name')) == true ? Request('name') : $Error['name'] = '영문과 숫자, 언더바(_)만 입력하여 주십시오.';
		$insert['info'] = Request('info') ? Request('info') : $Error['info'] = '테이블설명을 입력하여 주십시오.';
		$insert['field'] = serialize($field);

		if (sizeof($Error) == 0) {
			if ($mDB->DBcreate($insert['name'],$field) == true) {
				$mDB->DBinsert($mDatabase->table['table'],$insert);
			} else {
				$Error['name'] = '테이블을 생성할 수 없습니다.';
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}

	if ($do == 'modify') {
		$idx = Request('idx');
		$table = $mDB->DBfetch($mDatabase->table['table'],array('name','field'),"where `idx`=$idx");
		$oField = isset($table['field']) == true ? unserialize($table['field']) : array();
		$fField = array();
		for ($i=0, $loop=sizeof($oField);$i<$loop;$i++) {
			$fField[] = $oField[$i]['name'];
		}
		$nField = GetExtData('field');

		$sField = array();
		for ($i=0, $loop=sizeof($nField);$i<$loop;$i++) {
			$sField[] = $nField[$i]['name'];
		}

		for ($i=0, $loop=sizeof($fField);$i<$loop;$i++) {
			if (in_array($fField[$i],$sField) == false) {
				$mDB->DBdrop($table['name'],$fField[$i]);
			}
		}

		$cField = array();
		for ($i=0, $loop=sizeof($nField);$i<$loop;$i++) {
			if ($nField[$i]['name'] != $nField[$i]['newname']) {
				$fieldName = $nField[$i]['name'];
				$nField[$i]['name'] = $nField[$i]['newname'];
				unset($nField[$i]['newname']);
				$cField[] = $fieldName;
				$mDB->DBchange($table['name'],$fieldName,$nField[$i]);
				$prevField = $nField[$i]['name'];
			}
		}

		$prevField = 0;
		for ($i=0, $loop=sizeof($nField);$i<$loop;$i++) {
			if (isset($nField[$i]['newname']) == true && $nField[$i]['name'] == $nField[$i]['newname']) {
				$fieldName = $nField[$i]['name'];
				unset($nField[$i]['newname']);
				if (in_array($fieldName,$fField) == false || in_array($fieldName,$cField) == true) {
					$mDB->DBadd($table['name'],$nField[$i],$prevField);
				} else {
					$mDB->DBchange($table['name'],$fieldName,$nField[$i]);
				}
			}
			$prevField = $nField[$i]['name'];
		}

		$mDB->DBupdate($mDatabase->table['table'],array('field'=>serialize($nField)),'',"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		echo '<errors>';
		echo '<field><id>'.$idx.'</id></field>';
		echo '</errors>';

		echo '</message>';
	}

	if ($do == 'truncate') {
		$idx = Request('idx');
		$table = $mDB->DBfetchs($mDatabase->table['table'],'*',"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($table);$i<$loop;$i++) {
			$mDB->DBtruncate($table[$i]['name']);
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	if ($do == 'delete') {
		$idx = Request('idx');
		$table = $mDB->DBfetchs($mDatabase->table['table'],'*',"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($table);$i<$loop;$i++) {
			$mDB->DBremove($table[$i]['name']);
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

if ($action == 'item') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add' || $do == 'modify') {
		$tno = Request('tno');
		$Error = array();

		$insert = array();
		$data = $mDB->DBfetch($mDatabase->table['table'],array('name','field'),"where `idx`=$tno");

		$field = unserialize($data['field']);
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if (isset($field[$i]['option']) == false || $field[$i]['option'] != 'AUTO_INCREMENT') {
				if ($field[$i]['type'] == 'HTML') {
					$insert[$field[$i]['name']] = str_replace($mDatabase->moduleDir,'{$moduleDir}',Request($field[$i]['name']));
				} elseif ($field[$i]['type'] != 'FILE') {
					$insert[$field[$i]['name']] = Request($field[$i]['name']);
				}
			}
		}

		if ($do == 'add') {
			$idx = $mDB->DBinsert($data['name'],$insert);
			$oData = array();
		} else {
			$idx = Request('idx');
			$oData = $mDB->DBfetch($data['name'],'*',"where `idx`=$idx");
			for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
				if ($field[$i]['type'] == 'FILE' && Request($field[$i]['name'].'_delete') != null) {
					$insert[$field[$i]['name']] = '';
					if ($oData[$field[$i]['name']]) {
						$file = $mDB->DBfetch($mDatabase->table['file'],array('filepath'),"where `idx`={$oData[$field[$i]['name']]}");
						@unlink($_ENV['path'].$oFile['filepath']);
						$mDB->DBdelete($mDatabase->table['file'],"where `idx`={$oData[$field[$i]['name']]}");
						$oData[$field[$i]['name']] = '0';
					}
				}
			}
			$mDB->DBupdate($data['name'],$insert,'',"where `idx`=$idx");
		}

		$insert = array();
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if ($field[$i]['type'] == 'FILE' && isset($_FILES[$field[$i]['name']]) == true && $_FILES[$field[$i]['name']]['tmp_name'] != '') {
				$file = $_FILES[$field[$i]['name']];
				$filepath = '/userfile/database/'.$tno.'/'.md5_file($file['tmp_name']).'.'.time().'.'.rand(100000,999999);
				if (CreateDirectory($_ENV['path'].'/userfile/database/'.$tno) == true) {
					if (isset($oData[$field[$i]['name']]) == true && $oData[$field[$i]['name']] != '0') {
						$oFile = $mDB->DBfetch($mDatabase->table['file'],array('filepath'),"where `idx`={$oData[$field[$i]['name']]}");
						@unlink($_ENV['path'].$oFile['filepath']);
						$mDB->DBdelete($mDatabase->table['file'],"where `idx`={$oData[$field[$i]['name']]}");
					}
					@move_uploaded_file($file['tmp_name'],$_ENV['path'].$filepath);
					$fidx = $mDB->DBinsert($mDatabase->table['file'],array('type'=>'FILE','tno'=>$tno,'repto'=>$idx,'filename'=>$file['name'],'filepath'=>$filepath,'filetype'=>GetFileType($file['name'],$_ENV['path'].$filepath),'filesize'=>filesize($_ENV['path'].$filepath),'wysiwyg'=>''));
					$insert[$field[$i]['name']] = $fidx;
				}
			}
		}

		if (sizeof($insert) > 0) {
			$mDB->DBupdate($data['name'],$insert,'',"where `idx`=$idx");
		}

		$uploaderFile = Request('uploaderfile');
		for ($i=0, $loop=sizeof($uploaderfile);$i<$loop;$i++) {
			$temp = explode('|',$uploaderfile[$i]);
			$fidx = $temp[0];

			if (sizeof($temp) == 1) {
				$fileData = $mDB->DBfetch($mDatabase->table['file'],array('filepath','filetype'),"where `idx`=$fidx");
				@unlink($_ENV['path'].$fileData['filepath']);
				if ($fileData['filetype'] == 'IMG') @unlink($_ENV['path'].'/userfile/database/thumbneil/'.$fidx.'.thm');
				$mDB->DBdelete($mDatabase->table['file'],"where `idx`=$fidx");
			} else {
				$mDB->DBupdate($mDatabase->table['file'],array('repto'=>$idx),'',"where `idx`=$fidx");
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}
/*
	if ($do == 'modify') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$tidx = Request('tidx');
		$idx = Request('idx');
		$Error = array();
		$insert = array();
		$data = $mDB->DBfetch($mDatabase->table['table'],array('name','field'),"where `idx`=$tidx");
		$check = $mDB->DBfetch($data['name'],'*',"where `idx`=$idx");

		$field = unserialize($data['field']);
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if (isset($field[$i]['option']) == false || $field[$i]['option'] != 'AUTO_INCREMENT') {
				if ($field[$i]['type'] == 'FILE') {
					if (Request($field[$i]['name'].'_delete') && $check[$field[$i]['name']] && file_exists($_ENV['path'].$check[$field[$i]['name']]) == true) {
						@unlink($_ENV['path'].$check[$field[$i]['name']]);
					}
					if ($_FILES[$field[$i]['name']]['tmp_name']) {
						$file = $_FILES[$field[$i]['name']];
						$filepath = '/userfile/database/'.$tidx.'/'.md5_file($file['tmp_name']).'.'.rand(1000,9999).'.'.GetFileExec($file['name']);
						if (CreateDirectory($_ENV['path'].'/userfile/database/'.$tidx) == true) {
							@unlink($_ENV['path'].$check[$field[$i]['name']]);
							@move_uploaded_file($file['tmp_name'],$_ENV['path'].$filepath);
							$insert[$field[$i]['name']] = $filepath;
						}
					}
				} else {
					$insert[$field[$i]['name']] = Request($field[$i]['name']);
				}
			}
		}

		$mDB->DBupdate($data['name'],$insert,'',"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';

		if (sizeof($Error) > 0) {
			echo '<errors>';
			foreach ($Error as $id=>$msg) {
				echo '<field><id>'.$id.'</id><msg><![CDATA['.$msg.']]></msg></field>';
			}
			echo '</errors>';
		} else {
			echo '<errors>';
			echo '<field><id>'.$idx.'</id></field>';
			echo '</errors>';
		}

		echo '</message>';
	}
*/

	if ($do == 'delete') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = explode(',',Request('idx'));
		$tno = Request('tno');

		$table = $mDB->DBfetch($mDatabase->table['table'],array('name','field'),"where `idx`=$tno");

		$primary == null;
		$files = array();
		$field = unserialize($table['field']);
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if (isset($field[$i]['index']) == true && $field[$i]['index'] == 'PRIMARY') {
				$primary = $field[$i]['name'];
			}

			if ($field[$i]['type'] == 'FILE') {
				$files[] = $field[$i]['name'];
			}
		}

		if ($primary != null) {
			for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
				if (sizeof($files) > 0) {
					$data = $mDB->DBfetch($table['name'],$files,"where `$primary`={$idx[$i]}");

					for ($j=0, $loopj=sizeof($files);$j<$loopj;$j++) {
						if ($data[$files[$j]] && file_exists($_ENV['path'].$data[$files[$j]]) == true) {
							@unlink($_ENV['path'].$data[$files[$j]]);
						}
					}
				}

				$mDB->DBdelete($table['name'],"where `$primary`={$idx[$i]}");
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}
?>