<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');
$mDatabase = new ModuleDatabase();

$return = array();
$errors = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'user') {
	function CheckField($field) {
		if ($field['type'] == 'SELECT') {
			$temp = explode(',',$field['length']);
			for ($i=0, $loop=sizeof($temp);$i<$loop;$i++) {
				if (preg_match('/^\'[^\']+\'$/',$temp[$i]) == false) {
					return false;
				}
			}
		}
		
		if ($field['type'] != 'INT') {
			if ($field['option'] == 'AUTO_INCREMENT') {
				$field['option'] = '';
			}
		}
		
		if (in_array($field['type'],array('FILE','TEXT','HTML','DATE')) == true) {
			$field['length'] = '';
		}
		
		if (in_array($field['type'],array('SELECT','FILE','TEXT','HTML','DATE')) == true) {
			$field['option'] = '';
		}
		
		if (in_array($field['type'],array('SELECT','FILE','DATE')) == true) {
			$field['default'] = '';
		}
		
		return $field;
	}
	
	function SetDatabaseField($field) {
		$setField = array('name'=>$field['name'],'type'=>'','length'=>'','default'=>'','comment'=>$field['info']);
		if (in_array($field['type'],array('INT','FILE')) == true) {
			$setField['type'] = 'int';
			$setField['length'] = $field['type'] == 'FILE' ? '11' : $field['length'];
			$setField['default'] = $field['default'] ? $field['default'] : '0';
		} elseif ($field['type'] == 'VARCHAR') {
			$setField['type'] = 'varchar';
			$setField['length'] = $field['length'];
			$setField['default'] = $field['default'];
		} elseif (in_array($field['type'],array('TEXT','HTML')) == true) {
			$setField['type'] = 'longtext';
			$setField['default'] = '';
			$setField['index'] = '';
		} elseif ($field['type'] == 'DATE') {
			$setField['type'] = 'date';
			$setField['default'] = '0000-00-00';
		} elseif ($field['type'] == 'SELECT') {
			$setField['type'] = 'enum';
			$setField['length'] = '\'\','.$field['length'];
			$setField['default'] = '';
		}
		
		return $setField;
	}
	
	function SetDatabaseIndex($fields) {
		$setIndex = array();
		
		for ($i=0, $loop=sizeof($fields);$i<$loop;$i++) {
			if ($fields[$i]['option'] == 'AUTO_INCREMENT') {
				$setIndex[] = array('name'=>$fields[$i]['name'],'type'=>'auto_increment');
			} elseif ($fields[$i]['index'] == 'BTREE') {
				$setIndex[] = array('name'=>$fields[$i]['name'],'type'=>'index');
			} elseif ($fields[$i]['index'] == 'UNIQUE') {
				$setIndex[] = array('name'=>$fields[$i]['name'],'type'=>'index');
			} elseif ($fields[$i]['index'] == 'PRIMARY') {
				$setIndex[] = array('name'=>$fields[$i]['name'],'type'=>'primary');
			}
		}
		
		return $setIndex;
	}
	
	if ($do == 'add') {
		$database = Request('database') ? Request('database') : 'default';
		$name = Request('name');
		$info = Request('info');
		$field = json_decode(Request('field'),true);
		
		if ($mDB->DBfind($name,$database) == true) {
			$errors['name'] = '이미 생성되어 있는 테이블명입니다.';
		}
		
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			$field[$i] = CheckField($field[$i]);
			if ($field[$i] == false) {
				$errors['message'] = '필드설정이 잘못되었습니다.';
				break;
			}
			$databaseField[$i] = SetDatabaseField($field[$i]);
		}
		$databaseIndex = SetDatabaseIndex($field);
		
		if (sizeof($errors) == 0) {
			$mDB->DBcreate($name,$databaseField,$databaseIndex,$database);
			$mDB->DBinsert($mDatabase->table['table'],array('name'=>$name,'database'=>$database,'info'=>$info,'field'=>serialize($field)));
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'modify') {
		$idx = Request('idx');
		$data = $mDB->DBfetch($mDatabase->table['table'],'*',"where `idx`='$idx'");
		
		$oField = unserialize($data['field']);
		$database = Request('database') ? Request('database') : 'default';
		$name = Request('name');
		$info = Request('info');
		
		if (($data['name'] != $name || $data['database'] != $database) && $mDB->DBfind($name,$database) == true) {
			$errors['name'] = '이미 생성되어 있는 테이블명입니다.';
		}

		$sort = array('first');
		
		$field = json_decode(Request('field'),true);
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			$field[$i] = CheckField($field[$i]);
			if ($field[$i] == false) {
				$errors['message'] = '필드설정이 잘못되었습니다.';
				break;
			}
			$sort[] = $field[$i]['name'];
		}
		
		if (sizeof($errors) == 0) {
			$delete = json_decode(Request('delete'),true);
			for ($i=0, $loop=sizeof($delete);$i<$loop;$i++) {
				$mDB->FDdrop($name,$delete[$i],$database);
			}
			
			$update = json_decode(Request('update'),true);
			for ($i=0, $loop=sizeof($update);$i<$loop;$i++) {
				$updateField = SetDatabaseField($update[$i]['update']);
				if ($update[$i]['origin']['name'] != $updateField['name']) {
					$updateField['name'] = $updateField['name'].'-TEMP';
				}
				$mDB->FDchange($name,$update[$i]['origin']['name'],$updateField,'',$database);
			}
			
			for ($i=0, $loop=sizeof($update);$i<$loop;$i++) {
				$updateField = SetDatabaseField($update[$i]['update']);
				if ($update[$i]['origin']['name'] != $updateField['name']) {
					$update[$i]['origin']['name'] = $updateField['name'].'-TEMP';
					$mDB->FDchange($name,$update[$i]['origin']['name'],$updateField,'',$database);
				}
				if ($update[$i]['origin']['option'] != $update[$i]['update']['option']) {
					$mDB->IDdrop($name,$update[$i]['update']['name'],$database);
				}
			}
			
			$fieldList = $mDB->FDlist($name,$database);
			for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
				if (in_array($field[$i]['name'],$fieldList) == false) {
					$mDB->FDadd($name,SetDatabaseField($field[$i]),'',$database);
				}
			}
			
			for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
				$mDB->FDchange($name,$field[$i]['name'],SetDatabaseField($field[$i]),$sort[$i],$database);
				
				if ($field[$i]['index'] == '') {
					$mDB->IDdrop($name,$field[$i]['name'],$database);
				} elseif ($field[$i]['option'] == 'AUTO_INCREMENT') {
					$mDB->IDadd($name,array('name'=>$field[$i]['name'],'type'=>'auto_increment'),$database);
				} elseif ($field[$i]['index'] == 'BTREE') {
					$mDB->IDadd($name,array('name'=>$field[$i]['name'],'type'=>'index'),$database);
				} elseif ($field[$i]['index'] == 'UNIQUE') {
					$mDB->IDadd($name,array('name'=>$field[$i]['name'],'type'=>'index'),$database);
				} elseif ($field[$i]['index'] == 'PRIMARY') {
					$mDB->IDadd($name,array('name'=>$field[$i]['name'],'type'=>'primary'),$database);
				}
			}
			
			$mDB->DBupdate($mDatabase->table['table'],array('name'=>$name,'database'=>$database,'info'=>$info,'field'=>serialize($field)),'',"where `idx`='$idx'");
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'record') {
		$mode = Request('mode');
		$tno = Request('tno');
		$primary = Request('primary');
		
		$table = $mDB->DBfetch($mDatabase->table['table'],array('name','database','field'),"where `idx`='$tno'");
		$field = unserialize($table['field']);
		if ($mode == 'add' || $mode == 'modify') {
			$insert = array();
			for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
				if ($field[$i]['option'] != 'AUTO_INCREMENT') {
					if ($field[$i]['type'] == 'HTML') {
						$value = Request($field[$i]['name']);
						$value = str_replace('http://'.$_SERVER['HTTP_HOST'],'{$moduleHost}',$value);
						$value = str_replace($mDatabase->moduleDir,'{$moduleDir}',$value);
						$insert[$field[$i]['name']] = $value;
					} else if ($field[$i]['type'] != 'FILE') {
						$insert[$field[$i]['name']] = Request($field[$i]['name']);
					}
				}
			}
			
			if ($mode == 'add') {
				$idx = $mDB->DBinsert($table['name'],$insert,'',$table['database']);
			} else {
				$idx = Request('idx');
				$oData = $mDB->DBfetch($table['name'],'*',"where `$primary`='$idx'",'','',$table['database']);
				$mDB->DBupdate($table['name'],$insert,'',"where `$primary`='$idx'",$table['database']);
			}
			
			if ($idx) {
				for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
					if ($field[$i]['type'] == 'HTML') {
						$attach = Request($field[$i]['name'].'-Uploader-files');
						if ($attach != null) {
							for ($i=0, $loop=sizeof($attach);$i<$loop;$i++) {
								$temp = explode('|',$attach[$i]);
								$fidx = $temp[0];
					
								if (sizeof($temp) == 1) {
									$fileData = $mDB->DBfetch($mDatabase->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
									@unlink($_ENV['userfilePath'].$mDatabase->userfile.$fileData['filepath']);
									if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mDatabase->thumbnail.'/'.$fidx.'.thm');
									$mDB->DBdelete($mDatabase->table['file'],"where `idx`='$fidx'");
								} else {
									$mDB->DBupdate($mDatabase->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
								}
							}
						}
					} elseif ($field[$i]['type'] == 'FILE') {
						if (Request($field[$i]['name'].'-delete') == 'on' || (isset($_FILES[$field[$i]['name']]['tmp_name']) == true && $_FILES[$field[$i]['name']]['tmp_name'])) {
							$file = $mDB->DBfetch($mDatabase->table['file'],array('idx','filepath','filetype'),"where `idx`='{$oData[$field[$i]['name']]}'");
							@unlink($_ENV['userfilePath'].$mDatabase->userfile.$file['filepath']);
							if ($file['filetype'] == 'IMG') {
								@unlink($_ENV['userfilePath'].$mDatabase->thumbnail.'/'.$file['idx'].'.thm');
							}
							$mDB->DBupdate($table['name'],array($field[$i]['name']=>'0'),'',"where `idx`='$idx'",$table['database']);
						}
						
						if (isset($_FILES[$field[$i]['name']]['tmp_name']) == true && $_FILES[$field[$i]['name']]['tmp_name']) {
							$file = $_FILES[$field[$i]['name']];
							$filename = $file['name'];
							$temppath = $file['tmp_name'];
							$filesize = filesize($temppath);
							$filetype = GetFileType($filename,$temppath);
							$filepath = '/attach/'.$tno.'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);
							
							if (CreateDirectory($_ENV['userfilePath'].$mDatabase->userfile.'/attach/'.$tno) == true) {
								@move_uploaded_file($temppath,$_ENV['userfilePath'].$mDatabase->userfile.$filepath);
								$fidx = $mDB->DBinsert($mDatabase->table['file'],array('type'=>'FILE','tno'=>$tno,'repto'=>$idx,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype));
								
								if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$mDatabase->thumbnail) == true) {
									GetThumbnail($_ENV['userfilePath'].$mDatabase->userfile.$filepath,$_ENV['userfilePath'].$mDatabase->thumbnail.'/'.$fidx.'.thm',100,75,false);
								}
								
								$mDB->DBupdate($table['name'],array($field[$i]['name']=>$fidx),'',"where `idx`='$idx'",$table['database']);
							}
						}
					}
				}
				
				$return['success'] = true;
			} else {
				$return['success'] = false;
			}
			
			exit(json_encode($return));
		}
		
		if ($mode == 'delete') {
			$tno = Request('tno');
			$primary = Request('primary');
			$idx = Request('idx');
			
			$table = $mDB->DBfetch($mDatabase->table['table'],array('name','database','field'),"where `idx`='$tno'");
			$mDB->DBdelete($table['name'],"where `$primary` IN ($idx)",$table['database']);
			
			$file = $mDB->DBfetchs($mDatabase->table['file'],array('idx','filepath','filetype'),"where `tno`='$tno' and `repto` IN ($idx)");
			for ($i=0, $loop=sizeof($file);$i<$loop;$i++) {
				@unlink($_ENV['userfilePath'].$mDatabase->userfile.$file[$i]['filepath']);
				if ($file[$i]['filetype'] == 'IMG') {
					@unlink($_ENV['userfilePath'].$mDatabase->thumbnail.'/'.$file[$i]['idx'].'.thm');
				}
				$mDB->DBdelete($mDatabase->table['file'],"where `idx`='{$file[$i]['idx']}'");
			}
			
			$return['success'] = true;
			exit(json_encode($return));
		}
	}
}
?>