<?php
REQUIRE_ONCE '../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$get = Request('get');
$start = Request('start');
$limit = Request('limit');
$sort = Request('sort');
$dir = Request('dir') ? Request('dir') : 'desc';
$limiter = $start != null && $limit != null ? $start.','.$limit : '';
$orderer = $sort != null && $dir != null ? $sort.','.$dir : '';

$lists = array();

if ($action == 'module') {
	if ($get == 'list') {
		$modulePath = @opendir($_ENV['path'].'/module');
		$i = 0;
	
		while ($module = @readdir($modulePath)) {
			if ($module != '.' && $module != '..' && is_dir($_ENV['path'].'/module/'.$module) == true) {
				$mModule = new Module($module);
				
				if ($mModule->IsSetup() == true) {
					$db = $mDB->DBfetch($_ENV['table']['module'],'*',"where `module`='$module'");
				} else {
					$db = array('version'=>'');
				}
				
				$lists[$i] = array();
				$lists[$i]['module'] = $module;
				$lists[$i]['title'] = $mModule->GetModuleXML('title').'모듈';
				$lists[$i]['version'] = $mModule->GetModuleXML('version');
				$lists[$i]['db'] = $db['version'];
				$lists[$i]['folder'] = $mModule->CheckFolder() == true ? 'TRUE' : 'FALSE';
				$lists[$i]['dbsize'] = $mModule->GetDatabaseSize();
				$lists[$i]['filesize'] = $mModule->GetFolderSize();
				$lists[$i]['is_setup'] = $mModule->GetModuleXML('is_setup') == 'TRUE' ? ($mModule->IsSetup() == true ? 'TRUE' : 'FALSE') : 'DISABLE';
				$lists[$i]['is_config'] = $mModule->IsConfig() == true ? 'TRUE' : 'FALSE';
				$lists[$i]['is_manager'] = $mModule->GetModuleXML('is_manager') == 'TRUE' ? 'TRUE' : 'FALSE';
				$lists[$i]['is_direct'] = $mModule->GetModuleXML('is_setup') == 'TRUE' && $mModule->IsSetup() == true ? $mModule->GetAdminTop() : 'FALSE';
				$lists[$i]['path'] = $mModule->GetModulePath();
				
				if ($lists[$i]['is_setup'] == 'TRUE') {
					$mDB->DBupdate($_ENV['table']['module'],array('is_admin'=>$lists[$i]['is_manager']),'',"where `module`='{$lists[$i]['module']}'");
				}
				$i++;
			}
		}
		@closedir($skinPath);
	}
	
	if ($get == 'direct') {
		$lists = $mDB->DBfetchs($_ENV['table']['module'],array('module','name','sort','version'),"where `is_admin_top`='TRUE' and `is_admin`='TRUE'",'sort,asc');
		
		for ($i=0, $loop=sizeof($lists);$i<$loop;$i++) {
			$lists[$i]['title'] = $lists[$i]['name'].'모듈';
			$lists[$i]['path'] = $_ENV['path'].'/module/'.$lists[$i]['module'];
			
			$mDB->DBupdate($_ENV['table']['module'],array('sort'=>$i),'',"where `module`='{$lists[$i]['module']}'");
		}
	}
	
	if ($get == 'config') {
		$module = Request('module');
		$mModule = new Module($module);
		$return['success'] = true;
		$return['data'] = $mModule->GetConfig();
		exit(json_encode($return));
	}
}

if ($action == 'status') {
	$mStatus = new Status();
	$get = Request('get');

	if ($get == 'log_visit') {
		$date = Request('date');
		$find = "where `date`='$date'";
		$type = Request('type');
		if ($type == 'MEMBER') $find.= " and `mno`!=0";

		$data = $mDB->DBfetchs($mStatus->table['log_visit'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($mStatus->table['log_visit'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			if ($data[$i]['mno'] != 0) {
				$nickname = $mMember->GetMemberName($data[$i]['mno'],'nickname',false);
			} else {
				$nickname = '';
			}
			$list[$i] = '{';
			$list[$i].= '"visit_time":"'.GetTime('Y.m.d H:i:s',$data[$i]['visit_time']).'",';
			$list[$i].= '"pageurl":"'.GetString($data[$i]['pageurl'],'ext').'",';
			$list[$i].= '"refererurl":"'.GetString($data[$i]['refererurl'],'ext').'",';
			$list[$i].= '"ip":"'.$data[$i]['ip'].'",';
			$list[$i].= '"nickname":"'.GetString($nickname,'ext').'",';
			$list[$i].= '"user_agent":"'.GetString($data[$i]['user_agent'],'ext').'"';
			$list[$i].= '}';
		}
	}

	if ($get == 'log_bot') {
		$date = Request('date');
		$find = "where `date`='$date'";

		$data = $mDB->DBfetchs($mStatus->table['log_bot'],'*',$find,'visit,desc');
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"botname":"'.$mStatus->GetBotName($data[$i]['botname']).'",';
			$list[$i].= '"avgrevisit":"'.sprintf('%0.2f',($data[$i]['last_time']-$data[$i]['first_time'])/$data[$i]['visit']).'",';
			$list[$i].= '"visit":"'.$data[$i]['visit'].'",';
			$list[$i].= '"last_time":"'.GetTime('Y.m.d H:i:s',$data[$i]['last_time']).'",';
			$list[$i].= '"last_url":"'.GetString($data[$i]['last_url'],'ext').'"';
			$list[$i].= '}';
		}
	}
	
	if ($get == 'referer') {
		$date = Request('date');
		$find = "where `date`='$date'";
		$keyword = Request('keyword');
		if ($keyword != null) $find.= " and `referer` like '%$keyword%'";

		$data = $mDB->DBfetchs($mStatus->table['referer'],'*',$find,$orderer,$limiter);
		$total = $mDB->DBcount($mStatus->table['referer'],$find);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$list[$i] = '{';
			$list[$i].= '"visit_time":"'.GetTime('Y.m.d H:i:s',$data[$i]['visit_time']).'",';
			$list[$i].= '"keyword":"'.GetString($data[$i]['keyword'],'ext').'",';
			$list[$i].= '"refererurl":"'.GetString($data[$i]['refererurl'],'ext').'",';
			$list[$i].= '"ip":"'.GetString($data[$i]['ip'],'ext').'"';
			$list[$i].= '}';
		}
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>