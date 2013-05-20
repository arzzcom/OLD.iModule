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

if ($mMember->IsAdmin() == false) {
	$return['success'] = false;
	exit(json_encode($return));
}

if ($action == 'module') {
	if ($get == 'list') {
		$calcSize = Request('calcSize') == 'true';
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
				$lists[$i]['dbsize'] = $mModule->GetDatabaseSize($calcSize);
				$lists[$i]['filesize'] = $mModule->GetFileSize($calcSize);
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
	
	if ($get == 'managerlist') {
		$lists = $mDB->DBfetchs($_ENV['table']['module'],array('module','name'),"where `is_admin`='TRUE'");
	}
	
	if ($get == 'config') {
		$module = Request('module');
		$mModule = new Module($module);
		$return['success'] = true;
		$return['data'] = $mModule->GetConfig() == false ? array() : $mModule->GetConfig();
		exit(json_encode($return));
	}
}

$return = array();
$return['totalCount'] = isset($total) == true ? $total : sizeof($lists);
$return['lists'] = $lists;

exit(json_encode($return));
?>