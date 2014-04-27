<?php
REQUIRE_ONCE '../config/default.conf.php';

$action = Request('action');

if ($action == 'install') {
	function InstallError($msg) {
		unset($_SESSION['isInstalling']);
		echo '<script type="text/javascript">alert("'.$msg.'"); parent.ErrorInstall();</script>';
		GetDefaultFooter();
		exit;
	}
	
	if (Request('key','session') == null || Request('db','session') == null) {
		Alertbox('설치정보가 잘못되었습니다.\\n아이모듈 설치를 처음부터 다시 시작합니다.',3,'../install.php?step=1','parent');
	}
	
	$user_id = CheckUserID(Request('user_id')) == true ? strtolower(Request('user_id')) : InstallError('회원아이디를 정확하게 입력하여 주십시오.');
	$password = strlen(Request('password')) > 0 ? md5(strtolower(Request('password'))) : InstallError('패스워드를 입력하여 주십시오.');
	$name = Request('name') ? Request('name') : InstallError('이름을 입력하여 주십시오.');
	$nickname = Request('nickname') ? Request('nickname') : InstallError('닉네임을 입력하여 주십시오.');
	$email = CheckEmail(Request('email')) == true ? Request('email') : InstallError('이메일을 정확하게 입력하여 주십시오.');

	if (Request('isInstalling','session') == 'TRUE') InstallError('설치가 진행중입니다.\\n잠시만 기다려주십시오.');
	$_SESSION['isInstalling'] = 'TRUE';

	if (isset($_ENV['key']) == false) {
		$keyFile = @fopen('../config/key.conf.php','w') or InstallError('KEY 설정파일을 생성할 수 없습니다.');
		@fwrite($keyFile,"<?php /*\n".Request('key','session')."\n*/ ?>");
		@fclose($keyFile);
		@chmod('../config/key.conf.php',0707);
	}
	
	if (isset($_ENV['db']) == false) {
		$dbFile = @fopen('../config/db.conf.php','w') or InstallError('DB 설정파일을 생성할 수 없습니다.');
		@fwrite($dbFile,"<?php /*\nmysql\n".Request('db','session')."\n*/ ?>");
		@fclose($dbFile);
		@chmod('../config/db.conf.php',0707);
	}
	
	if (is_dir($_ENV['userfilePath'].'/temp') == false) {
		@mkdir($_ENV['userfilePath'].'/temp') or InstallError('TEMP폴더를 생성할 수 없습니다.');
		@chmod($_ENV['userfilePath'].'/temp',0707);
	}
	
	if (is_dir($_ENV['userfilePath'].'/log') == false) {
		@mkdir($_ENV['userfilePath'].'/log') or InstallError('LOG폴더를 생성할 수 없습니다.');
		@chmod($_ENV['userfilePath'].'/log',0707);
	}
	
	$mDB = &DB::instance();
	
	$XMLData = file_get_contents($_ENV['path'].'/index.xml');
	$XML = new SimpleXMLElement($XMLData);
	
	$table = $XML->database->table;
	for ($i=0, $loop=sizeof($table);$i<$loop;$i++) {
		$tablename = str_replace('{code}',$_ENV['code'],(string)($table[$i]->attributes()->name));

		$field = $table[$i]->field;
		$fields = array();
		for ($j=0, $loopj=sizeof($field);$j<$loopj;$j++) {
			$fields[$j] = array('name'=>(string)($field[$j]->attributes()->name),'type'=>(string)($field[$j]->attributes()->type),'length'=>(string)($field[$j]->attributes()->length),'default'=>(string)($field[$j]->attributes()->default),'comment'=>(string)($field[$j]));
		}
		
		$index = $table[$i]->index;
		$indexes = array();
		for ($j=0, $loopj=sizeof($index);$j<$loopj;$j++) {
			$indexes[$j] = array('name'=>(string)($index[$j]->attributes()->name),'type'=>(string)($index[$j]->attributes()->type),'comment'=>(string)($index[$j]));
		}
		
		if ($mDB->DBFind($tablename) == false) {
			if ($mDB->DBcreate($tablename,$fields,$indexes) == true) {
				$data = isset($table[$i]->data) == true ? $table[$i]->data : array();
				
				for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
					$insert = array_pop(array_values((array)($data[$j]->attributes())));
					$mDB->DBinsert($tablename,$insert);
				}
			}
		}
	}
	
	$mModule = new Module('member');
	$mModule->Install();
	
	$mDB->DBinsert($_ENV['table']['member'],array('type'=>'ADMINISTRATOR','group'=>'default','user_id'=>$user_id,'password'=>$password,'name'=>$name,'nickname'=>$nickname,'email'=>$email));
	Redirect('../install.php?step=6','parent');
	GetDefaultFooter();
}

if ($action == 'update') {
	$mDB = &DB::instance();
	$mMember = &Member::instance();
	$member = $mMember->GetMemberInfo();

	if ($member['type'] != 'ADMINISTRATOR') {
		Alertbox('최고관리자만 가능합니다.');
	}
	
	$XMLData = file_get_contents($_ENV['path'].'/index.xml');
	$XML = new SimpleXMLElement($XMLData);
	
	$table = $XML->database->table;
	for ($i=0, $loop=sizeof($table);$i<$loop;$i++) {
		$tablename = str_replace('{code}',$_ENV['code'],(string)($table[$i]->attributes()->name));

		$field = $table[$i]->field;
		$fields = array();
		for ($j=0, $loopj=sizeof($field);$j<$loopj;$j++) {
			$fields[$j] = array('name'=>(string)($field[$j]->attributes()->name),'type'=>(string)($field[$j]->attributes()->type),'length'=>(string)($field[$j]->attributes()->length),'default'=>(string)($field[$j]->attributes()->default),'comment'=>(string)($field[$j]));
		}
		
		$index = $table[$i]->index;
		$indexes = array();
		for ($j=0, $loopj=sizeof($index);$j<$loopj;$j++) {
			$indexes[$j] = array('name'=>(string)($index[$j]->attributes()->name),'type'=>(string)($index[$j]->attributes()->type),'comment'=>(string)($index[$j]));
		}
		
		if ($mDB->DBFind($tablename) == true) {
			if ($mDB->DBcompare($tablename,$fields,$indexes) == false) {
				if ($mDB->DBFind($tablename.'(NEW)') == true) {
					$mDB->DBdrop($tablename.'(NEW)');
				}
							
				if ($mDB->DBcreate($tablename.'(NEW)',$fields,$indexes) == true) {
					$data = $mDB->DBfetchs($tablename,'*');
					for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
						$insert = array();
						for ($k=0, $loopk=sizeof($fields);$k<$loopk;$k++) {
							if (isset($data[$j][$fields[$k]['name']]) == true) $insert[$fields[$k]['name']] = $data[$j][$fields[$k]['name']];
						}
						
						$mDB->DBinsert($tablename.'(NEW)',$insert);
					}
					
					$mDB->DBrename($tablename,$tablename.'(BK'.date('YmdHis').')');
					$mDB->DBrename($tablename.'(NEW)',$tablename);
				}
			}
		} else {
			if ($mDB->DBcreate($tablename,$fields,$indexes) == true) {
				$data = isset($table[$i]->data) == true ? $table[$i]->data : array();
				
				for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
					$insert = array_pop(array_values((array)($data[$j]->attributes())));
					$mDB->DBinsert($tablename,$insert);
				}
			}
		}
	}

	Alertbox('업데이트를 완료하였습니다.',3,'reload','parent');
}
?>