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
		$_ENV['isMobile'] = isset($_ENV['isMobile']) == true ? $_ENV['isMobile'] : false;
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
	
	function CheckInstalled($module) {
		if ($this->moduleName == $module) return $this->IsSetup();
		
		if ($this->mDB->DBcount($_ENV['table']['module'],"where `module`='$module'") == 0) return false;
		else return true;
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
						$this->mDB->DBdrop($tablename);
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
		$mFlush = new Flush();
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
					$indexes[$j] = array('name'=>(string)($index[$j]->attributes()->name),'type'=>(string)($index[$j]->attributes()->type));
				}
				
				if ($this->mDB->DBFind($tablename) == true) {
					if ($this->mDB->DBcompare($tablename,$fields,$indexes) == false) {
						if ($this->mDB->DBFind($tablename.'(NEW)') == true) {
							$this->mDB->DBdrop($tablename.'(NEW)');
						}
						
						if ($this->mDB->DBcreate($tablename.'(NEW)',$fields,$indexes) == true) {
							$sortField = '';
							for ($j=0, $loopj=sizeof($indexes);$j<$loopj;$j++) {
								if ($indexes[$j]['type'] == 'auto_increment' || $indexes[$j]['type'] == 'primary') {
									$sortField = $indexes[$j]['name'];
									break;
								}
							}
							
							$startPoint = 0;
							while (true) {
								$total = $this->mDB->DBcount($tablename);
								$data = $this->mDB->DBfetchs($tablename,'*','',$sortField.',asc',$startPoint.',1000');
								if (sizeof($data) == 0) break;
								for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
									$insert = array();
									for ($k=0, $loopk=sizeof($fields);$k<$loopk;$k++) {
										if (isset($data[$j][$fields[$k]['name']]) == true) $insert[$fields[$k]['name']] = $data[$j][$fields[$k]['name']];
									}
									
									$this->mDB->DBinsert($tablename.'(NEW)',$insert);
									
									if ($j%100 == 0) {
										if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($i+1).','.$loop.','.($startPoint+$j).','.$total.'); } catch(e) {} </script>';
										$mFlush->flush();
									}
								}
								
								$startPoint = $startPoint + 1000;
							}
							
							$this->mDB->DBrename($tablename,$tablename.'(BK'.date('YmdHis').')');
							$this->mDB->DBrename($tablename.'(NEW)',$tablename);
							
							if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($i+1).','.$loop.','.$total.','.$total.'); } catch(e) {} </script>';
							$mFlush->flush();
						}
					} else {
						if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($i+1).','.$loop.',0,0); } catch(e) {} </script>';
						$mFlush->flush();
					}
				} else {
					if ($this->mDB->DBcreate($tablename,$fields,$indexes) == true) {
						$data = isset($table[$i]->data) == true ? $table[$i]->data : array();
						
						for ($j=0, $loopj=sizeof($data);$j<$loopj;$j++) {
							$insert = array_pop(array_values((array)($data[$j]->attributes())));
							$this->mDB->DBinsert($tablename,$insert);
							
							if ($j%50 == 0) {
								if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($i+1).','.$loop.','.$j.','.$loopj.'); } catch(e) {} </script>';
								$mFlush->flush();
							}
						}
						if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("'.$tablename.'",'.($i+1).','.$loop.','.$loopj.','.$loopj.'); } catch(e) {} </script>';
						$mFlush->flush();
					}
				}
				
				sleep(1);
			}
			
			if (file_exists($this->modulePath.'/update.php') == true) {
				INCLUDE_ONCE $this->modulePath.'/update.php';
			}
			
			if ($this->IsSetup() == true) {
				$this->mDB->DBupdate($_ENV['table']['module'],array('version'=>$this->GetModuleXML('version')),'',"where `module`='{$this->moduleName}'");
			} else {
				$this->mDB->DBinsert($_ENV['table']['module'],array('module'=>$this->moduleName,'name'=>$this->GetModuleXML('title'),'version'=>$this->GetModuleXML('version'),'is_admin'=>$this->GetModuleXML('is_manager')));
			}

			if ($progress == true) echo '<script type="text/javascript"> try { top.ModuleProgressControl("",'.$loop.','.$loop.',0,0); } catch(e) {} </script>';
			$mFlush->flush();
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