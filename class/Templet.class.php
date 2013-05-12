<?php
REQUIRE_ONCE $_ENV['path'].'/smarty/Smarty.class.php';

class Templet extends Smarty {
	private $skinFile;
	private $skinPath;

	function __construct($template=null) {
		$this->compile_dir = $_ENV['userfilePath'].'/temp';
		$this->cache_dir = $_ENV['userfilePath'].'/temp';
		$this->config_dir = $_ENV['path'].'/smarty/configs';
		$this->plugins_dir = array($_ENV['path'].'/smarty/'.'plugins');

		$temp = explode('/',$template);
		$this->skinFile = $temp[(int)(sizeof($temp)-1)];
		$this->skinPath = str_replace('/'.$this->skinFile,'',$template);
		$this->template_dir = $this->skinPath;
		$this->template = $template;
	}

	function GetTemplet() {
		return $this->fetch($this->template);
		ob_start();
		$this->display($this->template);
		$layout = ob_get_contents();
		ob_clean();
		return $layout;
	}

	function PrintTemplet() {
		$this->display($this->template);
	}
}
?>