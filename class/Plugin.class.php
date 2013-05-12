<?php
class Plugin {
	private $mDB;
	private $module;
	private $execFunctions;
	
	function __construct($module) {
		$this->mDB = &DB::instance();
		$this->module = $module;
		$this->execFunctions = array();
		
		$check = $this->mDB->DBfetchs($_ENV['code'].'_plugin_table','*',"where `module`='{$this->module->moduleName}'");
		
		if ($_SERVER['REMOTE_ADDR'] == '112.163.190.67') {

			//$this->module->PrintView();
			for ($i=0, $loop=sizeof($check);$i<$loop;$i++) {
				
			}
		}
	}
}
?>