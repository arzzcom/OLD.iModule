<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mOneroom = new ModuleOneroom();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'region') {
	if ($do == 'add') {
		$insert['title'] = Request('title');
		$insert['parent'] = Request('parent');
		
		if ($mDB->DBcount($mOneroom->table['region'],"where `parent`='{$insert['parent']}' and `title`='{$insert['title']}'") > 0) {
			$errors['title'] = '지역명이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($mOneroom->table['region'],"where `parent`='{$insert['parent']}'");
			$idx = $mDB->DBinsert($mOneroom->table['region'],$insert);
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['title'] = $insert['title'];
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
			$mDB->DBupdate($mOneroom->table['region'],array('title'=>$data[$i]['title'],'sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		$parent = Request('parent');
		
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['region'],"where `idx`='{$idx[$i]}'");
			$subRegion = $mDB->DBfetchs($mOneroom->table['region'],array('idx'),"where `parent`='{$idx[$i]}'");
			for ($j=0, $loopj=sizeof($subRegion);$j<$loopj;$j++) {
				$mDB->DBdelete($mOneroom->table['region'],"where `idx`={$subRegion[$j]['idx']}");
				$mDB->DBdelete($mOneroom->table['region'],"where `parent`={$subRegion[$j]['idx']}");
			}
		}
		
		$region = $mDB->DBfetchs($mOneroom->table['region'],'*',"where `parent`='$parent'",'sort,asc');
		for ($i=0, $loop=sizeof($region);$i<$loop;$i++) {
			$mDB->DBupdate($mOneroom->table['region'],array('sort'=>$i),'',"where `idx`='{$region[$i]['idx']}'");
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'category') {
	if ($do == 'add') {
		$insert['title'] = Request('title');
		$insert['parent'] = Request('parent');
		
		if ($mDB->DBcount($mOneroom->table['category'],"where `parent`='{$insert['parent']}' and `title`='{$insert['title']}'") > 0) {
			$errors['title'] = '카테고리명이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($mOneroom->table['category'],"where `parent`='{$insert['parent']}'");
			$idx = $mDB->DBinsert($mOneroom->table['category'],$insert);
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['title'] = $insert['title'];
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
			$mDB->DBupdate($mOneroom->table['category'],array('title'=>$data[$i]['title'],'sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		$parent = Request('parent');
		
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['category'],"where `idx`='{$idx[$i]}'");
			$subCategory = $mDB->DBfetchs($mOneroom->table['category'],array('idx'),"where `parent`='{$idx[$i]}'");
			for ($j=0, $loopj=sizeof($subCategory);$j<$loopj;$j++) {
				$mDB->DBdelete($mOneroom->table['category'],"where `idx`={$subCategory[$j]['idx']}");
				$mDB->DBdelete($mOneroom->table['category'],"where `parent`={$subCategory[$j]['idx']}");
			}
		}
		
		$category = $mDB->DBfetchs($mOneroom->table['category'],'*',"where `parent`='$parent'",'sort,asc');
		for ($i=0, $loop=sizeof($category);$i<$loop;$i++) {
			$mDB->DBupdate($mOneroom->table['category'],array('sort'=>$i),'',"where `idx`='{$category[$i]['idx']}'");
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'option') {
	if ($do == 'add') {
		$insert['title'] = Request('title');
		$insert['parent'] = Request('parent');
		
		if ($mDB->DBcount($mOneroom->table['option'],"where `parent`='{$insert['parent']}' and `title`='{$insert['title']}'") > 0) {
			$errors['title'] = '옵션명이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($mOneroom->table['option'],"where `parent`='{$insert['parent']}'");
			$idx = $mDB->DBinsert($mOneroom->table['option'],$insert);
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['title'] = $insert['title'];
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
			$mDB->DBupdate($mOneroom->table['option'],array('title'=>$data[$i]['title'],'sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		$parent = Request('parent');
		
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['option'],"where `idx`='{$idx[$i]}'");
			$mDB->DBdelete($mOneroom->table['option'],"where `parent`='{$idx[$i]}'");
		}
		
		$option = $mDB->DBfetchs($mOneroom->table['option'],'*',"where `parent`='$parent'",'sort,asc');
		for ($i=0, $loop=sizeof($option);$i<$loop;$i++) {
			$mDB->DBupdate($mOneroom->table['option'],array('sort'=>$i),'',"where `idx`='{$option[$i]['idx']}'");
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'university') {
	if ($do == 'add') {
		$insert['title'] = Request('title');
		$insert['parent'] = Request('parent');
		
		if ($mDB->DBcount($mOneroom->table['university'],"where `parent`='{$insert['parent']}' and `title`='{$insert['title']}'") > 0) {
			$errors['title'] = '지역/대학명이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($mOneroom->table['university'],"where `parent`='{$insert['parent']}'");
			$idx = $mDB->DBinsert($mOneroom->table['university'],$insert);
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['title'] = $insert['title'];
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
			$mDB->DBupdate($mOneroom->table['university'],array('title'=>$data[$i]['title'],'sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		$parent = Request('parent');
		
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['university'],"where `idx`='{$idx[$i]}'");
			$mDB->DBdelete($mOneroom->table['university'],"where `parent`='{$idx[$i]}'");
		}
		
		$university = $mDB->DBfetchs($mOneroom->table['university'],'*',"where `parent`='$parent'",'sort,asc');
		for ($i=0, $loop=sizeof($university);$i<$loop;$i++) {
			$mDB->DBupdate($mOneroom->table['university'],array('sort'=>$i),'',"where `idx`='{$university[$i]['idx']}'");
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'subway') {
	if ($do == 'add') {
		$insert['title'] = Request('title');
		$insert['parent'] = Request('parent');
		
		if ($mDB->DBcount($mOneroom->table['subway'],"where `parent`='{$insert['parent']}' and `title`='{$insert['title']}'") > 0) {
			$errors['title'] = '노선/역명이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($mOneroom->table['subway'],"where `parent`='{$insert['parent']}'");
			$idx = $mDB->DBinsert($mOneroom->table['subway'],$insert);
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['title'] = $insert['title'];
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
			$mDB->DBupdate($mOneroom->table['subway'],array('title'=>$data[$i]['title'],'sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		$parent = Request('parent');
		
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['subway'],"where `idx`='{$idx[$i]}'");
			$mDB->DBdelete($mOneroom->table['subway'],"where `parent`='{$idx[$i]}'");
		}
		
		$subway = $mDB->DBfetchs($mOneroom->table['subway'],'*',"where `parent`='$parent'",'sort,asc');
		for ($i=0, $loop=sizeof($subway);$i<$loop;$i++) {
			$mDB->DBupdate($mOneroom->table['subway'],array('sort'=>$i),'',"where `idx`='{$subway[$i]['idx']}'");
		}

		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'item') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();

		$insert['category1'] = Request('category1');
		$insert['category2'] = Request('category2');
		$insert['category3'] = Request('category3');
		$insert['region1'] = Request('region1');
		$insert['region2'] = Request('region2');
		$insert['region3'] = Request('region3');
		$insert['title'] = Request('title');
		
		$insert['is_rent_month'] = Request('is_rent_month') == 'on' ? 'TRUE' : 'FALSE';
		$insert['is_rent_all'] = Request('is_rent_all') == 'on' ? 'TRUE' : 'FALSE';
		$insert['is_buy'] = Request('is_buy') == 'on' ? 'TRUE' : 'FALSE';
		$insert['is_rent_short'] = Request('is_rent_short') == 'on' ? 'TRUE' : 'FALSE';

		if ($insert['is_rent_month'] == 'TRUE') {
			$insert['price_rent_deposit'] = Request('price_rent_deposit');
			$insert['price_rent_month'] = Request('price_rent_month');
		} else {
			$insert['price_rent_deposit'] = $insert['price_rent_month'] = 0;
		}
		
		if ($insert['is_rent_all'] == 'TRUE') {
			$insert['price_rent_all'] = Request('price_rent_all');
		} else {
			$insert['price_rent_all'] = 0;
		}
		
		if ($insert['is_buy'] == 'TRUE') {
			$insert['price_buy'] = Request('price_buy');
		} else {
			$insert['price_buy'] = 0;
		}
		
		if ($insert['is_rent_month'] == 'FALSE' && $insert['is_rent_short'] == 'TRUE') {
			$insert['price_rent_month'] = Request('price_rent_short');
		}
		
		if (Request('floor1') && Request('floor2')) {
			$insert['floor'] = Request('floor1').'/'.Request('floor2');
		} elseif (Request('floor1')) {
			$insert['floor'] = Request('floor1').'/'.Request('floor1');
		} else {
			$insert['floor'] = '';
		}
		
		$insert['is_under'] = Request('is_under') == 'on' ? 'TRUE' : 'FALSE';
		$insert['rooms'] = Request('rooms');
		$insert['is_double'] = Request('is_double') == 'on' ? 'TRUE' : 'FALSE';
		$insert['parkings'] = Request('parkings');
		
		if (Request('areasize')) {
			$insert['areasize'] = Request('areasize');
		} else {
			$insert['areasize'] = 0;
		}
		
		if (Request('real_areasize')) {
			$insert['real_areasize'] = Request('real_areasize');
		} else {
			$insert['real_areasize'] = $insert['areasize'];
		}
		
		$insert['build_year'] = Request('build_year');
		
		if (Request('movein_date_now') == 'on') {
			$insert['movein_date'] = '0000-00-00';
		} else {
			$insert['movein_date'] = Request('movein_date') ? Request('movein_date') : date('Y-m-d');
		}
		
		$insert['zipcode'] = Request('zipcode');
		$insert['address1'] = Request('address1');
		$insert['address2'] = Request('address2');
		
		$insert['university'] = Request('university2');
		$insert['subway'] = Request('subway2');
		$insert['subway_distance'] = Request('subway_distance');
		
		$options = array();
		foreach ($_REQUEST as $key=>$value) {
			if (preg_match('/^options_([0-9]+)$/',$key,$match) == true) {
				$options[] = $match[1];
			}
		}
		
		$insert['options'] = implode(',',$options);
		$insert['detail'] = str_replace($mOneroom->moduleDir,'{$moduleDir}',Request('detail'));
		
		if ($do == 'add') {
			if ($_FILES['image']['tmp_name']) {
				$check = @getimagesize($_FILES['image']['tmp_name']);
				if ($check[2] != '1' && $check[2] != '2' && $check[2] != '3') {
					$errors['image'] = '대표이미지 파일은 이미지파일(JPG,GIF,PNG)만 가능합니다.';
				}
			} else {
				$errors['image'] = '대표 이미지를 선택하여 주십시오.';
			}
		} else {
			if ($_FILES['image']['tmp_name']) {
				$check = @getimagesize($_FILES['image']['tmp_name']);
				if ($check[2] != '1' && $check[2] != '2' && $check[2] != '3') {
					$errors['image'] = '대표이미지 파일은 이미지파일(JPG,GIF,PNG)만 가능합니다.';
				}
			}
		}
		
		if (sizeof($errors) == 0) {
			if ($do == 'add') {
				$insert['mno'] = 0;
				$insert['reg_date'] = GetGMT();
				$idx = $mDB->DBinsert($mOneroom->table['item'],$insert);

				$filepath = '/attach/'.md5_file($_FILES['image']['tmp_name']).'.'.time().'.'.rand(100000,999999);
				
				if (CreateDirectory($_ENV['userfilePath'].$mOneroom->userfile.'/attach') == true) {
					@move_uploaded_file($_FILES['image']['tmp_name'],$_ENV['userfilePath'].$mOneroom->userfile.$filepath);
					$fidx = $mDB->DBinsert($mOneroom->table['file'],array('type'=>'attach','filename'=>$_FILES['image']['name'],'filepath'=>$filepath,'filesize'=>filesize($_ENV['userfilePath'].$mOneroom->userfile.$filepath),'filetype'=>'IMG','repto'=>$idx));
					
					if (CreateDirectory($_ENV['userfilePath'].$mOneroom->thumbnail) == true) {
						GetThumbnail($_ENV['userfilePath'].$mOneroom->userfile.$filepath,$_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$fidx.'.thm',200,150,false);
					}
				}
				$mDB->DBupdate($mOneroom->table['item'],array('image'=>$fidx),'',"where `idx`='$idx'");
			} else {
				$idx = Request('idx');
				unset($insert['reg_date']);
				unset($insert['end_date']);
				
				if ($_FILES['image']['tmp_name']) {
					$filepath = '/attach/'.md5_file($_FILES['image']['tmp_name']).'.'.time().'.'.rand(100000,999999);
					@move_uploaded_file($_FILES['image']['tmp_name'],$_ENV['userfilePath'].$mOneroom->userfile.$filepath);
					$fidx = $mDB->DBinsert($mOneroom->table['file'],array('type'=>'attach','filename'=>$_FILES['image']['name'],'filepath'=>$filepath,'filesize'=>filesize($_ENV['userfilePath'].$mOneroom->userfile.$filepath),'filetype'=>'IMG','repto'=>$idx));
					
					if (CreateDirectory($_ENV['userfilePath'].$mOneroom->thumbnail) == true) {
						GetThumbnail($_ENV['userfilePath'].$mOneroom->userfile.$filepath,$_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$fidx.'.thm',200,150,false);
						$insert['image'] = $fidx;
					}
				}
				
				$mDB->DBupdate($mOneroom->table['item'],$insert,'',"where `idx`='$idx'");
			}
			
			$attach = Request('ItemFormAttach-files');
			if ($attach != null) {
				for ($i=0, $loop=sizeof($attach);$i<$loop;$i++) {
					$temp = explode('|',$attach[$i]);
					$fidx = $temp[0];
		
					if (sizeof($temp) == 1) {
						$fileData = $mDB->DBfetch($mOneroom->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
						@unlink($_ENV['userfilePath'].$mOneroom->userfile.$fileData['filepath']);
						if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$fidx.'.thm');
						$mDB->DBdelete($mOneroom->table['file'],"where `idx`='$fidx'");
					} else {
						$mDB->DBupdate($mOneroom->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
					}
				}
			}
			
			$wysiwyg = Request('ItemFormUploader-files');
			if ($wysiwyg != null) {
				for ($i=0, $loop=sizeof($wysiwyg);$i<$loop;$i++) {
					$temp = explode('|',$wysiwyg[$i]);
					$fidx = $temp[0];
		
					if (sizeof($temp) == 1) {
						$fileData = $mDB->DBfetch($mOneroom->table['file'],array('filepath','filetype'),"where `idx`='$fidx'");
						@unlink($_ENV['userfilePath'].$mOneroom->userfile.$fileData['filepath']);
						if ($fileData['filetype'] == 'IMG') @unlink($_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$fidx.'.thm');
						$mDB->DBdelete($mOneroom->table['file'],"where `idx`='$fidx'");
					} else {
						$mDB->DBupdate($mOneroom->table['file'],array('repto'=>$idx),'',"where `idx`='$fidx'");
					}
				}
			}
			$return['success'] = true;
		} else {
			$return['success'] = true;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'openmode') {
		$idx = Request('idx');
		$value = Request('value');
		
		if ($value == 'TRUE') {
			$idx = explode(',',$idx);
			for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
				$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='{$idx[$i]}'");
				if ($item['is_open'] == 'FALSE' && ($item['end_date'] == '0' || $item['end_date'] > GetGMT())) {
					if ($mOneroom->GetConfig('open_time') != '0') {
						$end_date = $item['end_date'] == '0' ? GetGMT()+$mOneroom->GetConfig('open_time')*60*60*24 : $item['end_date'];
					} else {
						$end_date = '0';
					}
					$mDB->DBupdate($mOneroom->table['item'],array('is_open'=>$value,'end_date'=>$end_date),'',"where `idx`='{$item['idx']}'");
				}
			}
		} else if ($value == 'FALSE') {
			$mDB->DBdelete($mOneroom->table['premium_item'],"where `ino` IN ($idx) and `type` IN ('SLOT','POINT')");
			$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>'0'),'',"where `ino` IN ($idx)");
			$mDB->DBupdate($mOneroom->table['premium_item'],array('ino'=>'0'),'',"where `ino` IN ($idx)");
			
			$mDB->DBupdate($mOneroom->table['item'],array('is_open'=>$value,'is_premium'=>'FALSE','is_regionitem'=>'FALSE'),'',"where `idx` IN ($idx)");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'defaultpremiummode') {
		$idx = Request('idx');
		$value = Request('value');
		
		$mDB->DBupdate($mOneroom->table['item'],array('is_default_premium'=>$value),'',"where `idx` IN ($idx)");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'defaultregionitemmode') {
		$idx = Request('idx');
		$value = Request('value');
		
		$mDB->DBupdate($mOneroom->table['item'],array('is_default_regionitem'=>$value),'',"where `idx` IN ($idx)");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'defaultprodealermode') {
		$value = Request('value');
		
		$return['success'] = false;
		if ($value == 'TRUE') {
			$mode = Request('mode');
			
			if ($mode == 'auto') {
				$data = json_decode(Request('data'),true);
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($data[$i]['region1'] != '0') {
						if ($mDB->DBcount($mOneroom->table['prodealer_default'],"where `mno`='{$data[$i]['idx']}'") == 0) {
							$mDB->DBinsert($mOneroom->table['prodealer_default'],array('mno'=>$data[$i]['idx'],'region1'=>$data[$i]['region1'],'region2'=>$data[$i]['region2'],'region3'=>$data[$i]['region3']));
						} else {
							$mDB->DBupdate($mOneroom->table['prodealer_default'],array('region1'=>$data[$i]['region1'],'region2'=>$data[$i]['region2'],'region3'=>$data[$i]['region3']),'',"where `mno`='{$lists[$i]['idx']}'");
						}
					}
				}
				
				$return['success'] = true;
			}
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'slot') {
	if ($do == 'add') {
		$insert = array();
		$insert['type'] = Request('type');
		$insert['term'] = Request('term');
		$insert['price'] = Request('price');
		
		if ($mDB->DBcount($mOneroom->table['slot'],"where `type`='{$insert['type']}' and `term`='{$insert['term']}'") > 0) {
			$errors['term'] = '기간이 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$idx = 10;//$mDB->DBinsert($mDB->table['slot'],$insert);
			
			$return['success'] = true;
			$return['idx'] = $idx;
			$return['term'] = $insert['term'];
			$return['price'] = $insert['price'];
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		exit(json_encode($return));
	}
	
	if ($do == 'modify') {
		$data = json_decode(Request('data'),true);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mOneroom->table['slot'],$data[$i],'',"where `idx`='{$data[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = Request('idx');
		$mDB->DBdelete($mOneroom->table['slot'],"where `idx` IN ($idx)");
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'file') {
	if ($do == 'retrench') {
		GetDefaultHeader('첨부파일 정리중');

		$fileLimit = Request('fileLimit') ? Request('fileLimit') : 0;

		$files = scandir($_ENV['userfilePath'].$mOneroom->userfile.'/attach',0);

		if ($fileLimit == 0) {
			$totalFile = sizeof($files);
			$deleteFile = 0;
		} else {
			$totalFile = Request('totalFile');
			$deleteFile = Request('deleteFile');
		}
		
		for ($i=$fileLimit-$deleteFile;$i<$fileLimit+100;$i++) {
			if (isset($files[$i]) == false) {
				break;
			} elseif (is_dir($_ENV['userfilePath'].$mOneroom->userfile.'/attach/'.$files[$i]) == false) {
				if ($mDB->DBcount($mOneroom->table['file'],"where `filepath`='/attach/{$files[$i]}'") == 0) {
					$deleteFile++;
					@unlink($_ENV['userfilePath'].$mOneroom->userfile.'/attach/'.$files[$i]) or $deleteFile--;
				}
			}
		}

		if ($totalFile > $fileLimit+100) {
			echo '<script type="text/javascript">top.RetrenchProgressControl('.($fileLimit+100).','.$totalFile.','.$deleteFile.');</script>';
			Redirect($_SERVER['PHP_SELF'].GetQueryString(array('fileLimit'=>$fileLimit+100,'totalFile'=>$totalFile,'deleteFile'=>$deleteFile),'',false));
		} else {
			echo '<script type="text/javascript">top.RetrenchProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		}

		GetDefaultFooter();
	}

	if ($do == 'removetemp') {
		GetDefaultHeader('첨부파일 정리중');
		
		$fileLimit = Request('fileLimit') ? Request('fileLimit') : 0;
		$deleteFile = Request('deleteFile') ? Request('deleteFile') : 0;
		if ($fileLimit == 0) {
			$totalFile = $mDB->DBcount($mOneroom->table['file'],"where `repto`=0");
		} else {
			$totalFile = Request('totalFile');
		}
		
		$data = $mDB->DBfetchs($mOneroom->table['file'],array('idx','filepath'),"where `repto`=0",'idx,asc',($fileLimit-$deleteFile).',1000');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$deleteFile++;
			@unlink($_ENV['userfilePath'].$mOneroom->userfile.$data[$i]['filepath']);
			@unlink($_ENV['userfilePath'].$mOneroom->thumbnail.'/'.$data[$i]['idx'].'.thm');
			$mDB->DBdelete($mOneroom->table['file'],"where `idx`='{$data[$i]['idx']}'");
		}
		
		if ($totalFile > $fileLimit + 1000) {
			echo '<script type="text/javascript">top.TempProgressControl('.($fileLimit+1000).','.$totalFile.','.$deleteFile.');</script>';
			Redirect($_SERVER['PHP_SELF'].GetQueryString(array('fileLimit'=>$fileLimit+1000,'deleteFile'=>$deleteFile,'totalFile'=>$totalFile),'',false));
		} else {
			echo '<script type="text/javascript">top.TempProgressControl('.$totalFile.','.$totalFile.','.$deleteFile.');</script>';
		}
		
		GetDefaultFooter();
	}
}
?>