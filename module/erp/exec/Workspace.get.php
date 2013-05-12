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

$wno = Request('wno');
$list = array();

$mErp = new ModuleErp();

if ($action == 'month') {
	$wno = Request('wno');
	$first = $mDB->DBfetch($mErp->table['workspace'],array('workstart_date'),"where `idx`=$wno");

	$first = explode('-',($first['workstart_date'] != '1970-01-01' ? $first['workstart_date'] : date('Y-m-d')));
	$date = 0;
	$i = 0;
	while (($date = mktime(0,0,0,$temp[1]+$i,1,$temp[0])) < time()) {
		$list[$i] = '{"date":"'.date('Y-m',$date).'","display":"'.date('Y년 m월',$date).'"}';
		$i++;
	}
}

/************************************************************************************************
 * 현장정보
 ***********************************************************************************************/
if ($action == 'workspace') {
	// 공정
	if ($get == 'workgroup') {
		if (Request('is_all') == 'true') {
			$list[] = '{"idx":"","workgroup":"전체","sort":"-1"}';
		}
		$data = $mDB->DBfetchs($mErp->table['workspace_workgroup'],'*',"where `wno`=$wno",'sort,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if (Request('is_base') == 'true') {
				$data[$i]['sort'] = sprintf('%02d',$data[$i]['bgno']).sprintf('%02d',$data[$i]['sort']);
				$data[$i]['workgroup'] = '['.$mErp->GetBaseWorkgroup($data[$i]['bgno']).']'.$data[$i]['workgroup'];
			}
			$list[] = '{"idx":"'.$data[$i]['idx'].'","bgno":"'.$data[$i]['bgno'].'","basegroup":"'.GetString($mErp->GetBaseWorkgroup($data[$i]['bgno']),'ext').'","workgroup":"'.GetString($data[$i]['workgroup'],'ext').'","sort":"'.$data[$i]['sort'].'"}';
		}

		if (Request('is_default') == 'true') {
			$list[] = '{"idx":"-1","workgroup":"일반경비","sort":"9999"}';
		}
	}

	// 공종
	if ($get == 'worktype') {
		$gno = Request('gno') ? Request('gno') : 0;

		if (Request('is_all') == 'true') {
			$list[] = '{"idx":"","worktype":"전체","sort":"-1"}';
		}

		if ($gno == '-1') {
			$list[] = '{"idx":"-1","bgno":"0","worktype":"일반관리비","sort":"0"}';
			$list[] = '{"idx":"-10","bgno":"0","worktype":"이윤","sort":"1"}';
			$list[] = '{"idx":"-11","bgno":"0","worktype":"산재보험료","sort":"2"}';
			$list[] = '{"idx":"-12","bgno":"0","worktype":"고용보험료","sort":"3"}';
			$list[] = '{"idx":"-13","bgno":"0","worktype":"국민건강보험료","sort":"4"}';
			$list[] = '{"idx":"-14","bgno":"0","worktype":"국민연금보험료","sort":"5"}';
			$list[] = '{"idx":"-15","bgno":"0","worktype":"노인장기요향보험료","sort":"6"}';
		} else {
			$workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],'*',"where `idx`=$gno",'sort,asc');
			$data = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`=$gno",'sort,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$list[] = '{"idx":"'.$data[$i]['idx'].'","bgno":"'.$workgroup['bgno'].'","worktype":"'.GetString($mErp->GetBaseWorktype($data[$i]['btno']),'ext').'","sort":"'.$data[$i]['sort'].'"}';
			}
		}
	}

	// 도급내역
	if ($get == 'contract') {
		$wno = Request('wno');
		$gno = Request('gno');
		$tno = Request('tno');
		$keyword= Request('keyword');

		$workspace = $mDB->DBfetch($mErp->table['workspace'],array('contract'),"where `idx`=$wno");

		$find = "where `repto`={$workspace['contract']}";
		if ($gno) $find.= " and `gno`='$gno'";
		if ($tno) $find.= " and `tno`='$tno'";
		if ($keyword) $find.= " and `keyword` like '%$keyword%'";
		$data = $mDB->DBfetchs($mErp->table['cost_item'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($mErp->table['cost_item'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"code":"'.$data[$i]['code'].'",';
			$list[$i].= '"itemcode":"'.$data[$i]['itemcode'].'",';
			$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
			$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
			$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
			$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$list[$i].= '"ea":"'.$data[$i]['ea'].'",';
			$list[$i].= '"cost1":"'.$data[$i]['cost1'].'",';
			$list[$i].= '"cost2":"'.$data[$i]['cost2'].'",';
			$list[$i].= '"cost3":"'.$data[$i]['cost3'].'",';
			$list[$i].= '"avgcost1":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
			$list[$i].= '"avgcost2":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost2'),'ext').'",';
			$list[$i].= '"avgcost3":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost3'),'ext').'",';
			$list[$i].= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'"';
			$list[$i].= '}';
		}
	}
}

/************************************************************************************************
 * 작업일보
 ***********************************************************************************************/
if ($action == 'work') {
	// 일일상황일지
	if ($get == 'daily') {
		$date = Request('date') ? Request('date') : date('Y-m-d');

		// 목록
		if ($mode == 'list') {
			// 직영노임
			$data = $mDB->DBfetchs($mErp->table['attend_member'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$worker = $mDB->DBfetch($mErp->table['worker'],array('name','grade','payment'),"where `idx`={$data[$i]['pno']}");
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($worker['name'],'ext').'",';
				$row.= '"size":"'.array_shift(explode('||',$worker['grade'])).'",';
				$row.= '"content":"'.$data[$i]['work'].'",';
				$row.= '"type":"member",';
				$row.= '"cooperation":"'.GetString($worker['name'],'ext').'",';
				$row.= '"ea":"1",';
				$row.= '"unit":"인",';
				$row.= '"cost":"'.$worker['payment'].'",';
				$row.= '"payment":"FALSE"';
				$row.= '}';

				$list[] = $row;
			}

			// 일용직노임
			$data = $mDB->DBfetchs($mErp->table['attend_dayworker'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$dayworker = $mDB->DBfetch($mErp->table['dayworker'],array('name'),"where `idx`={$data[$i]['dno']}");
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($dayworker['name'],'ext').'",';
				$row.= '"size":"'.$data[$i]['job'].'",';
				$row.= '"content":"'.$data[$i]['work'].'",';
				$row.= '"type":"dayworker",';
				$row.= '"cooperation":"'.GetString($dayworker['name'],'ext').'",';
				$row.= '"ea":"'.$data[$i]['worker'].'",';
				$row.= '"unit":"인",';
				$row.= '"cost":"'.($data[$i]['worker'] > 0 ? $data[$i]['payment']/$data[$i]['worker'] : 0).'",';
				$row.= '"payment":"FALSE"';
				$row.= '}';

				$list[] = $row;
			}

			// 외주
			$data = $mDB->DBfetchs($mErp->table['attend_outsourcing'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($cooperation['title'],'ext').'",';
				$row.= '"size":"'.$data[$i]['job'].'",';
				$row.= '"content":"'.$data[$i]['work'].'",';
				$row.= '"type":"outsourcing",';
				$row.= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$row.= '"ea":"'.$data[$i]['worker'].'",';
				$row.= '"unit":"인",';
				$row.= '"cost":"'.($data[$i]['worker'] > 0 ? $data[$i]['payment']/$data[$i]['worker'] : 0).'",';
				$row.= '"payment":"FALSE"';
				$row.= '}';

				$list[] = $row;
			}

			// 본사발주자재
			$data = $mDB->DBfetchs($mErp->table['itemorder_income'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
					$row.= '"code":"'.$data[$i]['code'].'",';
				} else {
					$row.= '"code":"",';
				}
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$row.= '"content":"현장입고",';
				$row.= '"type":"itemorder",';
				$row.= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$row.= '"ea":"'.$data[$i]['ea'].'",';
				$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$row.= '"cost":"'.$data[$i]['cost'].'",';
				$row.= '"payment":"FALSE"';
				$row.= '}';

				$list[] = $row;
			}

			// 현장발주자재
			$data = $mDB->DBfetchs($mErp->table['payment_item'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
					$row.= '"code":"'.$data[$i]['code'].'",';
				} else {
					$row.= '"code":"",';
				}
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$row.= '"content":"'.($data[$i]['etc'] ? GetString($data[$i]['etc'],'ext') : '현장입고').'",';
				$row.= '"type":"item",';
				$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
				$row.= '"ea":"'.$data[$i]['ea'].'",';
				$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$row.= '"cost":"'.$data[$i]['cost'].'",';
				$row.= '"payment":"'.$data[$i]['payment'].'"';
				$row.= '}';

				$list[] = $row;
			}

			// 경비
			$data = $mDB->DBfetchs($mErp->table['payment_expense'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
					$row.= '"code":"'.$data[$i]['code'].'",';
				} else {
					$row.= '"code":"",';
				}
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$row.= '"content":"'.($data[$i]['etc'] ? GetString($data[$i]['etc'],'ext') : '현장경비').'",';
				$row.= '"type":"expense",';
				$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
				$row.= '"ea":"'.$data[$i]['ea'].'",';
				$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$row.= '"cost":"'.$data[$i]['cost'].'",';
				$row.= '"payment":"'.$data[$i]['payment'].'"';
				$row.= '}';

				$list[] = $row;
			}

			// 장비
			$data = $mDB->DBfetchs($mErp->table['payment_equipment'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
					$row.= '"code":"'.$data[$i]['code'].'",';
				} else {
					$row.= '"code":"",';
				}
				$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$row.= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$row.= '"content":"'.($data[$i]['etc'] ? GetString($data[$i]['etc'],'ext') : '현장입고').'",';
				$row.= '"type":"equipment",';
				$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
				$row.= '"ea":"'.$data[$i]['ea'].'",';
				$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$row.= '"cost":"'.$data[$i]['cost'].'",';
				$row.= '"payment":"'.$data[$i]['payment'].'"';
				$row.= '}';

				$list[] = $row;
			}
		}

		// 체크
		if ($mode == 'check') {
			$saveData = $mDB->DBfetch($mErp->table['workreport'],array('weather'),"where `wno`=$wno and `date`='$date'");
			if (isset($saveData['weather']) == true) {
				if ($mErp->CheckWorkReport($wno,$date) == true) {
					$list[] = '{"check":"true","weather":"'.$saveData['weather'].'"}';
				} else {
					$list[] = '{"check":"false","weather":"'.$saveData['weather'].'"}';
				}
			} else {
				$list[] = '{"check":"false","weather":"SUNNY"}';
			}
		}
	}

	// 직영
	if ($get == 'member') {
		$mode = Request('mode');

		// 작업일보
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : GetTime('Y-m-d');
			$data = $mDB->DBfetchs($mErp->table['attend_member'],'*',"where `wno`=$wno and `date`='$date'");

			$month = substr($date,0,7);
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$worker = $mDB->DBfetch($mErp->table['worker'],array('name','jumin','grade'),"where `idx`={$data[$i]['pno']}");

				if ($data[$i]['owno'] != $data[$i]['wno']) {
					$oworkspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$data[$i]['owno']}");
					$oworkspace = $oworkspace['title'];
				} else {
					$oworkspace = '';
				}

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"work":"'.GetString($data[$i]['work'],'ext').'",';
				$list[$i].= '"pno":"'.$data[$i]['pno'].'",';
				$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
				$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
				$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$list[$i].= '"photo":"'.(file_exists($_ENV['path'].'/userfile/erp/worker/'.$data[$i]['pno'].'.jpg') == true ? $_ENV['dir'].'/userfile/erp/worker/'.$data[$i]['pno'].'.jpg' : $mErp->GetModuleDir().'/images/common/nopic120.gif').'",';
				$list[$i].= '"name":"'.$worker['name'].'",';
				$list[$i].= '"grade":"'.$worker['grade'].'",';
				$list[$i].= '"workernum":"'.$data[$i]['workernum'].'",';
				$list[$i].= '"jumin":"'.$worker['jumin'].'",';
				$list[$i].= '"intime":"'.($data[$i]['intime'] ? GetTime('H:i',$data[$i]['intime']) : '00:00').'",';
				$list[$i].= '"outtime":"'.($data[$i]['outtime'] ? GetTime('H:i',$data[$i]['outtime']) : '00:00').'",';
				$list[$i].= '"write_intime":"'.($data[$i]['write_intime'] ? GetTime('H:i',$data[$i]['write_intime']) : '').'",';
				$list[$i].= '"write_outtime":"'.($data[$i]['write_outtime'] ? GetTime('H:i',$data[$i]['write_outtime']) : '').'",';
				$list[$i].= '"inphoto":"'.(file_exists($_ENV['path'].'/userfile/erp/attend/'.$date.'/'.$data[$i]['workernum'].'.in.jpg') == true ? $_ENV['dir'].'/userfile/erp/attend/'.$date.'/'.$data[$i]['workernum'].'.in.jpg' : '').'",';
				$list[$i].= '"outphoto":"'.(file_exists($_ENV['path'].'/userfile/erp/attend/'.$date.'/'.$data[$i]['workernum'].'.out.jpg') == true ? $_ENV['dir'].'/userfile/erp/attend/'.$date.'/'.$data[$i]['workernum'].'.out.jpg' : '').'",';
				$list[$i].= '"is_write":"'.$data[$i]['is_write'].'",';
				$list[$i].= '"is_overwork":"'.$data[$i]['is_overwork'].'",';
				$list[$i].= '"is_support":"'.($data[$i]['owno'] != $data[$i]['wno'] ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"oworkspace":"'.GetString($oworkspace,'ext').'",';
				$list[$i].= '"write_memo":"'.GetString($data[$i]['write_memo'],'ext').' '.($data[$i]['is_write_time'] > 0 ? '('.GetTime('Y.m.d H:i',$data[$i]['is_write_time']).')' : '').'",';
				$list[$i].= '"working":"'.$data[$i]['working'].'",';
				$list[$i].= '"etc":"'.$data[$i]['etc'].'"';
				$list[$i].= '}';
			}
		}

		// 전일작업
		if ($mode == 'yesterday') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$yesterday = $mDB->DBfetch($mErp->table['attend_member'],array('date'),"where `wno`<$wno and `date`<'$date' and `work`!=''",'date,desc','0,1');
			$yesterday = isset($yesterday['date']) == true ? $yesterday['date'] : date('Y-m-d');

			$data = $mDB->DBfetchs($mErp->table['attend_member'],array('work'),"where `wno`=$wno and `date`>='$yesterday' and `work`!='' group by `work`");

			$list[] = '{"idx":"0","display":"신규작업등록","work":""}';
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$list[] = '{"idx":"'.($i+1).'","display":"'.GetString($data[$i]['work'],'ext').'","work":"'.GetString($data[$i]['work'],'ext').'"}';
			}
		}
	}

	// 하도급작업일보
	if ($get == 'outsourcing') {
		$mode = Request('mode');

		// 작업일보
		if ($mode == 'list') {
			$wno = Request('wno');
			$date = Request('date') ? Request('date') : GetTime('Y-m-d');

			$find = "where `wno`=$wno and `date`='$date'";

			$data = $mDB->DBfetchs($mErp->table['attend_outsourcing'],'*',$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$outsourcing = $mDB->DBfetch($mErp->table['outsourcing'],array('cno','title'),"where `idx`={$data[$i]['cno']}");
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$outsourcing['cno']}");
				$monthly_payment = $mDB->DBfetch($mErp->table['attend_outsourcing'],array('SUM(`payment`)'),"where `idx`!={$data[$i]['idx']} and `cno`={$data[$i]['cno']} and `date` like '".date('Y-m',strtotime($date))."%'");
				$monthly_payment = isset($monthly_payment[0]) == true ? $monthly_payment[0] : 0;
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
				$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
				$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$list[$i].= '"title":"'.GetString($outsourcing['title'],'ext').'",';
				$list[$i].= '"job":"'.$data[$i]['job'].'",';
				$list[$i].= '"worker":"'.$data[$i]['worker'].'",';
				$list[$i].= '"payment":"'.$data[$i]['payment'].'",';
				$list[$i].= '"monthly_payment":"'.$monthly_payment.'",';
				$list[$i].= '"intime":"'.($data[$i]['intime'] ? GetTime('H:i',$data[$i]['intime']) : '').'",';
				$list[$i].= '"outtime":"'.($data[$i]['outtime'] ? GetTime('H:i',$data[$i]['outtime']) : '').'",';
				$list[$i].= '"is_overwork":"'.$data[$i]['is_overwork'].'",';
				$list[$i].= '"work":"'.GetString($data[$i]['work'],'ext').'"';
				$list[$i].= '}';
			}
		}
		// 계약정보
		if ($mode == 'contract') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$data = $mDB->DBfetchs($mErp->table['outsourcing'],array('idx','cno','title'),"where `wno`=$wno and `start_date`<='$date' and (`end_date`='0000-00-00' or `end_date`>='$date')");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$list[$i] = '{"idx":"'.$data[$i]['idx'].'","title":"'.GetString('['.$cooperation['title'].'] '.$data[$i]['title'],'ext').'"}';
			}
		}
	}

	// 일용직작업일보
	if ($get == 'dayworker') {
		$mode = Request('mode');

		// 작업일보
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : GetTime('Y-m-d');
			$find = "where `wno`=$wno and `date`='$date'";

			$data = $mDB->DBfetchs($mErp->table['attend_dayworker'],'*',$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$worker = $mDB->DBfetch($mErp->table['dayworker'],array('name','jumin'),"where `idx`={$data[$i]['dno']}");
				$monthly_payment = $mDB->DBfetch($mErp->table['attend_dayworker'],array('SUM(`payment`)'),"where `idx`!={$data[$i]['idx']} and `dno`={$data[$i]['dno']} and `date` like '".date('Y-m',strtotime($date))."%'");
				$monthly_payment = isset($monthly_payment[0]) == true ? $monthly_payment[0] : 0;
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"name":"'.GetString($worker['name'],'ext').'",';
				$list[$i].= '"jumin":"'.$worker['jumin'].'",';
				$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
				$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
				$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$list[$i].= '"monthly_payment":"'.$monthly_payment.'",';
				$list[$i].= '"payment":"'.$data[$i]['payment'].'",';
				$list[$i].= '"intime":"'.($data[$i]['intime'] ? GetTime('H:i',$data[$i]['intime']) : '').'",';
				$list[$i].= '"outtime":"'.($data[$i]['outtime'] ? GetTime('H:i',$data[$i]['outtime']) : '').'",';
				$list[$i].= '"is_overwork":"'.$data[$i]['is_overwork'].'",';
				$list[$i].= '"work":"'.GetString($data[$i]['work'],'ext').'"';
				$list[$i].= '}';
			}
		}

		// 근로자목록
		if ($mode == 'worker') {
			$date = Request('date') ? Request('date') : date('Y-m-d');

			$data = $mDB->DBfetchs($mErp->table['dayworker'],'*',"where `wno`=$wno and `workstart_date`<='$date' and (`workend_date`='0000-00-00' or `workend_date`>='$date')");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"name":"'.GetString('['.$data[$i]['type'].'] '.$data[$i]['name'],'ext').'",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"job":"'.$data[$i]['job'].'",';
				$list[$i].= '"payment":"'.number_format($data[$i]['payment']).'"';
				$list[$i].= '}';
			}
		}
	}

	// 자재입고관리
	if ($get == 'item') {
		$mode = Request('mode');

		// 직접발주
		if ($mode == 'workspace') {
			$date = Request('date');
			// 현장구매내역
			$data = $mDB->DBfetchs($mErp->table['payment_item'],'*',"where `wno`=$wno and `date`='$date'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$row = '{';
				$row.= '"idx":"'.$data[$i]['idx'].'",';
				$row.= '"group":" ",';
				$row.= '"is_new":"FALSE",';
				$row.= '"type":"WORKSPACE",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				$row.= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
				$row.= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
				$row.= '"itemcode":"'.$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']).'",';
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
					$row.= '"code":"'.$data[$i]['code'].'",';
				} else {
					$row.= '"code":"",';
				}
				$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$row.= '"cost":"'.$data[$i]['cost'].'",';
				$row.= '"ea":"'.$data[$i]['ea'].'",';
				$row.= '"payment":"'.$data[$i]['payment'].'",';
				$row.= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
				$row.= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
				$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
				$row.= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
				$row.= '}';
				$list[] = $row;
			}

			// 본사발주내역
			$data = $mDB->DBfetchs($mErp->table['itemorder_income'],'*',"where `wno`=$wno and `date`='$date'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");

				$row = '{';
				$row.= '"idx":"-1",';
				$row.= '"is_new":"FALSE",';
				$row.= '"group":" ",';
				$row.= '"type":"ITEMORDER",';
				$row.= '"date":"'.$data[$i]['date'].'",';
				$row.= '"gno":"'.$data[$i]['gno'].'",';
				$row.= '"tno":"'.$data[$i]['tno'].'",';
				$row.= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
				$row.= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
					$row.= '"code":"'.$data[$i]['code'].'",';
				} else {
					$row.= '"code":"",';
				}
				$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$row.= '"cost":"'.$data[$i]['cost'].'",';
				$row.= '"ea":"'.$data[$i]['ea'].'",';
				$row.= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
				$row.= '"price":"'.$data[$i]['price'].'",';
				$row.= '"payment":"'.$data[$i]['payment'].'",';
				$row.= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$row.= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'"';
				$row.= '}';

				$list[] = $row;
			}
		}

		// 본사발주
		if ($mode == 'order') {
			$data = $mDB->DBfetchs($mErp->table['itemorder'],'*',"where `wno`=$wno and `status`!='COMPLETE'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$item = $mDB->DBfetchs($mErp->table['itemorder_item'],'*',"where `repto`={$data[$i]['idx']}");

				for ($j=0, $loopj=sizeof($item);$j<$loopj;$j++) {
					$income = $mDB->DBfetch($mErp->table['itemorder_income'],array('SUM(ea)'),"where `repto`={$data[$i]['idx']} and `code`='{$item[$j]['code']}' and `subcode`='{$item[$j]['subcode']}'");

					$row = '{';
					$row.= '"idx":"'.$item[$j]['idx'].'",';
					$row.= '"group":"'.GetString('['.$cooperation['title'].'] '.$data[$i]['title'],'ext').'",';
					$row.= '"workgroup":"'.GetString($mErp->GetWorkgroup($item[$j]['gno']),'ext').'",';
					$row.= '"worktype":"'.GetString($mErp->GetWorktype($item[$j]['tno']),'ext').'",';
					$row.= '"title":"'.GetString($item[$j]['title'],'ext').'",';
					$row.= '"size":"'.GetString($item[$j]['size'],'ext').'",';
					$row.= '"unit":"'.GetString($item[$j]['unit'],'ext').'",';
					if ($mErp->GetFindContractItem($wno,$item[$j]['code']) == true) {
						$row.= '"code":"'.$item[$j]['code'].'",';
					} else {
						$row.= '"code":"",';
					}
					$row.= '"order_ea":"'.$item[$j]['ea'].'",';
					$row.= '"income_ea":"'.$income[0].'"';
					$row.= '}';

					$list[] = $row;
				}
			}
		}
	}

	// 경비지출관리
	if ($get == 'expense') {
		$date = Request('date');
		// 현장구매내역
		$data = $mDB->DBfetchs($mErp->table['payment_expense'],'*',"where `wno`=$wno and `date`='$date'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$row = '{';
			$row.= '"idx":"'.$data[$i]['idx'].'",';
			$row.= '"group":" ",';
			$row.= '"is_new":"FALSE",';
			$row.= '"gno":"'.$data[$i]['gno'].'",';
			$row.= '"tno":"'.$data[$i]['tno'].'",';
			$row.= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
			$row.= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
			$row.= '"itemcode":"'.$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']).'",';
			if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
				$row.= '"code":"'.$data[$i]['code'].'",';
			} else {
				$row.= '"code":"",';
			}
			$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$row.= '"cost":"'.$data[$i]['cost'].'",';
			$row.= '"ea":"'.$data[$i]['ea'].'",';
			$row.= '"payment":"'.$data[$i]['payment'].'",';
			$row.= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
			$row.= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
			$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
			$row.= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
			$row.= '}';
			$list[] = $row;
		}
	}

	if ($get == 'equipment') {
		$date = Request('date');
		// 현장구매내역
		$data = $mDB->DBfetchs($mErp->table['payment_equipment'],'*',"where `wno`=$wno and `date`='$date'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$row = '{';
			$row.= '"idx":"'.$data[$i]['idx'].'",';
			$row.= '"group":" ",';
			$row.= '"is_new":"FALSE",';
			$row.= '"gno":"'.$data[$i]['gno'].'",';
			$row.= '"tno":"'.$data[$i]['tno'].'",';
			$row.= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
			$row.= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
			$row.= '"itemcode":"'.$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']).'",';
			if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
				$row.= '"code":"'.$data[$i]['code'].'",';
			} else {
				$row.= '"code":"",';
			}
			$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$row.= '"cost":"'.$data[$i]['cost'].'",';
			$row.= '"ea":"'.$data[$i]['ea'].'",';
			$row.= '"payment":"'.$data[$i]['payment'].'",';
			$row.= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
			$row.= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
			$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
			$row.= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
			$row.= '}';
			$list[] = $row;
		}
	}
}

/************************************************************************************************
 * 근로자관리
 ***********************************************************************************************/
if ($action == 'worker') {
	// 현장근로자
	if ($get == 'worker') {
		$mode = Request('mode');

		// 목록
		if ($mode == 'list') {
			$type = Request('type');

			$find = "where `wno`=$wno";
			if ($type == 'all') {
			} elseif ($type == 'working') {
				$find.= " and `workend_date`='1970-01-01'";
			} elseif ($type == 'retire') {
				$find.= "`workend_date`!='1970-01-01'";
			}

			if ($keyword) $find.= " and `workernum`='$keyword'";
			$worker = $mDB->DBfetchs($mErp->table['workerspace'],array('pno'),$find);
			$inworker = array();
			for ($i=0, $loop=sizeof($worker);$i<$loop;$i++) {
				$inworker[] = $worker[$i]['pno'];
			}

			if (sizeof($inworker) > 0) {
				$find = "where `idx` IN (".implode(',',$inworker).")";
				$keyword = Request('keyword');
				if ($keyword) $find.= " and (`name` like '%$keyword%' or `jumin` like '%$keyword%')";

				$data = $mDB->DBfetchs($mErp->table['worker'],'*',$find);

				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$workerspace = $mDB->DBfetch($mErp->table['workerspace'],array('idx','workernum','workstart_date','workend_date'),"where `pno`={$data[$i]['idx']}");

					$list[$i] = '{';
					$list[$i].= '"idx":"'.$workerspace['idx'].'",';
					$list[$i].= '"workernum":"'.$workerspace['workernum'].'",';
					$list[$i].= '"name":"'.GetString($data[$i]['name'],'ext').'",';
					$list[$i].= '"phone":"'.($data[$i]['cellphone'] ? $data[$i]['cellphone'] : $data[$i]['telephone']).'",';
					$list[$i].= '"group":"'.(ereg('협력사',$data[$i]['grade']) == true ? '협력사' : '광흥건설(주)').'",';
					$list[$i].= '"grade":"'.GetString($data[$i]['grade'],'ext').'",';
					$list[$i].= '"jumin":"'.GetString($data[$i]['jumin'],'ext').'",';
					$list[$i].= '"enter_date":"'.($data[$i]['enter_date'] != '0000-00-00' ? $data[$i]['enter_date'] : '').'",';
					$list[$i].= '"retire_date":"'.($data[$i]['retire_date'] != '0000-00-00' ? $data[$i]['retire_date'] : '').'",';
					$list[$i].= '"workstart_date":"'.($workerspace['workstart_date'] != '0000-00-00' ? $workerspace['workstart_date'] : '').'",';
					$list[$i].= '"workend_date":"'.($workerspace['workend_date'] != '0000-00-00' ? $workerspace['workend_date'] : '').'",';
					$list[$i].= '"pay_type":"'.$mErp->paytype[$data[$i]['pay_type']].'",';
					$list[$i].= '"payment":"'.$data[$i]['payment'].'",';
					$list[$i].= '"account":"'.$data[$i]['account'].'"';
					$list[$i].= '}';
				}
			}
		}

		// 정보
		if ($mode == 'data') {
			header('Content-type: text/xml; charset="UTF-8"', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$workerspace = $mDB->DBfetch($mErp->table['workerspace'],'*',"where `idx`=$idx");
			$workerspace['pno'] = isset($workerspace['pno']) == true ? $workerspace['pno'] : '0';
			$data = $mDB->DBfetch($mErp->table['worker'],'*',"where `idx`={$workerspace['pno']}");

			$grade = explode('||',$data['grade']);
			$data['grade'] = $grade[0];
			$data['grade_handwrite'] = $grade[1];
			$address = explode('||',$data['address']);
			unset($data['address']);
			$data['address1'] = $address[0];
			$data['address2'] = $address[1];
			$data['payment'] = number_format($data['payment']);
			$account = explode('||',$data['account']);
			unset($data['account']);
			$data['account_name'] = $account[0];
			$data['account_bank'] = $account[1];
			$data['account_number'] = $account[2];
			$data['photo'] = (file_exists($_ENV['path'].'/userfile/erp/worker/'.$data['idx'].'.jpg') == true ? $_ENV['dir'].'/userfile/erp/worker/'.$data['idx'].'.jpg' : '');

			$data['enter_date'] = $data['enter_date'] == '0000-00-00' ? '' : $data['enter_date'];
			$data['retire_date'] = $data['retire_date'] == '0000-00-00' ? '' : $data['retire_date'];

			$data['workstart_date'] = $workerspace['workstart_date'] == '0000-00-00' ? '' : $workerspace['workstart_date'];
			$data['workend_date'] = $workerspace['workend_date'] == '0000-00-00' ? '' : $workerspace['workend_date'];

			echo GetArrayToExtXML($data,true);
			exit;
		}
	}

	// 일용직근로자관리
	if ($get == 'dayworker') {
		$mode == Request('mode');
		if ($mode == 'list') {
			$type = Request('type');

			$find = "where `wno`=$wno";
			if ($type == 'all') {
			} elseif ($type == 'working') {
				$find.= " and `workend_date`='0000-00-00'";
			} elseif ($type == 'retire') {
				$find.= "`workend_date`!='0000-00-00'";
			}

			if ($keyword) $find.= " and `workernum`='$keyword'";

			$data = $mDB->DBfetchs($mErp->table['dayworker'],'*',$find);
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"group":"'.$data[$i]['type'].'",';
				$list[$i].= '"name":"'.GetString($data[$i]['name'],'ext').'",';
				$list[$i].= '"jumin":"'.GetString($data[$i]['jumin'],'ext').'",';
				$list[$i].= '"phone":"'.($data[$i]['cellphone'] ? $data[$i]['cellphone'] : $data[$i]['telephone']).'",';
				$list[$i].= '"payment":"'.$data[$i]['payment'].'",';
				$list[$i].= '"workstart_date":"'.($data[$i]['workstart_date'] != '0000-00-00' ? $data[$i]['workstart_date'] : '').'",';
				$list[$i].= '"workend_date":"'.($data[$i]['workend_date'] != '0000-00-00' ? $data[$i]['workend_date'] : '').'",';
				$list[$i].= '"account":"'.$data[$i]['account'].'"';
				$list[$i].= '}';
			}
		}

		// 정보
		if ($mode == 'data') {
			header('Content-type: text/xml; charset="UTF-8"', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['dayworker'],'*',"where `idx`=$idx");

			$data['payment'] = number_format($data['payment']);
			$account = explode('||',$data['account']);
			unset($data['account']);
			$data['account_name'] = $account[0];
			$data['account_bank'] = $account[1];
			$data['account_number'] = $account[2];
			$data['workstart_date'] = $data['workstart_date'] == '0000-00-00' ? '' : $data['workstart_date'];
			$data['workend_date'] = $data['workend_date'] == '0000-00-00' ? '' : $data['workend_date'];

			echo GetArrayToExtXML($data,true);
			exit;
		}
	}
}

/************************************************************************************************
 * 경비관리
 ***********************************************************************************************/
if ($action == 'payment') {
	$date = Request('date') ? Request('date') : date('Y-m');

	// 노무비관리
	if ($get == 'worker') {
		$mode = Request('mode');

		// 직영노임
		if ($mode == 'member') {
			$temp = explode('-',$date);
			$prevdate = date('Y-m',mktime(0,0,0,$temp[1]-1,1,$temp[0]));

			$data = array();
			$attend = $mDB->DBfetchs($mErp->table['attend_member'],'*',"where `wno`=$wno and `date` like '$date%'");
			$workday = $mDB->DBcount($mErp->table['workreport'],"where `wno`=$wno and `date` like '$date%'");

			for ($i=0, $loop=sizeof($attend);$i<$loop;$i++) {
				if (isset($data[$attend[$i]['workernum']]) == false) {
					$data[$attend[$i]['workernum']] = array();
					$data[$attend[$i]['workernum']]['pno'] = $attend[$i]['pno'];
					$data[$attend[$i]['workernum']]['workerspace'] = $mDB->DBfetch($mErp->table['workerspace'],'*',"where `workernum`='{$attend[$i]['workernum']}'");
					$data[$attend[$i]['workernum']]['worker'] = $mDB->DBfetch($mErp->table['worker'],'*',"where `idx`={$data[$attend[$i]['workernum']]['workerspace']['pno']}");
					$data[$attend[$i]['workernum']]['attend'] = 0;
					$data[$attend[$i]['workernum']]['working'] = 0;
					$data[$attend[$i]['workernum']]['delay'] = 0;
					$data[$attend[$i]['workernum']]['early'] = 0;
					$data[$attend[$i]['workernum']]['overwork'] = 0;
					$data[$attend[$i]['workernum']]['payment'] = 0;
					$data[$attend[$i]['workernum']]['tax1'] = 0;
					$data[$attend[$i]['workernum']]['day'] = array();
				}

				$data[$attend[$i]['workernum']]['attend']++;
				$data[$attend[$i]['workernum']]['working']+= $attend[$i]['working'];
				if ($attend[$i]['is_delay'] == 'TRUE') $data[$attend[$i]['workernum']]['delay']++;
				if ($attend[$i]['is_early'] == 'TRUE') $data[$attend[$i]['workernum']]['early']++;
				if ($attend[$i]['is_overwork'] == 'TRUE') $data[$attend[$i]['workernum']]['overwork']++;

				// 근태
				$data[$attend[$i]['workernum']]['day'][date('j',strtotime($attend[$i]['date']))] = $attend[$i]['working'];

				// 총액
				if ($data[$attend[$i]['workernum']]['worker']['pay_type'] == 'DAY') {
					$data[$attend[$i]['workernum']]['payment']+= round($data[$attend[$i]['workernum']]['worker']['payment']*$attend[$i]['working']/10);
				}

				// 소득세
				if ($data[$attend[$i]['workernum']]['worker']['pay_type'] == 'DAY') {
					if ($data[$attend[$i]['workernum']]['worker']['payment']*$attend[$i]['working'] > 1000000) {
						$data[$attend[$i]['workernum']]['tax1']+= floor(round(round(($data[$attend[$i]['workernum']]['worker']['payment']*$attend[$i]['working']-1000000)/10)*0.036)/10)*10;
					}
				}
			}

			$i = 0;
			foreach ($data as $workernum=>$payment) {
				$account = explode('||',$payment['worker']['account']);

				if ($payment['worker']['pay_type'] == 'MONTH') {
					$payment['payment'] = $payment['worker']['payment'];
					$payment['tax1'] = $payment['worker']['payment'] > 3000000 ? floor(round(($payment['worker']['payment']-3000000)*0.036)/10)*10 : 0;
				}
				$payment['tax2'] = floor(round($payment['tax1']*0.1)/10)*10;
				$payment['tax3'] = floor(round($payment['payment']*0.0045)/10)*10;
				$payment['tax4'] = $payment['attend'] >= 20 ? floor(round($payment['payment']*0.045)/10)*10 : 0;
				$payment['tax5'] = $payment['attend'] >= 20 ? floor(round($payment['payment']*0.0264287)/10)*10 : 0;

				$prevData = $mDB->DBfetch($mErp->table['payment_worker'],'*',"where `date`<'$date' and `wno`=$wno and `type`='MEMBER' and `pno`={$payment['pno']}");
				$saveData = $mDB->DBfetch($mErp->table['payment_worker'],'*',"where `date`='$date' and `wno`=$wno and `type`='MEMBER' and `pno`={$payment['pno']}");

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$payment['pno'].'",';
				$list[$i].= '"group":" ",';
				$list[$i].= '"is_save":"'.(isset($saveData['idx']) == true ? 'true' : 'false').'",';
				$list[$i].= '"pay_type":"'.(isset($saveData['pay_type']) == true ? $saveData['pay_type'] : $payment['worker']['pay_type']).'",';
				$list[$i].= '"name":"'.GetString($payment['worker']['name'],'ext').'",';
				$list[$i].= '"workernum":"'.$workernum.'",';
				$list[$i].= '"jumin":"'.$payment['worker']['jumin'].'",';
				$list[$i].= '"account_name":"'.$account[0].'",';
				$list[$i].= '"account_bank":"'.$account[1].'",';
				$list[$i].= '"account_number":"'.$account[2].'",';
				$list[$i].= '"attend_day":"'.(isset($saveData['attend']) == true ? $saveData['attend'] : $payment['working']).'",';
				$list[$i].= '"calc_attend_day":"'.$payment['working'].'",';
				$list[$i].= '"overwork_day":"'.$payment['overwork'].'",';
				$list[$i].= '"delay_day":"'.$payment['delay'].'",';
				$list[$i].= '"early_day":"'.$payment['early'].'",';
				$list[$i].= '"notattend_day":"'.($workday-$payment['attend']).'",';
				$list[$i].= '"prev_pay":"'.(isset($prevData['cost']) == true ? $prevData['cost'] : 0).'",';
				$list[$i].= '"pay":"'.(isset($saveData['cost']) == true ? $saveData['cost'] : $payment['worker']['payment']).'",';
				$list[$i].= '"payment":"'.$payment['payment'].'",';
				$list[$i].= '"tax1":"'.(isset($saveData['tax1']) == true ? $saveData['tax1'] : $payment['tax1']).'",';
				$list[$i].= '"tax2":"'.(isset($saveData['tax2']) == true ? $saveData['tax2'] : $payment['tax2']).'",';
				$list[$i].= '"tax3":"'.(isset($saveData['tax3']) == true ? $saveData['tax3'] : $payment['tax3']).'",';
				$list[$i].= '"tax4":"'.(isset($saveData['tax4']) == true ? $saveData['tax4'] : $payment['tax4']).'",';
				$list[$i].= '"tax5":"'.(isset($saveData['tax5']) == true ? $saveData['tax5'] : $payment['tax5']).'",';
				$list[$i].= '"calc_tax1":"'.$payment['tax1'].'",';
				$list[$i].= '"calc_tax2":"'.$payment['tax2'].'",';
				$list[$i].= '"calc_tax3":"'.$payment['tax3'].'",';
				$list[$i].= '"calc_tax4":"'.$payment['tax4'].'",';
				$list[$i].= '"calc_tax5":"'.$payment['tax5'].'",';
				$list[$i].= '"tax_total":"'.($payment['tax1']+$payment['tax2']+$payment['tax3']+$payment['tax4']+$payment['tax5']).'",';
				$list[$i].= '"revision":"'.(isset($saveData['revision']) == true ? $saveData['revision'] : '0').'",';

				for ($d=1, $loopd=date('t',mktime($date.'-01'));$d<=$loopd;$d++) {
					$list[$i].= '"day'.$d.'":"'.(isset($payment['day'][$d]) == true ? $payment['day'][$d] : '0').'",';
				}

				$list[$i].= '"comment":"'.(isset($saveData['etc']) == true ? GetString($saveData['etc'],'ext') : '').'",';
				$list[$i].= '"etc":""';
				$list[$i].= '}';
				$i++;
			}
		}

		// 일용직노임
		if ($mode == 'dayworker') {
			$data = array();
			$attend = $mDB->DBfetchs($mErp->table['attend_dayworker'],'*',"where `wno`=$wno and `date` like '$date%'");
			for ($i=0, $loop=sizeof($attend);$i<$loop;$i++) {
				if (isset($data[$attend[$i]['dno']]) == false) {
					$data[$attend[$i]['dno']] = array();
					$data[$attend[$i]['dno']]['payment'] = 0;
					$data[$attend[$i]['dno']]['attend_day'] = 0;
					$data[$attend[$i]['dno']]['overwork_day'] = 0;
					$data[$attend[$i]['dno']]['day'] = array();
				}

				if (isset($data[$attend[$i]['dno']]['day'][date('j',strtotime($attend[$i]['date']))]) == false) {
					$data[$attend[$i]['dno']]['attend_day']++;
					if ($attend[$i]['is_overwork'] == 'TRUE') $data[$attend[$i]['dno']]['overwork_day']++;
					$data[$attend[$i]['dno']]['day'][date('j',strtotime($attend[$i]['date']))] = $attend[$i]['payment'];
				} else {
					$data[$attend[$i]['dno']]['day'][date('j',strtotime($attend[$i]['date']))]+= $attend[$i]['payment'];
				}

				$data[$attend[$i]['dno']]['payment']+= $attend[$i]['payment'];
			}

			$i = 0;
			foreach ($data as $dno=>$payment) {
				$worker = $mDB->DBfetch($mErp->table['dayworker'],array('name','jumin','account'),"where `idx`=$dno");

				$account = explode('||',$worker['account']);

				$prevData = $mDB->DBfetch($mErp->table['payment_worker'],'*',"where `date`<'$date' and `wno`=$wno and `type`='MEMBER' and `pno`=$dno");
				$saveData = $mDB->DBfetch($mErp->table['payment_worker'],'*',"where `date`='$date' and `wno`=$wno and `type`='DAYWORKER' and `pno`=$dno");

				$list[$i] = '{';
				$list[$i].= '"group":" ",';
				$list[$i].= '"idx":"'.$dno.'",';
				$list[$i].= '"is_save":"'.(isset($saveData['idx']) == true ? 'true' : 'false').'",';
				$list[$i].= '"name":"'.$worker['name'].'",';
				$list[$i].= '"jumin":"'.$worker['jumin'].'",';
				$list[$i].= '"account_name":"'.$account[0].'",';
				$list[$i].= '"account_bank":"'.$account[1].'",';
				$list[$i].= '"account_number":"'.$account[2].'",';
				$list[$i].= '"attend_day":"'.$payment['attend_day'].'",';
				$list[$i].= '"calc_attend_day":"'.$payment['attend_day'].'",';
				$list[$i].= '"overwork_day":"'.$payment['overwork_day'].'",';
				$list[$i].= '"payment":"'.$payment['payment'].'",';
				$list[$i].= '"tax1":"'.(isset($saveData['tax1']) == true ? $saveData['tax1'] : $payment['tax1']).'",';
				$list[$i].= '"tax2":"'.(isset($saveData['tax2']) == true ? $saveData['tax2'] : $payment['tax2']).'",';
				$list[$i].= '"tax3":"'.(isset($saveData['tax3']) == true ? $saveData['tax3'] : $payment['tax3']).'",';
				$list[$i].= '"tax4":"'.(isset($saveData['tax4']) == true ? $saveData['tax4'] : $payment['tax4']).'",';
				$list[$i].= '"tax5":"'.(isset($saveData['tax5']) == true ? $saveData['tax5'] : $payment['tax5']).'",';
				$list[$i].= '"calc_tax1":"'.$payment['tax1'].'",';
				$list[$i].= '"calc_tax2":"'.$payment['tax2'].'",';
				$list[$i].= '"calc_tax3":"'.$payment['tax3'].'",';
				$list[$i].= '"calc_tax4":"'.$payment['tax4'].'",';
				$list[$i].= '"calc_tax5":"'.$payment['tax5'].'",';
				$list[$i].= '"tax_total":"'.($payment['tax1']+$payment['tax2']+$payment['tax3']+$payment['tax4']+$payment['tax5']).'",';
				$list[$i].= '"revision":"'.(isset($saveData['revision']) == true ? $saveData['revision'] : '0').'",';

				for ($d=1, $loopd=date('t',mktime($date.'-01'));$d<=$loopd;$d++) {
					$list[$i].= '"day'.$d.'":"'.(isset($payment['day'][$d]) == true ? $payment['day'][$d] : '0').'",';
				}

				$list[$i].= '"comment":"'.(isset($saveData['etc']) == true ? GetString($saveData['etc'],'ext') : '').'",';
				$list[$i].= '"etc":""';
				$list[$i].= '}';
				$i++;
			}
		}
	}

	// 식대
	if ($get == 'food') {
		$mode = Request('mode');
		$tab = Request('tab');

		$data = $mDB->DBfetch($mErp->table['payment'],array('data'),"where `wno`=$wno and `date`='$date' and `type`='food'");
		$data = isset($data['data']) == true ? unserialize($data['data']) : array();

		if ($mode == 'tab') {
			foreach ($data as $key=>$value) {
				$list[] = '{"tab":"'.$key.'","title":"'.GetString($value['title'],'ext').'"}';
			}
		}

		if ($mode == 'list') {
			if (substr($tab,0,1) == 'N') {
				$list = array();
				for ($i=0, $loop=date('t',strtotime($date.'-01'));$i<$loop;$i++) {
					$thisDate = date('Y-m-d',strtotime($date.'-'.sprintf('%02d',$i)));
					$attend = $mDB->DBcount($mErp->table['attend_member'],"where `wno`=$wno and `date`='$thisDate'");
					$list[$i] = '{"idx":"'.($i+1).'","group":" ","breakfast":"0","lunch":"0","dinner":"0","snack":"0","add":"0","addtext":"","attend":"'.$attend.'"}';
				}
			} else {
				$save = $data[$tab]['list'];
				$list = array();

				for ($i=0, $loop=date('t',strtotime($date.'-01'));$i<$loop;$i++) {
					$thisDate = date('Y-m-d',strtotime($date.'-'.sprintf('%02d',$i)));
					$attend = $mDB->DBcount($mErp->table['attend_member'],"where `wno`=$wno and `date`='$thisDate'");
					$list[$i] = '{"idx":"'.($i+1).'","group":" ","breakfast":"'.$save[$i]['breakfast'].'","lunch":"'.$save[$i]['lunch'].'","dinner":"'.$save[$i]['dinner'].'","snack":"'.$save[$i]['snack'].'","add":"'.$save[$i]['add'].'","addtext":"'.$save[$i]['addtext'].'","attend":"'.$attend.'"}';
				}
			}
		}
	}

	// 자재비관리
	if ($get == 'item') {
		// 현장구매내역
		$data = $mDB->DBfetchs($mErp->table['payment_item'],'*',"where `wno`=$wno and `date` like '$date%'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$row = '{';
			$row.= '"idx":"'.$data[$i]['idx'].'",';
			$row.= '"group":" ",';
			$row.= '"is_new":"FALSE",';
			$row.= '"type":"WORKSPACE",';
			$row.= '"date":"'.date('r',strtotime($data[$i]['date'])).'",';
			$row.= '"gno":"'.$data[$i]['gno'].'",';
			$row.= '"tno":"'.$data[$i]['tno'].'",';
			$row.= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
			$row.= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
			$row.= '"itemcode":"'.$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']).'",';
			if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
				$row.= '"code":"'.$data[$i]['code'].'",';
			} else {
				$row.= '"code":"",';
			}
			$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$row.= '"cost":"'.$data[$i]['cost'].'",';
			$row.= '"ea":"'.$data[$i]['ea'].'",';
			$row.= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
			$row.= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
			$row.= '"payment":"'.$data[$i]['payment'].'",';
			$row.= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
			$row.= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
			$row.= '}';
			$list[] = $row;
		}

		// 본사발주내역
		$data = $mDB->DBfetchs($mErp->table['itemorder_income'],'*',"where `wno`=$wno and `date` like '$date%'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");

			$row = '{';
			$row.= '"idx":"-1",';
			$row.= '"is_new":"FALSE",';
			$row.= '"group":" ",';
			$row.= '"type":"ITEMORDER",';
			$row.= '"date":"'.date('r',strtotime($data[$i]['date'])).'",';
			$row.= '"gno":"'.$data[$i]['gno'].'",';
			$row.= '"tno":"'.$data[$i]['tno'].'",';
			$row.= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
			$row.= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
			if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
				$row.= '"code":"'.$data[$i]['code'].'",';
			} else {
				$row.= '"code":"",';
			}
			$row.= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$row.= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$row.= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$row.= '"cost":"'.$data[$i]['cost'].'",';
			$row.= '"ea":"'.$data[$i]['ea'].'",';
			$row.= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
			$row.= '"price":"'.$data[$i]['price'].'",';
			$row.= '"payment":"FALSE",';
			$row.= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
			$row.= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'"';
			$row.= '}';

			$list[] = $row;
		}
	}

	// 경비관리
	if ($get == 'expense') {
		$data = $mDB->DBfetchs($mErp->table['payment_expense'],'*',"where `wno`=$wno and `date` like '$date%'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"group":" ",';
			$list[$i].= '"is_new":"FALSE",';
			$list[$i].= '"date":"'.date('r',strtotime($data[$i]['date'])).'",';
			$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
			$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
			$list[$i].= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
			$list[$i].= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
			$list[$i].= '"itemcode":"'.$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']).'",';
			if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
				$list[$i].= '"code":"'.$data[$i]['code'].'",';
			} else {
				$list[$i].= '"code":"",';
			}
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$list[$i].= '"cost":"'.$data[$i]['cost'].'",';
			$list[$i].= '"ea":"'.$data[$i]['ea'].'",';
			$list[$i].= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
			$list[$i].= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
			$list[$i].= '"payment":"'.$data[$i]['payment'].'",';
			$list[$i].= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
			$list[$i].= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
			$list[$i].= '}';
		}
	}

	// 장비비관리
	if ($get == 'equipment') {
		$data = $mDB->DBfetchs($mErp->table['payment_equipment'],'*',"where `wno`=$wno and `date` like '$date%'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"group":" ",';
			$list[$i].= '"is_new":"FALSE",';
			$list[$i].= '"date":"'.date('r',strtotime($data[$i]['date'])).'",';
			$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
			$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
			$list[$i].= '"workgroup":"'.$mErp->GetWorkgroup($data[$i]['gno']).'",';
			$list[$i].= '"worktype":"'.$mErp->GetWorktype($data[$i]['tno']).'",';
			$list[$i].= '"itemcode":"'.$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']).'",';
			if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == true) {
				$list[$i].= '"code":"'.$data[$i]['code'].'",';
			} else {
				$list[$i].= '"code":"",';
			}
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$list[$i].= '"cost":"'.$data[$i]['cost'].'",';
			$list[$i].= '"ea":"'.$data[$i]['ea'].'",';
			$list[$i].= '"payment":"'.$data[$i]['payment'].'",';
			$list[$i].= '"order_ea":"'.$mErp->GetOrderStatus($wno,$data[$i]['code']).'",';
			$list[$i].= '"avgcost":"'.GetString($mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1'),'ext').'",';
			$list[$i].= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
			$list[$i].= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
			$list[$i].= '}';
		}
	}
}

/************************************************************************************************
 * 하도급발주관리
 ***********************************************************************************************/
if ($action == 'outsourcing') {
	// 발주서
	if ($get == 'order') {
		$mode = Request('mode');
		// 목록
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$find = "where `wno`=$wno and `order_type`='OUTSOURCING' and `date` like '$date%'";
			$data = $mDB->DBfetchs($mErp->table['outsourcing_order'],'*',$find,$orderer,$limiter);
			$total = $mDB->DBcount($mErp->table['outsourcing_order'],$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$item = unserialize($data[$i]['data']);
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"item":"'.sizeof(unserialize($data[$i]['data'])).'",';
				$list[$i].= '"file":"'.$data[$i]['file'].'",';
				$list[$i].= '"is_confirm":"'.($data[$i]['status'] == 'NEW' ? 'FALSE' : 'TRUE').'",';
				$list[$i].= '"is_consult":"'.($mDB->DBcount($mErp->table['outsourcing_consult'],"where `repto`={$data[$i]['idx']}") > 0 ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"is_contract":"'.($mDB->DBcount($mErp->table['outsourcing_contract'],"where `repto`={$data[$i]['idx']} and `status`!='NEW'") > 0 ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['date'])).'"';
				$list[$i].= '}';
			}
		}

		// 발주서 품목
		if ($mode == 'item') {
			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('wno','data'),"where `idx`=$idx");
			$list = unserialize($data['data']);

			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				// 자재코드
				$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
				$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
				// 도급내역에서 검색
				$contract = $mErp->GetContractItem($data['wno'],$code);

				if (isset($contract['idx']) == true) {
					$list[$i]['code'] = $code;
					$list[$i]['contract_ea'] = $contract['ea'];
					$list[$i]['cost1'] = $contract['cost1'];
					$list[$i]['cost2'] = $contract['cost2'];
					$list[$i]['cost3'] = $contract['cost3'];
				} else {
					$list[$i]['code'] = '';
					$list[$i]['contract_ea'] = '0';
				}
				$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);
				$list[$i]['workgroup'] = $mErp->GetWorkgroup($list[$i]['gno']);
				$list[$i]['worktype'] = $mErp->GetWorktype($list[$i]['tno']);
			}
			$list = GetArrayToExtData($list);
		}

		// 발주서 기타 정보
		if ($mode == 'data') {
			header('Content-type: text/xml; charset="UTF-8"', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file','type','etc','title','status'),"where `idx`=$idx");

			echo GetArrayToExtXML($data,true);
			exit;
		}
	}

	// 하도급계약관리
	if ($get == 'contract') {
		$mode = Request('mode');

		// 목록
		if ($mode == 'list') {
			$data = $mDB->DBfetchs($mErp->table['outsourcing'],'*',"where `wno`=$wno");

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$contract = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)','COUNT(*)'),"where `repto`={$data[$i]['idx']}");
				$monthly_price = 0;

				$original = unserialize($data[$i]['original']);
				for ($j=0, $loopj=sizeof($original);$j<$loopj;$j++) {
					// 도급내역에서 검색
					$original_contract = $mErp->GetContractItem($wno,$mErp->GetItemUniqueCode($original[$j]['gno'],$original[$j]['tno'],$mErp->GetItemcode($original[$j]['title'],$original[$j]['size'],$original[$j]['unit'])));
					$original_price+= floor((($original[$j]['cost1'] == 'TRUE' ? $original_contract['cost1'] : 0)+($original[$j]['cost2'] == 'TRUE' ? $original_contract['cost2'] : 0)+($original[$j]['cost3'] == 'TRUE' ? $original_contract['cost3'] : 0))*$original[$j]['ea']);
				}

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"price":"'.$contract[0].'",';
				$list[$i].= '"item":"'.$contract[1].'",';
				$list[$i].= '"original_price":"'.$original_price.'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['start_date'])).'"';
				$list[$i].= '}';
			}
		}
	}
}

/************************************************************************************************
 * 자재발주관리
 ***********************************************************************************************/
if ($action == 'itemorder') {
	// 발주서
	if ($get == 'order') {
		$mode = Request('mode');
		// 목록
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$find = "where `wno`=$wno and `order_type`='ITEMORDER' and `date` like '$date%'";
			$data = $mDB->DBfetchs($mErp->table['outsourcing_order'],'*',$find,$orderer,$limiter);
			$total = $mDB->DBcount($mErp->table['outsourcing_order'],$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$item = unserialize($data[$i]['data']);
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"item":"'.sizeof(unserialize($data[$i]['data'])).'",';
				$list[$i].= '"file":"'.$data[$i]['file'].'",';
				$list[$i].= '"is_confirm":"'.($data[$i]['status'] == 'NEW' ? 'FALSE' : 'TRUE').'",';
				$list[$i].= '"is_consult":"'.($mDB->DBcount($mErp->table['outsourcing_consult'],"where `repto`={$data[$i]['idx']}") > 0 ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"is_contract":"'.($mDB->DBcount($mErp->table['outsourcing_contract'],"where `repto`={$data[$i]['idx']} and `status`!='NEW'") > 0 ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['date'])).'"';
				$list[$i].= '}';
			}
		}

		// 발주서 품목
		if ($mode == 'item') {
			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('wno','data'),"where `idx`=$idx");
			$list = unserialize($data['data']);

			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				// 자재코드
				$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
				$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
				// 도급내역에서 검색
				$contract = $mErp->GetContractItem($data['wno'],$code);

				if (isset($contract['idx']) == true) {
					$list[$i]['code'] = $code;
					$list[$i]['contract_ea'] = $contract['ea'];
					$list[$i]['cost1'] = $contract['cost1'];
					$list[$i]['cost2'] = $contract['cost2'];
					$list[$i]['cost3'] = $contract['cost3'];
				} else {
					$list[$i]['code'] = '';
					$list[$i]['contract_ea'] = '0';
				}
				$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);
				$list[$i]['workgroup'] = $mErp->GetWorkgroup($list[$i]['gno']);
				$list[$i]['worktype'] = $mErp->GetWorktype($list[$i]['tno']);
			}
			$list = GetArrayToExtData($list);
		}

		// 발주서 기타 정보
		if ($mode == 'data') {
			header('Content-type: text/xml; charset="UTF-8"', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file','type','etc','title','status'),"where `idx`=$idx");

			echo GetArrayToExtXML($data,true);
			exit;
		}
	}

	// 발주계약관리
	if ($get == 'contract') {
		$mode = Request('mode');

		// 계약목록
		if ($mode == 'list') {
			$data = $mDB->DBfetchs($mErp->table['itemorder'],'*',"where `wno`=$wno");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$contract = $mDB->DBfetch($mErp->table['itemorder_item'],array('SUM(price)','COUNT(*)'),"where `repto`={$data[$i]['idx']}");
				$income_price = 0;

				$original = unserialize($data[$i]['original']);
				for ($j=0, $loopj=sizeof($original);$j<$loopj;$j++) {
					// 도급내역에서 검색
					$original_contract = $mErp->GetContractItem($wno,$mErp->GetItemUniqueCode($original[$j]['gno'],$original[$j]['tno'],$mErp->GetItemcode($original[$j]['title'],$original[$j]['size'],$original[$j]['unit'])));
					$original_price+= floor((($original[$j]['cost1'] == 'TRUE' ? $original_contract['cost1'] : 0)+($original[$j]['cost2'] == 'TRUE' ? $original_contract['cost2'] : 0)+($original[$j]['cost3'] == 'TRUE' ? $original_contract['cost3'] : 0))*$original[$j]['ea']);
				}

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"price":"'.$contract[0].'",';
				$list[$i].= '"item":"'.$contract[1].'",';
				$list[$i].= '"original_price":"'.$original_price.'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['date'])).'"';
				$list[$i].= '}';
			}
		}

		// 계약품목
		if ($mode == 'item') {
			$idx = Request('idx');

			$data = $mDB->DBfetchs($mErp->table['itemorder_item'],'*',"where `repto`=$idx");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($mErp->GetFindContractItem($wno,$data[$i]['code']) == false) {
					$data[$i]['code'] = '';
				}

				$income_price = 0;
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$list[$i].= '"code":"'.$data[$i]['code'].'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$list[$i].= '"price":"'.floor(($data[$i]['cost1']+$data[$i]['cost1']+$data[$i]['cost1'])*$data[$i]['ea']).'",';
				$list[$i].= '"income_price":"'.$income_price.'"';
				$list[$i].= '}';
			}
		}
	}
}

/************************************************************************************************
 * 기성관리
 ***********************************************************************************************/
if ($action == 'monthly') {
	$date = Request('date') ? Request('date') : date('Y-m');

	// 청구확인
	if ($get == 'check') {
		$check = $mDB->DBcount($mErp->table['monthly'],"where `wno`=$wno and `date`='$date'");

		if ($check > 0) $list[] = '{"is_confirm":"TRUE"}';
		else $list[] = '{"is_confirm":"FALSE"}';
	}

	// 기성집계표
	if ($get == 'sheet') {
		$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date'");

		if (sizeof($data) == 0) {
			// 노무비
			$worker = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and (`type`='MEMBER' or `type`='DAYWORKER')");
			for ($i=0, $loop=sizeof($worker);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`<'$date' and `type`='{$worker[$i]['type']}' and `cno`={$worker[$i]['cno']}");
				$prev = $prev[0] ? $prev[0] : '0';

				$row = array(
					'idx'=>'0',
					'type'=>'WORKER',
					'original'=>'0',
					'contract'=>'-1',
					'prev'=>$prev,
					'monthly'=>$worker[$i]['price'],
					'cno'=>$worker[$i]['cno'],
					'repto'=>$worker[$i]['repto'],
					'cooperation'=>GetString($worker[$i]['cooperation'],'ext')
				);

				$data[] = $row;
			}

			// 외주비
			$outsourcing = $mDB->DBfetchs($mErp->table['outsourcing'],'*',"where `wno`=$wno and `start_date`<='$date-31' and (`end_date`='0000-00-00' or `end_date`>'$date-01')");

			for ($i=0, $loop=sizeof($outsourcing);$i<$loop;$i++) {
				$contract = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `repto`={$outsourcing[$i]['idx']}");
				$contract = $contract[0] ? $contract[0] : '0';
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `repto`={$outsourcing[$i]['idx']} and `date`<'$date' and `type`='OUTSOURCING'");
				$prev = $prev[0] ? $prev[0] : '0';
				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `repto`={$outsourcing[$i]['idx']} and `date`='$date' and `type`='OUTSOURCING'");
				$monthly = $monthly[0] ? $monthly[0] : '0';
				$original = $mErp->GetOriginalPrice($wno,$outsourcing[$i]['original']);

				$row = array(
					'idx'=>'0',
					'type'=>'OUTSOURCING',
					'original'=>$original,
					'contract'=>$contract,
					'prev'=>$prev,
					'monthly'=>$monthly,
					'cno'=>$outsourcing[$i]['cno'],
					'repto'=>$outsourcing[$i]['idx'],
					'cooperation'=>$mErp->GetCooperationTitle($outsourcing[$i]['cno'])
				);

				$data[] = $row;
			}

			// 자재비
			$row = array();
			$item = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and `type`='ITEM'");
			for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
				if (isset($row[$item[$i]['repto'].'-'.$item[$i]['cooperation']]) == false) {
					$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `repto`={$item[$i]['idx']} and `cooperation`='{$item[$i]['cooperation']}' and `date`<'$date' and `type`='ITEM'");

					if ($item[$i]['repto'] != '0') {
						$itemorder = $mDB->DBfetch($mErp->table['itemorder'],'*',"where `idx`={$item[$i]['repto']}");
						$original = $mErp->GetOriginalPrice($wno,$itemorder['original']);
						$contract = $mDB->DBfetch($mErp->table['itemorder_item'],array('SUM(price)'),"where `repto`={$item[$i]['repto']}");
						$contract = $contract[0] ? $contract[0] : 0;
					} else {
						$prevSheet = $mDB->DBfetch($mErp->table['monthly'],array('original'),"where `wno`=$wno and `repto`={$item[$i]['repto']} and `cooperation`='{$item[$i]['cooperation']}' and `date`<'$date' and `type`='ITEM'");
						$original = isset($prevSheet['original']) == true ? $prevSheet['original'] : 0;
						$contract = -1;
					}

					$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']] = array(
						'idx'=>'0',
						'type'=>'ITEM',
						'original'=>$original,
						'contract'=>$contract,
						'prev'=>($prev[0] ? $prev[0] : '0'),
						'monthly'=>0,
						'cno'=>$item[$i]['cno'],
						'repto'=>$item[$i]['repto'],
						'cooperation'=>$item[$i]['cooperation']
					);
				}

				$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']]['monthly']+= $item[$i]['price'];
				if ($item[$i]['repto'] == '0') {
					$contract = $mErp->GetContractItem($wno,$item[$i]['code']);
					$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']]['original']+= isset($contract['cost1']) == true ? $contract['cost1'] : 0;
				}
			}

			foreach ($row as $key=>$value) $data[] = $value;

			// 경비
			$row = array();
			$item = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and `type`='EXPENSE'");
			for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
				if (isset($row[$item[$i]['cooperation']]) == false) {
					$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `repto`={$item[$i]['idx']} and `cooperation`='{$item[$i]['cooperation']}' and `date`<'$date' and `type`='ITEM'");
					$prevSheet = $mDB->DBfetch($mErp->table['monthly'],array('original'),"where `wno`=$wno and `repto`={$item[$i]['repto']} and `cooperation`='{$item[$i]['cooperation']}' and `date`<'$date' and `type`='ITEM'");
					$original = isset($prevSheet['original']) == true ? $prevSheet['original'] : 0;
					$contract = -1;

					$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']] = array(
						'idx'=>'0',
						'type'=>'EXPENSE',
						'original'=>$original,
						'contract'=>$contract,
						'prev'=>($prev[0] ? $prev[0] : '0'),
						'monthly'=>0,
						'cno'=>$item[$i]['cno'],
						'repto'=>$item[$i]['repto'],
						'cooperation'=>$item[$i]['cooperation']
					);
				}

				$row[$item[$i]['cooperation']]['monthly']+= $item[$i]['price'];
				$contract = $mErp->GetContractItem($wno,$item[$i]['code']);
				$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']]['original']+= isset($contract['cost1']) == true ? $contract['cost1'] : 0;
			}

			foreach ($row as $key=>$value) $data[] = $value;

			// 장비비
			$row = array();
			$item = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and `type`='EQUIPMENT'");
			for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
				if (isset($row[$item[$i]['cooperation']]) == false) {
					$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `repto`={$item[$i]['idx']} and `cooperation`='{$item[$i]['cooperation']}' and `date`<'$date' and `type`='ITEM'");
					$prevSheet = $mDB->DBfetch($mErp->table['monthly'],array('original'),"where `wno`=$wno and `repto`={$item[$i]['repto']} and `cooperation`='{$item[$i]['cooperation']}' and `date`<'$date' and `type`='ITEM'");
					$original = isset($prevSheet['original']) == true ? $prevSheet['original'] : 0;
					$contract = -1;

					$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']] = array(
						'idx'=>'0',
						'type'=>'EQUIPMENT',
						'original'=>$original,
						'contract'=>$contract,
						'prev'=>($prev[0] ? $prev[0] : '0'),
						'monthly'=>0,
						'cno'=>$item[$i]['cno'],
						'repto'=>$item[$i]['repto'],
						'cooperation'=>$item[$i]['cooperation']
					);
				}

				$row[$item[$i]['cooperation']]['monthly']+= $item[$i]['price'];
				$contract = $mErp->GetContractItem($wno,$item[$i]['code']);
				$row[$item[$i]['repto'].'-'.$item[$i]['cooperation']]['original']+= isset($contract['cost1']) == true ? $contract['cost1'] : 0;
			}

			foreach ($row as $key=>$value) $data[] = $value;
		}

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"cno":"'.$data[$i]['cno'].'",';
			$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
			$list[$i].= '"type":"'.$data[$i]['type'].'",';
			$list[$i].= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
			$list[$i].= '"original":"'.$data[$i]['original'].'",';
			$list[$i].= '"contract":"'.$data[$i]['contract'].'",';
			$list[$i].= '"prev":"'.$data[$i]['prev'].'",';
			$list[$i].= '"monthly":"'.$data[$i]['monthly'].'"';
			$list[$i].= '}';
		}
	}

	// 하도급기성관리
	if ($get == 'outsourcing') {
		$mode = Request('mode');

		// 목록
		if ($mode == 'list') {
			// 계약내역에서 가져오기
			$data = $mDB->DBfetchs($mErp->table['outsourcing'],'*',"where `wno`=$wno and `start_date`<='$date-31' and (`end_date`='0000-00-00' or `end_date`>'$date-01')");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
				$contract = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `repto`={$data[$i]['idx']}");
				$prevmonthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `repto`={$data[$i]['idx']} and `date`<'$date' and `type`='OUTSOURCING'");
				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `repto`={$data[$i]['idx']} and `date`='$date' and `type`='OUTSOURCING'");
				$original = unserialize($data[$i]['original']);
				for ($j=0, $loopj=sizeof($original);$j<$loopj;$j++) {
					// 도급내역에서 검색
					$original_contract = $mErp->GetContractItem($wno,$mErp->GetItemUniqueCode($original[$j]['gno'],$original[$j]['tno'],$mErp->GetItemcode($original[$j]['title'],$original[$j]['size'],$original[$j]['unit'])));
					$original_price+= floor((($original[$j]['cost1'] == 'TRUE' ? $original_contract['cost1'] : 0)+($original[$j]['cost2'] == 'TRUE' ? $original_contract['cost2'] : 0)+($original[$j]['cost3'] == 'TRUE' ? $original_contract['cost3'] : 0))*$original[$j]['ea']);
				}

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"contract":"'.$contract[0].'",';
				$list[$i].= '"original":"'.$original_price.'",';
				$list[$i].= '"prevmonthly":"'.$prevmonthly[0].'",';
				$list[$i].= '"monthly":"'.$monthly[0].'"';
				$list[$i].= '}';
			}
		}

		// 세부내역
		if ($mode == 'item') {
			$idx = Request('idx');

			$data = $mDB->DBfetchs($mErp->table['outsourcing_item'],'*',"where `repto`=$idx");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$code = $mErp->GetItemUniqueCode($data[$i]['gno'],$data[$i]['tno'],$mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']));
				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('idx','cost','ea'),"where `repto`=$idx and `date`='$date' and `type`='OUTSOURCING' and `code`='$code'");
				if (isset($monthly['idx']) == false) {
					$monthly = array('cost'=>'0','ea'=>'0');
				}
				$prevmonthly = $mDB->DBfetch($mErp->table['monthly_item'],array('idx','cost','ea'),"where `repto`=$idx and `date`<'$date' and `type`='OUTSOURCING' and `code`='$code'");
				if (isset($prevmonthly['idx']) == false) {
					$prevmonthly = array('cost'=>'0','ea'=>'0');
				}
				$list[$i] = '{';
				$list[$i].= '"group":"'.GetString('['.$mErp->GetWorkgroup($data[$i]['gno']).'] '.$mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$list[$i].= '"itemcode":"'.$data[$i]['itemcode'].'",';
				$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
				$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
				$list[$i].= '"code":"'.$data[$i]['code'].'",';
				$list[$i].= '"subcode":"'.$data[$i]['subcode'].'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$list[$i].= '"contract_ea":"'.$data[$i]['ea'].'",';
				$list[$i].= '"contract_cost":"'.($data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3']).'",';
				$list[$i].= '"prevmonthly_ea":"'.$prevmonthly['ea'].'",';
				$list[$i].= '"prevmonthly_cost":"'.$prevmonthly['cost'].'",';
				$list[$i].= '"monthly_ea":"'.$monthly['ea'].'",';
				$list[$i].= '"monthly_cost":"'.$monthly['cost'].'"';
				$list[$i].= '}';
			}
		}
	}

	// 지급자재기성
	if ($get == 'item' || $get == 'expense' || $get == 'equipment') {
		$mode = Request('mode');

		// 지급자재기성 목록
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$data = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and `type`='".strtoupper($get)."'");

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(ea)'),"where `wno`=$wno and `type`='".strtoupper($get)."' and `date`<'{$date}' and `code`='{$data[$i]['code']}' and `subcode`='{$data[$i]['subcode']}'");
				$contract = $mErp->GetContractItem($wno,$temp[1]);

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"group":"'.$data[$i]['gno'].'-'.$data[$i]['tno'].'",';
				$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
				$list[$i].= '"cno":"'.$data[$i]['cno'].'",';
				$list[$i].= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'",';
				$list[$i].= '"gno":"'.$data[$i]['gno'].'",';
				$list[$i].= '"tno":"'.$data[$i]['tno'].'",';
				$list[$i].= '"code":"'.$data[$i]['code'].'",';
				$list[$i].= '"subcode":"'.$data[$i]['subcode'].'",';
				$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data[$i]['gno']),'ext').'",';
				$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data[$i]['tno']),'ext').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
				$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
				if (isset($contract['idx']) == true) {
					$list[$i].= '"contract_ea":"'.$contract['ea'].'",';
					$list[$i].= '"contract_cost":"'.($contract['cost1']+$contract['cost2']+$contract['cost3']).'",';
				} else {
					$list[$i].= '"contract_ea":"0",';
					$list[$i].= '"contract_cost":"0",';
				}
				$list[$i].= '"prev_ea":"'.($prev[0] ? $prev[0] : '0').'",';
				$list[$i].= '"ea":"'.$data[$i]['ea'].'",';
				$list[$i].= '"cost":"'.$data[$i]['cost'].'",';
				$list[$i].= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
				$list[$i].= '}';
			}
		}

		// 지급자재목록 불러오기
		if ($mode == 'load') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$load = Request('load') ? Request('load') : date('Y-m');

			$row = array();
			$data = $mDB->DBfetchs($mErp->table['payment_'.$get],'*',"where `wno`=$wno and `date` like '$load%'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if (isset($row['WORKSPACE-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cooperation']]) == false) {
					$row['WORKSPACE-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cooperation']] = $data[$i];
					$row['WORKSPACE-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cooperation']]['repto'] = 0;
					$row['WORKSPACE-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cooperation']]['cno'] = 0;
				} else {
					$row['WORKSPACE-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cooperation']]['ea']+= $data[$i]['ea'];
				}
			}


			if ($get == 'item') {
				$data = $mDB->DBfetchs($mErp->table['itemorder_income'],'*',"where `wno`=$wno and `date` like '$load%'");
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if (isset($row['ORDER-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cno']]) == false) {
						$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");
						$row['ORDER-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cno']] = $data[$i];
						$row['ORDER-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cno']]['cooperation'] = $cooperation['title'];
					} else {
						$row['ORDER-'.$data[$i]['code'].'-'.$data[$i]['subcode'].'-'.$data[$i]['cno']]['ea']+= $data[$i]['ea'];
					}
				}
			}


			$i = 0;
			foreach ($row as $code=>$data) {
				$temp = explode('-',$code);
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(ea)'),"where `wno`=$wno and `type`='".strtoupper($get)."' and `date`<'{$date}' and `code`='{$temp[1]}' and `subcode`='{$temp[2]}' and `cooperation`='{$data['cooperation']}'");
				$contract = $mErp->GetContractItem($wno,$temp[1]);

				$list[$i] = '{';
				$list[$i].= '"type":"'.$temp[0].'",';
				$list[$i].= '"code":"'.$temp[1].'",';
				$list[$i].= '"subcode":"'.$temp[2].'",';
				$list[$i].= '"gno":"'.$data['gno'].'",';
				$list[$i].= '"tno":"'.$data['tno'].'",';
				if (isset($contract['idx']) == true) {
					$list[$i].= '"contract_ea":"'.$contract['ea'].'",';
					$list[$i].= '"contract_cost":"'.($contract['cost1']+$contract['cost2']+$contract['cost3']).'",';
				} else {
					$list[$i].= '"contract_ea":"0",';
					$list[$i].= '"contract_cost":"0",';
				}
				$list[$i].= '"workgroup":"'.GetString($mErp->GetWorkgroup($data['gno']),'ext').'",';
				$list[$i].= '"worktype":"'.GetString($mErp->GetWorktype($data['tno']),'ext').'",';
				$list[$i].= '"title":"'.GetString($data['title'],'ext').'",';
				$list[$i].= '"size":"'.GetString($data['size'],'ext').'",';
				$list[$i].= '"unit":"'.GetString($data['unit'],'ext').'",';
				$list[$i].= '"ea":"'.$data['ea'].'",';
				$list[$i].= '"prev_ea":"'.($prev[0] ? $prev[0] : '0').'",';
				$list[$i].= '"cooperation":"'.GetString($data['cooperation'],'ext').'",';
				$list[$i].= '"repto":"'.$data['repto'].'",';
				$list[$i].= '"cno":"'.$data['cno'].'",';
				$list[$i].= '"cost":"'.$data['cost'].'"';
				$list[$i].= '}';
				$i++;
			}
		}
	}

	// 노무비
	if ($get == 'worker') {
		$data = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and (`type`='MEMBER' or `type`='DAYWORKER')");
		if (sizeof($data) > 0) {
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`<'$date' and `type`='{$data[$i]['type']}' and `cno`={$data[$i]['cno']}");
				$prev = $prev[0] ? $prev[0] : '0';

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"prev":"'.$prev.'",';
				$list[$i].= '"monthly":"'.$data[$i]['price'].'",';
				$list[$i].= '"cno":"'.$data[$i]['cno'].'",';
				$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
				$list[$i].= '"cooperation":"'.GetString($data[$i]['cooperation'],'ext').'"';
				$list[$i].= '}';
			}
		} else {
			// 노무비
			$data = $mDB->DBfetchs($mErp->table['payment_worker'],'*',"where `wno`=$wno and `date`='$date'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`<'$date' and `type`='{$data[$i]['type']}' and `cno`={$data[$i]['pno']}");
				$prev = $prev[0] ? $prev[0] : '0';
				if ($data[$i]['type'] == 'MEMBER') {
					$worker = $mDB->DBfetch($mErp->table['worker'],array('name'),"where `idx`={$data[$i]['pno']}");
				} else {
					$worker = $mDB->DBfetch($mErp->table['dayworker'],array('name'),"where `idx`={$data[$i]['pno']}");
				}

				$list[$i] = '{';
				$list[$i].= '"idx":"0",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"prev":"'.$prev.'",';
				$list[$i].= '"monthly":"'.$data[$i]['price'].'",';
				$list[$i].= '"cno":"'.$data[$i]['pno'].'",';
				$list[$i].= '"repto":"'.$data[$i]['idx'].'",';
				$list[$i].= '"cooperation":"'.GetString($worker['name'],'ext').'"';
				$list[$i].= '}';
			}
		}
	}
}


/************************************************************************************************
 * 품명DB관련
 ***********************************************************************************************/
if ($action == 'item') {
	// 자동완성
	if ($get == 'automatch') {
		$wno = Request('wno');
		$query = GetUTF8Divide(Request('query'));

		$looper = 0;
		$automatch = array();
		$matchlist = array();
		$workspace = $mDB->DBfetch($mErp->table['workspace'],array('contract'),"where `idx`=$wno");

		if ($workspace['contract'] != '0') {
			$data = $mDB->DBfetchs($mErp->table['cost_item'],array('itemcode','code','gno','tno','title','size','unit','cost1','cost2','cost3'),"where `repto`={$workspace['contract']} and `search` like '$query%'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if (isset($automatch[$data[$i]['code']]) == false) {
					$matchlist[$data[$i]['itemcode']] = true;
					$automatch[$data[$i]['code']] = array();
					$automatch[$data[$i]['code']]['itemcode'] = $data[$i]['itemcode'];
					$automatch[$data[$i]['code']]['display'] = '['.$mErp->GetWorkgroup($data[$i]['gno']).'>'.$mErp->GetWorktype($data[$i]['tno']).'] ';
					$automatch[$data[$i]['code']]['display'].= $data[$i]['size'] ? $data[$i]['title'].' ('.$data[$i]['size'].')' : $data[$i]['title'];
					$automatch[$data[$i]['code']]['workgroup'] = $mErp->GetWorkgroup($data[$i]['gno']);
					$automatch[$data[$i]['code']]['gno'] = $data[$i]['gno'];
					$automatch[$data[$i]['code']]['worktype'] = $mErp->GetWorktype($data[$i]['tno']);
					$automatch[$data[$i]['code']]['tno'] = $data[$i]['tno'];
					$automatch[$data[$i]['code']]['title'] = $data[$i]['title'];
					$automatch[$data[$i]['code']]['size'] = $data[$i]['size'];
					$automatch[$data[$i]['code']]['unit'] = $data[$i]['unit'];
					$automatch[$data[$i]['code']]['cost1'] = $data[$i]['cost1'];
					$automatch[$data[$i]['code']]['cost2'] = $data[$i]['cost2'];
					$automatch[$data[$i]['code']]['cost3'] = $data[$i]['cost3'];
					$automatch[$data[$i]['code']]['avgcost1'] = $mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1');
					$automatch[$data[$i]['code']]['avgcost2'] = $mErp->GetItemAvgCost($data[$i]['itemcode'],'cost2');
					$automatch[$data[$i]['code']]['avgcost3'] = $mErp->GetItemAvgCost($data[$i]['itemcode'],'cost3');
					$automatch[$data[$i]['code']]['sort'] = $looper++;
				}
				if ($looper == 15) break;
			}

			$loop = 0;
			foreach ($automatch as $code=>$match) {
				$list[$loop] = '{';
				$list[$loop].= '"code":"'.$code.'"';
				foreach($match as $key=>$value) {
					$list[$loop].= ',"'.$key.'":"'.GetString($value,'ext').'"';
				}
				$list[$loop].= '}';
				$loop++;
			}
		}

		$automatch = array();

		// 품명 DB에서 검색
		if ($looper < 15 && false) {
			$data = $mDB->DBfetchs($mErp->table['item'],array('itemcode','bgno','btno','title','size','unit','cost1','cost2','cost3'),"where `search` like '$query%'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if (isset($matchlist[$data[$i]['itemcode']]) == false) {
					$matchlist[$data[$i]['itemcode']] = true;
					$automatch[$data[$i]['itemcode']] = array();
					$automatch[$data[$i]['itemcode']]['code'] = '';
					$automatch[$data[$i]['itemcode']]['display'] = $data[$i]['size'] ? $data[$i]['title'].' ('.$data[$i]['size'].')' : $data[$i]['title'];
					$automatch[$data[$i]['itemcode']]['workgroup'] = '';
					$automatch[$data[$i]['itemcode']]['gno'] = '';
					$automatch[$data[$i]['itemcode']]['worktype'] = '';
					$automatch[$data[$i]['itemcode']]['tno'] = '';
					$automatch[$data[$i]['itemcode']]['title'] = $data[$i]['title'];
					$automatch[$data[$i]['itemcode']]['size'] = $data[$i]['size'];
					$automatch[$data[$i]['itemcode']]['unit'] = $data[$i]['unit'];
					$automatch[$data[$i]['itemcode']]['cost1'] = $data[$i]['cost1'];
					$automatch[$data[$i]['itemcode']]['cost2'] = $data[$i]['cost2'];
					$automatch[$data[$i]['itemcode']]['cost3'] = $data[$i]['cost3'];
					$automatch[$data[$i]['itemcode']]['avgcost1'] = $mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1');
					$automatch[$data[$i]['itemcode']]['avgcost2'] = $mErp->GetItemAvgCost($data[$i]['itemcode'],'cost2');
					$automatch[$data[$i]['itemcode']]['avgcost3'] = $mErp->GetItemAvgCost($data[$i]['itemcode'],'cost3');
					$automatch[$data[$i]['itemcode']]['sort'] = $looper++;
				}
				if ($looper == 15) break;
			}

			$loop = sizeof($list);
			foreach ($automatch as $itemcode=>$match) {
				$list[$loop] = '{';
				$list[$loop].= '"itemcode":"'.$itemcode.'"';
				foreach($match as $key=>$value) {
					$list[$loop].= ',"'.$key.'":"'.GetString($value,'ext').'"';
				}
				$list[$loop].= '}';
				$loop++;
			}
		}
	}

	// 자재확인
	if ($get == 'check') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$wno = Request('wno');
		$gno = Request('gno');
		$tno = Request('tno');
		$title = Request('title');
		$size = Request('size');
		$unit = Request('unit');

		$itemcode = $mErp->GetItemcode($title,$size,$unit);
		$code = $mErp->GetItemUniqueCode($gno,$tno,$itemcode);

		$isFind = false;
		if ($mErp->GetFindContractItem($wno,$code) ==  true) {
			$isFind = true;
			$avgcost1 = $mErp->GetItemAvgCost($check['itemcode'],'cost1');
			$avgcost2 = $mErp->GetItemAvgCost($check['itemcode'],'cost2');
			$avgcost3 = $mErp->GetItemAvgCost($check['itemcode'],'cost3');
		}

		if ($isFind == false) {
			$code = '';
			$check = $mDB->DBfetch($mErp->table['item'],array('itemcode','cost1','cost2','cost3'),"where `itemcode`='$itemcode'");

			if (isset($check['itemcode']) == true) {
				$isFind = true;
				$avgcost1 = $mErp->GetItemAvgCost($check['itemcode'],'cost1');
				$avgcost2 = $mErp->GetItemAvgCost($check['itemcode'],'cost2');
				$avgcost3 = $mErp->GetItemAvgCost($check['itemcode'],'cost3');
			}
		}

		if ($isFind == true) {
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '<itemcode>'.$itemcode.'</itemcode>';
			echo '<code>'.$code.'</code>';
			echo '<avgcost1>'.GetString($avgcost1,'xml').'</avgcost1>';
			echo '<avgcost2>'.GetString($avgcost2,'xml').'</avgcost2>';
			echo '<avgcost3>'.GetString($avgcost3,'xml').'</avgcost3>';
			echo '</message>';
		} else {
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '<itemcode></itemcode>';
			echo '</message>';
		}

		exit;
	}
}

$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(',',$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>