<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');
$mErp = new ModuleErp();

/************************************************************************************************
 * 현장관리
 ***********************************************************************************************/
if ($action == 'workspace') {
	// 현장목록
	if ($do == 'add' || $do == 'modify') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$Error = array();

		$insert = array();
		$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '현장명을 입력하여 주십시오.';
		$insert['orderer'] = Request('orderer') ? Request('orderer') : $Error['orderer'] = '발주처를 입력하여 주십시오.';
		$insert['contract_date'] = Request('contract_date') ? Request('contract_date') : '1970-01-01';
		$insert['workstart_date'] = Request('workstart_date') ? Request('workstart_date') : '1970-01-01';
		$insert['workend_date'] = Request('workend_date') ? Request('workend_date') : '1970-01-01';
		$insert['area'] = str_replace(',','',Request('area'));
		$insert['size'] = str_replace(',','',Request('size'));
		$insert['structure'] = Request('structure');
		$insert['totalarea'] = str_replace(',','',Request('totalarea'));
		$insert['buildarea'] = str_replace(',','',Request('buildarea'));
		$insert['buildpercent'] = str_replace(',','',Request('buildpercent'));
		$insert['buildingcoverage'] = str_replace(',','',Request('buildingcoverage'));
		$insert['purpose'] = Request('purpose');
		$insert['zone'] = Request('zone');
		$insert['zipcode'] = Request('zipcode');
		$insert['address'] = Request('address1').'||'.Request('address2');
		$insert['telephone'] = CheckPhoneNumber(Request('telephone')) ==  true ? GetPhoneNumber(Request('telephone')) : '';
		$insert['master'] = Request('master');
		$insert['architects'] = Request('architects');

		if ($do == 'add') {
			$insert['year'] = date('Y');
			$insert['type'] = Request('contract_date') ? 'WORKING' : 'ESTIMATE';
			if ($mDB->DBcount($mErp->table['workspace'],"where `title`='{$insert['title']}' and `type`!='BACKUP'") == 1) {
				$Error['title'] = '같은 현장명이 이미 등록되어 있습니다.';
			}

			if (sizeof($Error) == 0) {
				$idx = $mDB->DBinsert($mErp->table['workspace'],$insert);
				$mDB->DBinsert($mErp->table['workspace_master_log'],array('wno'=>$idx,'master'=>$insert['master'],'mno'=>$member['idx'],'reg_date'=>GetGMT()));
			}
		} else {
			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['workspace'],array('master','type'),"where `idx`=$idx");
			$insert['type'] = Request('contract_date') && $data['type'] == 'ESTIMATE' ? 'WORKING' : (Request('contract_date') == null && $data['type'] == 'WORKING' ? 'ESTIMATE' : $data['type']);
			if ($mDB->DBcount($mErp->table['workspace'],"where `idx`!=$idx and `title`='{$insert['title']}' and `type`!='BACKUP'") == 1) {
				$Error['title'] = '같은 현장명이 이미 등록되어 있습니다.';
			}

			if (sizeof($Error) == 0) {
				if ($data['master'] != $insert['master']) {
					$mDB->DBinsert($mErp->table['workspace_master_log'],array('wno'=>$idx,'master'=>$insert['master'],'mno'=>$member['idx'],'reg_date'=>GetGMT()));
				}
				$mDB->DBupdate($mErp->table['workspace'],$insert,'',"where `idx`=$idx");
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

	// 현장이미지
	if ($do == 'image') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		$idx = Request('idx');

		$data = $mDB->DBfetchs($mErp->table['workspace_image'],'*',"where `idx` IN ($idx)");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			@unlink($_ENV['path'].$data[$i]['filepath']);
			@unlink($_ENV['path'].'/userfile/erp/workspace/thumbneil/'.$data[$i]['idx'].'.thm');

			$mDB->DBdelete($mErp->table['workspace_image'],"where `idx`={$data[$i]['idx']}");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 현장상태변경
	if ($do == 'status') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = Request('idx');
		$type = Request('type');
		$mDB->DBupdate($mErp->table['workspace'],array('type'=>$type),'',"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 현장삭제
	if ($do == 'delete') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = Request('idx');
		$mDB->DBdelete($mErp->table['workspace'],"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}

	// 공정
	if ($do == 'workgroup') {
		$wno = Request('wno');
		$mode = Request('mode');

		if ($mode == 'add' || $mode == 'modify') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$workgroup = Request('workgroup') ? Request('workgroup') : $Error['workgroup'] = '공정명을 입력하여 주십시오.';
			$bgno = Request('bgno') ? Request('bgno') : $Error['bgno'] = '공종타입을 선택하여 주십시오.';
			$btno = Request('btno');
			$sort = Request('sort') ? Request('sort') : $Error['sort'] = '정렬순서를 입력하여 주십시오.';

			if ($mode == 'add') {
				if ($mDB->DBcount($mErp->table['workspace_workgroup'],"where `wno`=$wno and `workgroup`='$workgroup'") == 1) {
					$Error['workgroup'] = '이미 존재하는 공종명입니다.';
				}

				if (sizeof($Error) == 0) {
					$idx = $mDB->DBinsert($mErp->table['workspace_workgroup'],array('wno'=>$wno,'bgno'=>$bgno,'btno'=>$btno,'workgroup'=>$workgroup,'sort'=>$sort));
				}
			} else {
				$idx = Request('idx');
				if ($mDB->DBcount($mErp->table['workspace_workgroup'],"where `idx`!=$idx and `wno`=$wno and `workgroup`='$workgroup'") == 1) {
					$Error['workgroup'] = '이미 존재하는 공종명입니다.';
				}

				if (sizeof($Error) == 0) {
					$mDB->DBupdate($mErp->table['workspace_workgroup'],array('workgroup'=>$workgroup,'bgno'=>$bgno,'btno'=>$btno,'sort'=>$sort),'',"where `idx`=$idx and `wno`=$wno");
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

		if ($mode == 'delete') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$mDB->DBdelete($mErp->table['workspace_workgroup'],"where `idx` IN ($idx) and `wno`=$wno");
			$mDB->DBdelete($mErp->table['workspace_worktype'],"where `gno` IN ($idx) and `wno`=$wno");

			$mDB->DBdelete($mErp->table['cost_item'],"where `gno` IN ($idx) and `wno`=$wno");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 공종
	if ($do == 'worktype') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$wno = Request('wno');
		$mode = Request('mode');

		if ($mode == 'add') {
			$gno = Request('gno');
			$worktype = Request('worktype');
			$sort = Request('sort');

			$workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],array('wno','bgno'),"where `idx`=$gno");
			$check = $mDB->DBfetch($mErp->table['base_worktype'],array('idx'),"where `bgno`={$workgroup['bgno']} and `worktype`='$worktype'");

			if (isset($check['idx']) == false) {
				$tno = $mErp->InsertWorktype($gno,$worktype);
				$mDB->DBupdate($mErp->table['workspace_worktype'],array('sort'=>$sort),'',"where `idx`=$tno");
			} else {
				$btno = $check['idx'];
				$check = $mDB->DBfetch($mErp->table['workspace_worktype'],array('idx'),"where `gno`=$gno and `btno`='$btno'");
				if (isset($check['idx']) == true) {
					$Error['worktype'] = '해당 공종명이 이미 존재합니다.';
				} else {
					$mDB->DBinsert($mErp->table['workspace_worktype'],array('wno'=>$wno,'gno'=>$gno,'btno'=>$btno,'sort'=>$sort));
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

		if ($mode == 'modify') {
			$idx = Request('idx');
			$worktype = Request('worktype');
			$sort = Request('sort');

			$data = $mDB->DBfetch($mErp->table['workspace_worktype'],'*',"where `idx`=$idx");

			if ($worktype != $mErp->GetWorktype($idx)) {
				$workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],array('wno','bgno'),"where `idx`={$data['gno']}");
				$check = $mDB->DBfetch($mErp->table['base_worktype'],array('idx'),"where `bgno`={$workgroup['bgno']} and `worktype`='$worktype'");
				if (isset($check['idx']) == false) {
					$btno = $mDB->DBinsert($mErp->table['base_worktype'],array('bgno'=>$workgroup['bgno'],'worktype'=>$worktype));
				} else {
					$btno = $check['idx'];
				}

				$check = $mDB->DBfetch($mErp->table['workspace_worktype'],array('idx'),"where `gno`={$data['gno']} and `btno`='$btno'");
				if (isset($check['idx']) == true) {
					$Error['worktype'] = '해당 공종명이 이미 존재합니다.';
				} else {
					$mDB->DBupdate($mErp->table['workspace_worktype'],array('btno'=>$btno,'sort'=>$sort),'',"where `idx`=$idx");
				}
			} else {
				$mDB->DBupdate($mErp->table['workspace_worktype'],array('sort'=>$sort),'',"where `idx`=$idx");
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

		if ($mode == 'delete') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$mDB->DBdelete($mErp->table['workspace_worktype'],"where `idx` IN ($idx) and `wno`=$wno");
			$mDB->DBdelete($mErp->table['cost_item'],"where `tno` IN ($idx) and `wno`=$wno");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 건설동
	if ($do == 'buildtype') {
		$wno = Request('wno');
		$mode = Request('mode');

		if ($mode == 'add' || $mode == 'modify') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$buildtype = Request('buildtype') ? Request('buildtype') : $Error['buildtype'] = '건설동명을 입력하여 주십시오.';

			if ($mode == 'add') {
				if ($mDB->DBcount($mErp->table['workspace_buildtype'],"where `wno`=$wno and `buildtype`='$buildtype'") == 1) {
					$Error['buildtype'] = '이미 존재하는 건설동명입니다.';
				}

				if (sizeof($Error) == 0) {
					$idx = $mDB->DBinsert($mErp->table['workspace_buildtype'],array('wno'=>$wno,'buildtype'=>$buildtype));
				}
			} else {
				$idx = Request('idx');
				if ($mDB->DBcount($mErp->table['workspace_buildtype'],"where `idx`!=$idx and `wno`=$wno and `buildtype`='$buildtype'") == 1) {
					$Error['buildtype'] = '이미 존재하는 건설동명입니다.';
				}

				if (sizeof($Error) == 0) {
					$mDB->DBupdate($mErp->table['workspace_buildtype'],array('buildtype'=>$buildtype),'',"where `idx`=$idx and `wno`=$wno");
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

		if ($mode == 'delete') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$idx = Request('idx');
			$mDB->DBdelete($mErp->table['workspace_buildtype'],"where `idx` IN ($idx) and `wno`=$wno");
			$mDB->DBupdate($mErp->table['workspace_workgroup'],array('btno'=>''),'',"where `btno` IN ($idx) and `wno`=$wno");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}

	// 계약관리
	if ($do == 'cost') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 추가
		if ($mode == 'add') {
			$wno = Request('wno');
			$title = Request('title') ? Request('title') : $Error['title'] = '제목을 입력하세요.';
			$type = Request('type');

			if (sizeof($Error) == 0) {
				$idx = $mDB->DBinsert($mErp->table['cost'],array('wno'=>$wno,'type'=>$type,'title'=>$title,'reg_date'=>GetGMT()));

				if ($type == 'CHANGE') {
					$baseno = Request('baseno');
					$base = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`=$baseno");
					for ($i=0, $loop=sizeof($base);$i<$loop;$i++) {
						unset($base[$i]['idx']);
						$base[$i]['repto'] = $idx;
						$base[$i]['ovalue'] = $base[$i]['ea'].','.$base[$i]['cost1'].','.$base[$i]['cost2'].','.$base[$i]['cost3'];

						$mDB->DBinsert($mErp->table['cost_item'],$base[$i]);
					}

					$loadData = $mDB->DBfetch($mErp->table['cost'],array('sheet'),"where `idx`=$baseno");
					$mDB->DBupdate($mErp->table['cost'],array('sheet'=>$loadData['sheet'],'modify_date'=>GetGMT()),'',"where `idx`=$idx");
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

		// 현장에 적용하기
		if ($mode == 'apply') {
			$idx = Request('idx');
			$type = $type == 'CHANGE' ? 'CONTRACT' : $type;
			$cost = $mDB->DBfetch($mErp->table['cost'],array('wno'),"where `idx`=$idx");

			$mDB->DBupdate($mErp->table['workspace'],array(strtolower($type)=>$idx),'',"where `idx`={$cost['wno']}");
			$mDB->DBupdate($mErp->table['cost'],array('is_apply'=>'FALSE'),'',"where `wno`={$cost['wno']} and `type`='$type'");
			$mDB->DBupdate($mErp->table['cost'],array('is_apply'=>'TRUE'),'',"where `idx`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 시트저장
		if ($mode == 'sheet') {
			$idx = Request('idx');
			$data = GetExtData('data');

			$price = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) $price+= $data[$i]['price'];
			$mDB->DBupdate($mErp->table['cost'],array('sheet'=>serialize($data),'price'=>$price,'modify_date'=>GetGMT()),'',"where `idx`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 평당환산금액저장
		if ($mode == 'unit') {
			$idx = Request('idx');
			$data = GetExtData('data');

			$mDB->DBupdate($mErp->table['cost'],array('unit'=>serialize($data)),'',"where `idx`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 기존항목 로딩하기
		if ($mode == 'load') {
			$idx = Request('idx');
			$load = Request('load');
			$mDB->DBdelete($mErp->table['cost_item'],"where `repto`=$idx");
			$loadData = $mDB->DBfetch($mErp->table['cost'],array('sheet'),"where `idx`=$load");
			$mDB->DBupdate($mErp->table['cost'],array('sheet'=>$loadData['sheet'],'modify_date'=>GetGMT()),'',"where `idx`=$idx");

			$data = $mDB->DBfetchs($mErp->table['cost_item'],'*',"where `repto`=$load");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				unset($data[$i]['idx']);
				$data[$i]['repto'] = $idx;
				$mDB->DBinsert($mErp->table['cost_item'],$data[$i]);
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 엑셀에서 추가하기
		if ($mode == 'add_excel') {
			$idx = Request('idx');
			$gno = Request('gno');
			$file = $_FILES['file'];

			$cost = $mDB->DBfetch($mErp->table['cost'],'*',"where `idx`=$idx");
			if (GetFileExec($file['name']) == 'xlsx' || GetFileExec($file['name']) == 'xls') {
				$mPHPExcelReader = new PHPExcelReader($file['tmp_name']);

				if ($gno) {
					$insert_worktype = Request('insert_worktype') == 'on' ? true : false;
					$insert_itemdb = Request('insert_itemdb') == 'on' ? true : false;
					$insert_duplication = Request('insert_duplication') == 'on' ? true : false;

					$data = $mPHPExcelReader->GetExcelData();

					if ($data == false) {
						$Error['file'] = '엑셀파일만 업로드 하여 주십시오.';
					} else {
						$worktype = '';
						$beforeWorktype = '';
						$beforeWorktypeIDX = '';
						for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
							if ($data[$i]['A']) {
								$worktype = preg_replace('/[[:space:]]*/','',$data[$i]['A']);
							}

							if ($worktype == '') continue;

							if ($beforeWorktype == $worktype) {
								$tno = $beforeWorktypeIDX;
							} else {
								$beforeWorktype = $worktype;

								if ($mErp->CheckWorktype($cost['wno'],$gno,$worktype) == false) {
									if ($insert_worktype == true) {
										$beforeWorktypeIDX = $tno = $mErp->InsertWorktype($gno,$worktype);
									} else {
										$beforeWorktypeIDX = $tno = false;
									}
								} else {
									$beforeWorktypeIDX = $tno = $mErp->GetWorktypeIDX($wno,$gno,$worktype);
								}
							}

							if ($tno !== false) {
								$cost = $mDB->DBfetch($mErp->table['cost'],array('type','wno'),"where `idx`=$idx");
								$insert = array();
								$insert['repto'] = $idx;
								$insert['gno'] = $gno;
								$insert['tno'] = $tno;
								$insert['search'] = GetUTF8Divide($data[$i]['B']);
								$insert['title'] = $data[$i]['B'];
								$insert['size'] = $data[$i]['C'];
								$insert['unit'] = $data[$i]['D'];
								$insert['ea'] = $data[$i]['E'];
								$insert['cost1'] = $data[$i]['F'];
								$insert['cost2'] = $data[$i]['H'];
								$insert['cost3'] = $data[$i]['J'];
								$insert['price'] = ($data[$i]['F']+$data[$i]['H']+$data[$i]['J'])*$data[$i]['E'];
								$insert['itemcode'] = $mErp->GetItemcode($insert['title'],$insert['size'],$insert['unit']);
								$insert['code'] = $mErp->GetItemUniqueCode($insert['gno'],$insert['tno'],$insert['itemcode']);

								if ($insert_itemdb == true) {
									$mErp->InsertItem($insert['tno'],$insert['title'],$insert['size'],$insert['unit']);
								}

								if ($mDB->DBcount($mErp->table['cost_item'],"where `repto`=$idx and `code`='{$insert['code']}'") == 0) {
									$mDB->DBinsert($mErp->table['cost_item'],$insert);
									$mErp->InsertItemAvgCost($insert['itemcode'],$cost['type'],$cost['wno'],$insert['cost1'],$insert['cost2'],$insert['cost3']);
								} else {
									if ($insert_duplication == true) {
										$duplication = 1;
										while (true) {
											$insert['title'] = $data[$i]['B'].' ('.$duplication.')';
											$insert['itemcode'] = $mErp->GetItemcode($insert['title'],$insert['size'],$insert['unit']);
											$insert['code'] = $mErp->GetItemUniqueCode($insert['gno'],$insert['tno'],$insert['itemcode']);

											if ($mDB->DBcount($mErp->table['cost_item'],"where `repto`=$idx and `code`='{$insert['code']}'") == 0) {
												$mDB->DBinsert($mErp->table['cost_item'],$insert);
												break;
											} else {
												$duplication++;
											}
										}
									}
								}
							}
						}
					}
				} else {
					$excel = array();
					$looper = 0;
					foreach ($sheet as $workgroup=>$data) {
						$worktype = '';
						for ($i=0,$loop=sizeof($data);$i<$loop;$i++) {
							if ($data[$i]['A']) {
								$worktype = $data[$i]['A'];
							}

							if ($data[$i]['B']) {
								$itemcode = $mErp->GetItemcode($data[$i]['B'],$data[$i]['C'],$data[$i]['D']);

								if ($useGroup == true) $excel[$looper]['workgroup'] = $workgroup;
								$excel[$looper]['itemcode'] = $mErp->GetFindItem($itemcode) == true ? $itemcode : '';
								$excel[$looper]['worktype'] = $worktype;
								$excel[$looper]['title'] = $data[$i]['B'];
								$excel[$looper]['size'] = $data[$i]['C'];
								$excel[$looper]['unit'] = $data[$i]['D'];
								$excel[$looper]['ea'] = $data[$i]['E'];
								$excel[$looper]['cost1'] = $data[$i]['F'];
								$excel[$looper]['cost2'] = $data[$i]['H'];
								$excel[$looper]['cost3'] = $data[$i]['J'];
								$excel[$looper]['avgcost1'] = $mErp->GetItemAvgCost($itemcode,'cost1');
								$excel[$looper]['avgcost2'] = $mErp->GetItemAvgCost($itemcode,'cost2');
								$excel[$looper]['avgcost3'] = $mErp->GetItemAvgCost($itemcode,'cost3');
								$excel[$looper]['sort'] = $looper;

								$looper++;
							}
						}
					}

					$mDB->DBinsert($mErp->table['excel'],array('code'=>$code,'data'=>serialize($excel)));
				}
			} else {
				$Error['file'] = '엑셀파일만 업로드 하여 주십시오.';
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
				echo '<field><id>'.$code.'</id></field>';
				echo '</errors>';
			}

			echo '</message>';
		}

		// 품목추가하기
		if ($mode == 'add_item') {
			$idx = Request('idx');
			$gno = Request('gno');
			$data = GetExtData('data');
			$is_insert = Request('is_insert');

			if ($gno) $workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],array('idx','bgno'),"where `idx`=$gno");
			$cost = $mDB->DBfetch($mErp->table['cost'],array('type','wno'),"where `idx`=$idx");

			$except = 0;
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if (!$gno) {
					$workgroup = $mDB->DBfetch($mErp->table['workspace_workgroup'],array('idx','bgno'),"where `wno`={$cost['wno']} and `workgroup`='{$data[$i]['workgroup']}'");
				}
				$insert['repto'] = $idx;
				$insert['gno'] = $workgroup['idx'];
				$insert['tno'] = $mErp->InsertWorktype($workgroup['idx'],$data[$i]['worktype']);
				$insert['search'] = GetUTF8Divide($data[$i]['title']);
				$insert['title'] = $data[$i]['title'];
				$insert['size'] = $data[$i]['size'];
				$insert['unit'] = $data[$i]['unit'];
				$insert['ea'] = $data[$i]['ea'];
				$insert['cost1'] = $data[$i]['cost1'];
				$insert['cost2'] = $data[$i]['cost2'];
				$insert['cost3'] = $data[$i]['cost3'];
				$insert['price'] = ($data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3'])*$data[$i]['ea'];
				$insert['itemcode'] = $mErp->GetItemcode($data[$i]['title'],$data[$i]['size'],$data[$i]['unit']);
				$insert['code'] = $mErp->GetItemUniqueCode($insert['gno'],$insert['tno'],$insert['itemcode']);

				$check = $mDB->DBfetch($mErp->table['cost_item'],array('idx'),"where `repto`=$idx and `code`='{$insert['code']}'");
				if (isset($check['idx']) == false) {
					$mDB->DBinsert($mErp->table['cost_item'],$insert);
					if ($is_insert == 'true') {
						$mErp->InsertItem($insert['tno'],$insert['title'],$insert['size'],$insert['unit']);
					}
					$mErp->InsertItemAvgCost($insert['itemcode'],$cost['type'],$cost['wno'],$data[$i]['cost1'],$data[$i]['cost2'],$data[$i]['cost3']);
				} else {
					$looper = 0;
					while (true) {
						$insert['title'] = $data[$i]['title'].'('.(++$looper).')';
						$insert['search'] = GetUTF8Divide($insert['title']);
						$insert['itemcode'] = $mErp->GetItemcode($insert['title'],$data[$i]['size'],$data[$i]['unit']);
						$insert['code'] = $mErp->GetItemUniqueCode($insert['gno'],$insert['tno'],$insert['itemcode']);
						if ($mDB->DBcount($mErp->table['cost_item'],"where `repto`=$idx and `code`='{$insert['code']}'") == 0) {
							$mDB->DBinsert($mErp->table['cost_item'],$insert);
							break;
						}
					}
					$except++;
				}
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="'.(sizeof($Error) == 0 ? 'true' : 'false').'">';
			echo '<errors>';
			echo '<field><id>'.$except.'</id></field>';
			echo '</errors>';
			echo '</message>';
		}

		// 품목수정하기
		if ($mode == 'mod_item') {
			$idx = Request('idx');
			$data = GetExtData('data');

			$cost = $mDB->DBfetch($mErp->table['cost'],array('wno','type'),"where `idx`=$idx");
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$check = $mDB->DBfetch($mErp->table['cost_item'],array('cost1','cost2','cost3'),"where `idx`={$data[$i]['idx']}");
				if ($check['cost1'] != $data[$i]['cost1'] || $check['cost1'] != $data[$i]['cost1'] || $check['cost1'] != $data[$i]['cost1']) {
					$mErp->InsertItemAvgCost($data[$i]['itemcode'],$cost['type'],$cost['wno'],$data[$i]['cost1'],$data[$i]['cost2'],$data[$i]['cost3']);
				}
				$mDB->DBupdate($mErp->table['cost_item'],array('ea'=>$data[$i]['ea'],'cost1'=>$data[$i]['cost1'],'cost2'=>$data[$i]['cost2'],'cost3'=>$data[$i]['cost3'],'price'=>($data[$i]['cost1']+$data[$i]['cost2']+$data[$i]['cost3'])*$data[$i]['ea'],'etc'=>$data[$i]['etc']),'',"where `idx`={$data[$i]['idx']}");
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 품목 삭제하기
		if ($mode == 'del_item') {
			$ino = Request('ino');
			$mDB->DBdelete($mErp->table['cost_item'],"where `idx` IN ($ino)");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 금액 수정하기
		if ($mode == 'mod_cost') {
			$idx = Request('idx');
			$gno = Request('gno');
			$percent = Request('percent');
			$find = "where `repto`=$idx";
			$find = $gno != 0 ? " and `gno`=$gno" : "";
			$data = $mDB->DBfetchs($mErp->table['cost_item'],array('idx','cost1','cost2','cost3'),$find);
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$insert = array();
				$insert['cost1'] = floor($data[$i]['cost1']*$percent/100);
				$insert['cost2'] = floor($data[$i]['cost2']*$percent/100);
				$insert['cost3'] = floor($data[$i]['cost3']*$percent/100);
				$insert['price'] = $insert['cost1']+$insert['cost2']+$insert['cost3'];

				$mDB->DBupdate($mErp->table['cost_item'],$insert,'',"where `idx`={$data[$i]['idx']}");
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		// 새 견적/계약/실행/설계변경 수정
		if ($mode == 'modify') {
			$idx = Request('idx');
			$title = Request('title') ? Request('title') : $Error['title'] = '제목을 입력하세요.';
			if (sizeof($Error) == 0) {
				$mDB->DBupdate($mErp->table['cost'],array('title'=>$title),'',"where `idx`=$idx");
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

		// 새 견적/계약/실행/설계변경 삭제
		if ($mode == 'delete') {
			$idx = Request('idx');
			$mDB->DBdelete($mErp->table['cost'],"where `idx`=$idx");
			$mDB->DBdelete($mErp->table['cost_item'],"where `repto`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}
}

/************************************************************************************************
 * 근태관리
 ***********************************************************************************************/
if ($action == 'attend') {
	if ($do == 'condition') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$data = GetExtData('data');

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$mDB->DBupdate($mErp->table['workspace'],array('auto_delay_condition'=>$data[$i]['auto_delay_condition'],'auto_early_condition'=>$data[$i]['auto_early_condition']),'',"where `idx`={$data[$i]['idx']}");
		}

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

/************************************************************************************************
 * 현장발주관리
 ***********************************************************************************************/
if ($action == 'order') {
	// 발주요청서
	if ($do == 'order') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		// 발주서수정
		if ($mode == 'modify') {
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
			$insert['etc'] = Request('etc');

			$idx = Request('idx');
			$mDB->DBupdate($mErp->table['outsourcing_order'],$insert,'',"where `idx`=$idx");

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '<errors>';
			echo '<field><id>'.$idx.','.(sizeof($list)-sizeof($data)).'</id></field>';
			echo '</errors>';
			echo '</message>';
		}

		// 본사확인처리
		if ($mode == 'confirm') {
			$idx = Request('idx');
			$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('status'),"where `idx`=$idx");

			if ($data['status'] == 'NEW') {
				$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'CONFIRM'),'',"where `idx`=$idx");
				$status = 'CONFIRM';
			} elseif ($data['status'] == 'CONFIRM') {
				$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'NEW'),'',"where `idx`=$idx");
				$mDB->DBdelete($mErp->table['outsourcing_consult'],"where `repto`=$idx");
				$mDB->DBdelete($mErp->table['outsourcing_contract'],"where `repto`=$idx");
				$status = 'NEW';
			} else {
				$status = 'COMPLETE';
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '<status>'.$status.'</status>';
			echo '</message>';
		}

		if ($mode == 'delete') {
			$idx = Request('idx');
			if ($mDB->DBcount($mErp->table['outsourcing_consult'],"where `repto`=$idx") > 0) {
				echo '<?xml version="1.0" encoding="UTF-8"?>';
				echo '<message success="true">';
				echo '<result>FALSE</result>';
				echo '</message>';
			} else {
				$data = $mDB->DBfetch($mErp->table['outsourcing_order'],array('file'),"where `idx`=$idx");
				if ($data['file'] && file_exists($_ENV['path'].$data['file']) == true) {
					@unlink($_ENV['path'].$data['file']);
				}
				$mDB->DBdelete($mErp->table['outsourcing_order'],"where `idx`=$idx");
				echo '<?xml version="1.0" encoding="UTF-8"?>';
				echo '<message success="true">';
				echo '<result>TRUE</result>';
				echo '</message>';
			}
		}
	}

	// 본사품의서
	if ($do == 'consult') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$wno = Request('wno');
		$repto = Request('repto');
		$mode = Request('mode');

		// 작성 및 수정
		if ($mode == 'add' || $mode == 'modify') {
			$idx = Request('idx');

			$removes = array();
			$insert = array();
			$insert['contract'] = serialize(GetExtData('contract'));
			$insert['wno'] = $wno;
			$insert['type'] = Request('type');
			$insert['title'] = Request('title');
			$insert['repto'] = $repto;
			$insert['etc'] = Request('etc');
			$insert['date'] = GetTime('Y-m-d H:i:s');

			$insert['consult'] = array();
			$consults = explode("\n",Request('consult'));
			for ($i=0, $loop=sizeof($consults);$i<$loop;$i++) {
				$temp = explode("\t",$consults[$i]);

				$insert['consult'][$i] = array();
				$insert['consult'][$i]['cno'] = $temp[0];
				$insert['consult'][$i]['cost1'] = $temp[1];
				$insert['consult'][$i]['cost2'] = $temp[2];
				$insert['consult'][$i]['cost3'] = $temp[3];

				$check = array();
				$items = array();
				$list = GetExtDataToArray($temp[4]);
				for ($j=0, $loopj=sizeof($list);$j<$loopj;$j++) {
					// 자재코드
					$itemcode = $mErp->GetItemcode($list[$j]['title'],$list[$j]['size'],$list[$j]['unit']);
					$code = $mErp->GetItemUniqueCode($list[$j]['gno'],$list[$j]['tno'],$itemcode);

					if (isset($check[$code]) == false) {
						$check[$code] = true;

						if ($temp[1] == 'FALSE') unset($list[$j]['cost1']);
						if ($temp[2] == 'FALSE') unset($list[$j]['cost2']);
						if ($temp[3] == 'FALSE') unset($list[$j]['cost3']);
						unset($list[$j]['price']);

						$items[] = $list[$j];
					}
				}

				$removes[$i] = sizeof($list)-sizeof($items);
				$insert['consult'][$i]['items'] = $items;
			}

			$insert['consult'] = serialize($insert['consult']);

			if ($idx) {
				unset($insert['date']);
				unset($insert['title']);
				$mDB->DBupdate($mErp->table['outsourcing_consult'],$insert,'',"where `idx`=$idx");
			} else {
				$idx = $mDB->DBinsert($mErp->table['outsourcing_consult'],$insert);
			}
			$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'CONFIRM'),'',"where `idx`=$repto and `status`='NEW'");

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
				echo '<field><id>'.$idx.','.implode(',',$removes).'</id></field>';
				echo '</errors>';
			}

			echo '</message>';
		}
	}

	// 발주계약서
	if ($do == 'contract') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$mode = Request('mode');

		if ($mode == 'add' || $mode == 'modify') {
			$idx = Request('idx');
			$tno = Request('tno');
			$cidx = Request('parent');
			$consult = $mDB->DBfetch($mErp->table['outsourcing_consult'],array('wno','repto','contract','consult'),"where `idx`=$cidx");

			$insert = array();
			$insert['etc'] = Request('etc');
			$list = GetExtDataToArray(Request('data'));
			$check = array();
			$items = array();
			for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
				// 자재코드
				$itemcode = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
				$code = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$itemcode);

				if (isset($check[$code]) == false) {
					$check[$code] = true;
					unset($list[$i]['group']);
					$items[] = $list[$i];
				}
			}

			$insert['data'] = serialize($items);

			if ($mode == 'add') {
				$tabs = unserialize($consult['consult']);
				$insert['parent'] = $cidx;
				$insert['wno'] = $consult['wno'];
				$insert['repto'] = $consult['repto'];
				$insert['status'] = 'NEW';
				$insert['title'] = Request('title');
				$insert['cno'] = $tabs[$tno]['cno'];
				$insert['date'] = GetTime('Y-m-d H:i:s');

				$contract = unserialize($consult['contract']);

				for ($i=0, $loop=sizeof($contract);$i<$loop;$i++) {
					$contract[$i]['cost1'] = $tabs[$tno]['cost1'];
					$contract[$i]['cost2'] = $tabs[$tno]['cost2'];
					$contract[$i]['cost3'] = $tabs[$tno]['cost3'];
					unset($contract[$i]['cost']);
					unset($contract[$i]['price']);
					unset($contract[$i]['group']);
				}

				$insert['original'] = serialize($contract);

				$idx = $mDB->DBinsert($mErp->table['outsourcing_contract'],$insert);
			} else {
				$mDB->DBupdate($mErp->table['outsourcing_contract'],$insert,'',"where `idx`=$idx");
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
				echo '<field><id>'.$idx.','.(sizeof($list)-sizeof($items)).'</id></field>';
				echo '</errors>';
			}

			echo '</message>';
		}

		if ($mode == 'status') {
			$idx = Request('idx');
			$status = Request('status');

			$data = $mDB->DBfetch($mErp->table['outsourcing_contract'],'*',"where `idx`=$idx");
			if ($data['status'] == 'NEW') {
				$list = unserialize($data['data']);
				for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
					// 자재코드
					$list[$i]['itemcode'] = $mErp->GetItemcode($list[$i]['title'],$list[$i]['size'],$list[$i]['unit']);
					$list[$i]['code'] = $mErp->GetItemUniqueCode($list[$i]['gno'],$list[$i]['tno'],$list[$i]['itemcode']);
					// 도급내역에서 검색
					$contract = $mErp->GetContractItem($data['wno'],$code);
				}

				if ($status == 'outsourcing') {
					$repto = $mDB->DBinsert($mErp->table['outsourcing'],array('wno'=>$data['wno'],'cno'=>$data['cno'],'repto'=>$data['idx'],'title'=>$data['title'],'original'=>$data['original'],'status'=>'WORK','start_date'=>GetTime('Y-m-d')));
					for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
						$list[$i]['repto'] = $repto;
						$list[$i]['wno'] = $data['wno'];
						$list[$i]['cno'] = $data['cno'];
						$list[$i]['subcode'] = $mErp->GetItemPriceCode($list[$i]['cost1'],$list[$i]['cost2'],$list[$i]['cost3']);
						$mDB->DBinsert($mErp->table['outsourcing_item'],$list[$i]);
					}

					$mDB->DBupdate($mErp->table['outsourcing_contract'],array('status'=>'OUTSOURCING'),'',"where `idx`=$idx");
					$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'COMPLETE'),'',"where `idx`={$data['repto']}");

					echo '<?xml version="1.0" encoding="UTF-8"?>';
					echo '<message success="true">';
					echo '<status>OUTSOURCING</status>';
					echo '</message>';
				}

				if ($status == 'itemorder') {
					$repto = $mDB->DBinsert($mErp->table['itemorder'],array('wno'=>$data['wno'],'cno'=>$data['cno'],'repto'=>$data['idx'],'title'=>$data['title'],'original'=>$data['original'],'status'=>'NEW','date'=>GetTime('Y-m-d')));

					for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
						$list[$i]['repto'] = $repto;
						$list[$i]['wno'] = $data['wno'];
						$list[$i]['cno'] = $data['cno'];
						$list[$i]['subcode'] = $mErp->GetItemPriceCode($list[$i]['cost1'],$list[$i]['cost2'],$list[$i]['cost3']);
						$mDB->DBinsert($mErp->table['itemorder_item'],$list[$i]);
					}
					$mDB->DBupdate($mErp->table['outsourcing_contract'],array('status'=>'ITEMORDER'),'',"where `idx`=$idx");
					$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'COMPLETE'),'',"where `idx`={$data['repto']}");

					echo '<?xml version="1.0" encoding="UTF-8"?>';
					echo '<message success="true">';
					echo '<status>ITEMORDER</status>';
					echo '</message>';
				}
			} else {
				if ($data['status'] == 'OUTSOURCING') { // 현재 하도급계약실행중이라면, 실행취소
					$outsourcing = $mDB->DBfetch($mErp->table['outsourcing'],array('idx'),"where `repto`=$idx");
					$mDB->DBdelete($mErp->table['outsourcing'],"where `idx`={$outsourcing['idx']}");
					$mDB->DBdelete($mErp->table['outsourcing_item'],"where `repto`={$outsourcing['idx']}");
					$mDB->DBdelete($mErp->table['monthly_item'],"where `repto`={$outsourcing['idx']} and `type`='OUTSOURCING'");

					if ($status == 'outsourcing') {
						$status = 'NEW';
					} else { // 자재발주실행
						$status = 'ITEMORDER';
						$repto = $mDB->DBinsert($mErp->table['itemorder'],array('wno'=>$data['wno'],'cno'=>$data['cno'],'repto'=>$data['idx'],'title'=>$data['title'],'original'=>$data['original'],'status'=>'NEW','date'=>GetTime('Y-m-d')));

						for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
							$list[$i]['repto'] = $repto;
							$list[$i]['wno'] = $data['wno'];
							$list[$i]['cno'] = $data['cno'];
							$list[$i]['subcode'] = $mErp->GetItemPriceCode($list[$i]['cost1'],$list[$i]['cost2'],$list[$i]['cost3']);
							$mDB->DBinsert($mErp->table['itemorder_item'],$list[$i]);
						}
						$mDB->DBupdate($mErp->table['outsourcing_contract'],array('status'=>'ITEMORDER'),'',"where `idx`=$idx");
						$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'COMPLETE'),'',"where `idx`={$data['repto']}");
					}
				}

				if ($data['status'] == 'ITEMORDER') { // 현재 자재발주실행중이라면, 실행취소
					$itemorder = $mDB->DBfetch($mErp->table['itemorder'],array('idx'),"where `repto`=$idx");
					$mDB->DBdelete($mErp->table['itemorder'],"where `idx`={$outsourcing['idx']}");
					$mDB->DBdelete($mErp->table['itemorder_item'],"where `repto`={$outsourcing['idx']}");

					if ($status == 'itemorder') {
						$status = 'NEW';
					} else { // 하도급계약실행
						$status = 'OUTSOURCING';
						$repto = $mDB->DBinsert($mErp->table['outsourcing'],array('wno'=>$data['wno'],'cno'=>$data['cno'],'repto'=>$data['idx'],'title'=>$data['title'],'original'=>$data['original'],'status'=>'WORK','start_date'=>GetTime('Y-m-d')));
						for ($i=0, $loop=sizeof($list);$i<$loop;$i++) {
							$list[$i]['repto'] = $repto;
							$list[$i]['wno'] = $data['wno'];
							$list[$i]['cno'] = $data['cno'];
							$list[$i]['subcode'] = $mErp->GetItemPriceCode($list[$i]['cost1'],$list[$i]['cost2'],$list[$i]['cost3']);
							$mDB->DBinsert($mErp->table['outsourcing_item'],$list[$i]);
						}

						$mDB->DBupdate($mErp->table['outsourcing_contract'],array('status'=>'OUTSOURCING'),'',"where `idx`=$idx");
						$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'COMPLETE'),'',"where `idx`={$data['repto']}");
					}
				}

				if ($status == 'NEW') {
					$mDB->DBupdate($mErp->table['outsourcing_order'],array('status'=>'CONFIRM'),'',"where `idx`={$data['repto']}");
				}

				$mDB->DBupdate($mErp->table['outsourcing_contract'],array('status'=>$status),'',"where `idx`=$idx");

				echo '<?xml version="1.0" encoding="UTF-8"?>';
				echo '<message success="true">';
				echo '<status>'.$status.'</status>';
				echo '</message>';
			}
		}
	}
}

/************************************************************************************************
 * 협력업체관리
 ***********************************************************************************************/
if ($action == 'cooperation') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	if ($do == 'add' || $do == 'modify') {
		$Error = array();
		$insert = array();
		$insert['title'] = Request('title') ? Request('title') : $Error['name'] = '업체명 입력하여 주십시오.';
		$insert['type'] = Request('type');
		$insert['telephone'] = CheckPhoneNumber(Request('telephone')) == true ? Request('telephone') : '';
		$insert['company_number'] = CheckCompanyNumber(Request('company_number')) ? Request('company_number') : $Error['company_number'] = '사업자등록번호가 잘못 입력되었습니다.';
		$insert['master'] = Request('master') ? Request('master') : $Error['master'] = '대표자를 입력하여 주십시오.';
		$insert['zipcode'] = Request('zipcode');
		$insert['address'] = Request('address1').'||'.Request('address2');

		if ($do == 'add') {
			$idx = $mDB->DBinsert($mErp->table['cooperation'],$insert);
		} else {
			$idx = Request('idx');
			$mDB->DBupdate($mErp->table['cooperation'],$insert,'',"where `idx`=$idx");
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

	if ($do == 'delete') {
		header('Content-type: text/xml; charset=UTF-8', true);
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");

		$idx = Request('idx');
		$mDB->DBupdate($mErp->table['cooperation'],array('is_delete'=>'TRUE'),'',"where `idx`=$idx");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<message success="true">';
		echo '</message>';
	}
}

/************************************************************************************************
 * 기성관리
 ***********************************************************************************************/
if ($action == 'monthly_payment') {
	header('Content-type: text/xml; charset=UTF-8', true);
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");

	$wno = Request('wno');
	$date = Request('date');

	// 현장청구내역 불러오기
	if ($do == 'load') {
		// 기존내역 초기화
		$mDB->DBdelete($mErp->table['monthly_payment'],"where `wno`=$wno and `date`='$date'");

		// 현장기성내역
		$data = $mDB->DBfetchs($mErp->table['monthly_payment_list'],'*',"where `wno`=$wno and `date`='$date'");
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$check = $mDB->DBfetch($mErp->table['monthly_payment'],array('idx','ea','cost1','cost2','cost3'),"where `wno`=$wno and `date`='$date' and `code`='{$data[$i]['code']}' and `subcode`='{$data[$i]['subcode']}'");
			if (isset($check['idx']) == false) {
				$idx = $mDB->DBinsert($mErp->table['monthly_payment'],array('wno'=>$wno,'date'=>$date,'code'=>$data[$i]['code'],'subcode'=>$data[$i]['subcode'],'gno'=>$data[$i]['gno'],'tno'=>$data[$i]['tno'],'title'=>$data[$i]['title'],'size'=>$data[$i]['size'],'unit'=>$data[$i]['unit'],'ea'=>$data[$i]['ea'],'cost1'=>$data[$i]['cost1'],'cost2'=>$data[$i]['cost2'],'cost3'=>$data[$i]['cost3'],'price'=>$data[$i]['price'],'etc'=>$etc));
			} else {
				$update = array();
				$cost = 0;
				if ($data[$i]['osubcode']) { // 발주내역이면,
					if ($check['cost1'] == 0 && $data[$i]['cost1'] > 0) {
						$update['cost1'] = $data[$i]['cost1'];
						$cost+= $data[$i]['cost1'];
					} else {
						$cost+= $check['cost1'];
					}

					if ($check['cost2'] == 0 && $data[$i]['cost2'] > 0) {
						$update['cost2'] = $data[$i]['cost2'];
						$cost+= $data[$i]['cost2'];
					} else {
						$cost+= $check['cost2'];
					}

					if ($check['cost3'] == 0 && $data[$i]['cost3'] > 0) {
						$update['cost3'] = $data[$i]['cost3'];
						$cost+= $data[$i]['cost3'];
					} else {
						$cost+= $check['cost3'];
					}
					$update['price'] = $check['ea']*$cost;

					$mDB->DBupdate($mErp->table['monthly_payment'],$update,'',"where `idx`={$check['idx']}");
				} else {
					if ($check['cost1'] == 0 && $data[$i]['cost1'] > 0) {
						$update['cost1'] = $data[$i]['cost1'];
						$cost+= $data[$i]['cost1'];
					} else {
						$cost+= $check['cost1'];
					}

					if ($check['cost2'] == 0 && $data[$i]['cost2'] > 0) {
						$update['cost2'] = $data[$i]['cost2'];
						$cost+= $data[$i]['cost2'];
					} else {
						$cost+= $check['cost2'];
					}

					if ($check['cost3'] == 0 && $data[$i]['cost3'] > 0) {
						$update['cost3'] = $data[$i]['cost3'];
						$cost+= $data[$i]['cost3'];
					} else {
						$cost+= $check['cost3'];
					}
					$update['ea'] = $check['ea']+$data[$i]['ea'];
					$update['price'] = $update['ea']*$cost;

					$mDB->DBupdate($mErp->table['monthly_payment'],$update,'',"where `idx`={$check['idx']}");
				}
			}
		}
	}

	// 기성내역 저장하기
	if ($do == 'save') {
		$data = GetExtData('data');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['commander_ea'] != '0' || $data[$i]['commander_cost1'] != '0' || $data[$i]['commander_cost2'] != '0' || $data[$i]['commander_cost3'] != '0' || $data[$i]['etc']) {
				$check = $mDB->DBfetch($mErp->table['monthly_payment'],array('idx'),"where `wno`=$wno and `date`='$date' and `code`='{$data[$i]['code']}' and `subcode`='{$data[$i]['subcode']}'");
				if (isset($check['idx']) == true) {
					$mDB->DBupdate($mErp->table['monthly_payment'],array('ea'=>$data[$i]['commander_ea'],'cost1'=>$data[$i]['commander_cost1'],'cost2'=>$data[$i]['commander_cost2'],'cost3'=>$data[$i]['commander_cost3'],'price'=>$data[$i]['commander_price'],'etc'=>$data[$i]['etc']),'',"where `idx`={$check['idx']}");
				}
			} else {
				$mDB->DBdelete($mErp->table['monthly_payment'],"where `wno`=$wno and `date`='$date' and `code`='{$data[$i]['code']}' and `subcode`='{$data[$i]['subcode']}'");
			}
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<message success="true">';
	echo '</message>';
}

/*
	if ($action == 'contract') {
		if ($do == 'list') {
			$mode = Request('mode');

			if ($mode == 'add') {
				header('Content-type: text/xml; charset=UTF-8', true);
				header("Cache-Control: no-cache, must-revalidate");
				header("Pragma: no-cache");

				$Error = array();
				if ($mErp->IsAdmin() == false) $Error['title'] = '관리권한이 없습니다.';

				$insert = array();
				$insert['title'] = Request('title') ? Request('title') : $Error['title'] = '계약명을 입력하여 주십시오.';
				$insert['totalarea'] = Request('totalarea') ? Request('totalarea') : $Error['title'] = '연면적을 입력하여 주십시오.';

				if ($mDB->DBcount($mErp->table['contract'],"where `title`='{$insert['title']}'") == 1) {
					$Error['title'] = '같은 계약명이 이미 등록되어 있습니다.';
				}

				if (sizeof($Error) == 0) {
					$idx = $mDB->DBinsert($mErp->table['contract'],$insert);
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
		}

		if ($do == 'cost') {
			$mode = Request('mode');

			if ($mode == 'add') {
				$data = GetExtData('data');
				$cno = Request('cno');

				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($data[$i]['itemno']) {
						$check = $mDB->DBfetch($mErp->table['contract_cost'],array('idx'),"where `cno`=$cno and `itemno`={$data[$i]['itemno']}");

						if (isset($check['idx']) == true) {
							$mDB->DBupdate($mErp->table['contract_cost'],array('ea'=>$data[$i]['ea'],'cost1'=>$data[$i]['cost1'],'cost2'=>$data[$i]['cost2'],'cost3'=>$data[$i]['cost3']),'',"where `idx`={$check['idx']}");
						} else {
							$mDB->DBinsert($mErp->table['contract_cost'],array('cno'=>$cno,'group'=>$data[$i]['group'],'worktype'=>$data[$i]['worktype'],'itemno'=>$data[$i]['itemno'],'ea'=>$data[$i]['ea'],'cost1'=>$data[$i]['cost1'],'cost2'=>$data[$i]['cost2'],'cost3'=>$data[$i]['cost3']));
						}
					}
				}
			}

			if ($mode == 'modify') {
				$data = GetExtData('data');
				$cno = Request('cno');

				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					$mDB->DBupdate($mErp->table['contract_cost'],array('ea'=>$data[$i]['ea'],'cost1'=>$data[$i]['cost1'],'cost2'=>$data[$i]['cost2'],'cost3'=>$data[$i]['cost3']),'',"where `idx`={$data[$i]['idx']}");
				}
			}

			if ($mode == 'delete') {
				$cno = Request('cno');
				$ino = Request('ino');
				$mDB->DBdelete($mErp->table['contract_cost'],"where `idx` IN ($ino) and `cno`=$cno");
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		if ($do == 'price') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$cno = Request('cno');
			$data = GetExtData('data');

			$tax = array();
			for ($i=0,$loop=sizeof($data);$i<$loop;$i++) {
				if ($data[$i]['percent']) $tax[] = $data[$i]['percent'];
			}
			$tax = implode(',',$tax);

			$mDB->DBupdate($mErp->table['contract'],array('tax'=>$tax),'',"where `idx`=$cno");

			$data = serialize($data);

			$check = $mDB->DBfetch($mErp->table['contract_price'],array('idx'),"where `cno`=$cno and `type`='COST'");
			if (isset($check['idx']) == true) {
				$mDB->DBupdate($mErp->table['contract_price'],array('data'=>$data),'',"where `idx`={$check['idx']}");
			} else {
				$mDB->DBinsert($mErp->table['contract_price'],array('cno'=>$cno,'type'=>'PRICE','data'=>$data));
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}

		if ($do == 'unit_price') {
			header('Content-type: text/xml; charset=UTF-8', true);
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");

			$cno = Request('cno');
			$data = serialize(GetExtData('data'));

			$check = $mDB->DBfetch($mErp->table['contract_price'],array('idx'),"where `cno`=$cno and `type`='UNITPRICE'");
			if (isset($check['idx']) == true) {
				$mDB->DBupdate($mErp->table['contract_price'],array('data'=>$data),'',"where `idx`={$check['idx']}");
			} else {
				$mDB->DBinsert($mErp->table['contract_price'],array('cno'=>$cno,'type'=>'UNIT','data'=>$data));
			}

			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<message success="true">';
			echo '</message>';
		}
	}
*/
?>