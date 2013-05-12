<?php
class PHPExcelReader {
	public $reader;

	function __construct($file) {
		REQUIRE_ONCE $_ENV['path'].'/class/PHPExcel/Reader/Excel2007.php';
		REQUIRE_ONCE $_ENV['path'].'/class/PHPExcel/Reader/Excel5.php';

		$reader = new PHPExcel_Reader_Excel2007();
		if (!$reader->canRead($file)) {
			echo 'error';
			$reader = new PHPExcel_Reader_Excel5();
			if(!$reader->canRead($file)) {
				echo 'error2';
				return false;
			}
		}

		$this->reader = $reader->load($file);
	}

	function GetExcel() {
		return $this->reader;
	}

	function GetExcelSheetTitle($sheet=0) {
		return $this->reader->getSheet($sheet)->getTitle();
	}

	function GetExcelAllData() {
		$data = array();
		for ($i=0, $loop=$this->reader->getSheetCount();$i<$loop;$i++) {
			$data[$this->GetExcelSheetTitle($i)] = $this->GetExcelData($i);
		}

		return $data;
	}

	function GetExcelData($sheet=0) {
		$sheet = $this->reader->getSheet($sheet);
		$column = $sheet->getHighestColumn();
		$row = $sheet->getHighestRow();

		$data = array();
		for($i=1;$i<=$row;$i++){
			$data[$i-1] = array();
			for($j='A';$j<=$column;$j++){
				$data[$i-1][$j] = trim($sheet->getCell($j.$i)->getValue());
			}
		}

		return $data;
	}
}
?>