<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$wno = Request('wno');
$action = Request('action');
$do = Request('do');
$mErp = new ModuleErp();

/************************************************************************************************
 * 작업일보
 ***********************************************************************************************/
if ($action == 'work') {
	// 일일상황일지
	if ($do == 'daily') {
		$date = Request('date') ? Request('date') : date('Y-m-d');
		$data = serialize(GetExtData('data'));
		$weather = Request('weather');

		$check = $mDB->DBfetch($mErp->table['workreport'],array('idx'),"where `wno`=$wno and `date`='$date'");
		if (isset($check['idx']) == true) {
			$mDB->DBupdate($mErp->table['workreport'],array('weather'=>$weather,'data'=>$data),'',"where `idx`={$check['idx']}");
		} else {
			$mDB->DBinsert($mErp->table['workreport'],array('wno'=>$wno,'date'=>$date,'weather'=>$weather,'data'=>$data));
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 직영
	if ($do == 'member') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		if ($mode == 'modify') {
			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBupdate($mErp->table['attend_member'],array('gno'=>$data[$i]['gno'],'tno'=>$data[$i]['tno'],'work'=>$data[$i]['work'],'working'=>$data[$i]['working'],'is_overwork'=>$data[$i]['is_overwork'],'etc'=>$data[$i]['etc']),'',"where `idx`={$data[$i]['idx']}");
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 작업로딩
		if ($mode == 'load') {
			$yesterday = $mDB->DBfetch($mErp->table['attend_member'],array('date'),"where `wno`=$wno and `date`<'$date' and `work`!=''",'date,desc');
			if (isset($yesterday['date']) == true) {
				$data = $mDB->DBfetchs($mErp->table['attend_member'],'*',"where `wno`=$wno and `date`='{$yesterday['date']}'");
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$mDB->DBupdate($mErp->table['attend_member'],array('gno'=>$data[$i]['gno'],'tno'=>$data[$i]['tno'],'work'=>$data[$i]['work']),'',"where `wno`=$wno and `date`='$date' and `workernum`='{$data[$i]['workernum']}'");
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 외주업체
	if ($do == 'outsourcing') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 작업등록
		if ($mode == 'add') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$Error = array();
			$insert = array();
			$insert['wno'] = Request('wno');
			$insert['cno'] = Request('cno');
			$insert['gno'] = Request('gno');
			$insert['tno'] = Request('tno');
			$insert['date'] = $date;
			$insert['work'] = Request('work');
			$insert['job'] = Request('job');
			$insert['worker'] = Request('worker');
			$insert['payment'] = str_replace(',','',Request('payment'));
			$insert['intime'] = Request('intime') ? GetGMT(strtotime($date.' '.Request('intime').':00')) : '0';
			$insert['outtime'] = Request('outtime') ? GetGMT(strtotime($date.' '.Request('outtime').':00')) : '0';
			$insert['is_overwork'] = Request('is_overwork') == 'TRUE' ? 'TRUE' : 'FALSE';
			if (sizeof($Error) == 0) {
				$check = $mDB->DBfetch($mErp->table['attend_outsourcing'],array('idx'),"where `date`='$date' and `cno`={$insert['cno']} and `job`='{$insert['job']}'");
				if (isset($check['idx']) == true) {
					$idx = $check['idx'];
					$mDB->DBupdate($mErp->table['attend_outsourcing'],$insert,'',"where `idx`={$check['idx']}");
				} else {
					$idx = $mDB->DBinsert($mErp->table['attend_outsourcing'],$insert);
				}
			}

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
		}

		// 작업수정
		if ($mode == 'modify') {
			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBupdate($mErp->table['attend_outsourcing'],array('gno'=>$data[$i]['gno'],'tno'=>$data[$i]['tno'],'work'=>$data[$i]['work'],'payment'=>$data[$i]['payment'],'worker'=>$data[$i]['worker'],'is_overwork'=>$data[$i]['is_overwork']),'',"where `idx`={$data[$i]['idx']}");
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 작업삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['attend_outsourcing'],"where `idx` IN ($idx) and `wno`=$wno");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 작업로딩
		if ($mode == 'load') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$yesterday = $mDB->DBfetch($mErp->table['attend_outsourcing'],array('date'),"where `wno`=$wno and `date`<'$date'",'date,desc');
			if (isset($yesterday['date']) == true) {
				$mDB->DBdelete($mErp->table['attend_outsourcing'],"where `wno`=$wno and `date`='$date'");
				$data = $mDB->DBfetchs($mErp->table['attend_outsourcing'],'*',"where `wno`=$wno and `date`='{$yesterday['date']}'");
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					unset($data[$i]['idx']);
					$data[$i]['date'] = $date;
					$mDB->DBinsert($mErp->table['attend_outsourcing'],$data[$i]);
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 일용직노무
	if ($do == 'dayworker') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 작업등록
		if ($mode == 'add') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$dayworker = $mDB->DBfetch($mErp->table['dayworker'],'*',"where `idx`=".Request('dno'));

			$Error = array();
			$insert = array();
			$insert['wno'] = Request('wno');
			$insert['dno'] = Request('dno');
			$insert['gno'] = Request('gno');
			$insert['tno'] = Request('tno');
			$insert['date'] = $date;
			$insert['work'] = Request('work');
			$insert['job'] = $dayworker['type'] == '개인' ? $dayworker['job'] : Request('job');
			$insert['worker'] = $dayworker['type'] == '개인' ? 1 : Request('worker');
			$insert['payment'] = str_replace(',','',Request('payment'));
			$insert['intime'] = Request('intime') ? GetGMT(strtotime($date.' '.Request('intime').':00')) : '0';
			$insert['outtime'] = Request('outtime') ? GetGMT(strtotime($date.' '.Request('outtime').':00')) : '0';
			$insert['is_overwork'] = Request('is_overwork') == 'TRUE' ? 'TRUE' : 'FALSE';

			if (sizeof($Error) == 0) {
				$check = $mDB->DBfetch($mErp->table['attend_dayworker'],array('idx'),"where `date`='$date' and `dno`={$insert['dno']} and `job`='{$insert['job']}'");
				if (isset($check['idx']) == true) {
					$idx = $check['idx'];
					$mDB->DBupdate($mErp->table['attend_dayworker'],$insert,'',"where `idx`={$check['idx']}");
				} else {
					$idx = $mDB->DBinsert($mErp->table['attend_dayworker'],$insert);
				}
				$mDB->DBupdate($mErp->table['dayworker'],array('payment'=>$insert['payment']),'',"where `idx`={$insert['dno']}");
			}

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
		}

		// 작업수정
		if ($mode == 'modify') {
			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBupdate($mErp->table['attend_dayworker'],array('gno'=>$data[$i]['gno'],'tno'=>$data[$i]['tno'],'work'=>$data[$i]['work'],'payment'=>$data[$i]['payment'],'is_overwork'=>$data[$i]['is_overwork']),'',"where `idx`={$data[$i]['idx']}");
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 작업삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['attend_dayworker'],"where `idx` IN ($idx) and `wno`=$wno");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 작업로딩
		if ($mode == 'load') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$yesterday = $mDB->DBfetch($mErp->table['attend_dayworker'],array('date'),"where `wno`=$wno and `date`<'$date'",'date,desc');
			if (isset($yesterday['date']) == true) {
				$mDB->DBdelete($mErp->table['attend_dayworker'],"where `wno`=$wno and `date`='$date'");
				$data = $mDB->DBfetchs($mErp->table['attend_dayworker'],'*',"where `wno`=$wno and `date`='{$yesterday['date']}'");
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					unset($data[$i]['idx']);
					$data[$i]['date'] = $date;
					$mDB->DBinsert($mErp->table['attend_dayworker'],$data[$i]);
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 자재입고관리
	if ($do == 'item') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 수정
		if ($mode == 'modify') {
			$date = Request('date') ? Request('date') : date('Y-m-d');;
			$data = GetExtData('data');
			$check = array();
			$saveData = array();
			$total_price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['idx'] == '-1') continue; // 본사발주자재는 저장하지 않음

				$insert = array();
				$insert['wno'] = $wno;
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$insert['itemcode']);
				$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost'],0,0);
				$insert['date'] = $date;
				$insert['gno'] = $data[$i]['gno'];
				$insert['tno'] = $data[$i]['tno'];
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost'] = $data[$i]['cost'];
				$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
				$insert['payment'] = $data[$i]['payment'];
				$insert['cooperation'] = $data[$i]['cooperation'];
				$insert['etc'] = $data[$i]['etc'];

				if ($data[$i]['idx'] == '0') { // 신규자재일경우,
					$mDB->DBinsert($mErp->table['payment_item'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['payment_item'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['payment_item'],"where `wno`=$wno and `idx` IN ($idx)");
		}

		// 입고 및 반출처리
		if ($mode == 'income') {
			$date = Request('date') ? Request('date') : date('Y-m-d');

			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$item = $mDB->DBfetch($mErp->table['itemorder_item'],'*',"where `idx`={$data[$i]['idx']}");

				$insert = array();
				$insert['wno'] = $item['wno'];
				$insert['gno'] = $item['gno'];
				$insert['tno'] = $item['tno'];
				$insert['cno'] = $item['cno'];
				$insert['code'] = $item['code'];
				$insert['subcode'] = $item['subcode'];
				$insert['title'] = $item['title'];
				$insert['size'] = $item['size'];
				$insert['unit'] = $item['unit'];
				$insert['repto'] = $item['repto'];
				$insert['cost'] = $item['cost1']+$item['cost2']+$item['cost3'];
				$insert['date'] = $date;

				if ($data[$i]['income'] > 0) {
					$insert['ea'] = $data[$i]['income'];
					$insert['price'] = floor($insert['ea']*$insert['cost']);

					$mDB->DBinsert($mErp->table['itemorder_income'],$insert);
				}

				if ($data[$i]['outcome'] > 0) {
					$insert['ea'] = -$data[$i]['outcome'];
					$insert['price'] = floor($insert['ea']*$insert['cost']);

					$mDB->DBinsert($mErp->table['itemorder_income'],$insert);
				}
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 경비지출관리
	if ($do == 'expense') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 수정
		if ($mode == 'modify') {
			$date = Request('date') ? Request('date') : date('Y-m-d');;
			$data = GetExtData('data');
			$check = array();
			$saveData = array();
			$total_price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['idx'] == '-1') continue; // 본사발주자재는 저장하지 않음

				$insert = array();
				$insert['wno'] = $wno;
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$insert['itemcode']);
				$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost'],0,0);
				$insert['date'] = $date;
				$insert['gno'] = $data[$i]['gno'];
				$insert['tno'] = $data[$i]['tno'];
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost'] = $data[$i]['cost'];
				$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
				$insert['payment'] = $data[$i]['payment'];
				$insert['cooperation'] = $data[$i]['cooperation'];
				$insert['etc'] = $data[$i]['etc'];

				if ($data[$i]['idx'] == '0') { // 신규자재일경우,
					$mDB->DBinsert($mErp->table['payment_expense'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['payment_expense'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['payment_expense'],"where `wno`=$wno and `idx` IN ($idx)");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 장비입고관리
	if ($do == 'equipment') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 수정
		if ($mode == 'modify') {
			$date = Request('date') ? Request('date') : date('Y-m-d');;
			$data = GetExtData('data');
			$check = array();
			$saveData = array();
			$total_price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['idx'] == '-1') continue; // 본사발주자재는 저장하지 않음

				$insert = array();
				$insert['wno'] = $wno;
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$insert['itemcode']);
				$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost'],0,0);
				$insert['date'] = $date;
				$insert['gno'] = $data[$i]['gno'];
				$insert['tno'] = $data[$i]['tno'];
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost'] = $data[$i]['cost'];
				$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
				$insert['payment'] = $data[$i]['payment'];
				$insert['cooperation'] = $data[$i]['cooperation'];
				$insert['etc'] = $data[$i]['etc'];

				if ($data[$i]['idx'] == '0') { // 신규자재일경우,
					$mDB->DBinsert($mErp->table['payment_equipment'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['payment_equipment'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['payment_equipment'],"where `wno`=$wno and `idx` IN ($idx)");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

/************************************************************************************************
 * 근로자관리
 ***********************************************************************************************/
if ($action == 'worker') {
	// 현장근로자 관리
	if ($do == 'worker') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');
		// 신규 및 수정
		if ($mode == 'add' || $mode == 'modify') {
			$Error = array();
			$insert = array();
			$wno = Request('wno');
			$insert['name'] = Request('name') ? Request('name') : $Error['name'] = '이름을 입력하여 주십시오.';
			$insert['jumin'] = CheckJumin(Request('jumin')) ? GetJumin(Request('jumin')) : $Error['jumin'] = '주민등록번호가 잘못 입력되었습니다.';
			$insert['grade'] = Request('grade').'||'.Request('grade_handwrite');
			$insert['enter_date'] = Request('enter_date');
			$insert['retire_date'] = Request('retire_date');
			$insert['zipcode'] = Request('zipcode');
			$insert['address'] = Request('address1').'||'.Request('address2');
			$insert['pay_type'] = Request('pay_type');
			$insert['work_type'] = Request('work_type');
			$insert['payment'] = str_replace(',','',Request('payment'));
			$insert['account'] = Request('account_name').'||'.Request('account_bank').'||'.Request('account_number');
			$insert['telephone'] = CheckPhoneNumber(Request('telephone')) == true ? GetPhoneNumber(Request('telephone')) : '';
			$insert['cellphone'] = CheckPhoneNumber(Request('cellphone')) == true ? GetPhoneNumber(Request('cellphone')) : '';

			$photo = Request('photo');
			$workstart_date = Request('workstart_date');
			$workend_date = Request('workend_date');

			if ($mode == 'add') {
				$pno = $mDB->DBinsert($mErp->table['worker'],$insert);
				$workernum = rand(1,9).sprintf('%04d',$wno).sprintf('%07d',$pno);
				if (sizeof($Error) == 0) $idx = $mDB->DBinsert($mErp->table['workerspace'],array('wno'=>$wno,'pno'=>$pno,'workernum'=>$workernum,'workstart_date'=>$workstart_date,'workend_date'=>$workend_date));
			} else {
				$idx = Request('idx');
				if (sizeof($Error) == 0) $mDB->DBupdate($mErp->table['workerspace'],array('workstart_date'=>$workstart_date,'workend_date'=>$workend_date),'',"where `idx`=$idx");
				$workerspace = $mDB->DBfetch($mErp->table['workerspace'],array('pno'),"where `idx`=$idx");
				$pno = isset($workerspace['pno']) == true ? $workerspace['pno'] : '0';
				if (sizeof($Error) == 0) $mDB->DBupdate($mErp->table['worker'],$insert,'',"where `idx`='$pno'");
			}

			if ($photo && file_exists($photo) == true) {
				if (CreateDirectory($_ENV['path'].'/userfile/erp/worker') == true) {
					@unlink($_ENV['path'].'/userfile/erp/worker/'.$pno.'.jpg');
					@copy($photo,$_ENV['path'].'/userfile/erp/worker/'.$pno.'.jpg');
				}
				@unlink($photo);
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

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');
			$mDB->DBdelete($mErp->table['workerspace'],"where `wno`=$wno and `pno`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 일용직근로자 관리
	if ($do == 'dayworker') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');
		// 신규 및 수정
		if ($mode == 'add' || $mode == 'modify') {
			$Error = array();
			$insert = array();
			$insert['wno'] = Request('wno');
			$insert['type'] = Request('type');
			$insert['name'] = Request('name') ? Request('name') : $Error['name'] = '이름을 입력하여 주십시오.';
			$insert['jumin'] = Request('jumin');
			$insert['workstart_date'] = Request('workstart_date');
			$insert['job'] = Request('job');
			$insert['workend_date'] = Request('workend_date');
			$insert['payment'] = str_replace(',','',Request('payment'));
			$insert['account'] = Request('account_name').'||'.Request('account_bank').'||'.Request('account_number');
			$insert['telephone'] = CheckPhoneNumber(Request('telephone')) == true ? GetPhoneNumber(Request('telephone')) : '';
			$insert['cellphone'] = CheckPhoneNumber(Request('cellphone')) == true ? GetPhoneNumber(Request('cellphone')) : '';
			$insert['gno'] = Request('workgroup');
			$insert['tno'] = Request('worktype');
			$insert['contract'] = str_replace(',','',Request('contract'));

			if ($mode == 'add') {
				if (sizeof($Error) == 0) $idx = $mDB->DBinsert($mErp->table['dayworker'],$insert);
			} else {
				$idx = Request('idx');
				if (sizeof($Error) == 0) $mDB->DBupdate($mErp->table['dayworker'],$insert,'',"where `idx`=$idx");
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

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');
			$mDB->DBdelete($mErp->table['dayworker'],"where `idx`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}
}

/************************************************************************************************
 * 자재발주관리
 ***********************************************************************************************/
if ($action == 'itemorder') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	// 발주등록
	if ($do == 'add' || $do == 'modify') {
		$list = GetExtData('data');

		$check = array();
		$data = array();

		for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
			// 자재코드
			$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
			$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);

			if (isset($check[$code]) == false) {
				$check[$code] = true;
				unset($list[$i]['order_ea']);
				$data[] = $list[$i];
			}
		}

		$insert = array();
		$insert['data'] = serialize($data);

		if ($do == 'add') {
			$insert['wno'] = $wno;
			$insert['order_type'] = 'ITEMORDER';
			$insert['type'] = Request('type');
			$insert['title'] = Request('title');
			$insert['date'] = GetTime('Y-m-d H:i:s');
			$insert['status'] = 'NEW';
			$insert['etc'] = Request('etc');

			if (isset($_FILES['file']) == true && $_FILES['file']['tmp_name']) {
				$filepath = '/userfile/erp/attach/'.GetTime('Y-m');
				if (CreateDirectory($_ENV['path'].$filepath) == true) {
					$filename = $_FILES['file']['name'];
					$filepath.= '/'.md5_file($_FILES['file']['tmp_name']).'.'.time().'.'.rand(100000,999999);
					$insert['file'] = $filepath.'|'.$filename.'|'.filesize($_FILES['file']['tmp_name']);
					@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['path'].$filepath);
				}
			}
			$idx = $mDB->DBinsert($mErp->table['outsourcing_order'],$insert);
			//$mErp->SendSMS('order','[자재발주요청]'.$mErp->GetWorkspaceTitle($wno));
		} else {
			$idx = Request('idx');
			$mDB->DBupdate($mErp->table['outsourcing_order'],$insert,'',"where `idx`=$idx");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '<errors>';
		echo '<field><id>'.$idx.','.(sizeof($list)-sizeof($data)).'</id></field>';
		echo '</errors>';
		echo '</message>';
	}

	// 발주요청서명 변경
	if ($do == 'title') {
		$idx = Request('idx');
		$title = Request('title');

		$mDB->DBupdate($mErp->table['outsourcing_order'],array('title'=>$title),'',"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 삭제
	if ($do == 'delete') {
		$idx = Request('idx');
		$data = $mDB->DBdelete($mErp->table['outsourcing_order'],"where `idx`=$idx and `status`='NEW'");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 첨부파일 다운로드
	if ($do == 'file') {
		$idx = Request('idx');
		$mode = Request('mode');
		$step = Request('step');

		if ($mode == 'download') {
			if ($step == 'order') {
				$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
				if (isset($data['file']) == false || $data['file'] == '') {
					Alertbox('첨부된 파일이 없습니다.');
				} else {
					$file = explode('|',$data['file']);
					GetFileDownload($_ENV['path'].$file[0],$file[1],$file[2]);
				}
			}
		}

		if ($mode == 'upload') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$fileinfo = '';
			if (isset($_FILES['file']) == true && $_FILES['file']['tmp_name']) {
				$filepath = '/userfile/erp/attach/'.GetTime('Y-m');
				if (CreateDirectory($_ENV['path'].$filepath) == true) {
					$filename = $_FILES['file']['name'];
					$filepath.= '/'.md5_file($_FILES['file']['tmp_name']).'.'.time().'.'.rand(100000,999999);
					$fileinfo = $filepath.'|'.$filename.'|'.filesize($_FILES['file']['tmp_name']);
					@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['path'].$filepath);
				}

				if ($step == 'order') {
					$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
					if (isset($data['file']) == true && $data['file'] != '') {
						$file = explode('|',$data['file']);
						@unlink($_ENV['path'].$file[0]);
					}

					$mDB->DBupdate($mErp->table['outsourcing_order'],array('file'=>$fileinfo),'',"where `idx`=$idx");
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '<errors>';
			echo '<field><id>'.$fileinfo.'</id></field>';
			echo '</errors>';
			echo '</message>';
		}

		if ($mode == 'delete') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			if ($step == 'order') {
				$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
				if (isset($data['file']) == true && $data['file'] != '') {
					$file = explode('|',$data['file']);
					@unlink($_ENV['path'].$file[0]);
					$mDB->DBupdate($mErp->table['outsourcing_order'],array('file'=>''),'',"where `idx`=$idx");
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}
}

/************************************************************************************************
 * 하도급발주관리
 ***********************************************************************************************/
if ($action == 'outsourcing') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	// 발주등록
	if ($do == 'add' || $do == 'modify') {
		$list = GetExtData('data');

		$check = array();
		$data = array();

		for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
			// 자재코드
			$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
			$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);

			if (isset($check[$code]) == false) {
				$check[$code] = true;
				unset($list[$i]['order_ea']);
				$data[] = $list[$i];
			}
		}

		$insert = array();
		$insert['data'] = serialize($data);

		if ($do == 'add') {
			$insert['wno'] = $wno;
			$insert['order_type'] = 'OUTSOURCING';
			$insert['type'] = Request('type');
			$insert['title'] = Request('title');
			$insert['date'] = GetTime('Y-m-d H:i:s');
			$insert['status'] = 'NEW';
			$insert['etc'] = Request('etc');

			if (isset($_FILES['file']) == true && $_FILES['file']['tmp_name']) {
				$filepath = '/userfile/erp/attach/'.GetTime('Y-m');
				if (CreateDirectory($_ENV['path'].$filepath) == true) {
					$filename = $_FILES['file']['name'];
					$filepath.= '/'.md5_file($_FILES['file']['tmp_name']).'.'.time().'.'.rand(100000,999999);
					$insert['file'] = $filepath.'|'.$filename.'|'.filesize($_FILES['file']['tmp_name']);
					@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['path'].$filepath);
				}
			}
			$idx = $mDB->DBinsert($mErp->table['outsourcing_order'],$insert);

			$mErp->SendSMS('order','[하도급발주요청]'.$mErp->GetWorkspaceTitle($wno));
		} else {
			$idx = Request('idx');
			$mDB->DBupdate($mErp->table['outsourcing_order'],$insert,'',"where `idx`=$idx");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '<errors>';
		echo '<field><id>'.$idx.','.(sizeof($list)-sizeof($data)).'</id></field>';
		echo '</errors>';
		echo '</message>';
	}

	// 발주요청서명 변경
	if ($do == 'title') {
		$idx = Request('idx');
		$title = Request('title');

		$mDB->DBupdate($mErp->table['outsourcing_order'],array('title'=>$title),'',"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 삭제
	if ($do == 'delete') {
		$idx = Request('idx');
		$data = $mDB->DBdelete($mErp->table['outsourcing_order'],"where `idx`=$idx and `status`='NEW'");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 첨부파일 다운로드
	if ($do == 'file') {
		$idx = Request('idx');
		$mode = Request('mode');
		$step = Request('step');

		if ($mode == 'download') {
			if ($step == 'order') {
				$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
				if (isset($data['file']) == false || $data['file'] == '') {
					Alertbox('첨부된 파일이 없습니다.');
				} else {
					$file = explode('|',$data['file']);
					GetFileDownload($_ENV['path'].$file[0],$file[1],$file[2]);
				}
			}
		}

		if ($mode == 'upload') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$fileinfo = '';
			if (isset($_FILES['file']) == true && $_FILES['file']['tmp_name']) {
				$filepath = '/userfile/erp/attach/'.GetTime('Y-m');
				if (CreateDirectory($_ENV['path'].$filepath) == true) {
					$filename = $_FILES['file']['name'];
					$filepath.= '/'.md5_file($_FILES['file']['tmp_name']).'.'.time().'.'.rand(100000,999999);
					$fileinfo = $filepath.'|'.$filename.'|'.filesize($_FILES['file']['tmp_name']);
					@move_uploaded_file($_FILES['file']['tmp_name'],$_ENV['path'].$filepath);
				}

				if ($step == 'order') {
					$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
					if (isset($data['file']) == true && $data['file'] != '') {
						$file = explode('|',$data['file']);
						@unlink($_ENV['path'].$file[0]);
					}

					$mDB->DBupdate($mErp->table['outsourcing_order'],array('file'=>$fileinfo),'',"where `idx`=$idx");
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '<errors>';
			echo '<field><id>'.$fileinfo.'</id></field>';
			echo '</errors>';
			echo '</message>';
		}

		if ($mode == 'delete') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			if ($step == 'order') {
				$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
				if (isset($data['file']) == true && $data['file'] != '') {
					$file = explode('|',$data['file']);
					@unlink($_ENV['path'].$file[0]);
					$mDB->DBupdate($mErp->table['outsourcing_order'],array('file'=>''),'',"where `idx`=$idx");
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}
}

/************************************************************************************************
 * 경비관리
 ***********************************************************************************************/
if ($action == 'payment') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$wno = Request('wno');
	$date = Request('date') ? Request('date') : date('Y-m');

	// 노무비관리
	if ($do == 'worker') {
		$mode = Request('mode');

		// 직영
		if ($mode == 'member') {
			$submode = Request('submode');

			// 수정
			if ($submode == 'modify') {
				$data = GetExtData('data');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$insert = array();
					$insert['wno'] = $wno;
					$insert['type'] = 'MEMBER';
					$insert['date'] = $date;
					$insert['pno'] = $data[$i]['idx'];
					$insert['pay_type'] = $data[$i]['pay_type'];
					$insert['attend'] = $data[$i]['attend_day'];
					$insert['cost'] = $data[$i]['pay'];
					$insert['tax1'] = $data[$i]['tax1'];
					$insert['tax2'] = $data[$i]['tax2'];
					$insert['tax3'] = $data[$i]['tax3'];
					$insert['tax4'] = $data[$i]['tax4'];
					$insert['tax5'] = $data[$i]['tax5'];
					$insert['revision'] = $data[$i]['revision'];
					$insert['price'] = $data[$i]['send_payment'];
					$insert['etc'] = $data[$i]['comment'];

					$account = $data[$i]['account_name'].'||'.$data[$i]['account_bank'].'||'.$data[$i]['account_number'];
					$mDB->DBupdate($mErp->table['worker'],array('account'=>$account,'pay_type'=>$insert['pay_type'],'payment'=>$insert['cost']),'',"where `idx`={$data[$i]['idx']}");
					$check = $mDB->DBfetch($mErp->table['payment_worker'],array('idx'),"where `date`='$date' and `wno`=$wno and `type`='MEMBER' and `pno`={$data[$i]['idx']}");
					if (isset($check['idx']) == true) {
						$mDB->DBupdate($mErp->table['payment_worker'],$insert,'',"where `idx`={$check['idx']}");
					} else {
						$mDB->DBinsert($mErp->table['payment_worker'],$insert);
					}
				}
			}

			// 근태기록 재조절
			if ($submode == 'attend') {
				$date = Request('date') ? Request('date') : date('Y-m');
				$data = $mDB->DBfetchs($mErp->table['payment_worker'],'*',"where `date`='$date' and `wno`=$wno and `type`='MEMBER'");
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$attend = $mDB->DBfetch($mErp->table['attend_member'],array('SUM(working)'),"where `wno`=$wno and `pno`={$data[$i]['pno']} and `date` like '$date%'");
					$insert['attend'] = $attend[0];
					$insert['price'] = ($data[$i]['pay_type'] == 'MONTH' ? $data[$i]['cost'] : floor($data[$i]['cost']*$attend[0]/10))-$data[$i]['tax1']-$data[$i]['tax2']-$data[$i]['tax3']-$data[$i]['tax4']-$data[$i]['tax5']+$data[$i]['revision'];

					$mDB->DBupdate($mErp->table['payment_worker'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 일용직
		if ($mode == 'dayworker') {
			$submode = Request('submode');

			if ($submode == 'modify') {
				$data = GetExtData('data');

				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$insert = array();
					$insert['wno'] = $wno;
					$insert['type'] = 'DAYWORKER';
					$insert['date'] = $date;
					$insert['pno'] = $data[$i]['idx'];
					$insert['pay_type'] = 'MONTH';
					$insert['attend'] = $data[$i]['attend_day'];
					$insert['cost'] = $data[$i]['payment'];
					$insert['tax1'] = $data[$i]['tax1'];
					$insert['tax2'] = $data[$i]['tax2'];
					$insert['tax3'] = $data[$i]['tax3'];
					$insert['tax4'] = $data[$i]['tax4'];
					$insert['tax5'] = $data[$i]['tax5'];
					$insert['revision'] = $data[$i]['revision'];
					$insert['price'] = $data[$i]['send_payment'];
					$insert['etc'] = $data[$i]['comment'];

					$account = $data[$i]['account_name'].'||'.$data[$i]['account_bank'].'||'.$data[$i]['account_number'];
					//$mDB->DBupdate($mErp->table['worker'],array('account'=>$account,'pay_type'=>$insert['pay_type'],'payment'=>$insert['cost']),'',"where `idx`={$data[$i]['idx']}");
					$check = $mDB->DBfetch($mErp->table['payment_worker'],array('idx'),"where `date`='$date' and `wno`=$wno and `type`='DAYWORKER' and `pno`={$data[$i]['idx']}");
					if (isset($check['idx']) == true) {
						$mDB->DBupdate($mErp->table['payment_worker'],$insert,'',"where `idx`={$check['idx']}");
					} else {
						$mDB->DBinsert($mErp->table['payment_worker'],$insert);
					}
				}
			}
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 식대
	if ($do == 'food') {
		$tab = Request('tab');
		$data = $mDB->DBfetch($mErp->table['payment'],array('idx','data'),"where `wno`=$wno and `date`='$date' and `type`='food'");
		$food = isset($data['data']) == true ? unserialize($data['data']) : array();

		$insert = array();
		$insert['title'] = Request('rest_title');
		$insert['rest_info'] = array();
		$insert['rest_info']['rest_title'] = Request('rest_title');
		$insert['rest_info']['rest_number'] = Request('rest_number');
		$insert['rest_info']['rest_telephone'] = Request('rest_telephone');
		$insert['rest_info']['rest_address'] = Request('rest_address');
		$insert['rest_info']['rest_owner'] = Request('rest_owner');
		$insert['rest_info']['rest_account_name'] = Request('rest_account_name');
		$insert['rest_info']['rest_account_bank'] = Request('rest_account_bank');
		$insert['rest_info']['rest_account_number'] = Request('rest_account_number');
		$insert['rest_info']['rest_food_price'] = Request('rest_food_price');
		$insert['rest_info']['rest_snack_price'] = Request('rest_snack_price');
		$insert['rest_info']['rest_tax'] = Request('rest_tax');
		$insert['list'] = GetExtData('list');

		if (substr($tab,0,1) == 'N') {
			$tab = 'T'.time();
		}

		$food[$tab] = $insert;

		$food = serialize($food);

		if (isset($data['idx']) == true) {
			$mDB->DBupdate($mErp->table['payment'],array('data'=>$food),'',"where `idx`={$data['idx']}");
		} else {
			$mDB->DBinsert($mErp->table['payment'],array('wno'=>$wno,'date'=>$date,'type'=>'food','data'=>$food));
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '<errors>';
		echo '<field><id>'.$tab.'</id></field>';
		echo '</errors>';
		echo '</message>';
	}

	// 자재비관리
	if ($do == 'item') {
		$mode = Request('mode');

		// 수정
		if ($mode == 'modify') {
			$data = GetExtData('data');
			$check = array();
			$saveData = array();
			$total_price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['idx'] == '-1') continue; // 본사발주자재는 저장하지 않음

				$insert = array();
				$insert['wno'] = $wno;
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$insert['itemcode']);
				$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost'],0,0);
				$insert['date'] = date('Y-m-d',strtotime($data[$i]['date']));
				$insert['gno'] = $data[$i]['gno'];
				$insert['tno'] = $data[$i]['tno'];
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost'] = $data[$i]['cost'];
				$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
				$insert['payment'] = $data[$i]['payment'];
				$insert['cooperation'] = $data[$i]['cooperation'];
				$insert['etc'] = $data[$i]['etc'];

				if ($data[$i]['idx'] == '0') { // 신규자재일경우,
					$mDB->DBinsert($mErp->table['payment_item'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['payment_item'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['payment_item'],"where `wno`=$wno and `idx` IN ($idx)");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 경비관리
	if ($do == 'expense') {
		$mode = Request('mode');

		// 수정
		if ($mode == 'modify') {
			$data = GetExtData('data');
			$check = array();
			$saveData = array();
			$total_price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$insert = array();
				$insert['wno'] = $wno;
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$insert['itemcode']);
				$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost'],0,0);
				$insert['date'] = date('Y-m-d',strtotime($data[$i]['date']));
				$insert['gno'] = $data[$i]['gno'];
				$insert['tno'] = $data[$i]['tno'];
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost'] = $data[$i]['cost'];
				$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
				$insert['payment'] = $data[$i]['payment'];
				$insert['cooperation'] = $data[$i]['cooperation'];
				$insert['etc'] = $data[$i]['etc'];

				if ($data[$i]['idx'] == '0') { // 신규자재일경우,
					$mDB->DBinsert($mErp->table['payment_expense'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['payment_expense'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['payment_expense'],"where `wno`=$wno and `idx` IN ($idx)");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 장비비관리
	if ($do == 'equipment') {
		$mode = Request('mode');

		// 수정
		if ($mode == 'modify') {
			$data = GetExtData('data');
			$check = array();
			$saveData = array();
			$total_price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$insert = array();
				$insert['wno'] = $wno;
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$insert['itemcode']);
				$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost'],0,0);
				$insert['date'] = date('Y-m-d',strtotime($data[$i]['date']));
				$insert['gno'] = $data[$i]['gno'];
				$insert['tno'] = $data[$i]['tno'];
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost'] = $data[$i]['cost'];
				$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
				$insert['payment'] = $data[$i]['payment'];
				$insert['cooperation'] = $data[$i]['cooperation'];
				$insert['etc'] = $data[$i]['etc'];

				if ($data[$i]['idx'] == '0') { // 신규자재일경우,
					$mDB->DBinsert($mErp->table['payment_equipment'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['payment_equipment'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}

		// 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');

			$mDB->DBdelete($mErp->table['payment_equipment'],"where `wno`=$wno and `idx` IN ($idx)");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

/************************************************************************************************
 * 기성관리
 ***********************************************************************************************/
if ($action == 'monthly') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$date = Request('date') ? Request('date') : date('Y-m');

	// 시트
	if ($do == 'sheet') {
		$mode = Request('mode');

		if ($mode == 'save') {
			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$mDB->DBinsert($mErp->table['monthly'],array('wno'=>$wno,'date'=>$date,'type'=>$data[$i]['type'],'cno'=>$data[$i]['cno'],'repto'=>$data[$i]['repto'],'original'=>$data[$i]['original'],'contract'=>$data[$i]['contract'],'monthly'=>$data[$i]['monthly'],'cooperation'=>$data[$i]['cooperation']));
			}
		}
	}

	// 하도급기성
	if ($do == 'outsourcing') {
		$idx = Request('idx');
		$list = GetExtData('data');

		$outsourcing = $mDB->DBfetch($mErp->table['outsourcing'],'*',"where `idx`=$idx");
		for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
			$insert = array();
			$insert['itemcode'] = $list[$i]['itemcode'];
			$insert['code'] = $list[$i]['code'];
			$insert['subcode'] = $list[$i]['subcode'];
			$insert['wno'] = $wno;
			$insert['date'] = $date;
			$insert['type'] = 'OUTSOURCING';
			$insert['repto'] = $idx;
			$insert['cno'] = $outsourcing['cno'];
			$insert['gno'] = $list[$i]['gno'];
			$insert['tno'] = $list[$i]['tno'];
			$insert['title'] = $list[$i]['title'];
			$insert['size'] = $list[$i]['size'];
			$insert['unit'] = $list[$i]['unit'];
			$insert['ea'] = $list[$i]['monthly_ea'];
			$insert['cost'] = $list[$i]['monthly_cost'];
			$insert['price'] = floor($insert['cost']*$insert['ea']);
			$check = $mDB->DBfetch($mErp->table['monthly_item'],array('idx'),"where `repto`=$idx and `date`='$date' and `type`='OUTSOURCING' and `code`='{$insert['code']}'");
			if (isset($check['idx']) == true) {
				$mDB->DBupdate($mErp->table['monthly_item'],$insert,'',"where `idx`={$check['idx']}");
			} else {
				$mDB->DBinsert($mErp->table['monthly_item'],$insert);
			}
		}
	}

	// 지급자재기성
	if ($do == 'item' || $do == 'expense' || $do == 'equipment') {
		$mode = Request('mode');

		if ($mode == 'save') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['idx'] == '0') {
					$check = $mDB->DBfetch($mErp->table['monthly_item'],array('idx','ea'),"where `wno`=$wno and `type`='".strtoupper($do)."' and `date`='$date' and `code`='{$data[$i]['code']}' and `subcode`='{$data[$i]['subcode']}' and `cno`={$data[$i]['cno']} and `cooperation`='{$data[$i]['cooperation']}'");
					if (isset($check['idx']) == true) {
						$mDB->DBupdate($mErp->table['monthly_item'],array('ea'=>$check['ea']+$data[$i]['ea'],'price'=>floor($data[$i]['code']*($check['ea']+$data[$i]['ea']))),'',"where `idx`={$check['idx']}");
					} else {
						$insert = array();
						$insert['wno'] = $wno;
						$insert['date'] = $date;
						$insert['type'] = strtoupper($do);
						$insert['repto'] = $data[$i]['cno'];
						$insert['cno'] = $data[$i]['cno'];
						$insert['gno'] = $data[$i]['gno'];
						$insert['tno'] = $data[$i]['tno'];
						$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
						$insert['code'] = $data[$i]['code'];
						$insert['subcode'] = $data[$i]['subcode'];
						$insert['title'] = $data[$i]['title'];
						$insert['size'] = $data[$i]['size'];
						$insert['unit'] = $data[$i]['unit'];
						$insert['ea'] = $data[$i]['ea'];
						$insert['cost'] = $data[$i]['cost'];
						$insert['price'] = floor($data[$i]['ea']*$data[$i]['cost']);
						$insert['cooperation'] = $data[$i]['cooperation'];
						$insert['etc'] = $data[$i]['etc'];

						$mDB->DBinsert($mErp->table['monthly_item'],$insert);
					}
				}
			}
		}
	}

	// 노무
	if ($do == 'worker') {
		$mode = Request('mode');

		if ($mode == 'save') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$data = GetExtData('data');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$insert = array();
				$insert['wno'] = $wno;
				$insert['type'] = $data[$i]['type'];
				$insert['date'] = $date;
				$insert['cno'] = $data[$i]['cno'];
				$insert['repto'] = $data[$i]['repto'];
				$insert['ea'] = 1;
				$insert['cost'] = $data[$i]['monthly'];
				$insert['price'] = $data[$i]['monthly'];
				$insert['cooperation'] = $data[$i]['cooperation'];

				if ($data[$i]['idx'] == '0') {
					$mDB->DBinsert($mErp->table['monthly_item'],$insert);
				} else {
					$mDB->DBupdate($mErp->table['monthly_item'],$insert,'',"where `idx`={$data[$i]['idx']}");
				}
			}
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}

if ($action == 'sendsms') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$idx = Request('receiver');
	$content = Request('content');

	$mSMS = new ModuleSMS();
	$data = $mDB->DBfetchs($mErp->table['workerspace'],array('pno'),"where `idx` IN ($idx)");

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$worker = $mDB->DBfetch($mErp->table['worker'],array('cellphone'),"where `idx`={$data[$i]['pno']}");
		if ($worker['cellphone']) $mSMS->SendSMS($worker['cellphone'],$content,($member['cellphone'] ? array_shift(explode('||',$member['cellphone'])) : ''),false);
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '<errors>';
	echo '<field><id></id></field>';
	echo '</errors>';
	echo '</message>';
}
?>