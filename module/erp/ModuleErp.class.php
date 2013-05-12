<?php
class ModuleErp extends Module {
	public $table;
	public $wno;
	public $defaultURL;
	public $workspace;
	public $paytype;
	public $member;
	public $workspaceTitle;

	function __construct() {
		parent::__construct('erp');

		$this->workspaceTitle = array();

		$this->wno = Request('wno');
		$this->mMember = &Member::instance();
		$this->member = $this->mMember->GetMemberInfo();
		$this->defaultURL = $_SERVER['PHP_SELF'].'?wno='.$this->wno;

		// 현장관련
		$this->table['workspace'] = $_ENV['code'].'_erp_workspace_table';
		$this->table['workspace_image'] = $_ENV['code'].'_erp_workspace_image_table';
		//$this->table['workspace_outsourcing'] = $_ENV['code'].'_erp_workspace_outsourcing_table'; // DELETE
		$this->table['workspace_order'] = $_ENV['code'].'_erp_workspace_order_table';
		$this->table['workspace_master_log'] = $_ENV['code'].'_erp_workspace_master_log_table';

		// 근로자관련
		$this->table['worker'] = $_ENV['code'].'_erp_worker_table';
		$this->table['dayworker'] = $_ENV['code'].'_erp_dayworker_table';
		$this->table['workerspace'] = $_ENV['code'].'_erp_workerspace_table';

		// 근태관련
		$this->table['attend_member'] = $_ENV['code'].'_erp_attend_member_table';
		$this->table['attend_outsourcing'] = $_ENV['code'].'_erp_attend_outsourcing_table';
		$this->table['attend_dayworker'] = $_ENV['code'].'_erp_attend_dayworker_table';

		// 작업일보
		$this->table['workreport'] = $_ENV['code'].'_erp_workreport_table';
		$this->table['payment'] = $_ENV['code'].'_erp_payment_table';

		// 기성관련
		$this->table['monthly'] = $_ENV['code'].'_erp_monthly_table';
		$this->table['monthly_item'] = $_ENV['code'].'_erp_monthly_item_table'; // NEW
		$this->table['monthly_payment'] = $_ENV['code'].'_erp_monthly_payment_table';
		$this->table['monthly_payment_list'] = $_ENV['code'].'_erp_monthly_payment_list_table';

		// 통합자재 DB관련
		$this->table['item'] = $_ENV['code'].'_erp_item_table';
		$this->table['item_cost'] = $_ENV['code'].'_erp_item_cost_table';
		$this->table['item_status'] = $_ENV['code'].'_erp_item_status_table';

		// 공정 및 공종명
		$this->table['base_workgroup'] = $_ENV['code'].'_erp_base_workgroup_table';
		$this->table['base_worktype'] = $_ENV['code'].'_erp_base_worktype_table';
		$this->table['workspace_buildtype'] = $_ENV['code'].'_erp_workspace_buildtype_table';
		$this->table['workspace_workgroup'] = $_ENV['code'].'_erp_workspace_workgroup_table';
		$this->table['workspace_worktype'] = $_ENV['code'].'_erp_workspace_worktype_table';

		// 엑셀 임포팅
		$this->table['excel'] = $_ENV['code'].'_erp_excel_table';

		// 견적, 계약, 실행내역서
		$this->table['cost'] = $_ENV['code'].'_erp_cost_table';
		$this->table['cost_item'] = $_ENV['code'].'_erp_cost_item_table';

		// 자재발주관련
		$this->table['order'] = $_ENV['code'].'_erp_order_table';
		$this->table['item_status'] = $_ENV['code'].'_erp_item_status_table';
		$this->table['order_contract'] = $_ENV['code'].'_erp_order_contract_table';
		$this->table['order_item'] = $_ENV['code'].'_erp_order_contract_item_table';
		$this->table['order_contract_item'] = $_ENV['code'].'_erp_order_contract_item_table';


		// 현장발주관련
		$this->table['cooperation'] = $_ENV['code'].'_erp_cooperation_table'; // NEW
		$this->table['outsourcing_order'] = $_ENV['code'].'_erp_outsourcing_order_table'; // NEW
		$this->table['outsourcing_consult'] = $_ENV['code'].'_erp_outsourcing_consult_table'; // NEW
		$this->table['outsourcing_contract'] = $_ENV['code'].'_erp_outsourcing_contract_table'; // NEW

		// 하도급관련
		$this->table['outsourcing'] = $_ENV['code'].'_erp_outsourcing_table'; // NEW
		$this->table['outsourcing_item'] = $_ENV['code'].'_erp_outsourcing_item_table'; // NEW

		// 자재발주관련
		$this->table['itemorder'] = $_ENV['code'].'_erp_itemorder_table'; // NEW
		$this->table['itemorder_item'] = $_ENV['code'].'_erp_itemorder_item_table'; // NEW
		$this->table['itemorder_income'] = $_ENV['code'].'_erp_itemorder_income_table'; // MODIFY

		// 경비관련
		$this->table['payment_worker'] = $_ENV['code'].'_erp_payment_worker_table'; // NEW
		$this->table['payment_item'] = $_ENV['code'].'_erp_payment_item_table'; // NEW
		$this->table['payment_expense'] = $_ENV['code'].'_erp_payment_expense_table'; // NEW
		$this->table['payment_equipment'] = $_ENV['code'].'_erp_payment_equipment_table'; // NEW

		//$this->table['contract'] = $_ENV['code'].'_erp_contract_table';
		//$this->table['contract_cost'] = $_ENV['code'].'_erp_contract_cost_table';
		//$this->table['contract_price'] = $_ENV['code'].'_erp_contract_price_table';
		//$this->table['workspace_contract_change'] = $_ENV['code'].'_erp_workspace_contract_change_table';
		//$this->table['workspace_estimate'] = $_ENV['code'].'_erp_workspace_estimate_table';
		//$this->table['workspace_cost'] = $_ENV['code'].'_erp_workspace_cost_table';
		//$this->table['workspace_sheet'] = $_ENV['code'].'_erp_workspace_sheet_table';

		$this->paytype = array('DAY'=>'단가제','MONTH'=>'월급제');
	}

	function SendSMS($code,$msg) {
		$mSMS = new ModuleSMS();
		$recvNumber = '';
		if ($code == 'order') {
			$recvNumber = $this->module['sms_order'];
		}

		if ($recvNumber) {
			$sms = explode(',',$recvNumber);
			for ($i=0, $loop=sizeof($sms);$i<$loop;$i++) {
				$mSMS->SendSMS($sms[$i],$msg,'',false);
			}
		}
	}

	function PrintWorkspace($type='default') {
		if ($this->wno == null) {
			if (Request('isAir') == 'true') {
				$this->PrintError('iERP프로그램 설치 후, 환경설정이 필요합니다.<br />상단의 메뉴에서 "파일 &gt; 환경설정"메뉴에서 환경설정을 하여주십시오.');
			} else {
				$this->PrintError('현장번호가 전달되지 않았습니다.<br />example.php?wno=[현장번호] 형식으로 현장번호를 전달하여 주십시오.');
			}
		}

		$this->workspace = $this->GetWorkspace($this->wno);

		if ($type == 'manager' && $this->mMember->IsLogged() == false) {
			REQUIRE_ONCE $this->modulePath.'/workspace.login.php';
		} else {
			if ($type == null) {
				if ($this->CheckWorkspaceMaster() == true) {
					$page = Request('page') ? Request('page') : 'work';
					REQUIRE_ONCE $this->modulePath.'/workspace/'.$page.'.category.inc.php';
					$category = Request('category') ? Request('category') : $categorys[0]['category'];
					REQUIRE_ONCE $this->modulePath.'/workspace.manager.php';
				} else {
					REQUIRE_ONCE $this->modulePath.'/workspace.default.php';
				}
			}

			if ($type == 'manager') {
				if ($this->CheckWorkspaceMaster() == false) {
					$this->PrintError('해당현장을 관리할 권한이 없습니다.<br />시스템관리자에게 문의하여 주십시오.');
				} else {
					$page = Request('page') ? Request('page') : 'work';
					REQUIRE_ONCE $this->modulePath.'/workspace/'.$page.'.category.inc.php';
					$category = Request('category') ? Request('category') : $categorys[0]['category'];
					REQUIRE_ONCE $this->modulePath.'/workspace.manager.php';
				}
			} else {
				REQUIRE_ONCE $this->modulePath.'/workspace.default.php';
			}
		}
	}

	/************************************************************************************************
	 * 현장관련
	 ***********************************************************************************************/
	// 현장정보
	function GetWorkspace($wno=0) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],'*',"where `idx`=$wno");

		if ($workspace['master']) {
			$members = $this->mDB->DBfetchs($_ENV['table']['member'],array('idx','name'),"where `idx` IN ({$workspace['master']})",'name,asc');
			$master_idx = array();
			$master_name = array();
			for ($i=0, $loopj=sizeof($members);$i<$loopj;$i++) {
				$master_idx[] = $members[$i]['idx'];
				$master_name[] = $members[$i]['name'];
			}
			$master_idx = implode(',',$master_idx);
			$master_name = implode(',',$master_name);
		} else {
			$master_idx = $master_name = '';
		}

		$workspace['contract_date'] = $workspace['contract_date'] == '1970-01-01' ? '' : $workspace['contract_date'];
		$workspace['workstart_date'] = $workspace['workstart_date'] == '1970-01-01' ? '' : $workspace['workstart_date'];
		$workspace['workend_date'] = $workspace['workend_date'] == '1970-01-01' ? '' : $workspace['workend_date'];

		$temp = explode('||',$workspace['address']);
		$workspace['address1'] = $temp[0];
		$workspace['address2'] = $temp[1];

		$workspace['area'] = GetNumberFormat($workspace['area']);
		$workspace['buildarea'] = GetNumberFormat($workspace['buildarea']);
		$workspace['totalarea'] = GetNumberFormat($workspace['totalarea']);
		$workspace['buildingcoverage'] = GetNumberFormat($workspace['buildingcoverage']);
		$workspace['buildpercent'] = GetNumberFormat($workspace['buildpercent']);

		$workspace['master'] = $master_idx;
		$workspace['master_view'] = $master_name;

		return $workspace;
	}

	// 현장명
	function GetWorkspaceTitle($wno=0) {
		if (sizeof($this->workspaceTitle) == 0) {
			$data = $this->mDB->DBfetchs($this->table['workspace'],array('idx','title'));
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				$this->workspaceTitle[$data[$i]['idx']] = $data[$i]['title'];
			}
		}

		return $this->workspaceTitle[$wno];
	}

	// 현장소장
	function GetWorkspaceMasterName($wno=0) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],array('master'),"where `idx`=$wno");
		if ($workspace['master']) {
			$members = $this->mDB->DBfetchs($_ENV['table']['member'],array('name'),"where `idx` IN ({$workspace['master']})",'name,asc');
			$master = array();
			for ($j=0, $loopj=sizeof($members);$j<$loopj;$j++) {
				$master[] = $members[$j]['name'];
			}
			$master = implode(',',$master);
		} else {
			$master = '';
		}

		return $master;
	}

	// 현장공정률
	function GetWorkspaceWorkPercent($wno=0) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],array('workstart_date','workend_date'),"where `idx`=$wno");
		if ($workspace['workstart_date'] != '1970-01-01' && $workspace['workend_date'] != '1970-01-01') {
			$workpercent = sprintf('%0.2f',(time()-strtotime($workspace['workstart_date']))/(strtotime($workspace['workend_date'])-strtotime($workspace['workstart_date']))*100);
			$workpercent = $workpercent < 0 ? '0.00' : $workpercent;
			$workpercent = $workpercent > 100 ? '100' : $workpercent;
		} else {
			$workpercent = '0.00';
		}

		return $workpercent;
	}

	// 현장 금액
	function GetWorkspaceCost($wno,$type,$gno=0,$tno=0) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],array($type),"where `idx`=$wno");
		if (isset($workspace[$type]) == true) {
			if ($gno == 0 && $tno == 0) {
				$cost = $this->mDB->DBfetch($this->table['cost'],array('price'),"where `idx`={$workspace[$type]}");
				return isset($cost['price']) == true ? $cost['price'] : 0;
			} else {
				$find = "where `repto`=$wno";
				if ($gno) $find.= " and `gno`=$gno";
				if ($tno) $find.= " and `tno`=$tno";

				$cost = $this->mDB->DBfetch($this->table['cost_item'],array('SUM(price)'),$find);
				return isset($cost[0]) == true ? $cost[0] : 0;
			}
		} else {
			return 0;
		}
	}

	// 현장 금액 통계
	function GetWorkspaceCostStatus($wno,$type,$date) {
		$check = array();

		if (isset($check['idx']) == false || GetTime('Y-m') == $date) {
			if ($type == 'monthly_payment') {
				$status = $this->mDB->DBfetch($this->table['monthly_payment'],array('SUM(price)'),"where `wno`=$wno and `date`='$date'");
				$status = isset($status[0]) == true ? $status[0] : 0;
			}
		}

		return $status;
	}

	// 일일상황일지 체크
	function CheckWorkReport($wno,$date) {
		$checked = true;
		$saveData = $this->mDB->DBfetch($this->table['workreport'],array('weather','data'),"where `wno`=$wno and `date`='$date'");
		if (isset($saveData['data']) == true) {
			$check = unserialize($saveData['data']);

			$checked = true;
			$point = 0;

			$data = $this->mDB->DBfetchs($this->table['attend_member'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
			for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
				if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'member') {
					$checked = false;
					break;
				}
				$point++;
			}

			if ($checked == true) {
				$data = $this->mDB->DBfetchs($this->table['attend_dayworker'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'dayworker') {
						$checked = false;
						break;
					}
					$point++;
				}
			}

			if ($check == true) {
				$data = $this->mDB->DBfetchs($this->table['attend_outsourcing'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'outsourcing') {
						$checked = false;
						break;
					}
					$point++;
				}
			}

			if ($check == true) {
				$data = $this->mDB->DBfetchs($this->table['itemorder_income'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'itemorder') {
						$checked = false;
						break;
					}
					$point++;
				}
			}

			if ($check == true) {
				$data = $this->mDB->DBfetchs($this->table['payment_item'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'item') {
						$checked = false;
						break;
					}
					$point++;
				}
			}

			if ($check == true) {
				$data = $this->mDB->DBfetchs($this->table['payment_expense'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'expense') {
						$checked = false;
						break;
					}
					$point++;
				}
			}

			if ($check == true) {
				$data = $this->mDB->DBfetchs($this->table['payment_equipment'],'*',"where `wno`=$wno and `date`='$date'",'idx,asc');
				for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
					if ($check[$point]['idx'] != $data[$i]['idx'] && $check[$point]['type'] != 'equipment') {
						$checked = false;
						break;
					}
					$point++;
				}
			}

			return $checked;
		} else {
			return false;
		}
	}

	// 기본 공정
	function GetBaseWorkgroup($bgno=0) {
		$basegroup = $this->mDB->DBfetch($this->table['base_workgroup'],array('workgroup'),"where `idx`=$bgno");
		return isset($basegroup['workgroup']) ==  true ? $basegroup['workgroup'] : '';
	}

	// 기본 공종타입
	function GetBaseWorktype($btno=0) {
		$basetype = $this->mDB->DBfetch($this->table['base_worktype'],array('worktype'),"where `idx`=$btno");
		return isset($basetype['worktype']) ==  true ? $basetype['worktype'] : '';
	}

	function GetWorkgroupsInBaseWorkgroup($wno,$bgno) {
		$data = array();
		$workgroup = $this->mDB->DBfetchs($this->table['workspace_workgroup'],array('idx'),"where `wno`=$wno and `bgno`=$bgno");
		for ($i=0, $loop=sizeof($workgroup);$i<$loop;$i++) {
			$data[] = $workgroup[$i]['idx'];
		}

		return $data;
	}

	// 공종체크
	function CheckWorktype($wno,$gno,$worktype) {
		$workgroup = $this->mDB->DBfetch($this->table['workspace_workgroup'],array('bgno'),"where `idx`=$gno");
		$check = $this->mDB->DBfetch($this->table['base_worktype'],array('idx'),"where `bgno`={$workgroup['bgno']} and `worktype`='$worktype'");
		if (isset($check['idx']) == true) {
			return $this->mDB->DBcount($this->table['workspace_worktype'],"where `wno`=$wno and `gno`=$gno and `btno`={$check['idx']}") > 0;
		}
	}

	// 현장 공정
	function GetWorkgroup($gno) {
		if ($gno == '-1') return '일반경비';
		$data = $this->mDB->DBfetch($this->table['workspace_workgroup'],array('btno','workgroup'),"where `idx`=$gno");
		if ($data['btno']) {
			$buildtype = $this->mDB->DBfetch($this->table['workspace_buildtype'],array('buildtype'),"where `idx`={$data['btno']}");
			$buildtype = isset($buildtype['buildtype']) == true ? '['.$buildtype['buildtype'].']' : '';
		}
		return isset($data['workgroup']) == true ? $buildtype.$data['workgroup'] : '';
	}

	// 현장 공종명
	function GetWorktype($tno) {
		if ($tno < 0) {
			switch ($tno) {
				case '-1' :
					return '일반관리비';
				break;

				case '-10' :
					return '이윤';
				break;

				case '-11' :
					return '산재보험료';
				break;

				case '-12' :
					return '고용보험료';
				break;

				case '-13' :
					return '국민건강보험료';
				break;

				case '-14' :
					return '국민연금보험료';
				break;

				case '-15' :
					return '노인장기요향보험료';
				break;
			}
		} else {
			$data = $this->mDB->DBfetch($this->table['workspace_worktype'],array('btno'),"where `idx`=$tno");
			return isset($data['btno']) == true ? $this->GetBaseWorktype($data['btno']) : '';
		}
	}

	function GetWorktypeIDX($wno,$gno,$worktype) {
		switch ($worktype) {
			case '일반관리비' :
				return '-1';
			break;

			case '이윤' :
				return '-10';
			break;

			case '산재보험료' :
				return '-11';
			break;

			case '고용보험료' :
				return '-12';
			break;

			case '국민건강보험료' :
				return '-13';
			break;

			case '국민연금보험료' :
				return '-14';
			break;

			case '노인장기요향보험료' :
				return '-15';
			break;

			default :
				$workgroup = $this->mDB->DBfetch($this->table['workspace_workgroup'],array('bgno'),"where `idx`=$gno");
				if (isset($workgroup['bgno']) == true) {
					$baseWorktype = $this->mDB->DBfetch($this->table['base_worktype'],array('idx'),"where `bgno`={$workgroup['bgno']} and `worktype`='$worktype'");
					$worktype = $this->mDB->DBfetch($this->table['workspace_worktype'],array('idx'),"where `gno`=$gno and `btno`={$baseWorktype['idx']}");

					if (isset($worktype['idx']) == true) return $worktype['idx'];
					else return false;
				} else {
					return false;
				}
			break;
		}
	}

	function GetBuildtype($btno) {
		if ($btno == '0') return;
		$data = $this->mDB->DBfetch($this->table['workspace_buildtype'],array('buildtype'),"where `idx`=$btno");
		return isset($data['buildtype']) == true ? $data['buildtype'] : '';
	}

	// 공종명 추가
	function InsertWorktype($gno,$worktype) {
		$workgroup = $this->mDB->DBfetch($this->table['workspace_workgroup'],array('wno','bgno'),"where `idx`=$gno");
		$check = $this->mDB->DBfetch($this->table['base_worktype'],array('idx'),"where `bgno`={$workgroup['bgno']} and `worktype`='$worktype'");
		if (isset($check['idx']) == false) {
			$btno = $this->mDB->DBinsert($this->table['base_worktype'],array('bgno'=>$workgroup['bgno'],'worktype'=>$worktype));
		} else {
			$btno = $check['idx'];
		}

		$check = $this->mDB->DBfetch($this->table['workspace_worktype'],array('idx'),"where `gno`=$gno and `btno`='$btno'");
		if (isset($check['idx']) == true) {
			return $check['idx'];
		} else {
			$sort = $this->mDB->DBfetch($this->table['workspace_worktype'],array('MAX(sort)'),"where `wno`={$workgroup['wno']} and `gno`=$gno");
			$sort = $sort[0] + 1;
			return $this->mDB->DBinsert($this->table['workspace_worktype'],array('wno'=>$workgroup['wno'],'gno'=>$gno,'btno'=>$btno,'sort'=>$sort));
		}
	}

	/************************************************************************************************
	 * 자재관리
	 ***********************************************************************************************/
	// 자재코드
	function GetItemcode($title,$size,$unit) {
		return sha1(str_replace(' ','',$title.'.'.$size.'.'.$unit));
	}

	// 자재검색
	function GetFindItem($itemcode) {
		return $this->mDB->DBcount($this->table['item'],"where `itemcode`='$itemcode'") > 0;
	}

	// 현장자재코드
	function GetItemUniqueCode($gno=0,$tno=0,$itemcode) {
		return sha1($gno.'.'.$tno.'.'.$itemcode);
	}

	// 도급내역검색
	function GetFindContractItem($wno=0,$code) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],array('contract'),"where `idx`=$wno");
		return $this->mDB->DBcount($this->table['cost_item'],"where `repto`={$workspace['contract']} and `code`='$code'") > 0;
	}

	// 도급내역
	function GetContractItem($wno=0,$code) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],array('contract'),"where `idx`=$wno");
		return $this->mDB->DBfetch($this->table['cost_item'],'*',"where `repto`={$workspace['contract']} and `code`='$code'");
	}

	// 실행내역
	function GetExecItem($wno=0,$code) {
		$workspace = $this->mDB->DBfetch($this->table['workspace'],array('exec'),"where `idx`=$wno");
		return $this->mDB->DBfetch($this->table['cost_item'],'*',"where `repto`={$workspace['exec']} and `code`='$code'");
	}

	// 가격코드
	function GetItemPriceCode($cost1,$cost2,$cost3) {
		return sha1($cost1.'.'.$cost2.'.'.$cost3);
	}

	// 자재 평균가
	function GetItemAvgCost($itemcode,$cost) {
		$data = $this->mDB->DBfetchs($this->table['item_cost'],'*',"where `itemcode`='$itemcode'",'reg_date,desc','0,10');

		$type = array('COST'=>'실행','ESTIMATE'=>'견적','CONTRACT'=>'계약','ORDER'=>'발주');
		$avgcost = array();
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$avgcost[] = $this->GetWorkspaceTitle($data[$i]['wno']).'@'.$data[$i][$cost].'@'.GetTime('Y.m.d',$data[$i]['reg_date']).'@'.$type[$data[$i]['type']];
		}

		return implode(',',$avgcost);
	}

	// 자재추가
	function InsertItem($tno=0,$title,$size,$unit) {
		$itemcode = $this->GetItemcode($title,$size,$unit);

		$worktype = $this->mDB->DBfetch($this->table['workspace_worktype'],array('gno','btno'),"where `idx`=$tno");
		if (isset($worktype['gno']) == true) {
			$workgroup = $this->mDB->DBfetch($this->table['workspace_workgroup'],array('bgno'),"where `idx`={$worktype['gno']}");

			$insert = array();
			$insert['itemcode'] = $itemcode;
			$insert['search'] = GetUTF8Divide($title);
			$insert['bgno'] = $workgroup['bgno'];
			$insert['btno'] = $worktype['btno'];
			$insert['title'] = $title;
			$insert['size'] = $size;
			$insert['unit'] = $unit;

			if ($this->mDB->DBcount($this->table['item'],"where `itemcode`='{$insert['itemcode']}' and `bgno`={$insert['bgno']} and `btno`={$insert['btno']}") == 0) {
				$this->mDB->DBinsert($this->table['item'],$insert);
			}
		}
	}

	// 자재평균가 입력
	function InsertItemAvgCost($itemcode,$type,$wno,$cost1=0,$cost2=0,$cost3=0) {
		$check = $this->mDB->DBfetch($this->table['item_cost'],array('idx'),"where `itemcode`='$itemcode' and `type`='$type' and `wno`=$wno and `cost1`=$cost1 and `cost2`=$cost2 and `cost3`=$cost3");
		if (isset($check['idx']) == true) {
			$this->mDB->DBupdate($this->table['item_cost'],array('reg_date'=>GetGMT()),'',"where `idx`={$check['idx']}");
		} else {
			$this->mDB->DBinsert($this->table['item_cost'],array('itemcode'=>$itemcode,'wno'=>$wno,'type'=>$type,'cost1'=>$cost1,'cost2'=>$cost2,'cost3'=>$cost3,'reg_date'=>GetGMT()));
		}

		$start_date = GetGMT()-60*60*24*365;
		$avgcost = $this->mDB->DBfetch($this->table['item_cost'],array('COUNT(*)','SUM(cost1)','SUM(cost2)','SUM(cost3)'),"where `itemcode`='$itemcode' and reg_date>=$start_date");
		$this->mDB->DBupdate($this->table['item'],array('avgcost1'=>round($avgcost[1]/$avgcost[0]),'avgcost2'=>round($avgcost[2]/$avgcost[0]),'avgcost3'=>round($avgcost[3]/$avgcost[0])),'',"where `itemcode`='$itemcode'");
	}

	// 발주현황
	function GetOrderStatus($wno,$code) {
		$data = $this->mDB->DBfetch($this->table['item_status'],array('idx','ea1','ea2','ea3'),"where `wno`=$wno and `code`='$code'");

		if (isset($data['idx']) == true) {
			return $data['idx'].','.$data['ea1'].','.$data['ea2'].','.$data['ea3'];
		} else {
			return '0,0,0,0';
		}
	}

	// 발주현황 기록
	function InsertOrderStatus($wno,$itemcode,$code,$ea1,$ea2,$ea3) {
		$check = $this->mDB->DBfetch($this->table['item_status'],array('idx'),"where `wno`=$wno and `code`='$code'");

		if (isset($check['idx']) == true) {
			$this->mDB->DBupdate($this->table['item_status'],'',array('ea1'=>'`ea1`+'.$ea1,'ea2'=>'`ea2`+'.$ea2,'ea3'=>'`ea3`+'.$ea3),"where `idx`={$check['idx']}");
		} else {
			$this->mDB->DBinsert($this->table['item_status'],array('wno'=>$wno,'itemcode'=>$itemcode,'code'=>$code,'ea1'=>$ea1,'ea2'=>$ea2,'ea3'=>$ea3));
		}
	}

	// 협력업체이름
	function GetCooperationTitle($cno) {
		$cooperation = $this->mDB->DBfetch($this->table['cooperation'],array('title'),"where `idx`=$cno");
		return $cooperation['title'];
	}

	// 원도급금액합계
	function GetOriginalPrice($wno,$original) {
		if (is_array($origianl) == false) $original = unserialize($original);

		$original_price = 0;
		for ($j=0, $loopj=sizeof($original);$j<$loopj;$j++) {
			// 도급내역에서 검색
			$original_contract = $this->GetContractItem($wno,$this->GetItemUniqueCode($original[$j]['gno'],$original[$j]['tno'],$this->GetItemcode($original[$j]['title'],$original[$j]['size'],$original[$j]['unit'])));
			$original_price+= floor((($original[$j]['cost1'] == 'TRUE' ? $original_contract['cost1'] : 0)+($original[$j]['cost2'] == 'TRUE' ? $original_contract['cost2'] : 0)+($original[$j]['cost3'] == 'TRUE' ? $original_contract['cost3'] : 0))*$original[$j]['ea']);
		}

		return $original_price;
	}

	function PrintError($msg,$button='') {
		REQUIRE_ONCE $this->modulePath.'/error.php';
		exit;
	}

	function CheckWorkspaceMaster($wno='') {
		if ($this->mMember->IsLogged() == true && $this->member['type'] == 'ADMINISTRATOR') {
			return true;
		} elseif ($this->mMember->IsLogged() == true) {
			$wno = $wno ? $wno : $this->wno;

			if ($wno == null) {
				$this->PrintError('현장번호가 전달되지 않았습니다.<br />example.php?wno=[현장번호] 형식으로 현장번호를 전달하여 주십시오.');
			}

			$check = $this->mDB->DBfetch($this->table['workspace'],array('master'),"where `idx`=$wno");

			if (in_array($this->member['idx'],explode(',',$check['master'])) == true) {
				return true;
			} else {
				return false;
			}
		} else return false;
	}

	function GetDefaultTax() {
		return array('10.4','3.7','0.69','1.49','2.43','4.78','1.81','3','3.5','10');
	}

	function GetWorkspaceTax($wno) {
		return array('10.4','3.9','0.69','1.49','2.43','4.78','1.81','3','3.5','10');
	}
}
?>