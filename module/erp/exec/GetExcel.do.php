<?php
ob_start();
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$action = Request('action');
$get = Request('get');

function GetSumField($col,$page,$row,$start,$end,$last=0) {
	$sum = '=SUM(';
	for ($i=0;$i<$page;$i++) {
		if ($i != 0) $sum.= ',';
		if ($i+1 == $page) {
			$sum.= $col.($i*$row+$start).':'.$col.(($i+1)*$row-$end-$last);
		} else {
			$sum.= $col.($i*$row+$start).':'.$col.(($i+1)*$row-$end);
		}
	}
	$sum.= ')';

	return $sum;
}

if ($action == 'download') {
	$filepath = urldecode(Request('filepath'));
	if (preg_match('/Mac OS X/',$_SERVER['HTTP_USER_AGENT']) == false) {
		$filename = iconv('UTF-8','CP949//IGNORE',urldecode(Request('filename')));
	} else {
		$filename = urldecode(Request('filename'));
	}

	if (file_exists($filepath) == true) {
		header("Cache-control: private");

		if(ereg('IE',$_ENV['browser']) == true || ereg('OP',$_ENV['browser']) == true) {
			Header("Content-type:application/octet-stream");
			Header("Content-Length:".filesize($filepath));
			Header("Content-Disposition:attachment;filename=".$filename);
			Header("Content-Transfer-Encoding:binary");
			header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/module/erp/exec/GetExcel.do.php?action=complete");
			Header("Pragma:no-cache");
			Header("Expires:0");
			Header("Connection:close");
		} else {
			Header("Content-type:".GetFileMime(urldecode(Request('filename'))));
			Header("Content-Length:".filesize($filepath));
			Header("Content-Disposition:attachment; filename=".str_replace(' ','_',$filename));
			Header("Content-Description:PHP3 Generated Data");
			header("Refresh:0; http://".$_SERVER['HTTP_HOST'].$_ENV['dir']."/module/erp/exec/GetExcel.do.php?action=complete");
			Header("Pragma: no-cache");
			Header("Expires: 0");
			Header("Connection:close");
		}

		$fp = fopen($filepath,'rb');
		while(!feof($fp)) {
			echo fread($fp,1024*1024);
			sleep(1);
			flush();
		}
		fclose($fp);

		@unlink($filepath);
	}
} else {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml">';
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />';
	ob_flush();flush();

	if ($action == 'complete') {
		echo '<script type="text/javascript">try { parent.ExcelConvertEnd(); } catch(e) {}</script>';
	}

	$mErp = new ModuleErp();

	if ($action == 'worker') {
		if ($get == 'monthly') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$date = substr($date,0,7);
			$workernum = Request('workernum');

			$workerspace = $mDB->DBfetch($mErp->table['workerspace'],array('wno','pno'),"where `workernum`='$workernum'");

			if (isset($workerspace['pno']) == false) {
				exit;
			}

			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$workerspace['wno']}");
			$worker = $mDB->DBfetch($mErp->table['worker'],array('name','jumin','enter_date','pay_type','telephone','cellphone'),"where `idx`={$workerspace['pno']}");

			$mPHPExcelReader = new PHPExcelReader('../excel/WorkerMonthAttend.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();

			$mPHPExcel->setActiveSheetIndex(0);

			ExcelProgress(10,'엑셀파일 로딩완료');

			$mPHPExcel->getActiveSheet()->setCellValue('C3',$workspace['title']);
			$mPHPExcel->getActiveSheet()->setCellValue('J3',substr($date,0,4).'년 '.substr($date,5,2).'월');
			$mPHPExcel->getActiveSheet()->setCellValue('C4',$worker['name']);
			$mPHPExcel->getActiveSheet()->setCellValue('G4',$worker['jumin']);
			$mPHPExcel->getActiveSheet()->setCellValue('J4',$mErp->paytype[$worker['pay_type']]);
			$mPHPExcel->getActiveSheet()->setCellValue('C5',$worker['cellphone'] ? $worker['cellphone'] : $worker['telephone']);
			$mPHPExcel->getActiveSheet()->setCellValue('G5',$workernum);
			$mPHPExcel->getActiveSheet()->setCellValue('J5',date('Y년 m월 d일',strtotime($worker['enter_date'])));

			ExcelProgress(20,'기본정보 작성완료');

			$attend = array();
			$attends = $mDB->DBfetchs($mErp->table['attend_member'],'*',"where `workernum`='$workernum' and `date` like '$date%'");

			for ($i=0, $loop=sizeof($attends);$i<$loop;$i++) {
				$attend[date('j',strtotime($attends[$i]['date']))] = $attends[$i];
			}

			ExcelProgress(30,'근태기록 작성중');

			$dayArray = array('일','월','화','수','목','금','토');

			for ($i=1;$i<=31;$i++) {
				$row = $i+7;

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$i.'('.$dayArray[date('w',strtotime($date.'-'.sprintf('%02d',$i)))].')');

				if (isset($attend[$i]) == true) {
					$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,($attend[$i]['intime'] != 0 ? GetTime('H:i',$attend[$i]['intime']) : '-'));
					$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,($attend[$i]['write_intime'] != 0 ? GetTime('H:i',$attend[$i]['write_intime']) : '-'));
					$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,($attend[$i]['outtime'] != 0 ? GetTime('H:i',$attend[$i]['outtime']) : '-'));
					$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,($attend[$i]['write_outtime'] != 0 ? GetTime('H:i',$attend[$i]['write_outtime']) : '-'));
					$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$attend[$i]['working']/10);
					$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,($attend[$i]['is_delay'] == 'TRUE' ? '지각' : '정상').'/'.($attend[$i]['is_early'] == 'TRUE' ? '조퇴' : ($attend['is_overwork'] == 'TRUE' ? '야근' : '정상')));
					$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,($attend[$i]['wno'] != $attend[$i]['owno'] ? '타현장지원' : '-'));
					$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,$attend[$i]['work']);
				}

				if ($i == 15) ExcelProgress(60,'근태기록 작성중');
			}

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode($worker['name'].'근태기록('.$date.').xlsx'));
		}

		if ($get == 'all') {
			$wno = Request('wno');
			$date = Request('date');

			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`=$wno");

			$mPHPExcelReader = new PHPExcelReader('../excel/WorkerMonthAttend.xlsx');
			$mReadPHPExcel = $mPHPExcelReader->GetExcel();

			$data = $mDB->DBfetchs($mErp->table['attend_member'],array('workernum','pno'),"where `wno`=$wno and `date` like '$date%' group by workernum");

			if (sizeof($data) == 0) ExcelError('해당현장의 근태기록이 없습니다.');

			ExcelProgress(10,'엑셀파일 로딩완료');

			$sheet = $mReadPHPExcel->getSheet(0);

			$mPHPExcelReader = new PHPExcelReader('../excel/empty.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();
			$mPHPExcel->removeSheetByIndex(0);

			$percent = ceil(80/sizeof($data));
			$dayArray = array('일','월','화','수','목','금','토');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$worker = $mDB->DBfetch($mErp->table['worker'],array('name','jumin','enter_date','pay_type','telephone','cellphone'),"where `idx`={$data[$i]['pno']}");

				$mPHPExcel->addExternalSheet($sheet->copy(),$i);
				$mPHPExcel->getSheet($i)->setTitle($worker['name']);

				$mPHPExcel->setActiveSheetIndex($i);

				$mPHPExcel->getActiveSheet()->setCellValue('C3',$workspace['title']);
				$mPHPExcel->getActiveSheet()->setCellValue('J3',substr($date,0,4).'년 '.substr($date,5,2).'월');
				$mPHPExcel->getActiveSheet()->setCellValue('C4',$worker['name']);
				$mPHPExcel->getActiveSheet()->setCellValue('G4',$worker['jumin']);
				$mPHPExcel->getActiveSheet()->setCellValue('J4',$mErp->paytype[$worker['pay_type']]);
				$mPHPExcel->getActiveSheet()->setCellValue('C5',$worker['cellphone'] ? $worker['cellphone'] : $worker['telephone']);
				$mPHPExcel->getActiveSheet()->setCellValue('G5',$data[$i]['workernum']);
				$mPHPExcel->getActiveSheet()->setCellValue('J5',date('Y년 m월 d일',strtotime($worker['enter_date'])));

				$attend = array();
				$attends = $mDB->DBfetchs($mErp->table['attend_member'],'*',"where `workernum`='{$data[$i]['workernum']}' and `date` like '$date%'");

				for ($j=0, $loopj=sizeof($attends);$j<$loopj;$j++) {
					$attend[date('j',strtotime($attends[$j]['date']))] = $attends[$j];
				}

				for ($j=1;$j<=31;$j++) {
					$row = $j+7;

					$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$j.'('.$dayArray[date('w',strtotime($date.'-'.sprintf('%02d',$j)))].')');

					if (isset($attend[$j]) == true) {
						$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,($attend[$j]['intime'] != 0 ? GetTime('H:i',$attend[$j]['intime']) : '-'));
						$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,($attend[$j]['write_intime'] != 0 ? GetTime('H:i',$attend[$j]['write_intime']) : '-'));
						$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,($attend[$j]['outtime'] != 0 ? GetTime('H:i',$attend[$j]['outtime']) : '-'));
						$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,($attend[$j]['write_outtime'] != 0 ? GetTime('H:i',$attend[$j]['write_outtime']) : '-'));
						$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$attend[$j]['working']/10);
						$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,($attend[$j]['is_delay'] == 'TRUE' ? '지각' : '정상').'/'.($attend[$j]['is_early'] == 'TRUE' ? '조퇴' : ($attend['is_overwork'] == 'TRUE' ? '야근' : '정상')));
						$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,($attend[$j]['wno'] != $attend[$j]['owno'] ? '타현장지원' : '-'));
						$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,$attend[$j]['work']);
					}
				}

				ExcelProgress(10+$percent*($i+1),$worker['name'].' 근태기록 변환완료');
			}

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode(str_replace(' ','_',$workspace['title']).'('.$date.').xlsx'));
		}
	}

	if ($action == 'workspace') {
		if ($get == 'order') {
			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['outsourcing_order'],'*',"where `idx`=$idx");
			$list = unserialize($data['data']);

			$mPHPExcelReader = new PHPExcelReader('../excel/WorkspaceOrder.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			$mPHPExcel->setActiveSheetIndex(0);

			$master = array();
			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title','master'),"where `idx`={$data['wno']}");
			if ($workspace['master']) {
				$workspace_master = $mDB->DBfetchs($_ENV['table']['member'],array('name'),"where `idx` IN ({$workspace['master']})");
				for ($i=0, $loop=sizeof($workspace_master);$i<$loop;$i++) {
					$master[$i] = $workspace_master[$i]['name'];
				}
			}

			$mPHPExcel->setActiveSheetIndex(0);
			$mPHPExcel->getActiveSheet()->setCellValue('B3',$workspace['title']);
			$mPHPExcel->getActiveSheet()->setCellValue('H3',$data['title']);
			$mPHPExcel->getActiveSheet()->setCellValue('B4',implode(', ',$master));
			$mPHPExcel->getActiveSheet()->setCellValue('H4',date('Y년 m월 d일',strtotime($data['date'])));

			ExcelProgress(20,'기본정보 작성완료');

			if (sizeof($list) > 33) {
				$mPHPExcel->getActiveSheet()->copySheet(40,ceil(sizeof($list)/33));
			}

			ExcelProgress(25,'페이지 작성완료');

			$oPage = 1;
			$totalPage = ceil(sizeof($list)/33);
			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				$page = ceil(($i+1)/33);
				$row = $i-($page-1)*33+($page-1)*40+6;

				if ($oPage != $page) {
					ExcelProgress(25+ceil($page/$totalPage*50),($page-1).'/'.$totalPage.'페이지 작성완료');
					$mPHPExcel->getActiveSheet()->setCellValue('A'.($page-1)*40,$data['etc']);
					$mPHPExcel->getActiveSheet()->getStyle('A'.($page-1)*40)->getAlignment()->setWrapText(true);
				}

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$mErp->GetWorkgroup($list[$i]['gno']));
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$mErp->GetWorktype($list[$i]['tno']));
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$list[$i]['title']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$list[$i]['size']);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$list[$i]['unit']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,$list[$i]['ea']);

				$oPage = $page;
			}

			ExcelProgress(90,$totalPage.'/'.$totalPage.'페이지 작성완료');
			$mPHPExcel->getActiveSheet()->setCellValue('A'.($totalPage)*40,$data['etc']);
			$mPHPExcel->getActiveSheet()->getStyle('A'.($totalPage)*40)->getAlignment()->setWrapText(true);

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode(str_replace(' ','_',$data['title']).'('.$data['date'].').xlsx'));
		}

		if ($get == 'daily') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$wno = Request('wno');

			$data = $mDB->DBfetch($mErp->table['workreport'],array('weather','data'),"where `wno`=$wno and `date`='$date'");
			$list = isset($data['data']) == true ? unserialize($data['data']) : array();

			$mPHPExcelReader = new PHPExcelReader('../excel/WorkReport.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			$mPHPExcel->setActiveSheetIndex(0);

			$weather = array('SUNNY'=>'맑음','RAINY'=>'비','CLOUDY'=>'흐림','SNOWLY'=>'눈');

			$mPHPExcel->setActiveSheetIndex(0);
			$mPHPExcel->getActiveSheet()->setCellValue('B3',$mErp->GetWorkspaceTitle($wno));
			$mPHPExcel->getActiveSheet()->setCellValue('E3',$weather[$data['weather']]);
			$mPHPExcel->getActiveSheet()->setCellValue('G3',date('Y년 m월 d일',strtotime($date)));
			$mPHPExcel->getActiveSheet()->setCellValue('J3',$mErp->GetWorkspaceMasterName($wno));

			ExcelProgress(20,'기본정보 작성완료');

			if (sizeof($list) > 25) {
				$mPHPExcel->getActiveSheet()->copySheet(31,ceil(sizeof($list)/25));
			}

			ExcelProgress(25,'페이지 작성완료');

			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				$page = ceil(($i+1)/25);
				$row = $i-($page-1)*25+($page-1)*31+5;

				if ($list[$i]['type'] == 'member' || $list[$i]['type'] == 'dayworker') $list[$i]['type'] = '노무';
				elseif ($list[$i]['type'] == 'item' || $list[$i]['type'] == 'itemorder') $list[$i]['type'] = '자재';
				elseif ($list[$i]['type'] == 'outsourcing') $list[$i]['type'] = '외주';
				elseif ($list[$i]['type'] == 'expense') $list[$i]['type'] = '경비';
				elseif ($list[$i]['type'] == 'equipment') $list[$i]['type'] = '장비';

				$list[$i]['payment'] = $list[$i]['payment'] == 'TRUE' ? '지불' : '미불';

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$mErp->GetWorkgroup($list[$i]['gno']));
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$mErp->GetWorktype($list[$i]['tno']));
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$list[$i]['title']);
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$list[$i]['size']);
				$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,$list[$i]['content']);
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$list[$i]['type']);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$list[$i]['cooperation']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$list[$i]['ea']);
				$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,$list[$i]['unit']);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$list[$i]['cost']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,$list[$i]['price']);
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,$list[$i]['payment']);

				if ($i%10 == 0) {
					ExcelProgress(25+ceil(($i/$loop)*75),'데이터 기록중');
				}
			}

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode(str_replace(' ','_',$mErp->GetWorkspaceTitle($wno)).'일일상황일지('.$date.').xlsx'));
		}
	}

	if ($action == 'commander') {
		if ($get == 'order') {
			$idx = Request('idx');
			$tno = explode(',',Request('tno'));

			$data = $mDB->DBfetch($mErp->table['outsourcing_consult'],'*',"where `idx`=$idx");
			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`={$data['wno']}");

			$contract = unserialize($data['contract']);
			$order = unserialize($data['consult']);

			$worktype = array();
			for ($i=0, $loop=sizeof($contract);$i<$loop;$i++) {
				if (isset($worktype[$contract[$i]['tno']]) == false) $worktype[$contract[$i]['tno']] = $mErp->GetWorktype($data['wno'],$contract[$i]['tno']);
			}
			$worktype = implode(', ',$worktype);

			if ($data['type'] == 'EACH') {
				if (sizeof($tno) <= 2) {
					$mPHPExcelReader = new PHPExcelReader('../excel/CommanderOrderEachA4.xlsx');
					$mPHPExcel = $mPHPExcelReader->GetExcel();

					$mPHPExcel->setActiveSheetIndex(0);

					ExcelProgress(10,'엑셀파일 로딩완료');

					$mPHPExcel->getActiveSheet()->setCellValue('C3',$workspace['title']);
					$mPHPExcel->getActiveSheet()->setCellValue('I3',$worktype);
					$mPHPExcel->getActiveSheet()->setCellValue('T3',date('Y년 m월 d일',strtotime($data['date'])));

					ExcelProgress(20,'기본정보 작성완료');

					$total = 0;
					for ($i=0, $loop=sizeof($contract);$i<$loop;$i++) {
						$row = $i+6;
						$total+= $contract[$i]['cost']*$contract[$i]['ea'];
						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$contract[$i]['title']);
						$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$contract[$i]['size']);
						$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,$contract[$i]['unit']);
						$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$contract[$i]['size']);
						$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$contract[$i]['ea']);
						$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$contract[$i]['cost1']+$contract[$i]['cost2']+$contract[$i]['cost3']);
						$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,'=F'.$row.'*G'.$row);
					}
					$mPHPExcel->getActiveSheet()->setCellValue('G40',$total);

					ExcelProgress(30,'도급내역 작성완료');

					$titlePostion = array('I4','Q4');
					$startPosition = array(array('I','L','M','N','O','P'),array('Q','S','T','U','V','W'));

					$total = 0;
					for ($i=0, $loop=sizeof($tno);$i<$loop;$i++) {
						$list = $order[$tno[$i]];
						if ($data['type'] == 'EACH') {
							$temp = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$list['cno']}");
							$title = $temp['title'];
						} else {
							$title = ($tno[$i]+1).'차 발주';
						}
						$titleCol = 'I'+($i*8);
						$mPHPExcel->getActiveSheet()->setCellValue($titlePostion[$i],$title);

						for ($j=0, $loopj=sizeof($list['items']);$j<$loopj;$j++) {
							$row = $j+6;
							$total+= ($list['items'][$j]['cost1']+$list['items'][$j]['cost2']+$list['items'][$j]['cost3'])*$list['items'][$j]['ea'];
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][0].$row,$list['items'][$j]['title']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][1].$row,$list['items'][$j]['size']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][2].$row,$list['items'][$j]['unit']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][3].$row,$list['items'][$j]['ea']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][4].$row,$list['items'][$j]['cost1']+$list['items'][$j]['cost2']+$list['items'][$j]['cost3']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][5].$row,'='.$startPosition[$i][3].$row.'*'.$startPosition[$i][4].$row);
						}
						$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][4].'40',$total);

						ExcelProgress(30+round((60/$loop)*$i),$title.' 작성완료');
					}

					$mPHPExcel->getActiveSheet()->setCellValue('A42',$data['etc']);
				}
			} else {
				if (sizeof($tno) <= 4) {
					$mPHPExcelReader = new PHPExcelReader('../excel/CommanderOrderA4.xlsx');
					$mPHPExcel = $mPHPExcelReader->GetExcel();

					$mPHPExcel->setActiveSheetIndex(0);

					ExcelProgress(10,'엑셀파일 로딩완료');

					$mPHPExcel->getActiveSheet()->setCellValue('C3',$workspace['title']);
					$mPHPExcel->getActiveSheet()->setCellValue('I3',$worktype);
					$mPHPExcel->getActiveSheet()->setCellValue('T3',date('Y년 m월 d일',strtotime($data['date'])));

					ExcelProgress(20,'기본정보 작성완료');

					for ($i=0, $loop=sizeof($contract);$i<$loop;$i++) {
						$row = $i+6;
						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$contract[$i]['title']);
						$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$contract[$i]['size']);
						$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,$contract[$i]['unit']);
						$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$contract[$i]['size']);
						$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$contract[$i]['ea']);
						$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$contract[$i]['cost']);
						$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,'=F'.$row.'*G'.$row);
					}
					$mPHPExcel->getActiveSheet()->setCellValue('G40','=SUM(H6:H39)');

					ExcelProgress(30,'도급내역 작성완료');

					$titlePostion = array('I4','L4','O4','R4','Q4');
					$startPosition = array(array('I','J','K'),array('L','M','N'),array('O','P','Q'),array('R','S','T'));

					for ($i=0, $loop=sizeof($tno);$i<$loop;$i++) {
						$list = $order[$tno[$i]];
						if ($data['type'] == 'EQUAL') {
							$temp = $mDB->DBfetch($mErp->table['cooperation'],array('title'),"where `idx`={$list['cno']}");
							$title = $temp['title'];
						} else {
							$title = ($tno[$i]+1).'차 발주';
						}
						$titleCol = 'I'+($i*8);
						$mPHPExcel->getActiveSheet()->setCellValue($titlePostion[$i],$title);

						for ($j=0, $loopj=sizeof($list['items']);$j<$loopj;$j++) {
							$row = $j+6;
							$total+= ($list['items'][$j]['cost1']+$list['items'][$j]['cost2']+$list['items'][$j]['cost3'])*$list['items'][$j]['ea'];
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][0].$row,$list['items'][$j]['ea']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][1].$row,$list['items'][$j]['cost1']+$list['items'][$j]['cost2']+$list['items'][$j]['cost3']);
							$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][2].$row,'='.$startPosition[$i][0].$row.'*'.$startPosition[$i][1].$row);

						}

						$mPHPExcel->getActiveSheet()->setCellValue($startPosition[$i][1].'40','=SUM('.(++$startPosition[$i][1]).'6:'.(++$startPosition[$i][1]).'39)');

						ExcelProgress(30+round((60/$loop)*$i),$title.' 작성완료');
					}

					$mPHPExcel->getActiveSheet()->setCellValue('A42',$data['etc']);
				}
			}

			/*
			$data = $mDB->DBfetch($mErp->table['order'],'*',"where `idx`=$idx");

			$mPHPExcelReader = new PHPExcelReader('../excel/CommanderOrder.xlsx');
			$mReadPHPExcel = $mPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			$company = unserialize($data['company']);

			$sheet = $mReadPHPExcel->getSheet(0);

			$mPHPExcelReader = new PHPExcelReader('../excel/empty.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();
			$mPHPExcel->removeSheetByIndex(0);

			for ($i=0, $loop=sizeof($company);$i<$loop;$i++) {
				$mPHPExcel->addExternalSheet($sheet->copy(),$i);
				$mPHPExcel->getSheet($i)->setTitle($company[$i]);
			}

			ExcelProgress(10,'업체별 시트작성 완료');

			$list = unserialize($data['item']);
			$items = unserialize($data['order']);

			for ($i=0, $loop=sizeof($company);$i<$loop;$i++) {
				$item = array();
				for ($j=0, $loopj=sizeof($items);$j<$loopj;$j++) {
					if ($items[$j]['company'] == $company[$i]) $item[] = $items[$j];
				}

				$rows = sizeof($list) > sizeof($item) ? sizeof($list) : sizeof($item);

				if ($rows > 18) {
					$mPHPExcel->getActiveSheet()->copySheet(25,ceil($rows/18));
				}

				for ($j=0;$j<$rows;$j++) {
					$page = ceil(($j+1)/18);
					$row = $j-($page-1)*18+($page-1)*40+6;

					if (isset($list[$j]) == true) {
						$mPHPExcel->getSheet($i)->setCellValue('A'.$row,$list[$j]['title']);
						$mPHPExcel->getSheet($i)->setCellValue('C'.$row,$list[$j]['size']);
						$mPHPExcel->getSheet($i)->setCellValue('D'.$row,$list[$j]['unit']);
						$mPHPExcel->getSheet($i)->setCellValue('E'.$row,$list[$j]['ea']);
						$mPHPExcel->getSheet($i)->setCellValue('F'.$row,$list[$j]['cost']);
						$mPHPExcel->getSheet($i)->setCellValue('G'.$row,$list[$j]['price']);
					}

					if (isset($item[$j]) == true) {
						$mPHPExcel->getSheet($i)->setCellValue('H'.$row,$item[$j]['title']);
						$mPHPExcel->getSheet($i)->setCellValue('J'.$row,$item[$j]['size']);
						$mPHPExcel->getSheet($i)->setCellValue('K'.$row,$item[$j]['unit']);
						$mPHPExcel->getSheet($i)->setCellValue('L'.$row,$item[$j]['ea']);
						$mPHPExcel->getSheet($i)->setCellValue('M'.$row,$item[$j]['cost']);
						$mPHPExcel->getSheet($i)->setCellValue('N'.$row,$item[$j]['price']);
						$mPHPExcel->getSheet($i)->setCellValue('O'.$row,$item[$j]['etc']);
					}
				}
			}
			*/

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode('본사품위서.xlsx'));
		}

		if ($get == 'ordercompare') {
			$idx = Request('idx');

			$data = $mDB->DBfetch($mErp->table['order'],'*',"where `idx`=$idx");

			$mPHPExcelReader = new PHPExcelReader('../excel/CommanderOrderCompare.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			$company = unserialize($data['company']);

			ExcelProgress(10,'업체별 시트작성 완료');

			$list = unserialize($data['item']);
			$items = unserialize($data['order']);

			$cols = array();
			$cols[0] = array('G','H','I','J','K','L','M','N','O');
			$cols[1] = array('P','Q','R','S','T','U','V','W','X');
			$cols[2] = array('Y','Z','AA','AB','AC','AD','AE','AF','AG');
			$cols[3] = array('AH','AI','AJ','AK','AL','AM','AN','AO','AP');
			$cols[4] = array('AQ','AR','AS','AT','AU','AV','AW','AX','AY');

			$item = array();
			for ($i=0, $loop=sizeof($company);$i<$loop;$i++) {
				$item[$i] = array();

				for ($j=0, $loopj=sizeof($items);$j<$loopj;$j++) {
					if ($items[$j]['company'] == $company[$i]) $item[$i][] = $items[$j];
					$mPHPExcel->getSheet(0)->setCellValue($cols[$i][2].'1',$company[$i]);
				}
			}

			$rows = 0;
			while (true) {
				$is_insert = false;

				for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
					if (isset($list[$rows]) == true || isset($item[$i][$rows]) == true) {
						$mPHPExcel->getSheet(0)->insertNewRowBefore($rows+4,1);
						$is_insert = true;
						break;
					}
				}

				if ($is_insert == false) break;

				$row = $rows+3;
				if (isset($list[$rows]) == true) {
					$mPHPExcel->getSheet(0)->setCellValue('A'.$row,$list[$rows]['title']);
					$mPHPExcel->getSheet(0)->setCellValue('B'.$row,$list[$rows]['size']);
					$mPHPExcel->getSheet(0)->setCellValue('C'.$row,$list[$rows]['unit']);
					$mPHPExcel->getSheet(0)->setCellValue('D'.$row,$list[$rows]['ea']);
					$mPHPExcel->getSheet(0)->setCellValue('E'.$row,$list[$rows]['cost']);
					$mPHPExcel->getSheet(0)->setCellValue('F'.$row,$list[$rows]['price']);
				}

				for ($i=0, $loop=sizeof($item);$i<$loop;$i++) {
					if (isset($item[$i][$rows]) == true) {
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][0].$row,$item[$i][$rows]['title']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][1].$row,$item[$i][$rows]['size']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][2].$row,$item[$i][$rows]['unit']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][3].$row,$item[$i][$rows]['ea']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][4].$row,$item[$i][$rows]['cost1']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][5].$row,$item[$i][$rows]['cost2']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][6].$row,$item[$i][$rows]['cost3']);
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][7].$row,'='.($cols[$i][4].$row).'+'.($cols[$i][5].$row).'+'.($cols[$i][6].$row));
						$mPHPExcel->getSheet(0)->setCellValue($cols[$i][8].$row,$item[$i][$rows]['price']);
					}
				}
				$rows++;
			}
			$mPHPExcel->getSheet(0)->removeRow($row+1,2);

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode('도급대비표.xlsx'));
		}

		if ($get == 'daily') {
			$date = Request('date') ? Request('date') : date('Y-m-d');
			$find = "where `date`='$date'";
			$find.= Request('wno') ? " and `wno` IN (".Request('wno').")" : "";

			$data = $mDB->DBfetchs($mErp->table['workreport'],array('wno','weather','data'),$find);

			$mPHPExcelReader = new PHPExcelReader('../excel/WorkReport.xlsx');
			$mReadPHPExcel = $mPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			$sheet = $mReadPHPExcel->getSheet(0);

			$mPHPExcelReader = new PHPExcelReader('../excel/empty.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();
			$mPHPExcel->removeSheetByIndex(0);

			$weather = array('SUNNY'=>'맑음','RAINY'=>'비','CLOUDY'=>'흐림','SNOWLY'=>'눈');

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$list = isset($data[$i]['data']) == true ? unserialize($data[$i]['data']) : array();

				$mPHPExcel->addExternalSheet($sheet->copy(),$i);
				$mPHPExcel->getSheet($i)->setTitle($mErp->GetWorkspaceTitle($data[$i]['wno']));

				$mPHPExcel->setActiveSheetIndex($i);

				$mPHPExcel->getActiveSheet()->setCellValue('B3',$mErp->GetWorkspaceTitle($data[$i]['wno']));
				$mPHPExcel->getActiveSheet()->setCellValue('E3',$weather[$data[$i]['weather']]);
				$mPHPExcel->getActiveSheet()->setCellValue('G3',date('Y년 m월 d일',strtotime($date)));
				$mPHPExcel->getActiveSheet()->setCellValue('J3',$mErp->GetWorkspaceMasterName($data[$i]['wno']));

				if (sizeof($list) > 25) {
					$mPHPExcel->getActiveSheet()->copySheet(31,ceil(sizeof($list)/25));
				}

				for ($j=0, $loopj=sizeof($list);$j<$loopj;$j++) {
					$page = ceil(($j+1)/25);
					$row = $j-($page-1)*25+($page-1)*31+5;

					if ($list[$j]['type'] == 'member' || $list[$j]['type'] == 'dayworker') $list[$j]['type'] = '노무';
					elseif ($list[$j]['type'] == 'item' || $list[$j]['type'] == 'itemorder') $list[$j]['type'] = '자재';
					elseif ($list[$j]['type'] == 'outsourcing') $list[$j]['type'] = '외주';
					elseif ($list[$j]['type'] == 'expense') $list[$j]['type'] = '경비';
					elseif ($list[$j]['type'] == 'equipment') $list[$j]['type'] = '장비';

					$list[$j]['payment'] = $list[$j]['payment'] == 'TRUE' ? '지불' : '미불';

					$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$mErp->GetWorkgroup($list[$j]['gno']));
					$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$mErp->GetWorktype($list[$j]['tno']));
					$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$list[$j]['title']);
					$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$list[$j]['size']);
					$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,$list[$j]['content']);
					$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$list[$j]['type']);
					$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$list[$j]['cooperation']);
					$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$list[$j]['ea']);
					$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,$list[$j]['unit']);
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$list[$j]['cost']);
					$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,$list[$j]['price']);
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,$list[$j]['payment']);
				}

				ExcelProgress(20+ceil(($i/sizeof($data))*75),$mErp->GetWorkspaceTitle($data[$i]['wno']).' 작성완료');
			}

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode('현장일일상황일지('.$date.').xlsx'));
		}

		if ($get == 'monthly') {
			$wno = Request('wno');
			$date = Request('date');

			$mPHPExcelReader = new PHPExcelReader('../excel/Monthly.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			$types = array('ITEM'=>'자재비','EXPENSE'=>'경비','EQUIPMENT'=>'장비비','WORKER'=>'노무비','OUTSOURCING'=>'외주비');

			// 신청서
			$mPHPExcel->setActiveSheetIndex(0);

			$data = $mDB->DBfetch($mErp->table['monthly'],array('SUM(contract)','SUM(monthly)'),"where `wno`=$wno and `date`='$date'");
			$total = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno");

			$prevDate = date('Y-m',strtotime($date.'-01')-10);
			$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `date`='$prevDate'");

			$mPHPExcel->getActiveSheet()->setCellValue('C4',$mErp->GetWorkspaceTitle($wno));
			$mPHPExcel->getActiveSheet()->setCellValue('C7',floor($data[0]*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('C8',$data[0]);
			$mPHPExcel->getActiveSheet()->setCellValue('C9',floor($data[0]*0.1));
			$mPHPExcel->getActiveSheet()->setCellValue('C10',floor(($total[0])*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('F10','(공급가 : '.number_format($total[0]).', 부가세 : '.number_format(floor(($total[0])*0.1)).')');
			$mPHPExcel->getActiveSheet()->setCellValue('C11',floor($prev[0]*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('F11','(공급가 : '.number_format($prev[0]).', 부가세 : '.number_format(floor($prev[0]*0.1)).')');
			$mPHPExcel->getActiveSheet()->setCellValue('C12',floor($data[1]*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('F12','(공급가 : '.number_format($data[1]).', 부가세 : '.number_format(floor($data[1]*0.1)).')');


			$mPHPExcel->getActiveSheet()->setCellValue('C22',floor(($total[0])*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('F22','(공급가 : '.number_format($total[0]).', 부가세 : '.number_format(floor(($total[0])*0.1)).')');
			$mPHPExcel->getActiveSheet()->setCellValue('C23',floor($prev[0]*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('F23','(공급가 : '.number_format($prev[0]).', 부가세 : '.number_format(floor($prev[0]*0.1)).')');
			$mPHPExcel->getActiveSheet()->setCellValue('C24',floor($data[1]*1.1));
			$mPHPExcel->getActiveSheet()->setCellValue('F24','(공급가 : '.number_format($data[1]).', 부가세 : '.number_format(floor($data[1]*0.1)).')');

			// 집계표
			$mPHPExcel->setActiveSheetIndex(1);

			$sheet = array();
			$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date'");

			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if (isset($sheet[$data[$i]['type']]) == false) {
					$sheet[$data[$i]['type']] = array();
					$sheet[$data[$i]['type']]['original'] = 0;
					$sheet[$data[$i]['type']]['contract'] = 0;
					$sheet[$data[$i]['type']]['monthly'] = 0;

					$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='{$data[$i]['type']}' and `date`<'$date'");
					$sheet[$data[$i]['type']]['prev'] = $prev[0] ? $prev[0] : '0';
				}

				$sheet[$data[$i]['type']]['original']+= $data[$i]['original'];
				$sheet[$data[$i]['type']]['contract']+= $data[$i]['contract'];
				$sheet[$data[$i]['type']]['monthly']+= $data[$i]['monthly'];
			}

			$row = 5;
			foreach ($sheet as $type=>$data) {
				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$row-4);
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$types[$type]);
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$data['original']);
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$data['contract']);
				$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,floor($data['contract']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,'=D'.$row.'+E'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$data['prev']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,floor($data['prev']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,'=G'.$row.'+H'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$data['monthly']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,floor($data['monthly']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=J'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('M'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('O'.$row,'=G'.$row.'+J'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=H'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=O'.$row.'+P'.$row);

				$row++;
			}

			ExcelProgress(30,'기성집계표 작성완료');

			// 외주비청구서
			$mPHPExcel->setActiveSheetIndex(2);

			$row = 5;
			$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date' and `type`='OUTSOURCING'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='OUTSOURCING' and `date`<'$date' and `cno`={$data[$i]['cno']} and `repto`={$data[$i]['repto']} and `cooperation`='{$data[$i]['cooperation']}'");
				$prev = $prev[0] ? $prev[0] : '0';

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$row-4);
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$data[$i]['cooperation']);
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$data[$i]['original']);
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$data[$i]['contract']);
				$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,floor($data[$i]['contract']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,'=D'.$row.'+E'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$data[$i]['prev']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,floor($data[$i]['prev']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,'=G'.$row.'+H'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$data[$i]['monthly']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,floor($data[$i]['monthly']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=J'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('M'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('O'.$row,'=G'.$row.'+J'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=H'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=O'.$row.'+P'.$row);

				$row++;
			}

			ExcelProgress(50,'외주비청구서 작성완료');


			// 자재비청구서
			$mPHPExcel->setActiveSheetIndex(3);

			$row = 5;
			$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date' and `type`='ITEM'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='ITEM' and `date`<'$date' and `cno`={$data[$i]['cno']} and `repto`={$data[$i]['repto']} and `cooperation`='{$data[$i]['cooperation']}'");
				$prev = $prev[0] ? $prev[0] : '0';

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$row-4);
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$data[$i]['cooperation']);
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$data[$i]['original']);
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$data[$i]['contract']);
				$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,floor($data[$i]['contract']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,'=D'.$row.'+E'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$data[$i]['prev']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,floor($data[$i]['prev']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,'=G'.$row.'+H'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$data[$i]['monthly']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,floor($data[$i]['monthly']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=J'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('M'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('O'.$row,'=G'.$row.'+J'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=H'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=O'.$row.'+P'.$row);

				$row++;
			}

			ExcelProgress(70,'자재비청구서 작성완료');


			// 장비비청구서
			$mPHPExcel->setActiveSheetIndex(4);

			$row = 5;
			$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date' and `type`='EXPENSE'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='EXPENSE' and `date`<'$date' and `cno`={$data[$i]['cno']} and `repto`={$data[$i]['repto']} and `cooperation`='{$data[$i]['cooperation']}'");
				$prev = $prev[0] ? $prev[0] : '0';

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$row-4);
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$data[$i]['cooperation']);
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$data[$i]['original']);
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$data[$i]['contract']);
				$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,floor($data[$i]['contract']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,'=D'.$row.'+E'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$data[$i]['prev']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,floor($data[$i]['prev']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,'=G'.$row.'+H'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$data[$i]['monthly']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,floor($data[$i]['monthly']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=J'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('M'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('O'.$row,'=G'.$row.'+J'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=H'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=O'.$row.'+P'.$row);

				$row++;
			}

			ExcelProgress(90,'장비비청구서 작성완료');


			// 장비비청구서
			$mPHPExcel->setActiveSheetIndex(4);

			$row = 5;
			$data = $mDB->DBfetchs($mErp->table['monthly'],'*',"where `wno`=$wno and `date`='$date' and `type`='EXPENSE'");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$prev = $mDB->DBfetch($mErp->table['monthly'],array('SUM(monthly)'),"where `wno`=$wno and `type`='EXPENSE' and `date`<'$date' and `cno`={$data[$i]['cno']} and `repto`={$data[$i]['repto']} and `cooperation`='{$data[$i]['cooperation']}'");
				$prev = $prev[0] ? $prev[0] : '0';

				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$row-4);
				$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$data[$i]['cooperation']);
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$data[$i]['original']);
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$data[$i]['contract']);
				$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,floor($data[$i]['contract']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,'=D'.$row.'+E'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$data[$i]['prev']);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,floor($data[$i]['prev']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,'=G'.$row.'+H'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$data[$i]['monthly']);
				$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,floor($data[$i]['monthly']*0.1));
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=J'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('M'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'0');
				$mPHPExcel->getActiveSheet()->setCellValue('O'.$row,'=G'.$row.'+J'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=H'.$row.'+K'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=O'.$row.'+P'.$row);

				$row++;
			}

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode('기성집계표.xlsx'));
		}

		if ($get == 'contract') {
			$idx = Request('idx');

			$data = $mDB->DBfetch($mErp->table['cost'],array('wno','sheet','price'),"where `idx`=$idx");
			$wno = $data['wno'];

			$mPHPExcelReader = new PHPExcelReader('../excel/Contract.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();
			$mPHPExcel->removeSheetByIndex(3);
			$mPHPExcel->removeSheetByIndex(2);

			$mReadPHPExcelReader = new PHPExcelReader('../excel/Contract.xlsx');
			$mReadPHPExcel = $mReadPHPExcelReader->GetExcel();

			ExcelProgress(10,'엑셀파일 로딩완료');

			// 공사원가내역서
			$data = unserialize($data['sheet']);

			$mPHPExcel->setActiveSheetIndex(0);
			$mPHPExcel->getActiveSheet()->setCellValue('D3',$data[0]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('F3',$data[0]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D4',$data[1]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('F4',$data[1]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D5',$data[2]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('F5',$data[2]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D6','=D3+D4+D5');

			$mPHPExcel->getActiveSheet()->setCellValue('D7',$data[3]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('F7',$data[3]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D8',$data[4]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E8','직접노무비 * '.$data[4]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F8',$data[4]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D9','=D7+D8');

			$mPHPExcel->getActiveSheet()->setCellValue('D10',$data[5]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('F10',$data[5]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D11',$data[6]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E11','노무비 * '.$data[6]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F11',$data[6]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D12',$data[7]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E12','노무비 * '.$data[7]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F12',$data[7]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D13',$data[8]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E13','직접노무비 * '.$data[8]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F13',$data[8]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D14',$data[9]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E14','직접노무비 * '.$data[9]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F14',$data[9]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D15',$data[10]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E15','국민건강보험료 * '.$data[10]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F15',$data[10]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D16',$data[11]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E16','(재료비+직접노무비) * '.$data[11]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F16',$data[11]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D17','=D10+D11+D12+D13+D14+D15+D16');

			$mPHPExcel->getActiveSheet()->setCellValue('D18','=D6+D9+D17');

			$mPHPExcel->getActiveSheet()->setCellValue('D19',$data[12]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E19','순공사비 * '.$data[12]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F19',$data[12]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D20',$data[13]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E20','순공사비 * '.$data[13]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F20',$data[13]['etc']);
			$mPHPExcel->getActiveSheet()->setCellValue('D21',$data[14]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('F21',$data[14]['etc']);

			$mPHPExcel->getActiveSheet()->setCellValue('D22','=D18+D19+D20-D21');

			$mPHPExcel->getActiveSheet()->setCellValue('D23',$data[15]['price']);
			$mPHPExcel->getActiveSheet()->setCellValue('E23','공급가액 * '.$data[13]['percent'].'%');
			$mPHPExcel->getActiveSheet()->setCellValue('F23',$data[15]['etc']);

			$mPHPExcel->getActiveSheet()->setCellValue('D24','=D22+D23');

			ExcelProgress(15,'공사원가내역서 작성완료');

			// 총괄집계표
			$mPHPExcel->setActiveSheetIndex(1);

			$data = $mDB->DBfetchs($mErp->table['base_workgroup'],'*','','sort,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$row = 3+$i;
				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$data[$i]['workgroup']);

				$workgroups = array();
				$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx'),"where `bgno`={$data[$i]['idx']}");
				for ($j=0, $loopj=sizeof($workgroup);$j<$loopj;$j++) $workgroups[] = $workgroup[$j]['idx'];
				$workgroup = implode(',',$workgroups);

				$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(`cost1`*`ea`)','SUM(`cost2`*`ea`)','SUM(`cost3`*`ea`)'),"where `repto`=$idx and `gno` IN ($workgroup)");
				$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$cost[0]);
				$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$cost[1]);
				$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$cost[2]);
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=SUM(B'.$row.':D'.$row.')');
			}

			$mPHPExcel->getActiveSheet()->setCellValue('F25','=SUM(F3:F24)');
			$mPHPExcel->getActiveSheet()->setCellValue('H25','=SUM(H3:H24)');
			$mPHPExcel->getActiveSheet()->setCellValue('J25','=SUM(J3:J24)');
			$mPHPExcel->getActiveSheet()->setCellValue('L25','=SUM(L3:L24)');

			$mPHPExcel->getSheet(1)->setTitle('총괄집계표');

			ExcelProgress(20,'총괄집계표 작성완료');

			// 각 공종그룹별 총괄집계표
			$activeSheet = 1;
			$sheet = $mReadPHPExcel->getSheet(3);
			$detailSheet = $mReadPHPExcel->getSheet(3);
			$listSheet = $mReadPHPExcel->getSheet(3);
			$basegroup = $mDB->DBfetchs($mErp->table['base_workgroup'],'*','','sort,asc');

			$percent = 0;
			$totalWorktype = $mDB->DBcount($mErp->table['workspace_worktype'],"where `wno`=$wno");

			for ($i=0, $loop=sizeof($basegroup);$i<$loop;$i++) {
				// 시트추가

				$activeSheet++;
				$mPHPExcel->addExternalSheet($sheet->copy(),$activeSheet);
				$mPHPExcel->setActiveSheetIndex($activeSheet);
				$mPHPExcel->getActiveSheet()->setTitle($basegroup[$i]['workgroup'].'총괄집계표');
				$mPHPExcel->getActiveSheet()->setCellValue('A1','총 괄 집 계 표 ('.$basegroup[$i]['workgroup'].')');

				$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx','workgroup','sort'),"where `wno`=$wno and `bgno`={$basegroup[$i]['idx']}",'sort,asc');

				for ($j=0, $loopj=sizeof($workgroup);$j<$loopj;$j++) {
					$row = 3+$j;
					$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$workgroup[$j]['sort']).'.'.$mErp->GetWorkgroup($workgroup[$j]['idx']));

					$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(`cost1`*`ea`)','SUM(`cost2`*`ea`)','SUM(`cost3`*`ea`)'),"where `repto`=$idx and `gno`={$workgroup[$j]['idx']}");
					$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$cost[0]);
					$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$cost[1]);
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$cost[2]);
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=SUM(B'.$row.':D'.$row.')');
				}

				$mPHPExcel->getActiveSheet()->setCellValue('F25','=SUM(B3:B24)');
				$mPHPExcel->getActiveSheet()->setCellValue('H25','=SUM(C3:C24)');
				$mPHPExcel->getActiveSheet()->setCellValue('J25','=SUM(D3:D24)');
				$mPHPExcel->getActiveSheet()->setCellValue('L25','=SUM(E3:E24)');

				// 집계표 추가
				for ($j=0, $loopj=sizeof($workgroup);$j<$loopj;$j++) {
					// 시트추가
					$activeSheet++;
					$mPHPExcel->addExternalSheet($detailSheet->copy(),$activeSheet);
					$mPHPExcel->setActiveSheetIndex($activeSheet);
					$mPHPExcel->getActiveSheet()->setTitle(sprintf('%02d',$workgroup[$j]['sort']).'.'.$workgroup[$j]['workgroup'].'집계표');
					$mPHPExcel->getActiveSheet()->setCellValue('A1','공 종 별 집 계 표 ('.$workgroup[$j]['workgroup'].')');

					$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','sort'),"where `wno`=$wno and `gno`={$workgroup[$j]['idx']}");

					if (sizeof($worktype) > 23) {
						$mPHPExcel->getActiveSheet()->copySheet(25,ceil((sizeof($worktype)+1)/23));
					}

					for ($k=0, $loopk=sizeof($worktype);$k<$loopk;$k++) {
						$page = ceil(($k+1)/23);
						$row = $k-($page-1)*23+($page-1)*25+3;

						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$workgroup[$j]['sort']).sprintf('%02d',$worktype[$k]['sort']).'.'.$mErp->GetWorktype($worktype[$k]['idx']));

						$cost = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(`cost1`*`ea`)','SUM(`cost2`*`ea`)','SUM(`cost3`*`ea`)'),"where `repto`=$idx and `tno`={$worktype[$k]['idx']}");
						$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$cost[0]);
						$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$cost[1]);
						$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,$cost[2]);
						$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=SUM(B'.$row.':D'.$row.')');
					}

					$page = ceil((sizeof($worktype)+1)/23);
					$endrow = $page*25;

					$mPHPExcel->getActiveSheet()->setCellValue('A'.$endrow,'합계');
					$mPHPExcel->getActiveSheet()->setCellValue('F'.$endrow,GetSumField('F',$page,25,3,0,1));
					$mPHPExcel->getActiveSheet()->setCellValue('H'.$endrow,GetSumField('H',$page,25,3,0,1));
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$endrow,GetSumField('J',$page,25,3,0,1));
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$endrow,GetSumField('L',$page,25,3,0,1));

					// 내역서 추가
					$mPHPExcel->addExternalSheet($listSheet->copy(),$$activeSheet);
					$activeSheet++;
					$mPHPExcel->setActiveSheetIndex($activeSheet);
					$mPHPExcel->getActiveSheet()->setTitle(sprintf('%02d',$workgroup[$j]['sort']).'.'.$workgroup[$j]['workgroup'].'내역서');

					$itemcount = $mDB->DBcount($mErp->table['cost_item'],"where `repto`=$idx and `gno`={$workgroup[$j]['idx']}");
					if (sizeof($worktype)+$itemcount > 31) {
						$mPHPExcel->getActiveSheet()->copySheet(33,ceil((sizeof($worktype)*2+$itemcount+1)/31));
					}

					$rowcount = 0;
					$row = 1;
					for ($k=0, $loopk=sizeof($worktype);$k<$loopk;$k++) {
						$percent++;
						ExcelProgress(20+ceil($percent/$totalWorktype*70),$workgroup[$j]['workgroup'].' 집계표 작성중');

						$mPHPExcel->getActiveSheet()->setCellValue('A'.($row+2),sprintf('%02d',$worktype[$k]['sort']).'.'.$mErp->GetWorktype($worktype[$k]['idx']));
						$rowcount++;

						$item = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`=$idx and `tno`={$worktype[$k]['idx']}",'idx,asc');
						for ($l=0, $loopl=sizeof($item);$l<$loopl;$l++) {
							ob_flush();flush();
							$page = ceil(($rowcount+1)/31);
							$row = $rowcount-($page-1)*31+($page-1)*33+3;

							$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$item[$l]['title']);
							$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$item[$l]['size']);
							$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$item[$l]['unit']);
							$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$item[$l]['ea']);
							$mPHPExcel->getActiveSheet()->setCellValue('E'.$row,$item[$l]['cost1']);
							$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,'=D'.$row.'*E'.$row);
							$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$item[$l]['cost2']);
							$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,'=D'.$row.'*G'.$row);
							$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,$item[$l]['cost3']);
							$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,'=D'.$row.'*I'.$row);
							$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,'=E'.$row.'+G'.$row.'+I'.$row);
							$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=D'.$row.'*K'.$row);
							$rowcount++;
						}

						$mPHPExcel->getActiveSheet()->setCellValue('A'.($row+1),'');
						$rowcount++;
					}
				}

			}


			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode('계약서.xlsx'));
		}

		if ($get == 'status') {
			$wno = Request('wno');
			$date = Request('date') ? Request('date') : date('Y-m');
			$temp = explode('-',$date);
			$prevDate = $temp[1] == '01' ? ($temp[0]-1).'-12' : $temp[0].'-'.sprintf('%02d',(int)($temp[1])-1);

			$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title','contract'),"where `idx`=$wno");

			$mPHPExcelReader = new PHPExcelReader('../excel/CommanderStatus.xlsx');
			$mPHPExcel = $mPHPExcelReader->GetExcel();

			$mReadPHPExcelReader = new PHPExcelReader('../excel/CommanderStatus.xlsx');
			$mReadPHPExcel = $mReadPHPExcelReader->GetExcel();
			$listSheet = $mReadPHPExcel->getSheet(0);
			$listSheet->setCellValue('B3',$workspace['title']);
			$listSheet->setCellValue('K5',date('Y년 m월',strtotime($prevDate.'-01')));
			$listSheet->setCellValue('M5',date('Y년 m월',strtotime($date.'-01')));

			ExcelProgress(10,'엑셀파일 로딩완료');

			$mPHPExcel->setActiveSheetIndex(0);
			$mPHPExcel->getActiveSheet()->setTitle('총괄');
			$mPHPExcel->getActiveSheet()->setCellValue('A7','공사집계표');
			$mPHPExcel->getActiveSheet()->setCellValue('B3',$workspace['title']);
			$mPHPExcel->getActiveSheet()->setCellValue('K5',date('Y년 m월',strtotime($prevDate.'-01')));
			$mPHPExcel->getActiveSheet()->setCellValue('M5',date('Y년 m월',strtotime($date.'-01')));
			$row = 8;

			$basegroup = $mDB->DBfetchs($mErp->table['base_workgroup'],array('idx','sort'),'','sort,asc');
			for ($i=0, $loop=sizeof($basegroup);$i<$loop;$i++) {
				$workgroup = implode(',',$mErp->GetWorkgroupsInBaseWorkgroup($wno,$basegroup[$i]['idx']));
				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$basegroup[$i]['sort']).'.'.$mErp->GetBaseWorkgroup($basegroup[$i]['idx']));
				$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,'식');
				$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,'1');
				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$basegroup[$i]['sort']).'.'.$mErp->GetBaseWorkgroup($basegroup[$i]['idx']));

				if ($workgroup) {
					$contract = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(price)'),"where `repto`='{$workspace['contract']}' and `gno` IN ($workgroup)");
					$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,isset($contract[0]) == true ? $contract[0] : '0');

					$outsourcing = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `wno`=$wno and `gno` IN ($workgroup)");
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,isset($outsourcing[0]) == true ? $outsourcing[0] : '0');

					$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$prevDate' and `gno` IN ($workgroup)");
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,isset($prev_monthly[0]) == true ? $prev_monthly[0] : '0');

					$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno` IN ($workgroup)");
					$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,isset($monthly[0]) == true ? $monthly[0] : '0');

					$total_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `gno` IN ($workgroup)");
					$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,isset($total_monthly[0]) == true ? $total_monthly[0] : '0');
				} else {
					$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,'0');
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,'0');
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'0');
					$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'0');
					$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'0');
				}

				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);
				$row++;
			}

			$row++;
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

			for ($i=0, $loop=sizeof($etc);$i<$loop;$i++) {
				$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$etc[$i][0]);
				$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,$sheet[$etc[$i][1]]['price']);

				$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$prevDate' and `tno`={$etc[$i][2]}");
				$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,isset($prev_monthly[0]) == true ? $prev_monthly[0] : '0');

				$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno`={$etc[$i][2]}");
				$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,isset($monthly[0]) == true ? $monthly[0] : '0');

				$total_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `gno`={$etc[$i][2]}");
				$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,isset($total_monthly[0]) == true ? $total_monthly[0] : '0');

				$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
				$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);

				$row++;
			}

			$row++;
			$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,'합계');
			$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,'=SUM(G7:G'.($row-1).')');
			$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,'=SUM(J7:J'.($row-1).')');
			$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=SUM(L7:L'.($row-1).')');
			$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'=SUM(N7:N'.($row-1).')');
			$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=SUM(P7:P'.($row-1).')');
			$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
			$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);

			ExcelProgress(15,'총괄집계표 완료');
			$progressTotal = $mDB->DBcount($mErp->table['workspace_workgroup'],"where `wno`=$wno");

			// 집계표
			$activeSheet = 0;
			for ($i=0, $loop=sizeof($basegroup);$i<$loop;$i++) {
				$activeSheet++;
				$mPHPExcel->addExternalSheet($listSheet->copy(),$activeSheet);
				$mPHPExcel->setActiveSheetIndex($activeSheet);
				$mPHPExcel->getActiveSheet()->setTitle($mErp->GetBaseWorkgroup($basegroup[$i]['idx']).'총괄');

				$workgroup = $mDB->DBfetchs($mErp->table['workspace_workgroup'],array('idx','sort'),"where `wno`=$wno and `bgno`={$basegroup[$i]['idx']}",'sort,asc');
				$row = 7;
				for ($j=0, $loopj=sizeof($workgroup);$j<$loopj;$j++) {
					$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$workgroup[$j]['sort']).'.'.$mErp->GetWorkgroup($workgroup[$j]['idx']));
					$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,'식');
					$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,'1');
					$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$workgroup[$j]['sort']).'.'.$mErp->GetWorkgroup($workgroup[$j]['idx']));

					$contract = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(price)'),"where `repto`='{$workspace['contract']}' and `gno`={$workgroup[$j]['idx']}");
					$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,isset($contract[0]) == true ? $contract[0] : '0');

					$outsourcing = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `wno`=$wno and `gno`={$workgroup[$j]['idx']}");
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,isset($outsourcing[0]) == true ? $outsourcing[0] : '0');

					$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$prevDate' and `gno`={$workgroup[$j]['idx']}");
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,isset($prev_monthly[0]) == true ? $prev_monthly[0] : '0');

					$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$date' and `gno`={$workgroup[$j]['idx']}");
					$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,isset($monthly[0]) == true ? $monthly[0] : '0');

					$total_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `gno`={$workgroup[$j]['idx']}");
					$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,isset($total_monthly[0]) == true ? $total_monthly[0] : '0');

					$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
					$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);
					$row++;
				}

				for ($j=0, $loopj=sizeof($workgroup);$j<$loopj;$j++) {
					$activeSheet++;
					$mPHPExcel->addExternalSheet($listSheet->copy(),$activeSheet);
					$mPHPExcel->setActiveSheetIndex($activeSheet);
					$mPHPExcel->getActiveSheet()->setTitle($mErp->GetWorkgroup($workgroup[$j]['idx']).'총괄');

					$worktype = $mDB->DBfetchs($mErp->table['workspace_worktype'],array('idx','sort'),"where `wno`=$wno and `gno`={$workgroup[$j]['idx']}",'sort,asc');
					$row = 7;
					if (sizeof($worktype) > 33) {
						$mPHPExcel->getActiveSheet()->copySheet(39,ceil((sizeof($worktype)+1)/33));
					}
					for ($k=0, $loopk=sizeof($worktype);$k<$loopk;$k++) {
						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$worktype[$k]['sort']).'.'.$mErp->GetWorktype($worktype[$k]['idx']));
						$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,'식');
						$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,'1');
						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$worktype[$k]['sort']).'.'.$mErp->GetWorktype($worktype[$k]['idx']));

						$contract = $mDB->DBfetch($mErp->table['cost_item'],array('SUM(price)'),"where `repto`='{$workspace['contract']}' and `tno`={$worktype[$k]['idx']}");
						$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,isset($contract[0]) == true ? $contract[0] : '0');

						$outsourcing = $mDB->DBfetch($mErp->table['outsourcing_item'],array('SUM(price)'),"where `wno`=$wno and `tno`={$worktype[$k]['idx']}");
						$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,isset($outsourcing[0]) == true ? $outsourcing[0] : '0');

						$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$prevDate' and `tno`={$worktype[$k]['idx']}");
						$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,isset($prev_monthly[0]) == true ? $prev_monthly[0] : '0');

						$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `date`='$prev2' and `tno`={$worktype[$k]['idx']}");
						$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,isset($monthly[0]) == true ? $monthly[0] : '0');

						$total_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)'),"where `wno`=$wno and `tno`={$worktype[$k]['idx']}");
						$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,isset($total_monthly[0]) == true ? $total_monthly[0] : '0');

						$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
						$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);
						$row++;

						if ($row%40 == 0) $row = $row+6;
					}

					$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,'합계');
					$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,'=SUM(G7:G'.($row-1).')');
					$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,'=SUM(J7:J'.($row-1).')');
					$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=SUM(L7:L'.($row-1).')');
					$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'=SUM(N7:N'.($row-1).')');
					$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=SUM(P7:P'.($row-1).')');
					$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
					$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);

					$activeSheet++;
					$mPHPExcel->addExternalSheet($listSheet->copy(),$activeSheet);
					$mPHPExcel->setActiveSheetIndex($activeSheet);
					$mPHPExcel->getActiveSheet()->setTitle($mErp->GetWorkgroup($workgroup[$j]['idx']).'집계');

					$items = array();
					$itemsByTno = array();
					$item = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`={$workspace['contract']} and `gno`={$workgroup[$j]['idx']}");
					for ($k=0, $loopk=sizeof($item);$k<$loopk;$k++) {
						if (isset($items[$item[$k]['code']]) == false) {
							$items[$item[$k]['code']] = array();
							$items[$item[$k]['code']]['title'] = $item[$k]['title'];
							$items[$item[$k]['code']]['size'] = $item[$k]['size'];
							$items[$item[$k]['code']]['unit'] = $item[$k]['unit'];
							$items[$item[$k]['code']]['contract_ea'] = 0;
							$items[$item[$k]['code']]['outsourcing_ea'] = 0;
							$items[$item[$k]['code']]['total_monthly_ea'] = 0;
							$items[$item[$k]['code']]['contract_cost'] = 0;
							$items[$item[$k]['code']]['outsourcing_cost'] = 0;
							$items[$item[$k]['code']]['contract_price'] = 0;
							$items[$item[$k]['code']]['outsourcing_price'] = 0;
							$items[$item[$k]['code']]['total_monthly_price'] = 0;
						}
						$items[$item[$k]['code']]['contract_ea'] = $item[$k]['ea'];
						$items[$item[$k]['code']]['contract_cost'] = $item[$k]['cost1']+$item[$k]['cost2']+$item[$k]['cost3'];
						$items[$item[$k]['code']]['contract_price'] = ($item[$k]['cost1']+$item[$k]['cost2']+$item[$k]['cost3'])*$item[$k]['ea'];

						$itemsByTno[$item[$k]['tno']][$item[$k]['code']] = $items[$item[$k]['code']];
					}

					$item = $mDB->DBfetchs($mErp->table['outsourcing_item'],'*',"where `gno`={$workgroup[$j]['idx']}");
					for ($k=0, $loopk=sizeof($item);$k<$loopk;$k++) {
						if (isset($items[$item[$k]['code']]) == false) {
							$items[$item[$k]['code']] = array();
							$items[$item[$k]['code']]['title'] = $item[$k]['title'];
							$items[$item[$k]['code']]['size'] = $item[$k]['size'];
							$items[$item[$k]['code']]['unit'] = $item[$k]['unit'];
							$items[$item[$k]['code']]['contract_ea'] = 0;
							$items[$item[$k]['code']]['outsourcing_ea'] = 0;
							$items[$item[$k]['code']]['total_monthly_ea'] = 0;
							$items[$item[$k]['code']]['contract_cost'] = 0;
							$items[$item[$k]['code']]['outsourcing_cost'] = 0;
							$items[$item[$k]['code']]['contract_price'] = 0;
							$items[$item[$k]['code']]['outsourcing_price'] = 0;
							$items[$item[$k]['code']]['total_monthly_price'] = 0;
						}
						$items[$item[$k]['code']]['outsourcing_ea'] = $item[$k]['ea'];
						$items[$item[$k]['code']]['outsourcing_cost'] = $item[$k]['cost1']+$item[$k]['cost2']+$item[$k]['cost3'];
						$items[$item[$k]['code']]['outsourcing_price'] = ($item[$k]['cost1']+$item[$k]['cost2']+$item[$k]['cost3'])*$item[$k]['ea'];

						$itemsByTno[$item[$k]['tno']][$item[$k]['code']] = $items[$item[$k]['code']];
					}

					$item = $mDB->DBfetchs($mErp->table['monthly_item'],'*',"where `gno`={$workgroup[$j]['idx']}");
					for ($k=0, $loopk=sizeof($item);$k<$loopk;$k++) {
						if (isset($items[$item[$k]['code']]) == false) {
							$items[$item[$k]['code']] = array();
							$items[$item[$k]['code']]['title'] = $item[$k]['title'];
							$items[$item[$k]['code']]['size'] = $item[$k]['size'];
							$items[$item[$k]['code']]['unit'] = $item[$k]['unit'];
							$items[$item[$k]['code']]['contract_ea'] = 0;
							$items[$item[$k]['code']]['outsourcing_ea'] = 0;
							$items[$item[$k]['code']]['total_monthly_ea'] = 0;
							$items[$item[$k]['code']]['contract_cost'] = 0;
							$items[$item[$k]['code']]['outsourcing_cost'] = 0;
							$items[$item[$k]['code']]['contract_price'] = 0;
							$items[$item[$k]['code']]['outsourcing_price'] = 0;
							$items[$item[$k]['code']]['total_monthly_price'] = 0;
						}
						$items[$item[$k]['code']]['total_monthly_ea']+= $item[$k]['ea'];
						$items[$item[$k]['code']]['total_monthly_cost']+= $item[$k]['cost'];
						$items[$item[$k]['code']]['total_monthly_price']+= $item[$k]['price'];

						$itemsByTno[$item[$k]['tno']][$item[$k]['code']] = $items[$item[$k]['code']];
					}

					if (sizeof($worktype)*2+sizeof($items) > 33) {
						$mPHPExcel->getActiveSheet()->copySheet(39,ceil((sizeof($worktype)*2+sizeof($items)+1)/33));
					}
					$row = 6;

					for ($k=0, $loopk=sizeof($worktype);$k<$loopk;$k++) {
						$row++;
						if ($row%39 == 1) $row = $row+6;

						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,sprintf('%02d',$worktype[$k]['sort']).'.'.$mErp->GetWorktype($worktype[$k]['idx']));

						$startRow = $row;
						if (is_array($itemsByTno[$worktype[$k]['idx']]) == true) {
							$item = $itemsByTno[$worktype[$k]['idx']];

							foreach ($item as $itemcode=>$data) {
								$row++;
								if ($row%39 == 1) $row = $row+6;
								$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,$data['title']);
								$mPHPExcel->getActiveSheet()->setCellValue('B'.$row,$data['size']);
								$mPHPExcel->getActiveSheet()->setCellValue('C'.$row,$data['unit']);
								$mPHPExcel->getActiveSheet()->setCellValue('D'.$row,$data['contract_ea']);
								$mPHPExcel->getActiveSheet()->setCellValue('F'.$row,$data['contract_cost']);
								$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,'=D'.$row.'*F'.$row);

								$mPHPExcel->getActiveSheet()->setCellValue('H'.$row,$data['outsourcing_ea']);
								$mPHPExcel->getActiveSheet()->setCellValue('I'.$row,$data['outsourcing_cost']);
								$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,'=H'.$row.'*I'.$row);

								$prev_monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)','SUM(ea)'),"where `wno`=$wno and `date`='$prevDate' and `code`='$itemcode'");
								$mPHPExcel->getActiveSheet()->setCellValue('K'.$row,isset($prev_monthly[1]) == true ? $prev_monthly[1] : '0');
								$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,isset($prev_monthly[0]) == true ? $prev_monthly[0] : '0');

								$monthly = $mDB->DBfetch($mErp->table['monthly_item'],array('SUM(price)','SUM(ea)'),"where `wno`=$wno and `date`='$date' and `code`='$itemcode'");
								$mPHPExcel->getActiveSheet()->setCellValue('M'.$row,isset($monthly[1]) == true ? $monthly[1] : '0');
								$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,isset($monthly[0]) == true ? $monthly[0] : '0');

								$mPHPExcel->getActiveSheet()->setCellValue('O'.$row,$data['total_monthly_ea']);
								$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,$data['total_monthly_price']);

								$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
								$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);
							}
						}
						$row++;
						if ($row%39 == 1) $row = $row+6;

						$mPHPExcel->getActiveSheet()->setCellValue('A'.$row,'합계');
						$mPHPExcel->getActiveSheet()->setCellValue('G'.$row,'=SUM(G'.$startRow.':G'.($row-1).')');
						$mPHPExcel->getActiveSheet()->setCellValue('J'.$row,'=SUM(J'.$startRow.':J'.($row-1).')');
						$mPHPExcel->getActiveSheet()->setCellValue('L'.$row,'=SUM(L'.$startRow.':L'.($row-1).')');
						$mPHPExcel->getActiveSheet()->setCellValue('N'.$row,'=SUM(N'.$startRow.':N'.($row-1).')');
						$mPHPExcel->getActiveSheet()->setCellValue('P'.$row,'=SUM(P'.$startRow.':P'.($row-1).')');
						$mPHPExcel->getActiveSheet()->setCellValue('Q'.$row,'=P'.$row.'/G'.$row);
						$mPHPExcel->getActiveSheet()->setCellValue('R'.$row,'=G'.$row.'-P'.$row);
					}

					ExcelProgress(15+floor(($j+1)/$progressTotal*80),$mErp->GetWorkgroup($workgroup[$j]['idx']).'집계표 완료');
				}
			}

			ExcelProgress(100,'변환완료 및 다운로드 준비중');

			$mPHPExcelWriter = new PHPExcelWriter($mPHPExcel);
			$filepath = $mPHPExcelWriter->WriteExcel();

			flush(0.5);

			Redirect($_SERVER['PHP_SELF'].'?action=download&filepath='.urlencode($filepath).'&filename='.urlencode('원가대비표.xlsx'));
		}
	}

	REQUIRE_ONCE '../../../inc/footer.inc.php';
}
?>