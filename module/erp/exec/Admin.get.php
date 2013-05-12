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

$mErp = new ModuleErp();

if ($action == 'month') {
	$wno = Request('wno');
	$find = $wno != null ? "where `wno`=$wno" : "where `workstart_date`!='0000-00-00'";
	$first = $mDB->DBfetch($mErp->table['workspace'],array('workstart_date'),$find,'workstart_date,asc','0,1');

	$first = explode('-',($first['workstart_date'] != '1970-01-01' ? $first['workstart_date'] : date('Y-m-d')));
	$date = 0;
	$i = 0;
	while (($date = mktime(0,0,0,$temp[1]+$i,1,$temp[0])) < time()) {
		$list[$i] = '{"date":"'.date('Y-m',$date).'","display":"'.date('Y년 m월',$date).'"}';
		$i++;
	}
}

/************************************************************************************************
 * 기본정보
 ***********************************************************************************************/
if ($action == 'base') {
	// 공정
	if ($get == 'workgroup') {
		if (Request('is_all') == 'true') {
			$list[] = '{"idx":"","workgroup":"전체","sort":"-1"}';
		}

		$data = $mDB->DBfetchs($mErp->table['base_workgroup'],'*','','sort,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[] = '{"idx":"'.$data[$i]['idx'].'","workgroup":"'.GetString($data[$i]['workgroup'],'ext').'","sort":"'.$data[$i]['sort'].'"}';
		}
	}

	// 공종
	if ($get == 'worktype') {
		$bgno = Request('bgno') ? Request('bgno') : '0';
		if (Request('is_all') == 'true') {
			$list[] = '{"idx":"","worktype":"전체","sort":"-1"}';
		}

		$data = $mDB->DBfetchs($mErp->table['base_worktype'],'*',"where `bgno`='$bgno'",'worktype,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[] = '{"idx":"'.$data[$i]['idx'].'","worktype":"'.GetString($data[$i]['worktype'],'ext').'","sort":"'.$i.'"}';
		}
	}
}

/************************************************************************************************
 * 현장관리
 ***********************************************************************************************/
if ($action == 'workspace') {
	// 현장목록
	if ($get == 'list') {
		$category = Request('category');
		$year = Request('year');
		$keyword = Request('keyword');

		if ($category == 'all') {
			$find = "where 1";
		} elseif ($category == 'working') {
			$find = "where `type`='WORKING'";
		} elseif ($category == 'end') {
			$find = "where `type`='END'";
		} elseif ($category == 'backup') {
			$find = "where `type`='backup'";
		} elseif ($category == 'estimate') {
			$find = "where `type`='ESTIMATE'";
		}

		if ($year) $find.= " and `year`=$year";
		if ($keyword) $find.= " and `title` like '%$keyword%'";

		$data = $mDB->DBfetchs($mErp->table['workspace'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($mErp->table['workspace'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"orderer":"'.GetString($data[$i]['orderer'],'ext').'",';
			$list[$i].= '"workstart_date":"'.$data[$i]['workstart_date'].'",';
			$list[$i].= '"workend_date":"'.$data[$i]['workend_date'].'",';
			$list[$i].= '"master":"'.GetString($mErp->GetWorkspaceMasterName($data[$i]['idx']),'ext').'",';
			$list[$i].= '"telephone":"'.$data[$i]['telephone'].'",';
			$list[$i].= '"workpercent":"'.$mErp->GetWorkspaceWorkPercent($data[$i]['idx']).'",';
			$list[$i].= '"worker":"'.$mDB->DBcount($mErp->table['workerspace'],"where `wno`={$data[$i]['idx']}").'",';
			$list[$i].= '"type":"'.$data[$i]['type'].'",';
			$list[$i].= '"totalarea":"'.$data[$i]['totalarea'].'",';
			$list[$i].= '"estimate":"'.$data[$i]['estimate'].'",';
			$list[$i].= '"contract":"'.$data[$i]['contract'].'",';
			$list[$i].= '"exec":"'.$data[$i]['exec'].'"';
			$list[$i].= '}';
		}
	}

	// 현장정보
	if ($get == 'data') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = Request('idx');
		$workspace = $mErp->GetWorkspace($idx);

		echo GetArrayToExtXML($workspace,true);
		exit;
	}

	// 현장이미지
	if ($get == 'image') {
		$wno = Request('wno');

		$data = $mDB->DBfetchs($mErp->table['workspace_image'],'*',"where `wno`=$wno");

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"filename":"'.GetString($data[$i]['filename'],'ext').'",';
			$list[$i].= '"filesize":"'.$data[$i]['filesize'].'",';
			$list[$i].= '"filepath":"'.$_ENV['dir'].$data[$i]['filepath'].'"';
			$list[$i].= '}';
		}
	}

	// 공정
	if ($get == 'workgroup') {
		$wno = Request('wno');

		if (Request('is_all') == 'true') {
			$list[] = '{"idx":"","workgroup":"전체","sort":"-1"}';
		}
		$data = $mDB->DBfetchs($mErp->table['workspace_workgroup'],'*',"where `wno`=$wno",'sort,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if (Request('is_base') == 'true') {
				$data[$i]['sort'] = sprintf('%02d',$data[$i]['bgno']).sprintf('%02d',$data[$i]['sort']);
				$data[$i]['workgroup'] = '['.$mErp->GetBaseWorkgroup($data[$i]['bgno']).']'.$data[$i]['workgroup'];
			}
			$list[] = '{"idx":"'.$data[$i]['idx'].'","bgno":"'.$data[$i]['bgno'].'","btno":"'.$data[$i]['btno'].'","basegroup":"'.GetString($mErp->GetBaseWorkgroup($data[$i]['bgno']),'ext').'","buildtype":"'.GetString($mErp->GetBuildtype($data[$i]['btno']),'ext').'","workgroup":"'.GetString($data[$i]['workgroup'],'ext').'","sort":"'.$data[$i]['sort'].'"}';
		}
	}

	// 공종
	if ($get == 'worktype') {
		$gno = Request('gno') ? Request('gno') : 0;

		if (Request('is_all') == 'true') {
			$list[] = '{"idx":"","worktype":"전체","groupsort":"0000","sort":"-1"}';
		}

		$workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],'*',"where `idx`=$gno",'sort,asc');
		$basegroup = $mDB->DBfetch($mErp->table['base_workgroup'],'*',"where `idx`={$workgroup['bgno']}");
		$data = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`=$gno",'sort,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[] = '{"idx":"'.$data[$i]['idx'].'","bgno":"'.$workgroup['bgno'].'","worktype":"'.GetString($mErp->GetBaseWorktype($data[$i]['btno']),'ext').'","groupsort":"'.sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup['sort']).'","sort":"'.$data[$i]['sort'].'"}';
		}
	}

	// 공정 및 공종
	if ($get == 'workgrouptype') {
		$wno = Request('wno');
		$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],'*',"where `wno`=$wno",'sort,asc');
		for ($i=0, $loop=sizeof($workgroup);$i<$loop;$i++) {
			$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`={$workgroup[$i]['idx']}",'sort,asc');
			if (sizeof($worktype) > 0) {
				for ($j=0, $loopj=sizeof($worktype);$j<$loopj;$j++) {
					$list[] = '{"workgroup":"['.GetString($workgroup[$i]['workgroup'],'ext').']","worktype":"['.GetString($mErp->GetBaseWorktype($worktype[$j]['btno']),'ext').']","workgrouptype":"['.GetString($workgroup[$i]['workgroup'].'-'.$mErp->GetBaseWorktype($worktype[$j]['btno']),'ext').']"}';
				}
			} else {
				$list[] = '{"workgroup":"['.GetString($workgroup[$i]['workgroup'],'ext').']","worktype":"","workgrouptype":"['.GetString($workgroup[$i]['workgroup'].'-','ext').']"}';
			}
		}
	}

	// 건설동
	if ($get == 'buildtype') {
		$wno = Request('wno');

		if (Request('is_notselect') == 'true') {
			$list[] = '{"idx":"0","buildtype":"선택안함","sort":"-1"}';
		}

		$data = $mDB->DBfetchs($mErp->table['workspace_buildtype'],'*',"where `wno`=$wno",'buildtype,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[] = '{"idx":"'.$data[$i]['idx'].'","buildtype":"'.GetString($data[$i]['buildtype'],'ext').'","sort":"'.$i.'"}';
		}
	}

	// 현장소장
	if ($get == 'master') {
		$wno = Request('wno');
		$data = $mDB->DBfetchs($mErp->table['workspace_master_log'],'*',"where `wno`=$wno");

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$masters = array();
			if ($data[$i]['master']) {
				$master = $mDB->DBfetchs($_ENV['table']['member'],array('name'),"where `idx` IN ({$data[$i]['master']})");
				for ($j=0, $loopj=sizeof($master);$j<$loopj;$j++) {
					$masters[] = $master[$j]['name'];
				}
			}
			$register = $mMember->GetMemberInfo($data[$i]['mno']);
			$master = implode(', ',$masters);
			$list[] = '{"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'","master":"'.GetString($master,'ext').'","register":"'.GetString($register['name'],'ext').'"}';
		}
	}

	// 계약관리
	if ($get == 'cost') {
		$mode = Request('mode');

		// 목록
		if ($mode == 'list') {
			$wno = Request('wno');
			$type = Request('type');

			$data = $mDB->DBfetchs($mErp->table['cost'],array('idx','title','price','is_apply','reg_date','modify_date'),"where `wno`=$wno and `type`='$type'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$itemnum = $mDB->DBcount($mErp->table['cost_item'],"where `repto`={$data[$i]['idx']}");

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"wno":"'.$wno.'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"itemnum":"'.$itemnum.'",';
				$list[$i].= '"price":"'.$data[$i]['price'].'",';
				$list[$i].= '"is_apply":"'.$data[$i]['is_apply'].'",';
				$list[$i].= '"reg_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['reg_date']).'",';
				$list[$i].= '"modify_date":"'.GetTime('Y.m.d H:i:s',$data[$i]['modify_date']).'"';
				$list[$i].= '}';
			}
		}

		// 시트
		if ($mode == 'sheet') {
			function GetNegoPrice($sheet,$row) {
				$price = $sheet[$row]['price'];

				if ($sheet[$row]['nego']) {
					if (preg_match('/%/',$sheet[$row]['nego']) == true) {
						$nego = str_replace('%','',$sheet[$row]['nego']);
						$price = floor($sheet[$row]['price']+($sheet[$row]['price']*$nego/100));
					} else {
						$price = $sheet[$row]['price'] = $sheet[$row]['price'] + $sheet[$row]['nego'];
					}
				}

				return floor($price);
			}

			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['cost'],array('wno','sheet','price'),"where `idx`=$idx");

			if (isset($data['sheet']) == true && $data['sheet'] && is_array(unserialize($data['sheet'])) == true) {
				$isSave = false;
				$sheet = unserialize($data['sheet']);
			} else {
				$isSave = true;
				$sheet = array();
				$group = array('1','1','1','2','2','3','3','3','3','3','3','3','4','4','4','5');
				$type = array('재료비','재료비','재료비','노무비','노무비','경비','경비','경비','경비','경비','경비','경비','일반관리비','이윤','견적','부가가치세');
				$category = array('직접재료비','간접재료비','작업설,부산물','직접노무비','간접노무비','기계경비','산재보험료','고용보험료','국민건강보험료','국민연금보험료','노인장기요양보험료','산업안전보건관리비','','','','');
				$tax = $mErp->GetWorkspaceTax($data['wno']);
				$percent = array(-1,-1,-1,-1,$tax[0],-1,$tax[1],$tax[2],$tax[3],$tax[4],$tax[5],$tax[6],$tax[7],$tax[8],-1,$tax[9]);

				for ($i=0;$i<16;$i++) {
					$is_write = in_array($i,array(1,2,14)) == true ? "TRUE" : "FALSE";
					$sheet[$i] = array('idx'=>$i,'is_write'=>$is_write,'group'=>$group[$i],'type'=>$type[$i],'category'=>$category[$i],'price'=>'0','origin_price'=>'0','percent'=>$percent[$i],'etc'=>'','nego'=>'');
				}
			}

			$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(`cost1`*`ea`)','SUM(`cost2`*`ea`)','SUM(`cost3`*`ea`)'),"where `repto`=$idx");

			$sheet[0]['origin_price'] = $sheet[0]['price'] = $cost[0];
			$sheet[0]['price'] = GetNegoPrice($sheet,0);

			$sheet[1]['origin_price'] = $sheet[1]['price'];
			$sheet[1]['price'] = GetNegoPrice($sheet,1);

			$sheet[2]['origin_price'] = $sheet[2]['price'];
			$sheet[2]['price'] = GetNegoPrice($sheet,2);

			$sheet[3]['origin_price'] = $sheet[3]['price'] = $cost[1];
			$sheet[3]['price'] = GetNegoPrice($sheet,3);

			$sheet[4]['origin_price'] = $sheet[4]['price'] = floor($sheet[3]['price']*$sheet[4]['percent']/100);
			$sheet[4]['price'] = GetNegoPrice($sheet,4);

			$sheet[5]['origin_price'] = $sheet[5]['price'] = $cost[2];
			$sheet[5]['price'] = GetNegoPrice($sheet,5);

			$sheet[6]['origin_price'] = $sheet[6]['price'] = floor(($sheet[3]['price']+$sheet[4]['price'])*$sheet[6]['percent']/100);
			$sheet[6]['price'] = GetNegoPrice($sheet,6);

			$sheet[7]['origin_price'] = $sheet[7]['price'] = floor(($sheet[3]['price']+$sheet[4]['price'])*$sheet[7]['percent']/100);
			$sheet[7]['price'] = GetNegoPrice($sheet,7);

			$sheet[8]['origin_price'] = $sheet[8]['price'] = floor($sheet[3]['price']*$sheet[8]['percent']/100);
			$sheet[8]['price'] = GetNegoPrice($sheet,8);

			$sheet[9]['origin_price'] = $sheet[9]['price'] = floor($sheet[3]['price']*$sheet[9]['percent']/100);
			$sheet[9]['price'] = GetNegoPrice($sheet,9);

			$sheet[10]['origin_price'] = $sheet[10]['price'] = floor($sheet[8]['price']*$sheet[10]['percent']/100);
			$sheet[10]['price'] = GetNegoPrice($sheet,10);

			$sheet[11]['origin_price'] = $sheet[11]['price'] = floor(($sheet[0]['price']+$sheet[1]['price']+$sheet[2]['price']+$sheet[3]['price'])*$sheet[11]['percent']/10+3294000);
			$sheet[11]['price'] = GetNegoPrice($sheet,11);

			for ($i=0;$i<=11;$i++) $origin_price+= $sheet[$i]['price'];

			$sheet[12]['origin_price'] = $sheet[12]['price'] = floor($origin_price*$sheet[12]['percent']/100);
			$sheet[12]['price'] = GetNegoPrice($sheet,12);

			$sheet[13]['origin_price'] = $sheet[13]['price'] = floor($origin_price*$sheet[13]['percent']/100);
			$sheet[13]['price'] = GetNegoPrice($sheet,13);

			$sheet[14]['origin_price'] = $sheet[14]['price'];
			$sheet[14]['price'] = GetNegoPrice($sheet,14);

			$sheet[15]['origin_price'] = $sheet[15]['price'] = floor(($origin_price+$sheet[12]['price']+$sheet[13]['price']+$sheet[14]['price'])*$sheet[15]['percent']/100);
			$sheet[15]['price'] = GetNegoPrice($sheet,15);

			$total_price = 0;
			for ($i=0;$i<=15;$i++) $total_price+= $sheet[$i]['price'];

			$mDB->DBupdate($mErp->table['cost'],array('sheet'=>serialize($sheet),'price'=>$total_price),'',"where `idx`=$idx");
			if ($data['price'] != $total_price) $mDB->DBupdate($mErp->table['cost'],array('modify_date'=>GetGMT()),'',"where `idx`=$idx");

			$list = GetArrayToExtData($sheet);
		}

		// 공종별내역서
		if ($mode == 'group') {
			$idx = Request('idx');

			$cost = $mDB->DBfetch($mErp->table['cost'],array('wno'),"where `idx`=$idx");
			$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx','workgroup','bgno','sort'),"where `wno`={$cost['wno']}",'sort,asc');
			for ($i=0, $loop=sizeof($workgroup);$i<$loop;$i++) {
				// 공종별 그룹
				$basegroup = $mDB->DBfetch($mErp->table['base_workgroup'],array('workgroup','sort'),"where `idx`={$workgroup[$i]['bgno']}");
				$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(cost1)','SUM(cost2)','SUM(cost3)','SUM(`ea`*`cost1`)','SUM(`ea`*`cost2`)','SUM(`ea`*`cost3`)'),"where `repto`=$idx and `gno`={$workgroup[$i]['idx']}");
				if (isset($cost[0]) == false) $cost[0] = $cost[1] = $cost[2] = $cost[3] = $cost[4] = $cost[5] = '0';

				$list[$i] = '{';
				$list[$i].= '"basegroup":"'.sprintf('%02d',$basegroup['sort']).' '.GetString($basegroup['workgroup'],'ext').'",';
				$list[$i].= '"gno":"'.$workgroup[$i]['idx'].'",';
				$list[$i].= '"workgroup":"'.sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup[$i]['sort']).' '.GetString($mErp->GetWorkgroup($workgroup[$i]['idx']),'ext').'",';
				$list[$i].= '"cost1":"'.$cost[0].'",';
				$list[$i].= '"cost2":"'.$cost[1].'",';
				$list[$i].= '"cost3":"'.$cost[2].'",';
				$list[$i].= '"price1":"'.$cost[3].'",';
				$list[$i].= '"price2":"'.$cost[4].'",';
				$list[$i].= '"price3":"'.$cost[5].'",';
				$list[$i].= '"sort":"'.$workgroup[$i]['sort'].'"';
				$list[$i].= '}';
			}
		}

		// 공종별 하위 공종내역
		if ($mode == 'subgroup') {
			$submode = Request('submode');

			// 공종내역별 탭목록
			if ($submode == 'tab') {
				$idx = Request('idx');
				$gno = Request('gno');

				$cost = $mDB->DBfetch($mErp->table['cost'],array('wno'),"where `idx`=$idx");
				$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`=$gno",'sort,asc');

				$list[] = '{"tab":"0","title":"'.GetString($mErp->GetWorkgroup($gno),'ext').'"}';

				for ($i=0, $loop=sizeof($worktype);$i<$loop;$i++) {
					$list[] = '{"tab":"'.$worktype[$i]['idx'].'","title":"'.sprintf('%02d',$worktype[$i]['sort']).' '.GetString($mErp->GetBaseWorktype($worktype[$i]['btno']),'ext').'"}';
				}
			}

			// 집계
			if ($submode == 'group') {
				$idx = Request('idx');
				$gno = Request('gno');
				$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`=$gno");
				if (sizeof($worktype) > 0) {
					for ($j=0, $loopj=sizeof($worktype);$j<$loopj;$j++) {
						$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(cost1)','SUM(cost2)','SUM(cost3)','SUM(`ea`*`cost1`)','SUM(`ea`*`cost2`)','SUM(`ea`*`cost3`)'),"where `repto`=$idx and `tno`={$worktype[$j]['idx']}");
						if (isset($cost[0]) == false) $cost[0] = $cost[1] = $cost[2] = $cost[3] = $cost[4] = $cost[5] = '0';
						$list[] = '{"group":" ","worktype":"'.GetString($mErp->GetBaseWorktype($worktype[$j]['btno']),'ext').'","tno":"'.$worktype[$j]['idx'].'","cost1":"'.$cost[0].'","cost2":"'.$cost[1].'","cost3":"'.$cost[2].'","price1":"'.$cost[3].'","price2":"'.$cost[4].'","price3":"'.$cost[5].'","sort":"'.$worktype[$j]['sort'].'"}';
					}
				} else {
					$list[] = '{"group":" ","worktype":"하위공종없음","tno":"0","cost1":"0","cost2":"0","cost3":"0","price1":"0","price2":"0","price3":"0","sort":"0"}';
				}
			}

			// 공종내역별 품목목록
			if ($submode == 'tabdata') {
				$idx = Request('idx');
				$tno = Request('tno');
				$keyword = Request('keyword');

				$find = "where `repto`=$idx and `tno`=$tno";
				if ($keyword) $find.= " and `title` like '%$keyword%'";
				$data = $mDB->DBfetchs($mErp->table['cost_item'],'*',$find,'idx,asc');
				$total = $mDB->DBcount($mErp->table['cost_item'],$find);

				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$origin = $data[$i]['ovalue'] ? explode(',',$data[$i]['ovalue']) : array(0,0,0,0);
					$list[$i] = '{';
					$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
					$list[$i].= '"group":" ",';
					$list[$i].= '"itemcode":"'.$data[$i]['itemcode'].'",';
					$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
					$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
					$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
					$list[$i].= '"origin_ea":"'.$origin[0].'",';
					$list[$i].= '"origin_cost1":"'.$origin[1].'",';
					$list[$i].= '"origin_cost2":"'.$origin[2].'",';
					$list[$i].= '"origin_cost3":"'.$origin[3].'",';
					$list[$i].= '"ea":"'.$data[$i]['ea'].'",';
					$list[$i].= '"cost1":"'.$data[$i]['cost1'].'",';
					$list[$i].= '"cost2":"'.$data[$i]['cost2'].'",';
					$list[$i].= '"cost3":"'.$data[$i]['cost3'].'",';
					$list[$i].= '"avgcost1":"'.$mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1').'",';
					$list[$i].= '"avgcost2":"'.$mErp->GetItemAvgCost($data[$i]['itemcode'],'cost2').'",';
					$list[$i].= '"avgcost3":"'.$mErp->GetItemAvgCost($data[$i]['itemcode'],'cost3').'",';
					$list[$i].= '"etc":"'.GetString($data[$i]['etc'],'ext').'"';
					$list[$i].= '}';
				}
			}
		}

		// 평당환산금액
		if ($mode == 'unit') {
			function GetEtcData($etc,$geter) {
				return isset($etc[$geter]) == true ? GetString($etc[$geter],'ext') : '';
			}

			function GetPrice($sheet,$cost1,$cost2,$cost3) {
				$price = 0;

				if ($sheet[0]['nego']) {
					if (preg_match('/%/',$sheet[0]['nego']) == true) {
						$nego = str_replace('/%/','',$sheet[0]['nego']);
						$price+= floor($cost1+($cost1*$nego/100));
					} else {
						$price+= $cost1 + $sheet[0]['nego'];
					}
				} else {
					$price+= $cost1;
				}

				if ($sheet[3]['nego']) {
					if (preg_match('/%/',$sheet[3]['nego']) == true) {
						$nego = str_replace('/%/','',$sheet[3]['nego']);
						$price+= floor($cost2+($cost2*$nego/100));
					} else {
						$price+= $cost2 + $sheet[3]['nego'];
					}
				} else {
					$price+= $cost2;
				}

				if ($sheet[5]['nego']) {
					if (preg_match('/%/',$sheet[5]['nego']) == true) {
						$nego = str_replace('/%/','',$sheet[5]['nego']);
						$price+= floor($cost3+($cost3*$nego/100));
					} else {
						$price+= $cost3 + $sheet[3]['nego'];
					}
				} else {
					$price+= $cost3;
				}

				return floor($price);
			}

			function GetUnitPrice($price,$area) {
				return $area > 0 ? floor(round($price/($area*0.3025))/10)*10 : 0;
				return floor($price);
			}

			$idx = Request('idx');
			$cost = $mDB->DBfetch($mErp->table['cost'],array('wno','sheet','unit'),"where `idx`=$idx");
			$unit = $cost['unit'] && is_array(unserialize($cost['unit'])) == true ? unserialize($cost['unit']) : array();
			$sheet = unserialize($cost['sheet']);

			$etc = array();
			for ($i=0, $loop=sizeof($unit);$i<$loop;$i++) {
				$etc[$unit[$i]['idx']] = $unit[$i]['etc'];
			}

			$unit = array();
			$maxSort = $mDB->DBfetch($mErp->table['workspace_workgroup'],array('MAX(sort)'),"where `wno`={$cost['wno']}");
			$maxSort = $maxSort[0] + 1;

			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('totalarea'),"where `idx`={$cost['wno']}");
			$area = $workspace['totalarea'] ? $workspace['totalarea'] : 0;

			$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx','workgroup','bgno','sort'),"where `wno`={$cost['wno']}",'sort,asc');
			$looper = 0;
			for ($i=0, $loop=sizeof($workgroup);$i<$loop;$i++) {
				$basegroup = $mDB->DBfetch($mErp->table['base_workgroup'],array('sort'),"where `idx`={$workgroup[$i]['bgno']}");
				$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`={$workgroup[$i]['idx']}");
				if (sizeof($worktype) > 0) {
					for ($j=0, $loopj=sizeof($worktype);$j<$loopj;$j++) {
						$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(`ea`*`cost1`)','SUM(`ea`*`cost2`)','SUM(`ea`*`cost3`)'),"where `repto`=$idx and `tno`={$worktype[$j]['idx']}");

						$unit[$looper] = array();
						$unit[$looper]['idx'] = $workgroup[$i]['idx'].'-'.$worktype[$j]['idx'];
						$unit[$looper]['workgroup'] = sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup[$i]['sort']).' '.GetString($mErp->GetWorkgroup($workgroup[$i]['idx']),'ext');
						$unit[$looper]['worktype'] = GetString($mErp->GetBaseWorktype($worktype[$j]['btno']),'ext');
						$unit[$looper]['price'] = GetPrice($sheet,$cost[0],$cost[1],$cost[2]);
						$unit[$looper]['unit_price'] = GetUnitPrice($unit[$looper]['price'],$area);
						$unit[$looper]['etc'] = GetEtcData($etc,$workgroup[$i]['idx'].'-'.$worktype[$j]['idx']);
						$unit[$looper]['sort'] = $worktype[$j]['sort'];
						$looper++;
					}
				} else {
					$unit[$looper] = array();
					$unit[$looper]['idx'] = $workgroup[$i]['idx'].'-0';
					$unit[$looper]['workgroup'] = sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup[$i]['sort']).' '.GetString($workgroup[$i]['workgroup'],'ext');
					$unit[$looper]['worktype'] = '하위공종없음';
					$unit[$looper]['price'] = 0;
					$unit[$looper]['unit_price'] = 0;
					$unit[$looper]['etc'] = GetEtcData($etc,$workgroup[$i]['idx'].'-0');
					$unit[$looper]['sort'] = '1';
					$looper++;
				}
			}

			$unit[] = array('idx'=>'99-1','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'간접재료비','price'=>$sheet[1]['price'],'unit_price'=>GetUnitPrice($sheet[1]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-1'),'sort'=>1);
			$unit[] = array('idx'=>'99-2','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'작업설, 부산물','price'=>$sheet[2]['price'],'unit_price'=>GetUnitPrice($sheet[2]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-2'),'sort'=>2);
			$unit[] = array('idx'=>'99-3','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'간접노무비','price'=>$sheet[4]['price'],'unit_price'=>GetUnitPrice($sheet[4]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-3'),'sort'=>3);
			$unit[] = array('idx'=>'99-4','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'산재보험료','price'=>$sheet[6]['price'],'unit_price'=>GetUnitPrice($sheet[6]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-4'),'sort'=>4);
			$unit[] = array('idx'=>'99-5','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'고용보험료','price'=>$sheet[7]['price'],'unit_price'=>GetUnitPrice($sheet[7]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-5'),'sort'=>5);
			$unit[] = array('idx'=>'99-6','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'국민건강보험료','price'=>$sheet[8]['price'],'unit_price'=>GetUnitPrice($sheet[8]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-6'),'sort'=>6);
			$unit[] = array('idx'=>'99-7','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'국민연금보험료','price'=>$sheet[9]['price'],'unit_price'=>GetUnitPrice($sheet[9]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-7'),'sort'=>7);
			$unit[] = array('idx'=>'99-8','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'노인장기요양보험료','price'=>$sheet[10]['price'],'unit_price'=>GetUnitPrice($sheet[10]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-8'),'sort'=>8);
			$unit[] = array('idx'=>'99-9','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'산업안전보건관리비','price'=>$sheet[11]['price'],'unit_price'=>GetUnitPrice($sheet[11]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-9'),'sort'=>9);
			$unit[] = array('idx'=>'99-10','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'일반관리비','price'=>$sheet[12]['price'],'unit_price'=>GetUnitPrice($sheet[12]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-10'),'sort'=>10);
			$unit[] = array('idx'=>'99-11','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'이윤','price'=>$sheet[13]['price'],'unit_price'=>GetUnitPrice($sheet[13]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-11'),'sort'=>11);
			$unit[] = array('idx'=>'99-12','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'견적','price'=>$sheet[14]['price'],'unit_price'=>GetUnitPrice($sheet[14]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-12'),'sort'=>12);
			$unit[] = array('idx'=>'99-13','workgroup'=>sprintf('%02d',$maxSort).' 간접비용','worktype'=>'부가가치세','price'=>$sheet[15]['price'],'unit_price'=>GetUnitPrice($sheet[15]['price'],$area),'etc'=>GetEtcData($etc,$workgroup[$i]['idx'].'-13'),'sort'=>13);

			$mDB->DBupdate($mErp->table['cost'],array('unit'=>serialize($unit)),'',"where `idx`=$idx");

			$list = GetArrayToExtData($unit);
		}

		// 기존 내역서 목록
		if ($mode == 'allcost') {
			$wno = Request('wno');
			$idx = Request('idx');

			if ($idx) $find = "where `wno`=$wno and `idx`!=$idx";
			else $find = "where `wno`=$wno";

			$type = array('EXEC'=>'실행','ESTIMATE'=>'견적','CONTRACT'=>'계약','CHANGE'=>'변경');

			$data = $mDB->DBfetchs($mErp->table['cost'],array('idx','title','type','price','modify_date'),$find);
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$itemnum = $mDB->DBcount($mErp->table['cost_item'],"where `repto`={$data[$i]['idx']}");

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"title":"['.$type[$data[$i]['type']].'] '.GetString($data[$i]['title'],'ext').' ('.GetTime('Y.m.d',$data[$i]['modify_date']).', '.number_format($data[$i]['price']).'원)"';
				$list[$i].= '}';
			}
		}

		// 엑셀로딩
		if ($mode == 'excel') {
			$code = Request('code');
			$data = $mDB->DBfetch($mErp->table['excel'],array('data'),"where `code`='$code'");
			$data = isset($data['data']) == true ? unserialize($data['data']) : array();
			$mDB->DBdelete($mErp->table['excel'],"where `code`='$code'");

			$list = GetArrayToExtData($data);
		}
	}
}

/************************************************************************************************
 * 근태관리
 ***********************************************************************************************/
if ($action == 'attend') {
	if ($get == 'attend') {
		$wno = Request('wno');
		$date = Request('date') ? Request('date') : GetTime('Y-m-d');

		$find = "where `date`='$date'";
		if ($wno) $find.= " and `wno`=$wno";

		$data = $mDB->DBfetchs($mErp->table['attend_member'],'*',$find);

		$month = substr($date,0,7);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$worker = $mDB->DBfetch($mErp->table['worker'],array('name','jumin','grade'),"where `idx`={$data[$i]['pno']}");
			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$data[$i]['wno']}");

			$month_attend = 0;
			$month_early = 0;
			$month_delay = 0;
			$workerAttend = $mDB->DBfetchs($mErp->table['attend_member'],array('is_early','is_delay'),"where `date` like '$month%' and `workernum`='{$data[$i]['workernum']}' and `wno`={$data[$i]['wno']}");
			for ($j=0, $loopj=sizeof($workerAttend);$j<$loopj;$j++) {
				$month_attend++;
				if ($workerAttend[$j]['is_early'] == 'TRUE') $month_early++;
				if ($workerAttend[$j]['is_delay'] == 'TRUE') $month_delay++;
			}

			if ($data[$i]['owno'] != $data[$i]['wno']) {
				$oworkspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$data[$i]['owno']}");
				$oworkspace = $oworkspace['title'];
			} else {
				$oworkspace = '';
			}

			$list[$i] = '{';
			$list[$i].= '"workspace":"'.$workspace['title'].'",';
			$list[$i].= '"wno":"'.$data[$i]['wno'].'",';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"pno":"'.$data[$i]['pno'].'",';
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
			$list[$i].= '"is_early":"'.$data[$i]['is_early'].'",';
			$list[$i].= '"is_delay":"'.$data[$i]['is_delay'].'",';
			$list[$i].= '"month_attend":"'.$month_attend.'",';
			$list[$i].= '"month_delay":"'.$month_delay.'",';
			$list[$i].= '"month_early":"'.$month_early.'",';
			$list[$i].= '"is_support":"'.($data[$i]['owno'] != $data[$i]['wno'] ? 'TRUE' : 'FALSE').'",';
			$list[$i].= '"oworkspace":"'.GetString($oworkspace,'ext').'",';
			$list[$i].= '"write_memo":"'.GetString($data[$i]['write_memo'],'ext').' '.($data[$i]['is_write_time'] > 0 ? '('.GetTime('Y.m.d H:i',$data[$i]['is_write_time']).')' : '').'",';
			$list[$i].= '"working":"'.$data[$i]['working'].'",';
			$list[$i].= '"etc":"'.$data[$i]['etc'].'"';
			$list[$i].= '}';
		}
	}

	if ($get == 'monthly_list') {
		$workernum = Request('workernum');

		$first = $mDB->DBfetch($mErp->table['attend_member'],array('date'),"where `workernum`='$workernum'",'date,asc');
		$temp = explode('-',$first['date']);
		$date = 0;
		$i = 0;
		while (($date = mktime(0,0,0,$temp[1]+$i,1,$temp[0])) < time()) {
			$list[$i] = '{"date":"'.date('Y-m-d',$date).'","display":"'.date('Y년 m월',$date).'"}';
			$i++;
		}
	}

	if ($get == 'monthly') {
		$date = date('Y-m',strtotime(Request('date')));
		$workernum = Request('workernum');

		$find = "where `date` like '$date%' and `workernum`='$workernum'";

		$data = $mDB->DBfetchs($mErp->table['attend_member'],'*',$find);

		$month = substr($date,0,7);

		$dateArray = array();
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$data[$i]['wno']}");

			$month_attend = 0;
			$month_early = 0;
			$month_delay = 0;
			$workerAttend = $mDB->DBfetchs($mErp->table['attend_member'],array('is_early','is_delay'),"where `date` like '$month%' and `workernum`='{$data[$i]['workernum']}' and `wno`={$data[$i]['wno']}");
			for ($j=0, $loopj=sizeof($workerAttend);$j<$loopj;$j++) {
				$month_attend++;
				if ($workerAttend[$j]['is_early'] == 'TRUE') $month_early++;
				if ($workerAttend[$j]['is_delay'] == 'TRUE') $month_delay++;
			}

			if ($data[$i]['owno'] != $data[$i]['wno']) {
				$oworkspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$data[$i]['owno']}");
				$oworkspace = $oworkspace['title'];
			} else {
				$oworkspace = '';
			}

			$dateArray[$data[$i]['date']] = '{';
			$dateArray[$data[$i]['date']].= '"idx":"'.$data[$i]['idx'].'",';
			$dateArray[$data[$i]['date']].= '"date":"'.$data[$i]['date'].'",';
			$dateArray[$data[$i]['date']].= '"workspace":"'.$workspace['title'].'",';
			$dateArray[$data[$i]['date']].= '"intime":"'.($data[$i]['intime'] ? GetTime('H:i',$data[$i]['intime']) : '00:00').'",';
			$dateArray[$data[$i]['date']].= '"outtime":"'.($data[$i]['outtime'] ? GetTime('H:i',$data[$i]['outtime']) : '00:00').'",';
			$dateArray[$data[$i]['date']].= '"write_intime":"'.($data[$i]['write_intime'] ? GetTime('H:i',$data[$i]['write_intime']) : '').'",';
			$dateArray[$data[$i]['date']].= '"write_outtime":"'.($data[$i]['write_outtime'] ? GetTime('H:i',$data[$i]['write_outtime']) : '').'",';
			$dateArray[$data[$i]['date']].= '"inphoto":"'.(file_exists($_ENV['path'].'/userfile/erp/attend/'.$data[$i]['date'].'/'.$data[$i]['workernum'].'.in.jpg') == true ? $_ENV['dir'].'/userfile/erp/attend/'.$data[$i]['date'].'/'.$data[$i]['workernum'].'.in.jpg' : '').'",';
			$dateArray[$data[$i]['date']].= '"outphoto":"'.(file_exists($_ENV['path'].'/userfile/erp/attend/'.$data[$i]['date'].'/'.$data[$i]['workernum'].'.out.jpg') == true ? $_ENV['dir'].'/userfile/erp/attend/'.$data[$i]['date'].'/'.$data[$i]['workernum'].'.out.jpg' : '').'",';
			$dateArray[$data[$i]['date']].= '"is_write":"'.$data[$i]['is_write'].'",';
			$dateArray[$data[$i]['date']].= '"is_early":"'.$data[$i]['is_early'].'",';
			$dateArray[$data[$i]['date']].= '"is_delay":"'.$data[$i]['is_delay'].'",';
			$dateArray[$data[$i]['date']].= '"month_attend":"'.$month_attend.'",';
			$dateArray[$data[$i]['date']].= '"month_delay":"'.$month_delay.'",';
			$dateArray[$data[$i]['date']].= '"month_early":"'.$month_early.'",';
			$dateArray[$data[$i]['date']].= '"is_support":"'.($data[$i]['owno'] != $data[$i]['wno'] ? 'TRUE' : 'FALSE').'",';
			$dateArray[$data[$i]['date']].= '"oworkspace":"'.GetString($oworkspace,'ext').'",';
			$dateArray[$data[$i]['date']].= '"write_memo":"'.GetString($data[$i]['write_memo'],'ext').' '.($data[$i]['is_write_time'] > 0 ? '('.GetTime('Y.m.d H:i',$data[$i]['is_write_time']).')' : '').'",';
			$dateArray[$data[$i]['date']].= '"working":"'.$data[$i]['working'].'",';
			$dateArray[$data[$i]['date']].= '"etc":"'.$data[$i]['etc'].'"';
			$dateArray[$data[$i]['date']].= '}';
		}

		for ($i=1, $loop=date('t',strtotime(Request('date')));$i<=$loop;$i++) {
			if (isset($dateArray[$date.'-'.sprintf('%02d',$i)]) == true) {
				$list[] = $dateArray[$date.'-'.sprintf('%02d',$i)];
			} else {
				$list[] = '{"idx":"0","date":"'.$date.'-'.sprintf('%02d',$i).'"}';
			}
		}
	}

	if ($get == 'condition') {
		$data = $mDB->DBfetchs($mErp->table['workspace'],array('idx','title','auto_delay_condition','auto_early_condition'),"where `type`='WORKING'");

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"workspace":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"auto_delay_condition":"'.GetString($data[$i]['auto_delay_condition'],'ext').'",';
			$list[$i].= '"auto_early_condition":"'.GetString($data[$i]['auto_early_condition'],'ext').'"';
			$list[$i].= '}';
		}
	}
}

/************************************************************************************************
 * 현장일일상황일지
 ***********************************************************************************************/
if ($action == 'daily') {
	$date = Request('date') ? Request('date') : GetTime('Y-m-d');
	$find = "where `date`='$date'";
	$find.= Request('wno') ? " and `wno`=".Request('wno') : "";
	$data = $mDB->DBfetchs($mErp->table['workreport'],array('wno','data','weather'),$find);

	for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
		$daily = unserialize($data[$i]['data']);
		for ($j=0, $loopj=sizeof($daily);$j<$loopj;$j++) {
			$daily[$j]['group'] = $mErp->GetWorkspaceTitle($data[$i]['wno']);
			$daily[$j]['workgroup'] = $mErp->GetWorkgroup($daily[$j]['gno']);
			$daily[$j]['worktype'] = $mErp->GetWorkgroup($daily[$j]['tno']);
			unset($daily[$j]['gno']);
			unset($daily[$j]['tno']);
			$list[] = $daily[$j];
		}
	}
	$list = GetArrayToExtData($list);
}

/************************************************************************************************
 * 품명DB관련
 ***********************************************************************************************/
if ($action == 'item') {
	// 자동완성
	if ($get == 'automatch') {
		$query = GetUTF8Divide(Request('query'));

		$looper = 0;
		$automatch = array();

		// 품명 DB에서 검색
		if (sizeof($automatch) < 15) {
			$data = $mDB->DBfetchs($mErp->table['item'],array('itemcode','bgno','btno','title','size','unit','cost1','cost2','cost3'),"where `search` like '$query%'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if (isset($automatch[$data[$i]['itemcode']]) == false) {
					$automatch[$data[$i]['itemcode']] = array();
					$automatch[$data[$i]['itemcode']]['display'] = '['.$mErp->GetBaseWorkgroup($data[$i]['bgno']).'>'.$mErp->GetBaseWorktype($data[$i]['btno']).'] '.$data[$i]['size'] ? $data[$i]['title'].' ('.$data[$i]['size'].')' : $data[$i]['title'];
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
		}

		$looper = 0;
		foreach ($automatch as $itemcode=>$match) {
			$list[$looper] = '{';
			$list[$looper].= '"itemcode":"'.$itemcode.'"';
			foreach($match as $key=>$value) {
				$list[$looper].= ',"'.$key.'":"'.GetString($value,'ext').'"';
			}
			$list[$looper].= '}';
			$looper++;
		}
	}

	// 자재확인
	if ($get == 'check') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$title = Request('title');
		$size = Request('size');
		$unit = Request('unit');

		$itemcode = $mErp->GetItemcode($title,$size,$unit);

		$isFind = false;
		if ($isFind == false) {
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

	// 자재목록
	if ($get == 'list') {
		$keyword = Request('keyword');
		$bgno = Request('bgno');
		$btno = Request('btno');

		if ($keyword != null || $bgno != null || $btno != null) {
			$find = 'where 1';
			$find.= $bgno != null ? " and `bgno`='$bgno'" : '';
			$find.= $btno != null ? " and `btno`='$btno'" : '';
			$find.= $keyword != null ? " and `title` like '%$keyword%'" : '';
		} else {
			$find = '';
		}

		$data = $mDB->DBfetchs($mErp->table['item'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($mErp->table['item'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"itemcode":"'.$data[$i]['itemcode'].'",';
			$list[$i].= '"workgroup":"'.GetString($mErp->GetBaseWorkgroup($data[$i]['bgno']),'ext').'",';
			$list[$i].= '"worktype":"'.GetString($mErp->GetBaseWorktype($data[$i]['btno']),'ext').'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"size":"'.GetString($data[$i]['size'],'ext').'",';
			$list[$i].= '"unit":"'.GetString($data[$i]['unit'],'ext').'",';
			$list[$i].= '"cost1":"'.$data[$i]['cost1'].'",';
			$list[$i].= '"cost2":"'.$data[$i]['cost2'].'",';
			$list[$i].= '"cost3":"'.$data[$i]['cost3'].'",';
			$list[$i].= '"avgcost1":"'.$mErp->GetItemAvgCost($data[$i]['itemcode'],'cost1').'",';
			$list[$i].= '"avgcost2":"'.$mErp->GetItemAvgCost($data[$i]['itemcode'],'cost2').'",';
			$list[$i].= '"avgcost3":"'.$mErp->GetItemAvgCost($data[$i]['itemcode'],'cost3').'",';
			$list[$i].= '"yearcost1":"'.$data[$i]['avgcost1'].'",';
			$list[$i].= '"yearcost2":"'.$data[$i]['avgcost2'].'",';
			$list[$i].= '"yearcost3":"'.$data[$i]['avgcost3'].'",';
			$list[$i].= '}';
		}
	}

	// 평균단가목록
	if ($get == 'avglist') {
		$itemcode = Request('itemcode');
		$find = '';
		$data = $mDB->DBfetchs($mErp->table['item_cost'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($mErp->table['item_cost'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.($i+1).'",';
			$list[$i].= '"type":"'.$data[$i]['type'].'",';
			$list[$i].= '"workspace":"'.GetString($mErp->GetWorkspaceTitle($data[$i]['wno']),'ext').'",';
			$list[$i].= '"cost1":"'.$data[$i]['cost1'].'",';
			$list[$i].= '"cost2":"'.$data[$i]['cost2'].'",';
			$list[$i].= '"cost3":"'.$data[$i]['cost3'].'",';
			$list[$i].= '"reg_date":"'.GetTime('Y년 m월 d일',$data[$i]['reg_date']).'",';
			$list[$i].= '"chart_date":"'.GetTime('y.m.d',$data[$i]['reg_date']).'"';
			$list[$i].= '}';
		}
	}
}

/************************************************************************************************
 * 현장발주관리
 ***********************************************************************************************/
if ($action == 'order') {
	// 현장발주요청서
	if ($get == 'order') {
		$mode = Request('mode');

		// 목록
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$find = "where `date` like '$date%'";
			$data = $mDB->DBfetchs($mErp->table['outsourcing_order'],'*',$find,$orderer,$limiter);
			$total = $mDB->DBcount($mErp->table['outsourcing_order'],$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$consult = $mDB->DBfetchs($mErp->table['outsourcing_consult'],array('idx','type','title','date'),"where `repto`={$data[$i]['idx']}");
				$consultList = array();
				for ($j=0, $loopj=sizeof($consult);$j<$loopj;$j++) {
					$consultList[$j] = $consult[$j]['idx'].'||'.$consult[$j]['type'].'||'.GetString($consult[$j]['title'].' ('.date('Y.m.d',strtotime($consult[$j]['date'])).')','ext');

					$contract = $mDB->DBfetchs($mErp->table['outsourcing_contract'],array('idx','title','date'),"where `parent`={$consult[$j]['idx']}");
					$contractList = array();
					for ($k=0, $loopk=sizeof($contract);$k<$loopk;$k++) {
						$contractList[$k] = $contract[$k]['idx'].'##'.GetString($contract[$k]['title'].' ('.date('Y.m.d',strtotime($contract[$k]['date'])).')','ext');
					}
					$consultList[$j].= sizeof($contract) > 0 ? '||'.implode('||',$contractList) : '';
				}
				$consultList = implode("\t",$consultList);

				$item = unserialize($data[$i]['data']);
				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"wno":"'.$data[$i]['wno'].'",';
				$list[$i].= '"order_type":"'.$data[$i]['order_type'].'",';
				$list[$i].= '"workspace":"'.GetString($mErp->GetWorkspaceTitle($data[$i]['wno']),'xml').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"item":"'.sizeof(unserialize($data[$i]['data'])).'",';
				$list[$i].= '"file":"'.$data[$i]['file'].'",';
				$list[$i].= '"is_confirm":"'.($data[$i]['status'] == 'NEW' ? 'FALSE' : 'TRUE').'",';
				$list[$i].= '"is_consult":"'.($mDB->DBcount($mErp->table['outsourcing_consult'],"where `repto`={$data[$i]['idx']}") > 0 ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"is_contract":"'.($mDB->DBcount($mErp->table['outsourcing_contract'],"where `repto`={$data[$i]['idx']}") > 0 ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"is_complete":"'.($data[$i]['status'] == 'COMPLETE' ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"consult":"'.$consultList.'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['date'])).'",';
				$list[$i].= '"datetime":"'.date('Y년 m월 d일 H시 i분',strtotime($data[$i]['date'])).'"';
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
				$list[$i]['group'] = ' ';
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

	// 본사품의서
	if ($get == 'consult') {
		$mode = Request('mode');

		// 본사품의서 목록
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$find = "where `date` like '$date%'";
			$data = $mDB->DBfetchs($mErp->table['outsourcing_consult'],array('idx','type','wno','title','repto','contract','consult','date'),$find,$orderer,$limiter);
			$total = $mDB->DBcount($mErp->table['outsourcing_consult'],$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$items = unserialize($data[$i]['contract']);
				$consult = unserialize($data[$i]['consult']);
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$consult[0]['cno']}");
				if ($data[$i]['type'] == 'SERIES') {
					$cooperation = $cooperation['title'];
				} else {
					$cooperationNum = 0;
					for ($j=0, $loopj=sizeof($consult);$j<$loopj;$j++) {
						if ($consult[$j]['cno'] != $consult[0]['cno']) $cooperationNum++;
					}
					$cooperation = $cooperation['title'];
					if ($cooperationNum > 0) $cooperation.= '외 '.$cooperationNum.'개 업체';
				}

				$contract = $mDB->DBfetchs($mErp->table['outsourcing_contract'],array('idx','title','date'),"where `parent`={$data[$i]['idx']}");
				$contractList = array();
				for ($j=0, $loopj=sizeof($contract);$j<$loopj;$j++) {
					$contractList[$j] = $contract[$j]['idx'].'||'.GetString($contract[$j]['title'].' ('.date('Y.m.d',strtotime($contract[$j]['date'])).')','ext');
				}
				$contractList = implode("\t",$contractList);

				if ($data[$i]['repto']) {
					$check = $mDB->DBfetch($mErp->table['outsourcing_order'],array('status'),"where `idx`={$data[$i]['repto']}");
					$is_complete = isset($check['status']) == true && $check['status'] == 'COMPLETE' ? 'TRUE' : 'FALSE';
				} else {
					$is_complete = $mDB->DBcount($mErp->table['outsourcing_contract'],"where `parent`={$data[$i]['idx']} and `status`!='NEW'") > 0 ? 'TRUE' : 'FALSE';
				}

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"wno":"'.$data[$i]['wno'].'",';
				$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
				$list[$i].= '"type":"'.$data[$i]['type'].'",';
				$list[$i].= '"workspace":"'.GetString($mErp->GetWorkspaceTitle($data[$i]['wno']),'ext').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"item":"'.sizeof($items).'",';
				$list[$i].= '"cooperation":"'.GetString($cooperation,'ext').'",';
				$list[$i].= '"is_contract":"'.($contractList ? 'TRUE' : 'FALSE').'",';
				$list[$i].= '"contract":"'.$contractList.'",';
				$list[$i].= '"is_complete":"'.$is_complete.'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['date'])).'",';
				$list[$i].= '"datetime":"'.date('Y년 m월 d일 H시 i분',strtotime($data[$i]['date'])).'"';
				$list[$i].= '}';
			}
		}

		// 품의도급내역
		if ($mode == 'contract') {
			$idx = Request('idx');
			$repto = Request('repto');

			if ($idx == 0) { // 품의서가 작성되지 않았을 때
				if ($repto != 0) {
					$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('wno','data'),"where `idx`=$repto");
					$list = unserialize($data['data']);

					$workspace = $mDB->DBfetch($mErp->table['workspace'],array('contract'),"where `idx`={$data['wno']}");
					for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
						// 자재코드
						$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
						$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
						// 도급내역에서 검색
						$contract = $mErp->GetContractItem($data['wno'],$code);
						// 실행내역에서 검색
						$exec = $mErp->GetExecItem($data['wno'],$code);

						if (isset($contract['idx']) == true) {
							$list[$i]['code'] = $code;
							$list[$i]['cost1'] = $contract['cost1'];
							$list[$i]['cost2'] = $contract['cost2'];
							$list[$i]['cost3'] = $contract['cost3'];
						} else {
							$list[$i]['code'] = '';
							$list[$i]['cost1'] = '0';
							$list[$i]['cost2'] = '0';
							$list[$i]['cost3'] = '0';
						}

						if (isset($exec['idx']) == true) {
							$list[$i]['exec_cost1'] = $exec['cost1'];
							$list[$i]['exec_cost2'] = $exec['cost2'];
							$list[$i]['exec_cost3'] = $exec['cost3'];
						} else {
							$list[$i]['exec_cost1'] = '0';
							$list[$i]['exec_cost2'] = '0';
							$list[$i]['exec_cost3'] = '0';
						}

						$list[$i]['sort'] = $i;
						$list[$i]['group'] = ' ';
						$list[$i]['cost'] = '0';
						$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);

						$list[$i]['avgcost1'] = $mErp->GetItemAvgCost($itemcode,'cost1');
						$list[$i]['avgcost2'] = $mErp->GetItemAvgCost($itemcode,'cost2');
						$list[$i]['avgcost3'] = $mErp->GetItemAvgCost($itemcode,'cost3');

						$list[$i]['workgroup'] = $mErp->GetWorkgroup($list[$i]['gno']);
						$list[$i]['worktype'] = $mErp->GetWorktype($list[$i]['tno']);
					}
				}
			} else {
				$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('wno','contract'),"where `idx`=$idx");
				$list = unserialize($data['contract']);

				for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
					// 자재코드
					$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
					$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
					// 도급내역에서 검색
					$contract = $mErp->GetContractItem($data['wno'],$code);
					// 실행내역에서 검색
					$exec = $mErp->GetExecItem($data['wno'],$code);

					if (isset($contract['idx']) == true) {
						$list[$i]['code'] = $code;
						$list[$i]['cost1'] = $contract['cost1'];
						$list[$i]['cost2'] = $contract['cost2'];
						$list[$i]['cost3'] = $contract['cost3'];
					} else {
						$list[$i]['code'] = '';
						$list[$i]['cost1'] = '0';
						$list[$i]['cost2'] = '0';
						$list[$i]['cost3'] = '0';
					}

					if (isset($exec['idx']) == true) {
						$list[$i]['exec_cost1'] = $exec['cost1'];
						$list[$i]['exec_cost2'] = $exec['cost2'];
						$list[$i]['exec_cost3'] = $exec['cost3'];
					} else {
						$list[$i]['exec_cost1'] = '0';
						$list[$i]['exec_cost2'] = '0';
						$list[$i]['exec_cost3'] = '0';
					}

					$list[$i]['group'] = ' ';
					$list[$i]['cost'] = '0';
					$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);

					$list[$i]['avgcost1'] = $mErp->GetItemAvgCost($itemcode,'cost1');
					$list[$i]['avgcost2'] = $mErp->GetItemAvgCost($itemcode,'cost2');
					$list[$i]['avgcost3'] = $mErp->GetItemAvgCost($itemcode,'cost3');

					$list[$i]['workgroup'] = $mErp->GetWorkgroup($list[$i]['gno']);
					$list[$i]['worktype'] = $mErp->GetWorktype($list[$i]['tno']);
				}
			}

			$list = GetArrayToExtData($list);
		}

		// 업체별 품의
		if ($mode == 'tab') {
			$idx = Request('idx');

			$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('wno','consult'),"where `idx`=$idx");

			if (isset($data['consult']) == true || $data['consult'] != '') {
				$tabs = unserialize($data['consult']);
				for ($i=0, $loop=sizeof($tabs);$i<$loop;$i++) {
					$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$tabs[$i]['cno']}");
					if ($data['type'] != 'SERIES') {
						$title = $cooperation['title'];
					} else {
						$title = ($i+1).'회('.$cooperation['title'].')';
					}
					$list[$i] = '{"cno":"'.$tabs[$i]['cno'].'","tno":"'.$i.'","wno":"'.$data['wno'].'","title":"'.$title.'","cost1":"'.$tabs[$i]['cost1'].'","cost2":"'.$tabs[$i]['cost2'].'","cost3":"'.$tabs[$i]['cost3'].'"}';
				}
			}
		}

		if ($mode == 'excel') {
			$idx = Request('idx');

			$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('wno','consult'),"where `idx`=$idx");

			if (isset($data['consult']) == true || $data['consult'] != '') {
				$tabs = unserialize($data['consult']);

				for ($i=0, $loop=sizeof($tabs);$i<$loop;$i++) {
					$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$tabs[$i]['cno']}");
					if ($data['type'] != 'SERIES') {
						$title = $cooperation['title'];
					} else {
						$title = ($i+1).'회('.$cooperation['title'].')';
					}
					$price = 0;
					for ($j=0, $loopj=sizeof($tabs[$i]['items']);$j<$loopj;$j++) {
						if ($tabs[$i]['cost1'] == 'TRUE') $price+= floor($tabs[$i]['items'][$j]['ea']*$tabs[$i]['items'][$j]['cost1']);
						if ($tabs[$i]['cost2'] == 'TRUE') $price+= floor($tabs[$i]['items'][$j]['ea']*$tabs[$i]['items'][$j]['cost2']);
						if ($tabs[$i]['cost3'] == 'TRUE') $price+= floor($tabs[$i]['items'][$j]['ea']*$tabs[$i]['items'][$j]['cost3']);
					}
					$list[$i] = '{"tno":"'.$i.'","title":"'.$title.'","price":"'.$price.'"}';
				}
			}
		}

		// 업체별 품의 세부항목
		if ($mode == 'tabdata') {
			$idx = Request('idx');
			$tno = Request('tno');

			$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('wno','consult'),"where `idx`=$idx");
			$tabs = unserialize($data['consult']);
			$list = $tabs[$tno]['items'];
			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				// 자재코드
				$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
				$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
				// 도급내역에서 검색
				$contract = $mErp->GetContractItem($data['wno'],$code);

				if (isset($contract['idx']) == true) {
					$list[$i]['code'] = $code;
				} else {
					$list[$i]['code'] = '';
				}

				$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);

				$list[$i]['avgcost1'] = $mErp->GetItemAvgCost($itemcode,'cost1');
				$list[$i]['avgcost2'] = $mErp->GetItemAvgCost($itemcode,'cost2');
				$list[$i]['avgcost3'] = $mErp->GetItemAvgCost($itemcode,'cost3');
			}
			$list = GetArrayToExtData($list);
		}

		// 비고
		if ($mode == 'etc') {
			header('Content-type: text/xml; charset="UTF-8"', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');

			if ($idx == 0) {
				$data['repto'] = Request('repto');
			} else {
				$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('title','repto','etc'),"where `idx`=$idx");
			}
			echo GetArrayToExtXML($data);
			exit;
		}
	}

	// 발주계약서
	if ($get == 'contract') {
		$mode = Request('mode');

		// 발주계약서 목록
		if ($mode == 'list') {
			$date = Request('date') ? Request('date') : date('Y-m');
			$find = "where `date` like '$date%'";
			$data = $mDB->DBfetchs($mErp->table['outsourcing_contract'],'*',$find,$orderer,$limiter);
			$total = $mDB->DBcount($mErp->table['outsourcing_contract'],$find);

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$items = unserialize($data[$i]['data']);
				$price = 0;
				for ($j=0, $loopj=sizeof($items);$j<$loopj;$j++) $price+= $items[$j]['price'];
				$cooperation = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$data[$i]['cno']}");

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"wno":"'.$data[$i]['wno'].'",';
				$list[$i].= '"ono":"'.$data[$i]['ono'].'",';
				$list[$i].= '"repto":"'.$data[$i]['repto'].'",';
				$list[$i].= '"workspace":"'.GetString($mErp->GetWorkspaceTitle($data[$i]['wno']),'ext').'",';
				$list[$i].= '"cooperation":"'.GetString($cooperation['title'],'ext').'",';
				$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
				$list[$i].= '"item":"'.sizeof($items).'",';
				$list[$i].= '"price":"'.$price.'",';
				$list[$i].= '"status":"'.$data[$i]['status'].'",';
				$list[$i].= '"date":"'.date('Y년 m월 d일',strtotime($data[$i]['date'])).'",';
				$list[$i].= '"datetime":"'.date('Y년 m월 d일 H시 i분',strtotime($data[$i]['date'])).'"';
				$list[$i].= '}';
			}
		}

		if ($mode == 'item') {
			$idx = Request('idx');

			if ($idx == 0) { // 계약서가 작성되어 있지 않은 경우,
				$parent = Request('parent');
				$tno = Request('tno');

				$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('wno','consult'),"where `idx`=$parent");
				$tabs = unserialize($data['consult']);
				$list = $tabs[$tno]['items'];
				for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
					// 자재코드
					$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
					$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
					// 도급내역에서 검색
					$contract = $mErp->GetContractItem($data['wno'],$code);

					if (isset($contract['idx']) == true) {
						$list[$i]['code'] = $code;
					} else {
						$list[$i]['code'] = '';
					}

					$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);

					$list[$i]['avgcost1'] = $mErp->GetItemAvgCost($itemcode,'cost1');
					$list[$i]['avgcost2'] = $mErp->GetItemAvgCost($itemcode,'cost2');
					$list[$i]['avgcost3'] = $mErp->GetItemAvgCost($itemcode,'cost3');

					$list[$i]['workgroup'] = $mErp->GetWorkgroup($list[$i]['gno']);
					$list[$i]['worktype'] = $mErp->GetWorktype($list[$i]['tno']);
				}
				$list = GetArrayToExtData($list);
			} else {
				$data = $mDB->DBfetch($mErp->table['outsourcing_contract'],array('wno','data'),"where `idx`=$idx");
				$list = unserialize($data['data']);

				for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
					// 자재코드
					$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
					$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);
					// 도급내역에서 검색
					$contract = $mErp->GetContractItem($data['wno'],$code);

					if (isset($contract['idx']) == true) {
						$list[$i]['code'] = $code;
					} else {
						$list[$i]['code'] = '';
					}

					$list[$i]['group'] = ' ';
					$list[$i]['order_ea'] = $mErp->GetOrderStatus($data['wno'],$code);

					$list[$i]['avgcost1'] = $mErp->GetItemAvgCost($itemcode,'cost1');
					$list[$i]['avgcost2'] = $mErp->GetItemAvgCost($itemcode,'cost2');
					$list[$i]['avgcost3'] = $mErp->GetItemAvgCost($itemcode,'cost3');

					$list[$i]['workgroup'] = $mErp->GetWorkgroup($list[$i]['gno']);
					$list[$i]['worktype'] = $mErp->GetWorktype($list[$i]['tno']);
				}
				$list = GetArrayToExtData($list);
			}
		}

		if ($mode == 'data') {
			header('Content-type: text/xml; charset="UTF-8"', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$parent = Request('parent');

			if ($idx == 0) {
				$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('type','repto'),"where `idx`=$parent");
				$data['parent_type'] = $data['type'];
				$data['parent'] = $parent;
				unset($data['type']);
			} else {
				$data = $mDB->DBfetch($mErp->table['outsourcing_contract'],array('title','status','etc','cno','repto','parent'),"where `idx`=$idx");
				if ($data['parent'] != 0) {
					$consult = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('type'),"where `idx`={$data['parent']}");
					$data['parent_type'] = $consult['type'];
				}
			}
			echo GetArrayToExtXML($data);
			exit;
		}
	}
}

/************************************************************************************************
 * 협력업체관리
 ***********************************************************************************************/
if ($action == 'cooperation') {
	// 목록
	if ($get == 'list') {
		$find = "where `is_delete`='FALSE'";
		$keyword = Request('keyword');
		$find.= $keyword != null ? " and `title` like '%$keyword%' or `company_number` like `%$keyword%'" : '';

		$data = $mDB->DBfetchs($mErp->table['cooperation'],'*',$find,$orderer,$limiter);
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"company_number":"'.$data[$i]['company_number'].'",';
			$list[$i].= '"type":"'.GetString($data[$i]['type'],'ext').'",';
			$list[$i].= '"master":"'.GetString($data[$i]['master'],'ext').'",';
			$list[$i].= '"telephone":"'.$data[$i]['telephone'].'",';
			$list[$i].= '"contract":"0",';
			$list[$i].= '"contract_price":"0"';
			$list[$i].= '}';
		}
	}

	if ($get == 'data') {
		header('Content-type: text/xml; charset="UTF-8"', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = Request('idx');
		$data = $mDB->DBfetch($mErp->table['cooperation'],'*',"where `idx`=$idx");

		$temp = explode('||',$data['address']);
		unset($data['address']);
		$data['address1'] = $temp[0];
		$data['address2'] = $temp[1];
		echo GetArrayToExtXML($data);
		exit;
	}
}

/************************************************************************************************
 * 기성현황 및 관리
 ***********************************************************************************************/
if ($action == 'monthly') {
	if ($get == 'list') {
		$date = Request('date') ? Request('date') : date('Y-m');
		$mode = Request('mode');

		if ($mode == 'working') $find = "where `type`='WORKING'";
		else $find = "where `type`='END'";

		$data = $mDB->DBfetchs($mErp->table['workspace'],array('idx','title'),$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$monthly = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`={$data[$i]['idx']} and `date`='$date'");
			$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`={$data[$i]['idx']} and `date`<'$date'");
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"contract":"'.$mErp->GetWorkspaceCost($data[$i]['idx'],'contract').'",';
			$list[$i].= '"exec":"'.$mErp->GetWorkspaceCost($data[$i]['idx'],'exec').'",';
			$list[$i].= '"prev":"'.($prev[0] ? $prev[0] : '0').'",';
			$list[$i].= '"monthly":"'.($monthly[0] ? $monthly[0] : '0').'"';
			$list[$i].= '}';
		}
	}

	if ($get == 'sheet') {
		$date = Request('date') ? Request('date') : date('Y-d');
		$wno = Request('wno');

		$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date'");
		$monthly = array();

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if (isset($monthly[$data[$i]['type']]) == false) {
				$monthly[$data[$i]['type']] = array();
				$monthly[$data[$i]['type']]['original'] = 0;
				$monthly[$data[$i]['type']]['contract'] = 0;
				$monthly[$data[$i]['type']]['monthly'] = 0;

				$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='{$data[$i]['type']}' and `date`<'$date'");
				$monthly[$data[$i]['type']]['prev'] = $prev[0] ? $prev[0] : '0';
			}

			$monthly[$data[$i]['type']]['original']+= $data[$i]['original'];
			$monthly[$data[$i]['type']]['contract']+= $data[$i]['contract'];
			$monthly[$data[$i]['type']]['monthly']+= $data[$i]['monthly'];
		}

		foreach ($monthly as $type=>$data) {
			$list[] = '{"group":" ","type":"'.$type.'","original":"'.$data['original'].'","contract":"'.$data['contract'].'","prev":"'.$data['prev'].'","monthly":"'.$data['monthly'].'"}';
		}
	}

	if ($get == 'outsourcing' || $get == 'item' || $get == 'expense' || $get == 'equipment') {
		$date = Request('date') ? Request('date') : date('Y-d');
		$wno = Request('wno');
		$mode = Request('mode');

		if ($mode == 'list') {
			$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date' and `type`='".strtoupper($get)."'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='".strtoupper($get)."' and `date`<'$date' and `cno`={$data[$i]['cno']} and `repto`={$data[$i]['repto']} and `cooperation`='{$data[$i]['cooperation']}'");
				$prev = $prev[0] ? $prev[0] : '0';

				$list[$i] = '{"idx":"'.$data[$i]['idx'].'","group":" ","type":"'.$get.'","cno":"'.$data[$i]['cno'].'","repto":"'.$data[$i]['repto'].'","cooperation":"'.GetString($data[$i]['cooperation'],'ext').'","original":"'.$data[$i]['original'].'","contract":"'.$data[$i]['contract'].'","prev":"'.$prev.'","monthly":"'.$data[$i]['monthly'].'"}';
			}
		}

		if ($mode == 'detail') {
			$cno = Request('cno');
			$repto = Request('repto');
			$cooperation = Request('cooperation');
			$data = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and `type`='".strtoupper($get)."' and `cno`=$cno and `repto`=$repto and `cooperation`='{$cooperation}'");

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(ea)'),"where `wno`=$wno and `type`='".strtoupper($get)."' and `date`<'$date' and `cno`=$cno and `repto`=$repto and `cooperation`='{$cooperation}' and `code`='{$data[$i]['code']}' and `subcode`='{$data[$i]['subcode']}'");
				$contract = $mErp->GetContractItem($wno,$temp[1]);

				$list[$i] = '{';
				$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
				$list[$i].= '"group":"'.$data[$i]['gno'].'-'.$data[$i]['tno'].'",';
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
	}

	if ($get == 'worker') {
		$date = Request('date') ? Request('date') : date('Y-d');
		$wno = Request('wno');

		$data = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `wno`=$wno and `date`='$date' and (`type`='MEMBER' or `type`='DAYWORKER')");
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
	}
}


/*
if ($action == 'monthly_payment') {
	$wno = Request('wno');
	$date = Request('date');

	// 목록
	if ($get == 'list') {
		$year = Request('year');
		$category = Request('category');
		if ($category == 'working') $find = "where `type`='WORKING'";
		else $find = "where `type`='END'";

		$data = $mDB->DBfetchs($mErp->table['workspace'],array('idx','title'),$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"contract":"'.$mErp->GetWorkspaceCost($data[$i]['idx'],'contract').'",';
			$list[$i].= '"exec":"'.$mErp->GetWorkspaceCost($data[$i]['idx'],'exec').'",';
			$list[$i].= '"lastyear":"'.$mErp->GetWorkspaceCostStatus($data[$i]['idx'],'monthly_payment',$year-1).'",';
			for ($j=1;$j<=12;$j++) {
				$list[$i].= '"p'.$j.'":"'.$mErp->GetWorkspaceCostStatus($data[$i]['idx'],'monthly_payment',$year.'-'.sprintf('%02d',$j)).'",';
			}
			$list[$i].= '"etc":""';
			$list[$i].= '}';
		}
	}

	// 공종별 보기
	if ($get == 'group') {
		$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx','workgroup','bgno','sort'),"where `wno`=$wno",'sort,asc');
		for ($i=0, $loop=sizeof($workgroup);$i<$loop;$i++) {
			// 공종별 그룹
			$basegroup = $mDB->DBfetch($mErp->table['base_workgroup'],array('workgroup','sort'),"where `idx`={$workgroup[$i]['bgno']}");

			// 발주금액
			$order = $mDB->DBfetch($mErp->table['order_income'],array('SUM(price)'),"where `wno`=$wno and `gno`={$workgroup[$i]['idx']} and `date` like '$date%'");
			$order = isset($order[0]) == true ? $order[0] : 0;

			// 현장기성금액
			$monthly_payment_workspace = $mDB->DBfetch($mErp->table['monthly_payment_list'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno`={$workgroup[$i]['idx']}");
			$monthly_payment_workspace = isset($monthly_payment_workspace[0]) == true ? $monthly_payment_workspace[0] : 0;

			// 본사기성금액
			$monthly_payment = $mDB->DBfetch($mErp->table['monthly_payment'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno`={$workgroup[$i]['idx']}");
			$monthly_payment = isset($monthly_payment[0]) == true ? $monthly_payment[0] : 0;

			$list[$i] = '{';
			$list[$i].= '"basegroup":"'.sprintf('%02d',$basegroup['sort']).' '.GetString($basegroup['workgroup'],'ext').'",';
			$list[$i].= '"gno":"'.$workgroup[$i]['idx'].'",';
			$list[$i].= '"workgroup":"'.sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup[$i]['sort']).' '.GetString($workgroup[$i]['workgroup'],'ext').'",';
			$list[$i].= '"contract":"'.$mErp->GetWorkspaceCost($wno,'contract',$workgroup[$i]['idx']).'",';
			$list[$i].= '"exec":"'.$mErp->GetWorkspaceCost($wno,'exec',$workgroup[$i]['idx']).'",';
			$list[$i].= '"order":"'.$order.'",';
			$list[$i].= '"workspace":"'.$monthly_payment_workspace.'",';
			$list[$i].= '"commander":"'.$monthly_payment.'",';
			$list[$i].= '"sort":"'.$workgroup[$i]['sort'].'"';
			$list[$i].= '}';
		}
	}

	// 공종별 하위 공종내역
	if ($get == 'grouplist') {
		$gno = Request('gno');

		// 공종내역별 탭목록
		if ($mode == 'tab') {
			$workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],array('workgroup','bgno','sort'),"where `idx`=$gno");
			$basegroup = $mDB->DBfetch($mErp->table['base_workgroup'],array('sort'),"where `idx`={$workgroup['bgno']}");
			$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`=$gno",'sort,asc');

			$list[] = '{"tab":"0","title":"'.sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup['sort']).' '.GetString($workgroup['workgroup'],'ext').'"}';

			for ($i=0, $loop=sizeof($worktype);$i<$loop;$i++) {
				$list[] = '{"tab":"'.GetString($worktype[$i]['idx'],'ext').'","title":"'.sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup['sort']).sprintf('%02d',$worktype[$i]['sort']).' '.GetString($mErp->GetBaseWorktype($worktype[$i]['btno']),'ext').'","contract":"TRUE","exec":"FALSE","order":"TRUE","workspace":"TRUE"}';
			}
		}

		// 집계
		if ($mode == 'group') {
			$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','btno','sort'),"where `gno`=$gno");
			if (sizeof($worktype) > 0) {
				for ($j=0, $loopj=sizeof($worktype);$j<$loopj;$j++) {
					// 발주금액
					$order = $mDB->DBfetch($mErp->table['order_income'],array('SUM(price)'),"where `wno`=$wno and `gno`=$gno and `tno`={$worktype[$j]['idx']} and `date` like '$date%'");
					$order = isset($order[0]) == true ? $order[0] : 0;

					// 현장기성금액
					$monthly_payment_workspace = $mDB->DBfetch($mErp->table['monthly_payment_list'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno`=$gno and `tno`={$worktype[$j]['idx']}");
					$monthly_payment_workspace = isset($monthly_payment_workspace[0]) == true ? $monthly_payment_workspace[0] : 0;

					// 본사기성금액
					$monthly_payment = $mDB->DBfetch($mErp->table['monthly_payment'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno`=$gno and `tno`={$worktype[$j]['idx']}");
					$monthly_payment = isset($monthly_payment[0]) == true ? $monthly_payment[0] : 0;

					$list[] = '{"group":" ","worktype":"'.GetString($mErp->GetBaseWorktype($worktype[$j]['btno']),'ext').'","tno":"'.$worktype[$j]['idx'].'","contract":"'.$mErp->GetWorkspaceCost($wno,'contract',$gno,$worktype[$j]['idx']).'","exec":"'.$mErp->GetWorkspaceCost($wno,'exec',$gno,$worktype[$j]['idx']).'","order":"'.$order.'","workspace":"'.$monthly_payment_workspace.'","commander":"'.$monthly_payment.'","sort":"'.$worktype[$j]['sort'].'"}';
				}
			} else {
				$list[] = '{"group":" ","worktype":"하위공종없음","tno":"0","contract":"0","exec":"0","order":"0","workspace":"0","commander":"0","sort":"0"}';
			}
		}

		// 기성내역
		if ($mode == 'tabdata') {
			function GetMonthlyPayment($data) {
				$payment = array();
				$payment['code'] = $data['code'];
				$payment['title'] = $data['title'];
				$payment['size'] = $data['size'];
				$payment['unit'] = $data['unit'];
				$payment['itemcode'] = $data['itemcode'];
				$payment['gno'] = $data['gno'];
				$payment['tno'] = $data['tno'];
				$payment['contract_cost1'] = 0;
				$payment['contract_cost2'] = 0;
				$payment['contract_cost3'] = 0;
				$payment['contract_ea'] = 0;
				$payment['exec_cost1'] = 0;
				$payment['exec_cost2'] = 0;
				$payment['exec_cost3'] = 0;
				$payment['exec_ea'] = 0;
				$payment['subcode'] = array();

				return $payment;
			}

			function GetMonthlyPaymentSubcode() {
				$subcode = array();
				$subcode['osubcode'] = '';
				$subcode['order_ea'] = 0;
				$subcode['order_cost1'] = 0;
				$subcode['order_cost2'] = 0;
				$subcode['order_cost3'] = 0;
				$subcode['workspace_ea'] = 0;
				$subcode['workspace_cost1'] = 0;
				$subcode['workspace_cost2'] = 0;
				$subcode['workspace_cost3'] = 0;
				$subcode['commander_ea'] = 0;
				$subcode['commander_cost1'] = 0;
				$subcode['commander_cost2'] = 0;
				$subcode['commander_cost3'] = 0;

				return $subcode;
			}

			$tno = Request('tno');
			$payment = array();
			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('contract','exec'),"where `idx`=$wno");

			// 도급내역
			$contract = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`={$workspace['contract']} and `gno`=$gno and `tno`=$tno");
			for ($i=0, $loop=sizeof($contract);$i<$loop;$i++) {
				if (isset($payment[$contract[$i]['code']]) == false) $payment[$contract[$i]['code']] = GetMonthlyPayment($contract[$i]);
				$payment[$contract[$i]['code']]['contract_cost1'] = $contract[$i]['cost1'];
				$payment[$contract[$i]['code']]['contract_cost2'] = $contract[$i]['cost2'];
				$payment[$contract[$i]['code']]['contract_cost3'] = $contract[$i]['cost3'];
				$payment[$contract[$i]['code']]['contract_ea'] = $contract[$i]['ea'];
			}

			// 실행내역
			$exec = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`={$workspace['exec']} and `gno`=$gno and `tno`=$tno");
			for ($i=0, $loop=sizeof($exec);$i<$loop;$i++) {
				if (isset($payment[$exec[$i]['code']]) == false) $payment[$exec[$i]['code']] = GetMonthlyPayment($exec[$i]);
				$payment[$exec[$i]['code']]['exec_cost1'] = $exec[$i]['cost1'];
				$payment[$exec[$i]['code']]['exec_cost2'] = $exec[$i]['cost2'];
				$payment[$exec[$i]['code']]['exec_cost3'] = $exec[$i]['cost3'];
				$payment[$exec[$i]['code']]['exec_ea'] = $exec[$i]['ea'];
			}

			// 발주내역
			$order = $mDB->DBfetchs($mErp->table['order_income'],'*',"where `wno`=$wno and `gno`=$gno and `tno`=$tno and `date` like '$date%'");
			for ($i=0, $loop=sizeof($order);$i<$loop;$i++) {
				if (isset($payment[$order[$i]['code']]) == false) $payment[$order[$i]['code']] = GetMonthlyPayment($order[$i]);
				if (isset($payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']]) == false) $payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']] = GetMonthlyPaymentSubcode();

				// 원 발주내역
				$origin = $mDB->DBfetch($mErp->table['order_contract_item'],'*',"where `wno`=$wno and `code`='{$order[$i]['code']}' and `subcode`='{$order[$i]['osubcode']}'");
				$payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']]['order_ea']+= $order[$i]['ea'];
				$payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']]['osubcode'] = $order[$i]['osubcode'];
				$payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']]['order_cost1']+= $origin['cost1'];
				$payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']]['order_cost2']+= $origin['cost2'];
				$payment[$order[$i]['code']]['subcode'][$order[$i]['osubcode']]['order_cost3']+= $origin['cost3'];
			}

			// 현장기성내역
			$mpworkspace = $mDB->DBfetchs($mErp->table['monthly_payment_list'],'*',"where `wno`=$wno and `gno`=$gno and `tno`=$tno and `date`='$date'");
			for ($i=0, $loop=sizeof($mpworkspace);$i<$loop;$i++) {
				if (isset($payment[$mpworkspace[$i]['code']]) == false) $payment[$mpworkspace[$i]['code']] = GetMonthlyPayment($mpworkspace[$i]);
				if (isset($payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]) == false) $payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']] = GetMonthlyPaymentSubcode();

				if ($mpworkspace[$i]['osubcode']) { // 발주내역이면,
					$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_ea'] = $mpworkspace[$i]['ea'];
					if ($mpworkspace[$i]['cost1'] > 0) {
						$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_cost1'] = $mpworkspace[$i]['cost1'];
					}
					if ($mpworkspace[$i]['cost2'] > 0) {
						$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_cost2'] = $mpworkspace[$i]['cost2'];
					}
					if ($mpworkspace[$i]['cost3'] > 0) {
						$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_cost3'] = $mpworkspace[$i]['cost3'];
					}
				} else { // 현장 직접 발주내역이면
					$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_ea']+= $mpworkspace[$i]['ea'];
					$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_cost1'] = $mpworkspace[$i]['cost1'];
					$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_cost2'] = $mpworkspace[$i]['cost2'];
					$payment[$mpworkspace[$i]['code']]['subcode'][$mpworkspace[$i]['subcode']]['workspace_cost3'] = $mpworkspace[$i]['cost3'];
				}
			}

			// 본사기성내역
			$mpcommander = $mDB->DBfetchs($mErp->table['monthly_payment'],'*',"where `wno`=$wno and `gno`=$gno and `tno`=$tno and `date`='$date'");
			for ($i=0, $loop=sizeof($mpcommander);$i<$loop;$i++) {
				if (isset($payment[$mpcommander[$i]['code']]) == false) $payment[$mpcommander[$i]['code']] = GetMonthlyPayment($mpcommander[$i]);
				if (isset($payment[$mpcommander[$i]['code']]['subcode'][$mpcommander[$i]['subcode']]) == false) $payment[$mpcommander[$i]['code']]['subcode'][$mpcommander[$i]['subcode']] = GetMonthlyPaymentSubcode();

				$payment[$mpcommander[$i]['code']]['subcode'][$mpcommander[$i]['subcode']]['commander_ea'] = $mpcommander[$i]['ea'];
				$payment[$mpcommander[$i]['code']]['subcode'][$mpcommander[$i]['subcode']]['commander_cost1'] = $mpcommander[$i]['cost1'];
				$payment[$mpcommander[$i]['code']]['subcode'][$mpcommander[$i]['subcode']]['commander_cost2'] = $mpcommander[$i]['cost2'];
				$payment[$mpcommander[$i]['code']]['subcode'][$mpcommander[$i]['subcode']]['commander_cost3'] = $mpcommander[$i]['cost3'];
			}

			// 리스트만들기
			foreach ($payment as $code=>$data) {
				if (sizeof($data['subcode']) == 0) {
					$insert = $data;
					$insert['subcode'] = $mErp->GetItemPriceCode($data[$i]['cost1'],$data[$i]['cost2'],$data[$i]['cost3']);
					$insert['order_ea'] = 0;
					$insert['order_cost1'] = 0;
					$insert['order_cost2'] = 0;
					$insert['order_cost3'] = 0;
					$insert['workspace_ea'] = 0;
					$insert['workspace_cost1'] = 0;
					$insert['workspace_cost2'] = 0;
					$insert['workspace_cost3'] = 0;
					$insert['commander_ea'] = 0;
					$insert['commander_cost1'] = 0;
					$insert['commander_cost2'] = 0;
					$insert['commander_cost3'] = 0;
					array_push($list,$insert);
				} else {
					$insert = $data;
					unset($insert['subcode']);
					foreach ($data['subcode'] as $subcode=>$subdata) {
						$insert['subcode'] = $subcode;
						$insert['order_ea'] = $subdata['order_ea'];
						$insert['order_cost1'] = $subdata['order_cost1'];
						$insert['order_cost2'] = $subdata['order_cost2'];
						$insert['order_cost3'] = $subdata['order_cost3'];
						$insert['workspace_ea'] = $subdata['workspace_ea'];
						$insert['workspace_cost1'] = $subdata['workspace_cost1'];
						$insert['workspace_cost2'] = $subdata['workspace_cost2'];
						$insert['workspace_cost3'] = $subdata['workspace_cost3'];
						$insert['commander_ea'] = $subdata['commander_ea'];
						$insert['commander_cost1'] = $subdata['commander_cost1'];
						$insert['commander_cost2'] = $subdata['commander_cost2'];
						$insert['commander_cost3'] = $subdata['commander_cost3'];

						array_push($list,$insert);
					}
				}
			}

			$list = GetArrayToExtData($list);
		}
	}
}
*/

/************************************************************************************************
 * 실시간투입현황
 ***********************************************************************************************/
if ($action == 'status') {
	if ($get == 'list') {
		$category = Request('category');
		if ($category == 'working') $find = "where `type`='WORKING'";
		else $find = "where `type`='END'";

		$data = $mDB->DBfetchs($mErp->table['workspace'],array('idx','title','contract','exec'),$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$contract = $mDB->DBfetch($mErp->table['cost'],array('price'),"where `idx`='{$data[$i]['contract']}'");
			$exec = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `wno`='{$data[$i]['idx']}'");

			$lastyear = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`={$data[$i]['idx']} and `date`<'".date('Y')."-01'");

			$list[$i] = '{';
			$list[$i].= '"idx":"'.$data[$i]['idx'].'",';
			$list[$i].= '"title":"'.GetString($data[$i]['title'],'ext').'",';
			$list[$i].= '"contract":"'.$contract['price'].'",';
			$list[$i].= '"exec":"'.$exec[0].'",';
			$list[$i].= '"lastyear":"'.$lastyear[0].'",';
			for ($j=1;$j<=12;$j++) {
				$monthly = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`={$data[$i]['idx']} and `date`='".date('Y')."-".sprintf('%02d',$j)."'");
				$list[$i].= '"p'.$j.'":"'.$monthly[0].'",';
			}
			$list[$i].= '"etc":""';
			$list[$i].= '}';
		}
	}

	if ($get == 'sheet') {
		$idx = Request('idx');
		$gno = Request('gno');
		$date = Request('date') ? Request('date') : date('Y-m');
		$temp = explode('-',$date);
		$prevDate = $temp[1] == '01' ? ($temp[0]-1).'-12' : $temp[0].'-'.sprintf('%02d',(int)($temp[1]-1));
		$workspace = $mDB->DBfetch($mErp->table['workspace'],array('contract'),"where `idx`=$idx");


		if ($gno) {
			$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','sort'),"where `wno`=$idx and `gno`=$gno",'sort,asc');
			for ($i=0, $loop=sizeof($worktype);$i<$loop;$i++) {
				// 공종별 그룹
				$contract = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(price)'),"where `repto`={$workspace['contract']} and `tno`={$worktype[$i]['idx']}");
				$outsourcing = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `wno`=$idx and `tno`={$worktype[$i]['idx']}");
				$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `date`='$prevDate' and `tno`={$worktype[$i]['idx']}");
				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `date`='$date' and `tno`={$worktype[$i]['idx']}");
				$total_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `tno`={$worktype[$i]['idx']}");

				$list[$i] = '{';
				$list[$i].= '"group":" ",';
				$list[$i].= '"worktype":"'.sprintf('%02d',$worktype[$i]['sort']).' '.GetString($mErp->GetWorktype($worktype[$i]['idx']),'ext').'",';
				$list[$i].= '"tno":"'.$workgroup[$i]['idx'].'",';
				$list[$i].= '"contract":"'.$contract[0].'",';
				$list[$i].= '"outsourcing":"'.$outsourcing[0].'",';
				$list[$i].= '"prev_monthly":"'.$prev_monthly[0].'",';
				$list[$i].= '"monthly":"'.$monthly[0].'",';
				$list[$i].= '"total_monthly":"'.$total_monthly[0].'",';
				$list[$i].= '"sort":"'.$workgroup[$i]['sort'].'"';
				$list[$i].= '}';
			}
		} else {
			$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx','workgroup','bgno','sort'),"where `wno`=$idx",'sort,asc');
			for ($i=0, $loop=sizeof($workgroup);$i<$loop;$i++) {
				// 공종별 그룹
				$basegroup = $mDB->DBfetch($mErp->table['base_workgroup'],array('workgroup','sort'),"where `idx`={$workgroup[$i]['bgno']}");
				$contract = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(price)'),"where `repto`={$workspace['contract']} and `gno`={$workgroup[$i]['idx']}");
				$outsourcing = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `wno`=$idx and `gno`={$workgroup[$i]['idx']}");
				$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `date`='$prevDate' and `gno`={$workgroup[$i]['idx']}");
				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `date`='$date' and `gno`={$workgroup[$i]['idx']}");
				$total_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `gno`={$workgroup[$i]['idx']}");

				$list[$i] = '{';
				$list[$i].= '"basegroup":"'.sprintf('%02d',$basegroup['sort']).' '.GetString($basegroup['workgroup'],'ext').'",';
				$list[$i].= '"gno":"'.$workgroup[$i]['idx'].'",';
				$list[$i].= '"workgroup":"'.sprintf('%02d',$basegroup['sort']).sprintf('%02d',$workgroup[$i]['sort']).' '.GetString($workgroup[$i]['workgroup'],'ext').'",';
				$list[$i].= '"contract":"'.$contract[0].'",';
				$list[$i].= '"outsourcing":"'.$outsourcing[0].'",';
				$list[$i].= '"prev_monthly":"'.$prev_monthly[0].'",';
				$list[$i].= '"monthly":"'.$monthly[0].'",';
				$list[$i].= '"total_monthly":"'.$total_monthly[0].'",';
				$list[$i].= '"sort":"'.$workgroup[$i]['sort'].'"';
				$list[$i].= '}';
			}

			$etc = array(
				array('산재보험료',6,-11),
				array('고용보험료',7,-12),
				array('국민건강보험료',8,-13),
				array('노인장기요양보험료',9,-14),
				array('산업안전보건관리비',10,-15),
				array('일반관리비',11,-1),
				array('이윤',12,-10),
				array('절삭',13,0)
			);
			$contract = $mDB->DBfetch($mErp->table['cost'],array('sheet'),"where `idx`='{$workspace['contract']}'");
			$sheet = unserialize($contract['sheet']);

			$row = $i;
			for ($i=0, $loop=sizeof($etc);$i<$loop;$i++) {
				$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `date`='$prevDate' and `tno`={$etc[$i][2]}");
				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `date`='$date' and `tno`={$etc[$i][2]}");
				$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$idx and `tno`={$etc[$i][2]}");

				$list[$row] = '{';
				$list[$row].= '"basegroup":"일반경비",';
				$list[$row].= '"gno":"-1",';
				$list[$row].= '"workgroup":"'.$etc[$i][0].'",';
				$list[$row].= '"contract":"'.$sheet[$etc[$i][1]]['price'].'",';
				$list[$row].= '"outsourcing":"0",';
				$list[$row].= '"prev_monthly":"'.$prev_monthly[0].'",';
				$list[$row].= '"monthly":"'.$prev_monthly[0].'",';
				$list[$row].= '"total_monthly":"'.$prev_monthly[0].'",';
				$list[$row].= '"sort":"'.$row.'"';
				$list[$row].= '}';

				$row++;
			}
		}
	}

	if ($get == 'tab') {
		$idx = Request('idx');
		$gno = Request('gno');

		$data = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx'),"where `gno`=$gno",'sort,asc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{"tno":"'.$data[$i]['idx'].'","title":"'.GetString($mErp->GetWorktype($data[$i]['idx']),'ext').'"}';
		}
	}

	if ($get == 'tabdata') {
		$idx = Request('idx');
		$gno = Request('gno');
		$tno = Request('tno');

		$items = array();

		$workspace = $mDB->DBfetch($mErp->table['workspace'],array('contract','exec'),"where `idx`=$idx");

		$data = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`={$workspace['contract']} and `tno`=$tno");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if (isset($items[$data[$i]['code']]) == false) {
				$items[$data[$i]['code']] = array();
				$items[$data[$i]['code']]['title'] = $data[$i]['title'];
				$items[$data[$i]['code']]['size'] = $data[$i]['size'];
				$items[$data[$i]['code']]['unit'] = $data[$i]['unit'];
				$items[$data[$i]['code']]['contract_ea'] = 0;
				$items[$data[$i]['code']]['outsourcing_ea'] = 0;
				$items[$data[$i]['code']]['total_monthly_ea'] = 0;
				$items[$data[$i]['code']]['contract_cost'] = 0;
				$items[$data[$i]['code']]['outsourcing_cost'] = 0;
				$items[$data[$i]['code']]['contract_price'] = 0;
				$items[$data[$i]['code']]['outsourcing_price'] = 0;
				$items[$data[$i]['code']]['total_monthly_price'] = 0;
			}
			$items[$data[$i]['code']]['contract_ea'] = $data[$i]['ea'];
			$items[$data[$i]['code']]['contract_cost'] = $data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3'];
			$items[$data[$i]['code']]['contract_price'] = ($data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3'])*$data[$i]['ea'];
		}

		$data = $mDB->DBfetchs($mErp->table['outsourcing_item'],'*',"where `wno`=$idx and `tno`=$tno");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if (isset($items[$data[$i]['code']]) == false) {
				$items[$data[$i]['code']] = array();
				$items[$data[$i]['code']]['title'] = $data[$i]['title'];
				$items[$data[$i]['code']]['size'] = $data[$i]['size'];
				$items[$data[$i]['code']]['unit'] = $data[$i]['unit'];
				$items[$data[$i]['code']]['contract_ea'] = 0;
				$items[$data[$i]['code']]['outsourcing_ea'] = 0;
				$items[$data[$i]['code']]['total_monthly_ea'] = 0;
				$items[$data[$i]['code']]['contract_cost'] = 0;
				$items[$data[$i]['code']]['outsourcing_cost'] = 0;
				$items[$data[$i]['code']]['contract_price'] = 0;
				$items[$data[$i]['code']]['outsourcing_price'] = 0;
				$items[$data[$i]['code']]['total_monthly_price'] = 0;
			}
			$items[$data[$i]['code']]['outsourcing_ea'] = $data[$i]['ea'];
			$items[$data[$i]['code']]['outsourcing_cost'] = $data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3'];
			$items[$data[$i]['code']]['outsourcing_price'] = ($data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3'])*$data[$i]['ea'];
		}

		$data = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `tno`=$tno");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if (isset($items[$data[$i]['code']]) == false) {
				$items[$data[$i]['code']] = array();
				$items[$data[$i]['code']]['title'] = $data[$i]['title'];
				$items[$data[$i]['code']]['size'] = $data[$i]['size'];
				$items[$data[$i]['code']]['unit'] = $data[$i]['unit'];
				$items[$data[$i]['code']]['contract_ea'] = 0;
				$items[$data[$i]['code']]['outsourcing_ea'] = 0;
				$items[$data[$i]['code']]['total_monthly_ea'] = 0;
				$items[$data[$i]['code']]['contract_cost'] = 0;
				$items[$data[$i]['code']]['outsourcing_cost'] = 0;
				$items[$data[$i]['code']]['contract_price'] = 0;
				$items[$data[$i]['code']]['outsourcing_price'] = 0;
				$items[$data[$i]['code']]['total_monthly_price'] = 0;
			}
			$items[$data[$i]['code']]['total_monthly_ea']+= $data[$i]['ea'];
			$items[$data[$i]['code']]['total_monthly_price']+= $data[$i]['price'];
		}

		foreach ($items as $code=>$data) {
			$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(ea)','SUM(price)'),"where `wno`=$idx and `code`='$code' and `date`='$prevDate'");
			$prev_monthly_ea = isset($prev_monthly[0]) == true ? $prev_monthly[0] : '0';
			$prev_monthly_price = isset($prev_monthly[1]) == true ? $prev_monthly[1] : '0';

			$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(ea)','SUM(price)'),"where `wno`=$idx and `code`='$code' and `date`='$date'");
			$monthly_ea = isset($monthly[0]) == true ? $monthly[0] : '0';
			$monthly_price = isset($monthly[1]) == true ? $monthly[1] : '0';

			$list[] = '{"title":"'.GetString($data['title'],'ext').'","size":"'.GetString($data['size'],'ext').'","unit":"'.GetString($data['unit'],'ext').'","contract_ea":"'.$data['contract_ea'].'","contract_cost":"'.$data['contract_cost'].'","contract_price":"'.$data['contract_price'].'","outsourcing_ea":"'.$data['outsourcing_ea'].'","outsourcing_cost":"'.$data['outsourcing_cost'].'","outsourcing_price":"'.$data['outsourcing_price'].'","prev_monthly_ea":"'.$prev_monthly_ea.'","prev_monthly_price":"'.$prev_monthly_price.'","monthly_ea":"'.$monthly_ea.'","monthly_price":"'.$monthly_price.'","total_monthly_ea":"'.$data['total_monthly_ea'].'","total_monthly_price":"'.$data['total_monthly_price'].'"}';
		}
	}
}


$total = isset($total) == true ? $total : sizeof($list);
$lists = isset($lists) == true ? $lists : implode(",\n",$list);
echo $callbackStart;
echo '{"totalCount":"'.$total.'","lists":['.$lists.']}';
echo $callbackEnd;
?>