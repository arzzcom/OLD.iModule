<?php
$updateVersion = '2.0.0';

if ($this->GetVersionToNumber($this->GetDBVersion()) <= $this->GetVersionToNumber('2.0.0')) {
	while(true) {
		$data = $this->mDB->DBfetchs($_ENV['code'].'_board_file_table',array('idx','filepath'),"where `filepath` like '/userfile/board%'",'','0,1000');
		if (sizeof($data) == 0) break;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$this->mDB->DBupdate($_ENV['code'].'_board_file_table',array('filepath'=>preg_replace('/^\/userfile\/board/','/attach',$data[$i]['filepath'])),'',"where `idx`='{$data[$i]['idx']}'");
		}
	}
}
?>