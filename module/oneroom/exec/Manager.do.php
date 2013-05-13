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

if ($action == 'item') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		
		if ($do == 'add' && $mOneroom->GetConfig('register_point') > $member['point']) {
			$return['success'] = false;
			$return['message'] = '회원님의 포인트가 부족합니다.<br />매물등록시 '.number_format($mOneroom->GetConfig('register_point')).'포인트가 필요합니다.';
			exit(json_encode($return));
		}

		$insert['mno'] = $member['idx'];
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
				
				if ($mOneroom->GetConfig('register_point') != '0') {
					$mMember->SendPoint($member['idx'],$mOneroom->GetConfig('register_point')*-1,'매물등록');
				}
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
			if ($mOneroom->GetConfig('open_limit') != '0') {
				$open = $mDB->DBcount($mOneroom->table['item'],"where `mno`='{$member['idx']}' and `is_open`='TRUE'");
				$newopen = $mDB->DBcount($mOneroom->table['item'],"where `mno`='{$member['idx']}' and `idx` IN ($idx) and `is_open`='FALSE' and (`end_date`=0 or `end_date`>".GetGMT().")");
				
				if ($mOneroom->GetConfig('open_limit') < $open + $newopen) {
					$return['success'] = false;
					$return['message'] = '1인당 공개가능한 매물갯수를 초과합니다.';
					exit(json_encode($return));
				}
			}
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
}

if ($action == 'premium') {
	if ($do == 'slot_buy') {
		if ($mOneroom->GetConfig('premium_method') == 'slot') {
			$idx = Request('idx');
			
			$data = $mDB->DBfetch($mOneroom->table['slot'],'*',"where `idx`='$idx' and `type`='PREMIUM'");
			
			if ($mOneroom->GetConfig('premium_limit') > 0 && $mOneroom->GetConfig('premium_limit') <= $mDB->DBcount($mOneroom->table['user_slot'],"where `type`='PREMIUM' and `start_time`<".GetGMT()." and `end_time`>".GetGMT())) {
				$return['success'] = false;
				$return['message'] = '현재 활성화된 슬롯이 구매제한수를 초과하기 때문에 구매할 수 없습니다.';
				exit(json_encode($return));
			}
			
			if (isset($data['idx']) == true) {
				if ($mMember->IsLogged() == false) {
					$return['message'] = '먼저 회원로그인을 하여 주십시오.';
					$return['success'] = false;
				} elseif ($member['point'] < $data['price']) {
					$return['message'] = '포인트가 부족합니다.<br />먼저 포인트를 구매하여 주시기 바랍니다.';
					$return['success'] = false;
				} else {
					$mDB->DBinsert($mOneroom->table['user_slot'],array('mno'=>$member['idx'],'type'=>$data['type'],'start_time'=>GetGMT(),'end_time'=>GetGMT()+60*60*24*$data['term']));
					$mMember->SendPoint($member['idx'],$data['price']*-1,'프리미엄슬롯 ('.$data['term'].'일) 구매');
					$return['success'] = true;
				}
			} else {
				$return['success'] = false;
				$return['message'] = '선택한 슬롯상품을 찾을 수 없습니다.';
			}
		} else {
			$return['success'] = false;
			$return['message'] = '프리미엄노출방식이 슬롯방식이 아닙니다.';
		}
		exit(json_encode($return));
	}
	
	if ($do == 'slot_link') {
		$idx = Request('idx');
		$slot = Request('slot');
		$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
		$slot = $mDB->DBfetch($mOneroom->table['user_slot'],'*',"where `idx`='$slot' and `type`='PREMIUM'");
		
		if (isset($item['idx']) == false || $item['is_open'] == 'FALSE') {
			$return['success'] = false;
			$return['message'] = '선택 매물이 비공개중이거나, 게시만료일자가 지났습니다.';
		} else {
			if (isset($slot['idx']) == true && $slot['start_time'] < GetGMT() && $slot['end_time'] > GetGMT()) {
				if ($slot['ino'] != '0') {
					$mDB->DBdelete($mOneroom->table['premium_item'],"where `ino`='{$slot['ino']}' and `type`='SLOT'");
					$mDB->DBupdate($mOneroom->table['item'],array('is_premium'=>'FALSE'),'',"where `idx`='{$slot['ino']}'");
				}
				
				$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
				if ($item['is_premium'] == 'TRUE') {
					$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>'0'),'',"where `ino`='{$item['idx']}' and `type`='PREMIUM'");
				}
				$mDB->DBinsert($mOneroom->table['premium_item'],array('ino'=>$item['idx'],'type'=>'SLOT','region1'=>$item['region1'],'region2'=>$item['region2'],'region3'=>$item['region3'],'category1'=>$item['category1'],'category2'=>$item['category2'],'category3'=>$item['category3'],'start_time'=>$slot['start_time'],'end_time'=>$slot['end_time']));
				$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>$item['idx']),'',"where `idx`='{$slot['idx']}'");
				$mDB->DBupdate($mOneroom->table['item'],array('is_premium'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
				
				$return['success'] = true;
			} else {
				$return['success'] = false;
				$return['message'] = '해당 슬롯의 활성화기간이 지났습니다.';
			}
		}
		exit(json_encode($return));
	}
	
	if ($do == 'slot_unlink') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$slot = $mDB->DBfetch($mOneroom->table['user_slot'],'*',"where `idx`='{$idx[$i]}' and `type`='PREMIUM'");
			if (isset($slot['idx']) == true && $slot['ino'] != '0') {
				$mDB->DBdelete($mOneroom->table['premium_item'],"where `ino`='{$slot['ino']}' and `type`='SLOT'");
				$mDB->DBupdate($mOneroom->table['item'],array('is_premium'=>'FALSE'),'',"where `idx`='{$slot['ino']}'");
				
				$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>'0'),'',"where `idx`='{$idx[$i]}'");
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'auction') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		$start_time = mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_start'),date('Y'));
		$end_time = mktime(0,0,10,date('m'),$mOneroom->GetConfig('auction_end'),date('Y'));
		
		if ($start_time < time() && $end_time > time()) {
			$point = Request('point');
			
			if ($point + $mOneroom->GetConfig('auction_point') > $member['point']) {
				$return['success'] = false;
				$return['message'] = '포인트가 부족합니다.<br />먼저 포인트를 구매 후 참여하여 주십시오.';
			} else {
				$type = Request('type') ? Request('type') : '0';
				
				$lastAuction = $mDB->DBfetch($mOneroom->table['premium_item'],'*',"where `idx`='$type' and `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
				
				if (isset($lastAuction['idx']) == true) {
					if ($mOneroom->GetConfig('auction_limit') == '0' || $mOneroom->GetConfig('auction_limit')-$lastAuction['bidding']) {
						if ($lastAuction['point'] < $point) {
							$mMember->SendPoint($member['idx'],($point+$mOneroom->GetConfig('auction_point'))*-1,'프리미엄매물 경매 참여');
							$mMember->SendPoint($member['idx'],$lastAuction['point'],'프리미엄매물 상회입찰에 따른 반환');
							$mDB->DBupdate($mOneroom->table['premium_item'],array('point'=>$point,'last_bidding'=>GetGMT()),array('bidding'=>'`bidding`+1'),"where `idx`='{$lastAuction['idx']}'");
							$return['success'] = true;
						} else {
							$return['success'] = false;
							$return['message'] = '입찰가는 이전입찰가보다 높아야 합니다.';
						}
					} else {
						$return['success'] = false;
						$return['message'] = '최대입찰횟수를 초과하였습니다.';
					}
				} else {
					if ($mOneroom->GetConfig('auction_limit') == '0' || $mOneroom->GetConfig('auction_limit') - $mDB->DBcount($mOneroom->table['premium_item'],"where `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'") > 0) {
						if ($mOneroom->GetConfig('auction_min') <= $point) {
							$mMember->SendPoint($member['idx'],($point+$mOneroom->GetConfig('auction_point'))*-1,'프리미엄매물 경매 참여');
							$mDB->DBinsert($mOneroom->table['premium_item'],array('type'=>'AUCTION','month'=>$month,'mno'=>$member['idx'],'point'=>$point,'bidding'=>'1','last_bidding'=>GetGMT()));
							$return['success'] = true;
						} else {
							$return['success'] = false;
							$return['message'] = '입찰가는 경매최소입찰가('.$mOneroom->GetConfig('auction_min').'포인트)보다 높아야 합니다.';
						}
					} else {
						$return['success'] = false;
						$return['message'] = '최대입찰횟수를 초과하였습니다.';
					}
				}
			}
		} else {
			$return['success'] = false;
			$return['message'] = '현재 경매중이 아닙니다.<br />경매기간은 매월 '.$mOneroom->GetConfig('auction_start').'일부터 '.$mOneroom->GetConfig('auction_end').'일까지 입니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'return') {
		$returnPoint = 0;
		$limit_time = ((date('d') == $mOneroom->GetConfig('auction_end') && date('s') > 10) || date('d') > $mOneroom->GetConfig('auction_end')) ? date('Y-m') : date('Y-m',mktime(0,0,0,date('m')-1,1,date('Y')));
		$data = $mDB->DBfetchs($mOneroom->table['premium_item'],'*',"where `type`='AUCTION' and `mno`='{$member['idx']}' and `month`<='$limit_time' and (`status`='NEW' or `status`='FAIL')");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['status'] == 'NEW') {
				if (mktime(0,0,10,intval(array_pop(explode('-',$data[$i]['month'])))-1,$mOneroom->GetConfig('auction_end'),array_shift(explode('-',$data[$i]['month']))) < time()) {
					$data[$i]['status'] = $mDB->DBcount($mOneroom->table['premium_item'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('premium_limit') ? 'SUCCESS' : 'FAIL';
					$mDB->DBupdate($mOneroom->table['premium_item'],array('status'=>$lists[$i]['status']),'',"where `idx`='{$data[$i]['idx']}'");
				}
			}
			if ($data[$i]['status'] == 'FAIL') {
				$returnPoint+= $lists[$i]['point'];
			}
		}
		
		$return['success'] = true;
		if ($returnPoint == 0) {
			$return['message'] = '반환을 받지 않은 낙찰실패건이 없습니다.';
		} else {
			$mMember->SendPoint($member['idx'],$returnPoint,'프리미엄매물경매 낙찰실패건 포인트 반환');
			$return['message'] = '총 '.number_format($returnPoint).'포인트를 반환받았습니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'auction_link') {
		$idx = Request('idx');
		$auction = Request('auction');
		$auction = $mDB->DBfetch($mOneroom->table['premium_item'],'*',"where `idx`='$auction' and `mno`='{$member['idx']}' and `type`='AUCTION'");
		
		if (isset($auction['idx']) == true && strtotime($auction['month'].'-31') > time()) {
			if ($auction['ino'] != '0') {
				$mDB->DBupdate($mOneroom->table['item'],array('is_premium'=>'FALSE'),'',"where `idx`='{$slot['ino']}'");
			}
			
			$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
			if ($item['is_premium'] == 'TRUE') {
				$mDB->DBupdate($mOneroom->table['premium_item'],array('ino'=>'0'),'',"where `ino`='{$item['idx']}' and `type`='AUCTION'");
			}
			$mDB->DBupdate($mOneroom->table['premium_item'],array('ino'=>$item['idx'],'region1'=>$item['region1'],'region2'=>$item['region2'],'region3'=>$item['region3'],'category1'=>$item['category1'],'category2'=>$item['category2'],'category3'=>$item['category3']),'',"where `idx`='{$auction['idx']}'");
			$mDB->DBupdate($mOneroom->table['item'],array('is_premium'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['message'] = '해당 낙찰건의 활성화기간이 지났습니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'auction_unlink') {
		$idx = Request('idx');
		$mDB->DBupdate($mOneroom->table['premium_item'],array('ino'=>'0'),'',"where `idx` IN ($idx) and `mno`='{$member['idx']}' and `type`='AUCTION'");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'buy') {
		$idx = Request('idx');
		$myitem = $mDB->DBcount($mOneroom->table['premium_item'],"where `type`='POINT' and `ino` IN ($idx) and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
		$allitem = $mDB->DBcount($mOneroom->table['premium_item'],"where `type`='POINT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
		
		if (sizeof(explode(',',$idx)) * $mOneroom->GetConfig('premium_point') < $member['point']) {
			$return['success'] = false;
			$return['message'] = '회원님이 현재 보유하고 있는 포인트가 부족합니다.<br />총 '.number_format(sizeof(explode(',',$idx)) * $mOneroom->GetConfig('premium_point')).'포인트가 필요합니다.';
			exit(json_encode($return));
		}
		
		if ($mOneroom->GetConfig('premium_limit') == '0' || $mOneroom->GetConfig('premium_limit') >= $allitem+sizeof(explode(',',$idx))-$myitem) {
			$idx = explode(',',$idx);
			for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
				$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','mno','category1','category2','category3','region1','region2','region3','end_date'),"where `idx`='{$idx[$i]}' and `is_open`='TRUE'");
				if (isset($item['idx']) == false) continue;
				
				$premium = $mDB->DBfetch($mOneroom->table['premium_item'],'*',"where `type`='POINT' and `ino`='{$idx[$i]}' and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
				
				if (isset($premium['idx']) == true) {
					$end_time = $premium['end_time']+60*60*24*$mOneroom->GetConfig('premium_time');
					$mDB->DBupdate($mOneroom->table['premium_item'],array('end_time'=>$end_time),'',"where `idx`='{$premium['idx']}'");
					$end_date = $item['end_date'] == '0' ? '0' : $end_time;
					$mDB->DBupdate($mOneroom->table['item'],array('end_date'=>$end_date,'is_open'=>'TRUE','is_premium'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
				} else {
					$start_time = GetGMT();
					$end_time = $start_time + 60*60*24*$mOneroom->GetConfig('premium_time');
					$mDB->DBinsert($mOneroom->table['premium_item'],array('type'=>'POINT','ino'=>$item['idx'],'mno'=>$item['mno'],'category1'=>$item['category1'],'category2'=>$item['category3'],'category3'=>$item['category3'],'region1'=>$item['region1'],'region2'=>$item['region2'],'region3'=>$item['region3'],'start_time'=>$start_time,'end_time'=>$end_time));
					$end_date = $item['end_date'] == '0' ? '0' : $end_time;
					$mDB->DBupdate($mOneroom->table['item'],array('end_date'=>$end_date,'is_open'=>'TRUE','is_premium'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
				}
			}
			$mMember->SendPoint($member['idx'],$mOneroom->GetConfig('premium_point')*sizeof($idx)*1,'프리미엄매물 등록');
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['message'] = '등록가능한 지역추천매물갯수를 초과하였습니다.<br />현재 등록가능갯수는 '.number_format($mOneroom->GetConfig('premium_limit')-$allitem).'개 입니다.';
		}
		exit(json_encode($return));
	}
	
	if ($do == 'sale') {
		$idx = Request('idx');
		$premium = $mDB->DBfetchs($mOneroom->table['premium_item'],'*',"where `idx` IN ($idx) and `mno`='{$member['idx']}'");
		for ($i=0, $loop=sizeof($premium);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['premium_item'],"where `idx`='{$premium[$i]['idx']}'");
			$mDB->DBupdate($mOneroom->table['item'],array('is_premium'=>'FALSE'),'',"where `idx`='{$premium[$i]['ino']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'regionitem') {
	if ($do == 'slot_buy') {
		if ($mOneroom->GetConfig('regionitem_method') == 'slot') {
			$idx = Request('idx');
			
			$data = $mDB->DBfetch($mOneroom->table['slot'],'*',"where `idx`='$idx' and `type`='REGIONITEM'");
			
			if ($mOneroom->GetConfig('regionitem_limit') > 0 && $mOneroom->GetConfig('regionitem_limit') <= $mDB->DBcount($mOneroom->table['user_slot'],"where `type`='REGIONITEM' and `start_time`<".GetGMT()." and `end_time`>".GetGMT())) {
				$return['success'] = false;
				$return['message'] = '현재 활성화된 슬롯이 구매제한수를 초과하기 때문에 구매할 수 없습니다.';
				exit(json_encode($return));
			}
			
			if (isset($data['idx']) == true) {
				if ($mMember->IsLogged() == false) {
					$return['message'] = '먼저 회원로그인을 하여 주십시오.';
					$return['success'] = false;
				} elseif ($member['point'] < $data['price']) {
					$return['message'] = '포인트가 부족합니다.<br />먼저 포인트를 구매하여 주시기 바랍니다.';
					$return['success'] = false;
				} else {
					$mDB->DBinsert($mOneroom->table['user_slot'],array('mno'=>$member['idx'],'type'=>$data['type'],'start_time'=>GetGMT(),'end_time'=>GetGMT()+60*60*24*$data['term']));
					$mMember->SendPoint($member['idx'],$data['price']*-1,'지역추천슬롯 ('.$data['term'].'일) 구매');
					$return['success'] = true;
				}
			} else {
				$return['success'] = false;
				$return['message'] = '선택한 슬롯상품을 찾을 수 없습니다.';
			}
		} else {
			$return['success'] = false;
			$return['message'] = '지역추천노출방식이 슬롯방식이 아닙니다.';
		}
		exit(json_encode($return));
	}
	
	if ($do == 'slot_link') {
		$idx = Request('idx');
		$slot = Request('slot');
		$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
		$slot = $mDB->DBfetch($mOneroom->table['user_slot'],'*',"where `idx`='$slot' and `type`='REGIONITEM'");
		
		if (isset($item['idx']) == false || $item['is_open'] == 'FALSE') {
			$return['success'] = false;
			$return['message'] = '선택 매물이 비공개중이거나, 게시만료일자가 지났습니다.';
		} else {
			if (isset($slot['idx']) == true && $slot['start_time'] < GetGMT() && $slot['end_time'] > GetGMT()) {
				if ($slot['ino'] != '0') {
					$mDB->DBdelete($mOneroom->table['region_item'],"where `ino`='{$slot['ino']}' and `type`='SLOT'");
					$mDB->DBupdate($mOneroom->table['item'],array('is_regionitem'=>'FALSE'),'',"where `idx`='{$slot['ino']}'");
				}
				
				$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
				if ($item['is_regionitem'] == 'TRUE') {
					$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>'0'),'',"where `ino`='{$item['idx']}' and `type`='REGIONITEM'");
				}
				$mDB->DBinsert($mOneroom->table['region_item'],array('ino'=>$item['idx'],'type'=>'SLOT','region1'=>$item['region1'],'region2'=>$item['region2'],'region3'=>$item['region3'],'category1'=>$item['category1'],'category2'=>$item['category2'],'category3'=>$item['category3'],'start_time'=>$slot['start_time'],'end_time'=>$slot['end_time']));
				$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>$item['idx']),'',"where `idx`='{$slot['idx']}'");
				$mDB->DBupdate($mOneroom->table['item'],array('is_regionitem'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
				
				$return['success'] = true;
			} else {
				$return['success'] = false;
				$return['message'] = '해당 슬롯의 활성화기간이 지났습니다.';
			}
		}
		exit(json_encode($return));
	}
	
	if ($do == 'slot_unlink') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$slot = $mDB->DBfetch($mOneroom->table['user_slot'],'*',"where `idx`='{$idx[$i]}' and `type`='REGIONITEM'");
			if (isset($slot['idx']) == true && $slot['ino'] != '0') {
				$mDB->DBdelete($mOneroom->table['region_item'],"where `ino`='{$slot['ino']}' and `type`='SLOT'");
				$mDB->DBupdate($mOneroom->table['item'],array('is_regionitem'=>'FALSE'),'',"where `idx`='{$slot['ino']}'");
				
				$mDB->DBupdate($mOneroom->table['user_slot'],array('ino'=>'0'),'',"where `idx`='{$idx[$i]}'");
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'auction') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		$start_time = mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_start'),date('Y'));
		$end_time = mktime(0,0,10,date('m'),$mOneroom->GetConfig('auction_end'),date('Y'));
		
		if ($start_time < time() && $end_time > time()) {
			$point = Request('point');
			
			if ($point + $mOneroom->GetConfig('auction_point') > $member['point']) {
				$return['success'] = false;
				$return['message'] = '포인트가 부족합니다.<br />먼저 포인트를 구매 후 참여하여 주십시오.';
			} else {
				$type = Request('type') ? Request('type') : '0';
				
				$lastAuction = $mDB->DBfetch($mOneroom->table['region_item'],'*',"where `idx`='$type' and `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
				
				if (isset($lastAuction['idx']) == true) {
					if ($mOneroom->GetConfig('auction_limit') == '0' || $mOneroom->GetConfig('auction_limit')-$lastAuction['bidding']) {
						if ($lastAuction['point'] < $point) {
							$mMember->SendPoint($member['idx'],($point+$mOneroom->GetConfig('auction_point'))*-1,'지역추천매물 경매 참여');
							$mMember->SendPoint($member['idx'],$lastAuction['point'],'지역추천매물 상회입찰에 따른 반환');
							$mDB->DBupdate($mOneroom->table['region_item'],array('point'=>$point,'last_bidding'=>GetGMT()),array('bidding'=>'`bidding`+1'),"where `idx`='{$lastAuction['idx']}'");
							$return['success'] = true;
						} else {
							$return['success'] = false;
							$return['message'] = '입찰가는 이전입찰가보다 높아야 합니다.';
						}
					} else {
						$return['success'] = false;
						$return['message'] = '최대입찰횟수를 초과하였습니다.';
					}
				} else {
					if ($mOneroom->GetConfig('auction_limit') == '0' || $mOneroom->GetConfig('auction_limit') - $mDB->DBcount($mOneroom->table['region_item'],"where `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'") > 0) {
						if ($mOneroom->GetConfig('auction_min') <= $point) {
							$mMember->SendPoint($member['idx'],($point+$mOneroom->GetConfig('auction_point'))*-1,'지역추천매물 경매 참여');
							$mDB->DBinsert($mOneroom->table['region_item'],array('type'=>'AUCTION','month'=>$month,'mno'=>$member['idx'],'point'=>$point,'bidding'=>'1','last_bidding'=>GetGMT()));
							$return['success'] = true;
						} else {
							$return['success'] = false;
							$return['message'] = '입찰가는 경매최소입찰가('.$mOneroom->GetConfig('auction_min').'포인트)보다 높아야 합니다.';
						}
					} else {
						$return['success'] = false;
						$return['message'] = '최대입찰횟수를 초과하였습니다.';
					}
				}
			}
		} else {
			$return['success'] = false;
			$return['message'] = '현재 경매중이 아닙니다.<br />경매기간은 매월 '.$mOneroom->GetConfig('auction_start').'일부터 '.$mOneroom->GetConfig('auction_end').'일까지 입니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'return') {
		$returnPoint = 0;
		$limit_time = ((date('d') == $mOneroom->GetConfig('auction_end') && date('s') > 10) || date('d') > $mOneroom->GetConfig('auction_end')) ? date('Y-m') : date('Y-m',mktime(0,0,0,date('m')-1,1,date('Y')));
		$data = $mDB->DBfetchs($mOneroom->table['region_item'],'*',"where `type`='AUCTION' and `mno`='{$member['idx']}' and `month`<='$limit_time' and (`status`='NEW' or `status`='FAIL')");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['status'] == 'NEW') {
				if (mktime(0,0,10,intval(array_pop(explode('-',$data[$i]['month'])))-1,$mOneroom->GetConfig('auction_end'),array_shift(explode('-',$data[$i]['month']))) < time()) {
					$data[$i]['status'] = $mDB->DBcount($mOneroom->table['region_item'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('regionitem_limit') ? 'SUCCESS' : 'FAIL';
					$mDB->DBupdate($mOneroom->table['region_item'],array('status'=>$lists[$i]['status']),'',"where `idx`='{$data[$i]['idx']}'");
				}
			}
			if ($data[$i]['status'] == 'FAIL') {
				$returnPoint+= $lists[$i]['point'];
			}
		}
		
		$return['success'] = true;
		if ($returnPoint == 0) {
			$return['message'] = '반환을 받지 않은 낙찰실패건이 없습니다.';
		} else {
			$mMember->SendPoint($member['idx'],$returnPoint,'지역추천매물경매 낙찰실패건 포인트 반환');
			$return['message'] = '총 '.number_format($returnPoint).'포인트를 반환받았습니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'auction_link') {
		$idx = Request('idx');
		$auction = Request('auction');
		$auction = $mDB->DBfetch($mOneroom->table['region_item'],'*',"where `idx`='$auction' and `mno`='{$member['idx']}' and `type`='AUCTION'");
		
		if (isset($auction['idx']) == true && strtotime($auction['month'].'-31') > time()) {
			if ($auction['ino'] != '0') {
				$mDB->DBupdate($mOneroom->table['item'],array('is_regionitem'=>'FALSE'),'',"where `idx`='{$slot['ino']}'");
			}
			
			$item = $mDB->DBfetch($mOneroom->table['item'],'*',"where `idx`='$idx'");
			if ($item['is_regionitem'] == 'TRUE') {
				$mDB->DBupdate($mOneroom->table['region_item'],array('ino'=>'0'),'',"where `ino`='{$item['idx']}' and `type`='AUCTION'");
			}
			$mDB->DBupdate($mOneroom->table['region_item'],array('ino'=>$item['idx'],'region1'=>$item['region1'],'region2'=>$item['region2'],'region3'=>$item['region3'],'category1'=>$item['category1'],'category2'=>$item['category2'],'category3'=>$item['category3']),'',"where `idx`='{$auction['idx']}'");
			$mDB->DBupdate($mOneroom->table['item'],array('is_regionitem'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
			
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['message'] = '해당 낙찰건의 활성화기간이 지났습니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'auction_unlink') {
		$idx = Request('idx');
		$mDB->DBupdate($mOneroom->table['region_item'],array('ino'=>'0'),'',"where `idx` IN ($idx) and `mno`='{$member['idx']}' and `type`='AUCTION'");
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'buy') {
		$idx = Request('idx');
		$myitem = $mDB->DBcount($mOneroom->table['region_item'],"where `type`='POINT' and `ino` IN ($idx) and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
		$allitem = $mDB->DBcount($mOneroom->table['region_item'],"where `type`='POINT' and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
		
		if (sizeof(explode(',',$idx)) * $mOneroom->GetConfig('regionitem_point') < $member['point']) {
			$return['success'] = false;
			$return['message'] = '회원님이 현재 보유하고 있는 포인트가 부족합니다.<br />총 '.number_format(sizeof(explode(',',$idx)) * $mOneroom->GetConfig('regionitem_point')).'포인트가 필요합니다.';
			exit(json_encode($return));
		}
		
		if ($mOneroom->GetConfig('regionitem_limit') == '0' || $mOneroom->GetConfig('regionitem_limit') >= $allitem+sizeof(explode(',',$idx))-$myitem) {
			$idx = explode(',',$idx);
			for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
				$item = $mDB->DBfetch($mOneroom->table['item'],array('idx','mno','category1','category2','category3','region1','region2','region3','end_date'),"where `idx`='{$idx[$i]}' and `is_open`='TRUE'");
				if (isset($item['idx']) == false) continue;
				
				$regionitem = $mDB->DBfetch($mOneroom->table['region_item'],'*',"where `type`='POINT' and `ino`='{$idx[$i]}' and `start_time`<".GetGMT()." and `end_time`>".GetGMT());
				
				if (isset($regionitem['idx']) == true) {
					$end_time = $regionitem['end_time']+60*60*24*$mOneroom->GetConfig('regionitem_time');
					$mDB->DBupdate($mOneroom->table['region_item'],array('end_time'=>$end_time),'',"where `idx`='{$regionitem['idx']}'");
					$end_date = $item['end_date'] == '0' ? '0' : $end_time;
					$mDB->DBupdate($mOneroom->table['item'],array('end_date'=>$end_date,'is_open'=>'TRUE','is_regionitem'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
				} else {
					$start_time = GetGMT();
					$end_time = $start_time + 60*60*24*$mOneroom->GetConfig('regionitem_time');
					$mDB->DBinsert($mOneroom->table['region_item'],array('type'=>'POINT','ino'=>$item['idx'],'mno'=>$item['mno'],'category1'=>$item['category1'],'category2'=>$item['category3'],'category3'=>$item['category3'],'region1'=>$item['region1'],'region2'=>$item['region2'],'region3'=>$item['region3'],'start_time'=>$start_time,'end_time'=>$end_time));
					$end_date = $item['end_date'] == '0' ? '0' : $end_time;
					$mDB->DBupdate($mOneroom->table['item'],array('end_date'=>$end_date,'is_open'=>'TRUE','is_regionitem'=>'TRUE'),'',"where `idx`='{$item['idx']}'");
				}
			}
			
			$mMember->SendPoint($member['idx'],$mOneroom->GetConfig('regionitem_point')*sizeof($idx)*1,'지역추천매물 등록');
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['message'] = '등록가능한 지역추천매물갯수를 초과하였습니다.<br />현재 등록가능갯수는 '.number_format($mOneroom->GetConfig('regionitem_limit')-$allitem).'개 입니다.';
		}
		exit(json_encode($return));
	}
	
	if ($do == 'sale') {
		$idx = Request('idx');
		$regionitem = $mDB->DBfetchs($mOneroom->table['region_item'],'*',"where `idx` IN ($idx) and `mno`='{$member['idx']}'");
		for ($i=0, $loop=sizeof($regionitem);$i<$loop;$i++) {
			$mDB->DBdelete($mOneroom->table['region_item'],"where `idx`='{$regionitem[$i]['idx']}'");
			$mDB->DBupdate($mOneroom->table['item'],array('is_regionitem'=>'FALSE'),'',"where `idx`='{$regionitem[$i]['ino']}'");
		}
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'prodealer') {
	if ($do == 'auction') {
		$month = date('Y-m',mktime(0,0,0,date('m')+1,1,date('Y')));
		$start_time = mktime(0,0,0,date('m'),$mOneroom->GetConfig('auction_start'),date('Y'));
		$end_time = mktime(0,0,10,date('m'),$mOneroom->GetConfig('auction_end'),date('Y'));
		
		if ($start_time < time() && $end_time > time()) {
			$point = Request('point');
			
			if ($point + $mOneroom->GetConfig('auction_point') > $member['point']) {
				$return['success'] = false;
				$return['message'] = '포인트가 부족합니다.<br />먼저 포인트를 구매 후 참여하여 주십시오.';
			} else {
				$region1 = Request('region1');
				$region2 = Request('region2');
				$region3 = Request('region3');
				
				$lastAuction = $mDB->DBfetch($mOneroom->table['prodealer'],'*',"where `mno`='{$member['idx']}' and `type`='AUCTION' and `month`='$month'");
			
				if (isset($lastAuction['idx']) == true) {
					if ($lastAuction['point'] < $point) {
						$mMember->SendPoint($member['idx'],($point+$mOneroom->GetConfig('auction_point'))*-1,'지역전문가 경매 참여');
						$mMember->SendPoint($member['idx'],$lastAuction['point'],'지역전문가 상회입찰에 따른 반환');
						$mDB->DBupdate($mOneroom->table['prodealer'],array('month'=>$month,'mno'=>$member['idx'],'point'=>$point,'region1'=>$region1,'region2'=>$region2,'region3'=>$region3,'last_bidding'=>GetGMT()),array('bidding'=>'`bidding`+1'),"where `idx`='{$lastAuction['idx']}'");
						$return['success'] = true;
					} else {
						$return['success'] = false;
						$return['message'] = '입찰가는 이전입찰가보다 높아야 합니다.';
					}
				} else {
					if ($mOneroom->GetConfig('auction_min') <= $point) {
						$mMember->SendPoint($member['idx'],($point+$mOneroom->GetConfig('auction_point'))*-1,'지역전문가 경매 참여');
						$mDB->DBinsert($mOneroom->table['prodealer'],array('type'=>'AUCTION','month'=>$month,'mno'=>$member['idx'],'point'=>$point,'region1'=>$region1,'region2'=>$region2,'region3'=>$region3,'bidding'=>'1','last_bidding'=>GetGMT()));
						$return['success'] = true;
					} else {
						$return['success'] = false;
						$return['message'] = '입찰가는 경매최소입찰가('.$mOneroom->GetConfig('auction_min').'포인트)보다 높아야 합니다.';
					}
				}
			}
		} else {
			$return['success'] = false;
			$return['message'] = '현재 경매중이 아닙니다.<br />경매기간은 매월 '.$mOneroom->GetConfig('auction_start').'일부터 '.$mOneroom->GetConfig('auction_end').'일까지 입니다.';
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'return') {
		$returnPoint = 0;
		$limit_time = ((date('d') == $mOneroom->GetConfig('auction_end') && date('s') > 10) || date('d') > $mOneroom->GetConfig('auction_end')) ? date('Y-m') : date('Y-m',mktime(0,0,0,date('m')-1,1,date('Y')));
		$data = $mDB->DBfetchs($mOneroom->table['prodealer'],'*',"where `type`='AUCTION' and `mno`='{$member['idx']}' and `month`<='$limit_time' and (`status`='NEW' or `status`='FAIL')");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['status'] == 'NEW') {
				if (mktime(0,0,10,intval(array_pop(explode('-',$data[$i]['month'])))-1,$mOneroom->GetConfig('auction_end'),array_shift(explode('-',$data[$i]['month']))) < time()) {
					$data[$i]['status'] = $mDB->DBcount($mOneroom->table['prodealer'],"where `type`='AUCTION' and `month`='{$lists[$i]['month']}' and (`point`>{$lists[$i]['point']} or (`point`={$lists[$i]['point']} and `last_bidding`<{$lists[$i]['last_bidding']}))") < $mOneroom->GetConfig('prodealer_limit') ? 'SUCCESS' : 'FAIL';
					$mDB->DBupdate($mOneroom->table['prodealer'],array('status'=>$lists[$i]['status']),'',"where `idx`='{$data[$i]['idx']}'");
				}
			}
			if ($data[$i]['status'] == 'FAIL') {
				$returnPoint+= $lists[$i]['point'];
			}
		}
		
		$return['success'] = true;
		if ($returnPoint == 0) {
			$return['message'] = '반환을 받지 않은 낙찰실패건이 없습니다.';
		} else {
			$mMember->SendPoint($member['idx'],$returnPoint,'지역전문가경매 낙찰실패건 포인트 반환');
			$return['message'] = '총 '.number_format($returnPoint).'포인트를 반환받았습니다.';
		}
		
		exit(json_encode($return));
	}
}

if ($action == 'point') {
	if ($do == 'payment') {
		$mModule = new Module('point');
		if ($mModule->IsSetup() == true) {
			$mPoint = new ModulePoint();
			
			$insert['mno'] = $member['idx'];
			$insert['point'] = Request('point');
			$insert['price'] = ceil($insert['point']/$mPoint->GetConfig('ratio')/100)*100;
			$insert['payment'] = Request('payment');
			
			$payment = $mDB->DBfetch($mPoint->table['payment'],'*',"where `idx`='{$insert['payment']}'");
			switch ($payment['type']) {
				case 'BANKING' :
					$insert['payinfo'] = Request('banking2').' (입금예정일 : '.Request('banking1').')';
					$message = Request('banking1').'일까지 '.number_format($insert['price']).'원을 입금하여 주십시오.<br />입금계좌 : '.$payment['value'];
					break;
					
				default :
					$message = '';
			}
			
			$insert['reg_date'] = GetGMT();
			$mDB->DBinsert($mPoint->table['buy'],$insert);
			$return['success'] = true;
			$return['type'] = $payment['type'];
			$return['message'] = $message;
		} else {
			$return['success'] = false;
		}
		
		exit(json_encode($return));
	}
}
?>