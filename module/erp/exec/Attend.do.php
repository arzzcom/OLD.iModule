<?php
REQUIRE_ONCE '../../../config/default.conf.php';

//header('Content-type: text/xml; charset="UTF-8"',true);
//header("Cache-Control: no-cache, must-revalidate");
//header("Pragma: no-cache");

$mDB = &DB::instance();
$mErp = new ModuleErp();

$wno = Request('wno');
$action = Request('action');

if ($action == 'infor') {
	$workspace = $mDB->DBfetch($mErp->table['workspace'],array('title'),"where `idx`=$wno");

	echo '<infor>';
	echo '<title>'.GetString($workspace['title'],'xml').'</title>';
	echo '</infor>';
}

if ($action == 'attend') {
	$workernum = Request('workernum');
	$workerspace = $mDB->DBfetch($mErp->table['workerspace'],array('wno','pno'),"where `workernum`='$workernum'");
	$pno = isset($workerspace['pno']) == true ? $workerspace['pno'] : '0';
	$worker = $mDB->DBfetch($mErp->table['worker'],array('name','jumin'),"where `idx`=$pno");
	$workspace = $mDB->DBfetch($mErp->table['workspace'],array('auto_delay_condition','auto_early_condition'),"where `idx`=$wno");

	$type = GetTime('H') <= 12 ? 'in' : 'out';
	$data = $GLOBALS['HTTP_RAW_POST_DATA'];

	if (isset($worker['name']) == true) {
		$path = $_ENV['path'].'/userfile/erp/attend/'.GetTime('Y-m-d');
		if (CreateDirectory($path) == true) {
			$result = true;
			$temp = $_ENV['path'].'/temp/attend.'.$workernum.'.'.time().'.jpg';
			$file = @fopen($temp,'w') or $result = false;
			@fwrite($file,$data);
			@fclose($file);

			if ($result == true) {
				if (GetThumbneil($temp,$path.'/'.$workernum.'.'.$type.'.jpg',120,90,true) == true) {
					$insert = array('wno'=>$wno,'date'=>GetTime('Y-m-d'),'workernum'=>$workernum,'pno'=>$pno,'owno'=>$workerspace['wno']);

					if ($mDB->DBcount($mErp->table['attend_member'],"where `workernum`='$workernum' and `date`='".GetTime('Y-m-d')."'") == 0) {
						$insert[$type.'time'] = GetGMT();

						if ($type == 'in' && $workspace['auto_delay_condition']) {
							$gender = (int)(substr($worker['jumin'],7,1)) % 2 == 1 ? 'M' : 'F';
							$hour = GetTime('H');
							$minute = GetTime('m');

							try {
								if (eval("return (".$workspace['auto_delay_condition'].");") == true) {
									$insert['is_delay'] = 'TRUE';
								}
							} catch (Exception $e) {}
						}

						if ($type == 'out' && $workspace['auto_early_condition']) {
							$gender = (int)(substr($worker['jumin'],7,1)) % 2 == 1 ? 'M' : 'F';
							$hour = GetTime('H');
							$minute = GetTime('m');

							try {
								if (eval("return (".$workspace['auto_early_condition'].";") == true) {
									$insert['is_early'] = 'TRUE';
								}
							} catch (Exception $e) {}
						}

						$mDB->DBinsert($mErp->table['attend_member'],$insert);
					} else {
						$update[$type.'time'] = GetGMT();

						$mDB->DBupdate($mErp->table['attend_member'],$update,'',"where `workernum`='$workernum' and `date`='".GetTime('Y-m-d')."'");
					}

					echo '<attend>';
					echo '<result>SUCCESS</result>';
					echo '<name>'.GetString($worker['name'],'xml').'</name>';
					echo '<type>'.($type == 'in' ? '출근' : '퇴근').'</type>';
					echo '</attend>';
				} else {
					$result = false;
				}
			}
		}

		if ($result == false) {
			echo '<attend>';
			echo '<result>ERROR</result>';
			echo '</attend>';
		}
	} else {
		echo '<attend>';
		echo '<result>FALSE</result>';
		echo '</attend>';
	}
}
?>