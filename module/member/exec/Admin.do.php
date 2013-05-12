<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();

if ($action == 'member') {
	if ($do == 'add') {
		$group = Request('group');
		$insert = array();
		$insert['group'] = $group;
		
		$form = $mDB->DBfetchs($_ENV['table']['signin'],array('name','type','allowblank','value'),"where `group`='$group'");
		for ($i=0, $loop=sizeof($form);$i<$loop;$i++) {
			if (in_array($form[$i]['type'],array('agreement','privacy','young')) == false) {
				switch ($form[$i]['type']) {
					case 'user_id' :
						$insert['user_id'] = CheckUserID(Request('user_id')) == true ? Request('user_id') : $errors['user_id'] = '회원아이디를 아이디규칙에 맞게 입력하여 주십시오.';
						if ($mDB->DBcount($_ENV['table']['member'],"where `user_id`='{$insert['user_id']}' and ((`is_leave`='TRUE' and `leave_date`>".(GetGMT()-60*60*24*180).") or `is_leave`='FALSE')") > 0) {
							$errors['user_id'] = '아이디가 중복됩니다.';
						}
					break;
					
					case 'name' :
						if (Request('name') != null) $insert['name'] = Request('name');
					break;

					case 'nickname' :
						$insert['nickname'] = CheckNickname(Request('nickname')) == true ? Request('nickname') : $errors['nickname'] = '닉네임을 입력하여 주십시오.';
						if ($mDB->DBcount($_ENV['table']['member'],"where `nickname`='{$insert['nickname']}' and `is_leave`='FALSE'") > 0) {
							$errors['nickname'] = '닉네임이 중복됩니다.';
						}
					break;

					case 'password' :
						$insert['password'] = Request('password1') != null && Request('password1') == Request('password2') ? md5(strtolower(Request('password1'))) : $errors['password1'] = '패스워드를 정확하게 입력하여 주십시오.';
					break;

					case 'jumin' :
						$insert['jumin'] = CheckJumin(Request('jumin1').'-'.Request('jumin2')) == true ? Request('jumin1').'-'.Request('jumin2') : $errors['jumin1'] = '주민등록번호를 정확하게 입력하여 주십시오.';
						if ($mDB->DBcount($_ENV['table']['member'],"where `jumin`='{$insert['jumin']}' and `is_leave`='FALSE'") > 0) {
							$errors['jumin1'] = '주민등록번호가 중복됩니다.';
						}
					break;

					case 'email' :
						$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : $errors['email'] = '이메일주소를 정확하게 입력하여 주십시오.';
						if ($mDB->DBcount($_ENV['table']['member'],"where `email`='{$insert['email']}' and `is_leave`='FALSE'") > 0) {
							$errors['email'] = '이메일주소가 중복됩니다.';
						}
					break;

					case 'cellphone' :
						$value = unserialize($form[$i]['value']);
						$cellphone = Request('cellphone1').'-'.Request('cellphone2').'-'.Request('cellphone3');
						if (Request('cellphone1') != null && Request('cellphone2') != null && Request('cellphone3') != null && (isset($value['provider']) == false || ($value['provider'] == 'on' && Request('provider')))) {
							if (CheckPhoneNumber($cellphone) == true) {
								$insert['cellphone'] = $cellphone;
								$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
							} else {
								$errors['cellphone1'] = '핸드폰 번호를 정확하게 입력하여 주십시오.';
								$errors['cellphone2'] = '핸드폰 번호를 정확하게 입력하여 주십시오.';
								$errors['cellphone3'] = '핸드폰 번호를 정확하게 입력하여 주십시오.';
							}
						}
					break;
					
					case 'birthday' :
						if (Request('birthday1') != null && Request('birthday2') != null && Request('birthday3') != null) {
							$insert['birthday'] = date('Y-m-d',mktime(0,0,0,Request('birthday2'),Request('birthday3'),Request('birthday1')));
						}
					break;

					case 'address' :
						if (Request('zipcode') != null && Request('address1') != null && Request('address2') != null) {
							$insert['zipcode'] = Request('zipcode');
							$insert['address'] = Request('address1').'||'.Request('address2');
						}
					break;

					case 'gender' :
						$insert['gender'] = Request('gender');
					break;

					case 'homepage' :
						if (Request('homepage') != null) {
							$insert['homepage'] = preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage');
						}
					break;
				}
			}
		}
		
		if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
			$check = @getimagesize($_FILES['nickcon']['tmp_name']);
			if ($check[2] != '1' || $check[0] > 80 || $check[1] > 16) {
				$errors['nickcon'] = '닉이미지 파일은 80*16픽셀 이하의 GIF파일만 가능합니다.';
			}
		}
		
		if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
			$check = @getimagesize($_FILES['photo']['tmp_name']);
			if (in_array($check[2],array('1','2','3')) == false) {
				$errors['photo'] = '회원사진은 이미지파일(GIF,JPG,PNG)만 가능합니다.';
			}
		}
	
		if (sizeof($errors) == 0) {
			$idx = $mDB->DBinsert($_ENV['table']['member'],$insert);

			if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
				$check = @getimagesize($_FILES['nickcon']['tmp_name']);
				if ($check[2] == '1' && $check[0] <= 80 && $check[1] <= 16) {
					@move_uploaded_file($_FILES['nickcon']['tmp_name'],$_ENV['userfilePath'].'/member/nickcon/'.$idx.'.gif');
				}
			}
			
			if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
				$check = @getimagesize($_FILES['photo']['tmp_name']);
				if (in_array($check[2],array('1','2','3')) == true) {
					GetThumbnail($_FILES['photo']['tmp_name'],$_ENV['userfilePath'].'/member/photo/'.$idx,60,60);
				}
			}
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'modify') {
		$idx = Request('idx');
		$check = $mDB->DBfetch($_ENV['table']['member'],array('group'),"where `idx`='$idx'");
		
		if (isset($check['group']) ==  true) {
			$form = $mDB->DBfetchs($_ENV['table']['signin'],array('name','type','allowblank','value'),"where `group`='{$check['group']}'");
			for ($i=0, $loop=sizeof($form);$i<$loop;$i++) {
				if (in_array($form[$i]['type'],array('agreement','privacy','young')) == false) {
					switch ($form[$i]['type']) {
						case 'name' :
							if (Request('name') != null) $insert['name'] = Request('name');
						break;
	
						case 'nickname' :
							$insert['nickname'] = CheckNickname(Request('nickname')) == true ? Request('nickname') : $errors['nickname'] = '닉네임을 입력하여 주십시오.';
							if ($mDB->DBcount($_ENV['table']['member'],"where `nickname`='{$insert['nickname']}' and `is_leave`='FALSE' and `idx`!=$idx") > 0) {
								$errors['nickname'] = '닉네임이 중복됩니다.';
							}
						break;
	
						case 'password' :
							if (Request('password_modify')) {
								$insert['password'] = Request('password1') != null && Request('password1') == Request('password2') ? md5(strtolower(Request('password1'))) : $errors['password1'] = '패스워드를 정확하게 입력하여 주십시오.';
							}
						break;
	
						case 'jumin' :
							$insert['jumin'] = CheckJumin(Request('jumin1').'-'.Request('jumin2')) == true ? Request('jumin1').'-'.Request('jumin2') : $errors['jumin1'] = '주민등록번호를 정확하게 입력하여 주십시오.';
							if ($mDB->DBcount($_ENV['table']['member'],"where `jumin`='{$insert['jumin']}' and `is_leave`='FALSE' and `idx`!=$idx") > 0) {
								$errors['jumin1'] = '주민등록번호가 중복됩니다.';
							}
						break;
	
						case 'email' :
							$insert['email'] = CheckEmail(Request('email')) == true ? Request('email') : $errors['email'] = '이메일주소를 정확하게 입력하여 주십시오.';
							if ($mDB->DBcount($_ENV['table']['member'],"where `email`='{$insert['email']}' and `is_leave`='FALSE' and `idx`!=$idx") > 0) {
								$errors['email'] = '이메일주소가 중복됩니다.';
							}
						break;
	
						case 'cellphone' :
							$value = GetUnSerialize($form[$i]['value']);
							$cellphone = Request('cellphone1').'-'.Request('cellphone2').'-'.Request('cellphone3');
							if (Request('cellphone1') != null && Request('cellphone2') != null && Request('cellphone3') != null && (isset($value['provider']) == false || ($value['provider'] == 'on' && Request('provider')))) {
								if (CheckPhoneNumber($cellphone) == true) {
									$insert['cellphone'] = $cellphone;
									$insert['cellphone'].= isset($value['provider']) == true && $value['provider'] == 'on' ? '||'.Request('provider') : '';
								} else {
									$errors['cellphone1'] = '핸드폰 번호를 정확하게 입력하여 주십시오.';
								}
							}
						break;
						
						case 'birthday' :
							if (Request('birthday1') != null && Request('birthday2') != null && Request('birthday3') != null) {
								$insert['birthday'] = date('Y-m-d',mktime(0,0,0,Request('birthday2'),Request('birthday3'),Request('birthday1')));
							}
						break;
	
						case 'address' :
							if (Request('zipcode') != null && Request('address1') != null && Request('address2') != null) {
								$insert['zipcode'] = Request('zipcode');
								$insert['address'] = Request('address1').'||'.Request('address2');
							}
						break;
	
						case 'gender' :
							$insert['gender'] = Request('gender');
						break;
	
						case 'homepage' :
							if (Request('homepage') != null) {
								$insert['homepage'] = preg_match('/^http:\/\//',Request('homepage')) == true ? Request('homepage') : 'http://'.Request('homepage');
							}
						break;
					}
				}
			}
		} else {
			$errors['name'] = '회원을 찾을수 없습니다.';
		}
		
		if (sizeof($errors) == 0) {
			$mDB->DBupdate($_ENV['table']['member'],$insert,'',"where `idx`='$idx'");

			if (isset($_FILES['nickcon']['tmp_name']) == true && $_FILES['nickcon']['tmp_name']) {
				$check = @getimagesize($_FILES['nickcon']['tmp_name']);
				if ($check[2] == '1' && $check[0] <= 80 && $check[1] <= 16) {
					@move_uploaded_file($_FILES['nickcon']['tmp_name'],$_ENV['userfilePath'].'/member/nickcon/'.$idx.'.gif');
				}
			}
			
			if (isset($_FILES['photo']['tmp_name']) == true && $_FILES['photo']['tmp_name']) {
				$check = @getimagesize($_FILES['photo']['tmp_name']);
				if (in_array($check[2],array('1','2','3')) == true) {
					GetThumbnail($_FILES['photo']['tmp_name'],$_ENV['userfilePath'].'/member/photo/'.$idx,60,60);
				}
			}

			if (Request('delete_nickcon')) {
				@unlink($_ENV['userfilePath'].'/member/nickcon/'.$idx.'.gif');
			}
			
			if (Request('delete_photo')) {
				@unlink($_ENV['userfilePath'].'/member/photo/'.$idx);
			}
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}

	if ($do == 'activemode') {
		$idx = Request('idx');
		$value = Request('value');
		
		$mDB->DBupdate($_ENV['table']['member'],array('is_active'=>$value),'',"where `idx` IN ($idx)");
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'group') {
	if ($do == 'add') {
		$insert = array();
		$insert['group'] = preg_match('/^[a-z0-9]+$/',Request('group')) == true ? Request('group') : $errors['group'] = '그룹아이디는 영어소문자와 숫자만 가능합니다.';
		$insert['title'] = Request('title') ? Request('title') : $errors['title'] = '그룹명을 선택하여 주십시오.';
		$insert['allow_signin'] = Request('allow_signin') == 'on' ? 'TRUE' : 'FALSE';
		$insert['allow_active'] = Request('allow_active') == 'on' ? 'TRUE' : 'FALSE';
		
		if ($mDB->DBcount($_ENV['table']['group'],"where `group`='{$insert['group']}'") > 0) {
			$errors['group'] = '기존 그룹명과 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$insert['sort'] = $mDB->DBcount($_ENV['table']['group']);
			$mDB->DBinsert($_ENV['table']['group'],$insert);
			$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$insert['group'],'name'=>'agreement','type'=>'agreement','title'=>'회원약관','msg'=>'위의 약관에 동의합니다.','sort'=>0));
			$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$insert['group'],'name'=>'user_id','type'=>'user_id','title'=>'회원아이디','msg'=>'아이디는 영문, 숫자, _(언더바)조합만 가능합니다.','sort'=>0));
			$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$insert['group'],'name'=>'name','type'=>'name','title'=>'이름','sort'=>1));
			$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$insert['group'],'name'=>'nickname','type'=>'nickname','title'=>'닉네임','msg'=>'사이트에서 자신을 알릴 닉네임을 20자이내로 입력하세요.','sort'=>2));
			$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$insert['group'],'name'=>'password','type'=>'password','title'=>'패스워드','sort'=>3));
			$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$insert['group'],'name'=>'email','type'=>'email','title'=>'이메일','sort'=>4));
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'sort') {
		$data = json_decode(Request('data'),true);
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($_ENV['table']['group'],array('sort'=>$data[$i]['sort']),'',"where `group`='{$data[$i]['group']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'signinmode') {
		$group = "'".implode("','",explode(',',Request('group')))."'";
		$value = Request('value');
		
		$mDB->DBupdate($_ENV['table']['group'],array('allow_signin'=>$value),'',"where `group` IN ($group)");
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'activemode') {
		$group = "'".implode("','",explode(',',Request('group')))."'";
		$value = Request('value');
		
		$mDB->DBupdate($_ENV['table']['group'],array('allow_active'=>$value),'',"where `group` IN ($group)");
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'title') {
		$group = Request('group');
		$title = Request('title');
		
		$mDB->DBupdate($_ENV['table']['group'],array('title'=>$title),'',"where `group`='$group'");
		
		$return['success'] = true;
		exit(json_encode($return));
	}

	if ($do == 'id') {
		$group = Request('group');
		$id = Request('id');
		
		if ($mDB->DBcount($_ENV['table']['group'],"where `group`!='$group' and `group`='$id'") > 0) {
			$errors['id'] = '기존 그룹아이디와 중복됩니다.';
		}
		
		if (sizeof($errors) == 0) {
			$mDB->DBupdate($_ENV['table']['group'],array('group'=>$id),'',"where `group`='$group'");
			$mDB->DBupdate($_ENV['table']['signin'],array('group'=>$id),'',"where `group`='$group'");
			$mDB->DBupdate($_ENV['table']['member'],array('group'=>$id),'',"where `group`='$group'");
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		exit(json_encode($return));
	}

	if ($do == 'delete') {
		$group = explode(',',Request('group'));
		$move = Request('move');
		$delete = Request('delete') == 'on';
		
		if ($delete == false && in_array($move,$group) == true) {
			$errors['move'] = '삭제할 그룹과 회원을 옮길 그룹이 일치합니다.';
		}
		
		$group = "'".implode("','",explode(',',Request('group')))."'";
		
		if (sizeof($errors) == 0) {
			if ($delete == true) {
				$mDB->DBupdate($_ENV['table']['member'],array('is_leave'=>'TRUE','leave_date'=>GetGMT()),'',"where `group` IN ($group)");
			} else {
				$mDB->DBupdate($_ENV['table']['member'],array('group'=>$move),'',"where `group` IN ($group)");
			}
			$mDB->DBdelete($_ENV['table']['signin'],"where `group` IN ($group)");
			$mDB->DBdelete($_ENV['table']['group'],"where `group` IN ($group)");
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'signin') {
	if ($do == 'agreement') {
		$group = Request('group');
		$value = Request('value');
		$msg = Request('msg');
		
		$mDB->DBupdate($_ENV['table']['signin'],array('value'=>$value,'msg'=>$msg),'',"where `group`='$group' and `type`='agreement'");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'privacy') {
		$group = Request('group');
		$value = Request('value');
		$msg = Request('msg');
		$disable = Request('disable') == 'on';
		
		if ($disable == true) {
			$mDB->DBdelete($_ENV['table']['signin'],"where `group`='$group' and `type`='privacy'");
		} else {
			$check = $mDB->DBfetch($_ENV['table']['signin'],'*',"where `group`='$group' and `type`='privacy'");
			if (isset($check['idx']) == true) {
				$mDB->DBupdate($_ENV['table']['signin'],array('value'=>$value,'msg'=>$msg,'sort'=>1),'',"where `idx`='{$check['idx']}'");
			} else {
				$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$group,'name'=>'privacy','type'=>'privacy','title'=>'개인정보보호정책','msg'=>$msg,'value'=>$value,'allowblank'=>'FALSE','sort'=>1));
			}
		}
	}
	
	if ($do == 'youngpolicy') {
		$group = Request('group');
		$value = Request('value');
		$msg = Request('msg');
		$disable = Request('disable') == 'on';
		
		if ($disable == true) {
			$mDB->DBdelete($_ENV['table']['signin'],"where `group`='$group' and `type`='youngpolicy'");
		} else {
			$check = $mDB->DBfetch($_ENV['table']['signin'],'*',"where `group`='$group' and `type`='youngpolicy'");
			if (isset($check['idx']) == true) {
				$mDB->DBupdate($_ENV['table']['signin'],array('value'=>$value,'msg'=>$msg,'sort'=>2),'',"where `idx`='{$check['idx']}'");
			} else {
				$mDB->DBinsert($_ENV['table']['signin'],array('group'=>$group,'name'=>'youngpolicy','type'=>'youngpolicy','title'=>'개인정보보호정책','msg'=>$msg,'value'=>$value,'allowblank'=>'FALSE','sort'=>2));
			}
		}
	}
	
	if ($do == 'add_default') {
		$insert = array();
		$insert['group'] = Request('group');
		$insert['name'] = $insert['type'] = Request('field');
		$insert['title'] = Request('title');
		$insert['msg'] = Request('msg');
		$insert['allowblank'] = Request('allowblank') == 'on' ? 'FALSE' : 'TRUE';
		
		if (in_array($insert['type'],array('user_id','name','nickname','password','email','jumin','companyno')) == true) {
			$insert['allowblank'] = 'FALSE';
		}
		
		if ($insert['type'] == 'cellphone') {
			$insert['value'] = serialize(array('realphone'=>Request('realphone'),'provider'=>Request('provider')));
		}
		
		if ($insert['type'] == 'voter') {
			$insert['value'] = serialize(array('vote'=>Request('vote'),'voter'=>Request('voter')));
		}
		
		if ($insert['type'] == 'jumin') {
			if ($mDB->DBcount($_ENV['table']['signin'],"where `group`='{$insert['group']}' and `name`='companyno'") > 0) {
				$errors['message'] = '주민등록번호필드는 사업자등록번호필드와 함께 사용될 수 없습니다.';
			}
		}
		
		if ($insert['type'] == 'companyno') {
			if ($mDB->DBcount($_ENV['table']['signin'],"where `group`='{$insert['group']}' and `name`='jumin'") > 0) {
				$errors['message'] = '사업자등록번호필드는 주민등록번호필드와 함께 사용될 수 없습니다.';
			}
		}
		
		if (sizeof($errors) == 0) {
			$sort = $mDB->DBfetch($_ENV['table']['signin'],array('MAX(sort)'),"where `group`='{$insert['group']}'");
			$insert['sort'] = $sort[0] + 1;
			$mDB->DBinsert($_ENV['table']['signin'],$insert);
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'add_extra') {
		$insert = array();
		$insert['group'] = Request('group');
		$insert['name'] = 'extra_'.Request('name');
		$insert['type'] = Request('type');
		$insert['title'] = Request('title');
		$insert['msg'] = Request('msg');
		$insert['allowblank'] = Request('allowblank') == 'on' ? 'FALSE' : 'TRUE';
		
		if (preg_match('/^[a-z0-9_]+$/',$insert['name']) == false) {
			$errors['message'] = '필드명은 영어소문자 및 숫자, 그리고 언더바(_)만 가능합니다.';
		}
		
		if ($mDB->DBcount($_ENV['table']['signin'],"where `name`='{$insert['name']}'") > 0) {
			$errors['message'] = '기존의 필드명과 중복됩니다.';
		}
		
		if (in_array($insert['type'],array('checkbox','radio','select')) == true) {
			$list = explode("\n",Request('list'));
			$insert['value'] = array();
			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				if (strlen(trim($list[$i])) > 0) {
					$insert['value'][] = trim($list[$i]);
				}
			}
			
			if (sizeof($insert['value']) == 0) {
				$errors['message'] = '항목을 하나이상 입력하여 주십시오.';
			} else {
				$insert['value'] = serialize($insert['value']);
			}
		}

		if ($insert['type'] == 'textarea') {
			$insert['value'] = serialize(Request('height'));
		}
		
		if ($insert['type'] == 'input') {
			$valid = Request('valid');

			if ($valid) {
				@preg_match('/'.$valid.'/',$insert['name']) or $errors['message'] = '정규식이 잘못입력되었습니다.';
			}
			$insert['value'] = serialize($valid);
		}
		
		
		if (sizeof($errors) == 0) {
			$sort = $mDB->DBfetch($_ENV['table']['signin'],array('MAX(sort)'),"where `group`='{$insert['group']}'");
			$insert['sort'] = $sort[0] + 1;
			$mDB->DBinsert($_ENV['table']['signin'],$insert);
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'modify_default') {
		$idx = Request('idx');
		
		$insert = array();
		$insert['title'] = Request('title');
		$insert['msg'] = Request('msg');
		$insert['allowblank'] = Request('allowblank') == 'on' ? 'FALSE' : 'TRUE';
		
		$field = $mDB->DBfetch($_ENV['table']['signin'],'*',"where `idx`='$idx'");
		
		if (in_array($field['type'],array('user_id','name','nickname','password','email','jumin','companyno')) == true) {
			$insert['allowblank'] = 'FALSE';
		}
		
		if ($field['type'] == 'cellphone') {
			$insert['value'] = serialize(array('realphone'=>Request('realphone'),'provider'=>Request('provider')));
		}
		
		if ($field['type'] == 'voter') {
			$insert['value'] = serialize(array('vote'=>Request('vote'),'voter'=>Request('voter')));
		}

		$mDB->DBupdate($_ENV['table']['signin'],$insert,'',"where `idx`='$idx'");
		$return['success'] = true;
		
		exit(json_encode($return));
	}
	
	if ($do == 'modify_extra') {
		$idx = Request('idx');
		
		$insert = array();
		$insert['name'] = 'extra_'.Request('name');
		$insert['title'] = Request('title');
		$insert['msg'] = Request('msg');
		$insert['allowblank'] = Request('allowblank') == 'on' ? 'FALSE' : 'TRUE';
		
		$field = $mDB->DBfetch($_ENV['table']['signin'],'*',"where `idx`='$idx'");
		
		if (preg_match('/^[a-z0-9_]+$/',$insert['name']) == false) {
			$errors['message'] = '필드명은 영어소문자 및 숫자, 그리고 언더바(_)만 가능합니다.';
		}
		
		if ($mDB->DBcount($_ENV['table']['signin'],"where `idx`!='$idx' and `name`='{$insert['name']}'") > 0) {
			$errors['message'] = '기존의 필드명과 중복됩니다.';
		}
		
		if (in_array($field['type'],array('checkbox','radio','select')) == true) {
			$list = explode("\n",Request('list'));
			$insert['value'] = array();
			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				if (strlen(trim($list[$i])) > 0) {
					$insert['value'][] = trim($list[$i]);
				}
			}
			
			if (sizeof($field['value']) == 0) {
				$errors['message'] = '항목을 하나이상 입력하여 주십시오.';
			} else {
				$insert['value'] = serialize($insert['value']);
			}
		}

		if ($field['type'] == 'textarea') {
			$insert['value'] = serialize(Request('height'));
		}
		
		if ($field['type'] == 'input') {
			$valid = Request('valid');

			if ($valid) {
				@preg_match('/'.$valid.'/',$insert['name']) or $errors['message'] = '정규식이 잘못입력되었습니다.';
			}
			$insert['value'] = serialize($valid);
		}
		
		
		if (sizeof($errors) == 0) {
			$mDB->DBupdate($_ENV['table']['signin'],$insert,'',"where `idx`='$idx'");
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'sort') {
		$data = json_decode(Request('data'),true);
		
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($_ENV['table']['signin'],array('sort'=>$data[$i]['sort']),'',"where `idx`='{$data[$i]['idx']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = Request('idx');
		$group = Request('group');
		
		$delete = array();
		$field = $mDB->DBfetchs($_ENV['table']['signin'],array('idx','name'),"where `idx` IN ($idx) and `group`='$group'");
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if (in_array($field[$i]['name'],array('user_id','password','name','email','privacy','youngpolicy','agreement')) == false) {
				$delete[] = $field[$i]['name'];
				$mDB->DBdelete($_ENV['table']['signin'],"where `idx`='{$field[$i]['idx']}'");
			}
		}
		
		$user = $mDB->DBfetchs($_ENV['table']['member'],array('idx','extra_data'),"where `group`='$group'");
		for ($i=0, $loop=sizeof($user);$i<$loop;$i++) {
			$update = array();
			$update['extra_data'] = $user[$i]['extra_data'] ? unserialize($user[$i]['extra_data']) : array();
			
			for ($j=0, $loopj=sizeof($delete);$j<$loopj;$j++) {
				if (preg_match('/^extra_/',$delete[$j]) == true) {
					unset($update['extra_data'][preg_replace('/^extra_/','',$delete[$j])]);
				} else {
					$update[$delete[$j]] = '';
				}
			}
			$update['extra_data'] = sizeof($update['extra_data']) > 0 ? serialize($update['extra_data']) : '';
			
			$mDB->DBupdate($_ENV['table']['member'],$update,'',"where `idx`='{$user[$i]['idx']}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'leave') {
	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		$module = $mDB->DBfetchs($_ENV['table']['module'],'*');//,"where `module`='member'");
		
		for ($i=0, $loop=sizeof($module);$i<$loop;$i++) {
			if (file_exists($_ENV['path'].'/module/'.$module[$i]['module'].'/class.php') == true) {
				$mClass = include($_ENV['path'].'/module/'.$module[$i]['module'].'/class.php');

				if (method_exists($mClass,'MemberLeave') == true) {
					$mClass->RemoveAllMemberData($idx);
				}
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'member') {
	if ($do == 'type') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = Request('idx');
		$type = Request('type');

		$mDB->DBupdate($_ENV['table']['member'],array('type'=>$type),'',"where `idx` IN ($idx)");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

if ($action == 'status') {
	header('Content-type: text/xml; charset="UTF-8"', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$mStatus = new Status();

	if ($do == 'log_visit_delete') {
		$mDB->DBtruncate($mStatus->table['log_visit']);
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}
?>