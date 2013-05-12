<?php
class ModulePoint extends Module {
	public $table;
	
	function __construct() {
		$this->table['payment'] = $_ENV['code'].'_point_payment_table';
		$this->table['buy'] = $_ENV['code'].'_point_buy_table';

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