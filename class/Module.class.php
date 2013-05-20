<?php
class Module {
	protected $mDB;
	protected $mMember;
	protected $member;
	protected $module;
	protected $moduleXML;
	protected $moduleInfo;
	protected $adminTop;
	public $moduleName;
	public $mIPBan;
	public $modulePath;
	public $moduleDir;
	public $baseURL;

	function __construct($module) {
		$this->moduleName = $module;
		$this->mDB = &DB::instance();
		
		$this->mMember = &Member::instance();
		$this->member = $this->mMember->GetMemberInfo();
		$this->mIPBan = &IPBan::instance();

		$this->modulePath = $_ENV['path'].'/module/'.$module;
		$this->moduleDir = $_ENV['dir'].'/module/'.$module;
		$this->baseURL = array_shift(explode('?',$_SERVER['REQUEST_URI']));

		$this->moduleInfo = $this->mDB->DBfetch($_ENV['table']['module'],array('version','dbsize','filesize','config','is_admin_top'),"where `module`='$module'");
		$this->module = isset($this->moduleInfo['config']) == true ? ($this->moduleInfo['config'] ? unserialize($this->moduleInfo['config']) : array()) : false;
		$this->moduleXML = null;
		$this->adminTop = $this->moduleInfo['is_admin_top'];
	}

	function IsSetup() {
		if ($this->module === false) return false;
		else return true;
	}
	
	function IsConfig() {
		if ($this->GetModuleXML('is_config') == 'TRUE' || file_exists($this->modulePath.'/admin/default.php') == true) return true;
		else false;
	}

	function IsAdmin() {
		if ($this->member['type'] == 'ADMINISTRATOR') {
			return true;
		} else {
			return false;
		}
	}
	
	function Install($config='',$isTop=false) {
		if ($this->GetModuleXML('is_setup') == 'TRUE' && $this->IsSetup() == false) {
			$database = $this->GetModuleXML('database');
		
			if ($database) {
				$table = $database->table;
			
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
					
					if ($this->mDB->DBFind($tablename) == true) {
						$this->mDB->DBremove($tablename);
					}
					
					if ($this->mDB->DBcreate($tablename,$fields,$indexes) == true) {
						$data = isset($table[$i]->data) == true ? $table[$i]->data : array();
						
						for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
							$insert = array_pop(array_values((array)($data[$j]->attributes())));
							$this->mDB->DBinsert($tablename,$insert);
						}
					}
				}
			}
			
			$folder = $this->GetModuleXML('folder');

			if ($folder) {
				$root = $folder->attributes()->root;
				$path = $folder->path;
				for ($i=0, $loop=sizeof($path);$i<$loop;$i++) {
					CreateDirectory($_ENV['userfilePath'].'/'.$root.'/'.$path[$i]);
				}
			}
			
			if ($isTop == true) {
				$sort = $this->mDB->DBcount($_ENV['table']['module'],"where `is_admin_top`='TRUE'");
			} else {
				$sort = 0;
			}
			
			if ($config == '' && $this->GetModuleXML('is_config') == 'TRUE') {
				$configs = $this->GetModuleXML()->config->set;
				
				$default_config = array();
				for ($i=0, $loop=sizeof($configs);$i<$loop;$i++) {
					foreach ($configs[$i] as $name=>$conf) {
						$default_config[$name] = (string)$conf->default;
					}
				}
				
				$config = serialize($default_config);
			}
			
			$this->mDB->DBinsert($_ENV['table']['module'],array('module'=>$this->moduleName,'name'=>$this->GetModuleXML('title'),'version'=>$this->GetModuleXML('version'),'config'=>$config,'is_admin'=>$this->GetModuleXMl('is_manager'),'is_admin_top'=>($isTop == true ? 'TRUE' : 'FALSE'),'sort'=>$sort));
			
			$this->GetFileSize(true);
			$this->GetDatabaseSize(true);
		}
	}

	function GetModulePath() {
		return $this->modulePath;
	}
	
	function GetModuleName() {
		return $this->moduleName;
	}

	function GetModuleDir() {
		return $this->moduleDir;
	}

	function GetConfig($config='') {
		if ($config) {
			return isset($this->module[$config]) == true ? $this->module[$config] : '';
		} else {
			if ($this->IsSetup() == false) {
				$configs = $this->GetModuleXML()->config->set;
				
				$default_config = array();
				for ($i=0, $loop=sizeof($configs);$i<$loop;$i++) {
					foreach ($configs[$i] as $name=>$conf) {
						$default_config[$name] = (string)$conf->default;
					}
				}
				return $default_config;
			} else {
				return $this->module;
			}
		}
	}

	function GetAdminTop() {
		return $this->adminTop;
	}

	function SetConfig($config) {
		/*
		$a = array();
		$a['signin'] = '/index.php?page=main&menu=signin';
		$a['signin_redirect'] = '/index.php';
		$a['signin_alert'] = 'TRUE';
		$a['default_point'] = '2000';
		$a['default_exp'] = '0';

		echo base64_encode(serialize($a));
		*/
		$config = serialize($config);
		$this->mDB->DBupdate($_ENV['table']['module'],array('config'=>$config),"where `module`='{$this->module}'");
	}

	function GetModuleXML($tag='') {
		if ($this->moduleXML == null) {
			$XMLData = file_get_contents($this->modulePath.'/index.xml');
			$this->moduleXML = new SimpleXMLElement($XMLData);
		}

		switch($tag) {
			case 'title' :
				return (string)($this->moduleXML->title);
			break;

			case 'version' :
				return (string)($this->moduleXML->version);
			break;

			case 'is_setup' :
				return (string)($this->moduleXML->setup);
			break;

			case 'is_config' :
				if ((string)($this->moduleXML->config)) {
					return 'TRUE';
				} else {
					return 'FALSE';
				}
			break;

			case 'is_manager' :
				return (string)($this->moduleXML->manager);
			break;

			case 'config' :
				if ((string)($this->moduleXML->config)) {
					return $this->moduleXML->config->children()->set;
				} else {
					return array();
				}
			break;
			
			case 'folder' :
				return $this->moduleXML->folder;
			break;
			
			case 'database' :
				return $this->moduleXML->database;
			break;

			default :
				return $this->moduleXML;
		}
	}
	
	function CheckFolder() {
		if ($this->IsSetup() == false) return false;
		
		$folder = $this->GetModuleXML('folder');
		
		if ($folder) {
			$root = $folder->attributes()->root;
			if (is_dir($_ENV['userfilePath'].'/'.$root) == false || substr(sprintf('%o',fileperms($_ENV['userfilePath'].'/'.$root)),-4) != '0707') {
				return false;
			}
			$path = $folder->path;
			for ($i=0, $loop=sizeof($path);$i<$loop;$i++) {
				if (is_dir($_ENV['userfilePath'].'/'.$root.'/'.$path[$i]) == false || substr(sprintf('%o',fileperms($_ENV['userfilePath'].'/'.$root.'/'.$path[$i])),-4) != '0707') {
					return false;
				}
			}
		}
		
		return true;
	}
	
	function GetFileSize($is_calc=false) {
		if ($this->IsSetup() == false) return 0;
		if ($is_calc == false) return $this->moduleInfo['filesize'];
		
		$folder = $this->GetModuleXML('folder');
		$totalsize = 0;
		
		if ($folder) {
			$root = $folder->attributes()->root;
			if (function_exists('exec') == true) {
				$totalsize = intval(exec('du -sb '.$_ENV['userfilePath'].'/'.$root));
			}
		}
		
		$this->mDB->DBupdate($_ENV['table']['module'],array('filesize'=>$totalsize),'',"where `module`='{$this->moduleName}'");
		
		return $totalsize;
	}
	
	function GetDatabaseSize($is_calc=false) {
		if ($this->IsSetup() == false) return 0;
		if ($is_calc == false) return $this->moduleInfo['dbsize'];
		$totalsize = 0;
		
		$database = $this->GetModuleXML('database');
		if ($database) {
			$table = $database->table;
			
			for ($i=0, $loop=sizeof($table);$i<$loop;$i++) {
				$tablename = str_replace('{code}',$_ENV['code'],(string)($table[$i]->attributes()->name));
				$totalsize+= $this->mDB->DBfind($tablename) == true ? $this->mDB->DBsize($tablename) : 0;
			}
		}
		
		$this->mDB->DBupdate($_ENV['table']['module'],array('dbsize'=>$totalsize),'',"where `module`='{$this->moduleName}'");
		
		return $totalsize;
	}
	
	function GetDBVersion() {
		return $this->IsSetup() == true ? $this->moduleInfo['version'] : '';
	}
	
	function GetVersionToNumber($ver='') {
		if ($ver == '') return 0;
		$temp = explode('.',$ver);
		
		return $temp[0]*1000+$temp[1]*100+$temp[2];
	}
	
	function CreateDatabase($progress=false) {
		$database = $this->GetModuleXML('database');

		$tableCode = Request('tableCode') ? intval(Request('tableCode')) : 0;
		$dataLimit = Request('dataLimit') ? intval(Request('dataLimit')) : 0;
		
		if ($database) {
			$table = $database->table;
			
			if ($tableCode == sizeof($table)) {
				if (file_exists($this->modulePath.'/update.php') == true) {
					@REQUIRE_ONCE $this->modulePath.'/update.php';
				}
				if ($this->IsSetup() == true) {
					$this->mDB->DBupdate($_ENV['table']['module'],array('version'=>$this->GetModuleXML('version')),'',"where `module`='{$this->moduleName}'");
				} else {
					$this->mDB->DBinsert($_ENV['table']['module'],array('module'=>$this->moduleName,'name'=>$this->GetModuleXML('title'),'version'=>$this->GetModuleXML('version'),'is_admin'=>$this->GetModuleXML('is_manager')));
				}

				if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("",'.$tableCode.','.sizeof($database->table).',0,0); } catch(e) {} </script>';
			} else {
				$table = $table[$tableCode];
				$tablename = str_replace('{code}',$_ENV['code'],(string)($table->attributes()->name));

				$field = $table->field;
				$fields = array();
				for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
					$fields[$i] = array('name'=>(string)($field[$i]->attributes()->name),'type'=>(string)($field[$i]->attributes()->type),'length'=>(string)($field[$i]->attributes()->length),'default'=>(string)($field[$i]->attributes()->default),'comment'=>(string)($field[$i]));
				}
				
				$index = $table->index;
				$indexes = array();
				for ($i=0, $loop=sizeof($index);$i<$loop;$i++) {
					$indexes[$i] = array('name'=>(string)($index[$i]->attributes()->name),'type'=>(string)($index[$i]->attributes()->type));
				}
				
				
				if ($dataLimit == 0) {
					if ($this->mDB->DBFind($tablename) == true) {
						if ($this->mDB->DBcompare($tablename,$fields,$indexes) == false) {
							if ($this->mDB->DBFind($tablename.'(NEW)') == true) {
								$this->mDB->DBremove($tablename.'(NEW)');
							}
							
							if ($this->mDB->DBcreate($tablename.'(NEW)',$fields,$indexes) == true) {
								$total = $this->mDB->DBcount($tablename);
								if ($total > 1000000) $maxLimit = 10000;
								elseif ($total > 500000) $maxLimit = 5000;
								elseif ($total > 100000) $maxLimit = 3000;
								elseif ($total > 50000) $maxLimit = 1500;
								elseif ($total > 10000) $maxLimit = 500;
								else $maxLimit = 100;
								
								$data = $this->mDB->DBfetchs($tablename,'*','',$fields[0]['name'].',asc','0,'.$maxLimit);
								for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
									$insert = array();
									for ($j=0, $loopj=sizeof($fields);$j<$loopj;$j++) {
										if (isset($data[$i][$fields[$j]['name']]) == true) $insert[$fields[$j]['name']] = $data[$i][$fields[$j]['name']];
									}
									
									$this->mDB->DBinsert($tablename.'(NEW)',$insert);
								}
								
								if ($total < $dataLimit+$maxLimit) {
									$this->mDB->DBname($tablename,$tablename.'(BK'.date('YmdHis').')');
									$this->mDB->DBname($tablename.'(NEW)',$tablename);
									if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($tableCode+1).','.sizeof($database->table).','.$total.','.$total.'); } catch(e) {} </script>';
									Redirect($_SERVER['PHP_SELF'].GetQueryString(array('tableCode'=>$tableCode+1,'dataLimit'=>0),'',false));
								} else {
									if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($tableCode+1).','.sizeof($database->table).','.$dataLimit.','.$total.'); } catch(e) {} </script>';
									Redirect($_SERVER['PHP_SELF'].GetQueryString(array('tableCode'=>$tableCode,'dataLimit'=>$dataLimit+$maxLimit),'',false));
								}
							}
						} else {
							if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($tableCode+1).','.sizeof($database->table).',0,0); } catch(e) {} </script>';
							Redirect($_SERVER['PHP_SELF'].GetQueryString(array('tableCode'=>$tableCode+1,'dataLimit'=>0),'',false));
						}
					} else {
						if ($this->mDB->DBcreate($tablename,$fields,$indexes) == true) {
							$data = isset($table->data) == true ? $table->data : array();
							
							for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
								$insert = array_pop(array_values((array)($data[$j]->attributes())));
								$this->mDB->DBinsert($tablename,$insert);
							}
							if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($tableCode+1).','.sizeof($database->table).',0,0); } catch(e) {} </script>';
							Redirect($_SERVER['PHP_SELF'].GetQueryString(array('tableCode'=>$tableCode+1,'dataLimit'=>0),'',false));
						}
					}
				} else {
					$total = $this->mDB->DBcount($tablename);
					if ($total > 1000000) $maxLimit = 10000;
					elseif ($total > 500000) $maxLimit = 5000;
					elseif ($total > 100000) $maxLimit = 3000;
					elseif ($total > 50000) $maxLimit = 1500;
					elseif ($total > 10000) $maxLimit = 500;
					else $maxLimit = 100;
					$data = $this->mDB->DBfetchs($tablename,'*','',$fields[0]['name'].',asc',$dataLimit.','.$maxLimit);
					for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
						$insert = array();
						for ($j=0, $loopj=sizeof($fields);$j<$loopj;$j++) {
							if (isset($data[$i][$fields[$j]['name']]) == true) $insert[$fields[$j]['name']] = $data[$i][$fields[$j]['name']];
						}
						
						$this->mDB->DBinsert($tablename.'(NEW)',$insert);
					}
					if ($total < $dataLimit+$maxLimit) {
						$this->mDB->DBname($tablename,$tablename.'(BK'.date('YmdHis').')');
						$this->mDB->DBname($tablename.'(NEW)',$tablename);
						if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($tableCode+1).','.sizeof($database->table).','.$total.','.$total.'); } catch(e) {} </script>';
						Redirect($_SERVER['PHP_SELF'].GetQueryString(array('tableCode'=>$tableCode+1,'dataLimit'=>0),'',false));
					} else {
						if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($tableCode+1).','.sizeof($database->table).','.$dataLimit.','.$total.'); } catch(e) {} </script>';
						Redirect($_SERVER['PHP_SELF'].GetQueryString(array('tableCode'=>$tableCode,'dataLimit'=>$dataLimit+$maxLimit),'',false));
					}
				}
			}
		}
	}
	
	function CreateFolder() {
		$folder = $this->GetModuleXML('folder');
		
		if ($folder) {
			$root = $folder->attributes()->root;
			if (CreateDirectory($_ENV['userfilePath'].'/'.$root) == true) {
				@chmod($_ENV['userfilePath'].'/'.$root,0707);
				$path = $folder->path;
				for ($i=0, $loop=sizeof($path);$i<$loop;$i++) {
					@mkdir($_ENV['userfilePath'].'/'.$root.'/'.$path[$i]);
					@chmod($_ENV['userfilePath'].'/'.$root.'/'.$path[$i],0707);
				}
			}
		}
	}
}
?>