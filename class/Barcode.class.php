<?php
class Barcode {
	private $number;

	function __construct($number) {
		$this->number = $number;
	}

	function GetBarcode() {
		$barcodeWidth = 13;
		$barcode = '<img src="'.$_ENV['dir'].'/images/common/barcode/start.gif" style="vertical-align:middle;" />';

		for ($i=0, $loop=strlen($this->number);$i<$loop;$i++) {
			$barcodeWidth+= 13;
			$barcode.= '<img src="'.$_ENV['dir'].'/images/common/barcode/'.substr($this->number,$i,1).'.gif" style="vertical-align:middle;" />';
		}
		$barcode.= '<img src="'.$_ENV['dir'].'/images/common/barcode/start.gif" style="vertical-align:middle;" />';
		$barcodeWidth+= 13;

		$barcode = '<div style="width:'.$barcodeWidth.'px; margin:0 auto;"><div>'.$barcode.'</div><div style="padding-top:5px; text-align:center; font-family:tahoma; Letter-spacing:7px; font-size:9px; font-weight:bold; padding-left:7px;">'.$this->number.'</div></div>';
		return $barcode;
	}
}
?>