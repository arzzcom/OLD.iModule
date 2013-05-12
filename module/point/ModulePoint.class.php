<?php
class ModulePoint extends Module {
	public $table;
	
	function __construct() {
		$this->table['item'] = $_ENV['code'].'_point_item_table';

		parent::__construct('point');
	}
	
	function GetMoneyByPoint($point) {
		return ceil($point/$this->GetConfig('ratio'));
	}
	
	function GetPointByMoney($money) {
		return floor($money*$this->GetConfig('ratio'));
	}
}
?>