<?php
class PHPExcelWriter {
	private $writer;

	function __construct($mPHPExcel) {
		REQUIRE_ONCE $_ENV['path'].'/class/PHPExcel/IOFactory.php';

		$this->writer = PHPExcel_IOFactory::createWriter($mPHPExcel,'Excel2007');
	}

	function WriteExcel() {
		$filepath = $_ENV['path'].'/userfile/temp/'.time().'.'.rand(10000,99999).'.xlsx';
		$this->writer->save($filepath);

		return $filepath;
	}
}
?>