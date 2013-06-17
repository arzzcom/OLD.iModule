<?php
if ($this->GetVersionToNumber($this->GetDBVersion()) <= $this->GetVersionToNumber('2.0.0')) {
	while(true) {
		$data = $this->mDB->DBfetchs($_ENV['code'].'_database_file_table',array('idx','filepath'),"where `filepath` like '/userfile/database%'",'','0,1000');
		if (sizeof($data) == 0) break;
		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			$tno = array_shift(explode('/',str_replace('/userfile/database/','',$data[$i]['filepath'])));
			$this->mDB->DBupdate($_ENV['code'].'_database_file_table',array('tno'=>$tno,'filepath'=>preg_replace('/^\/userfile\/database/','/attach',$data[$i]['filepath'])),'',"where `idx`='{$data[$i]['idx']}'");
		}
	}
}
?>