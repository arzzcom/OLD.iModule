<?php
class ModuleDatabase extends Module {
	public $mDB;
	public $table;
	
	public $userfile;
	public $thumbnail;

	function __construct() {
		parent::__construct('database');

		$this->mDB = &DB::instance();

		$this->table['table'] = $_ENV['code'].'_database_table';
		$this->table['file'] = $_ENV['code'].'_database_file_table';
		
		$this->userfile = '/database';
		$this->thumbnail = '/database/thumbnail';
	}

	function GetTable($idx=0,$table='') {
		if ($idx == 0) {
			$data = $this->mDB->DBfetch($this->table['table'],'*',"where `name`='$table'");
		} else {
			$data = $this->mDB->DBfetch($this->table['table'],'*',"where `idx`=$idx");
		}

		$field = GetUnSerialize($data['field']);
		$data['field'] = array();
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			$data['field'][$field[$i]['name']] = $field[$i];
		}

		return $data;
	}

	function DBfetch($table,$selector,$find='',$order='',$limit='') {
		$table = $this->GetTable(0,$table);
		$data = $this->mDB->DBfetch($table['name'],$selector,$find,$order,$limit,$table['database']);

		foreach ($data as $field=>$value) {
			if ($table['field'][$field]['type'] == 'FILE') {
				if ($value && $value != '0') {
					$file = $this->mDB->DBfetch($this->table['file'],array('idx','filename','filepath','filesize','filetype','hit'),"where `idx`=$value");
					$file['filepath'] = $_ENV['path'].$file['filepath'];
					$file['filedir'] = $file['filetype'] == 'IMG' ? $this->moduleDir.'/exec/ShowImage.do.php?idx='.$file['idx'].'&tno='.$table['idx'] : $_ENV['dir'].$file['filepath'];
					$file['download'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file['idx'].'&tno='.$table['idx'];
					$data[$field] = $file;
				} else {
					$data[$field] = array('idx'=>0,'filename'=>'','filepath'=>'','filedir'=>'','filesize'=>0,'filetype'=>'ETC','hit'=>0,'download'=>'');
				}
			} else {
				if ($table['field'][$field]['type'] == 'HTML') {
					$value = '<div class="smartOutput">'.str_replace('{$moduleDir}',$this->moduleDir,$value).'</div>';
				}
				$data[$field] = $value;
			}
		}

		return $data;
	}

	function DBfetchs($table,$selector,$find='',$order='',$limit='') {
		$table = $this->GetTable(0,$table);
		$data = $this->mDB->DBfetchs($table['name'],$selector,$find,$order,$limit,$table['database']);

		for ($i=0, $loop=sizeof($data);$i<$loop;$i++) {
			foreach ($data[$i] as $field=>$value) {

				if ($table['field'][$field]['type'] == 'FILE') {
					if ($value && $value != '0') {
						$file = $this->mDB->DBfetch($this->table['file'],array('idx','filename','filepath','filesize','filetype','hit'),"where `idx`=$value");
						$file['filepath'] = $_ENV['path'].$file['filepath'];
						$file['filedir'] = $file['filetype'] == 'IMG' ? $this->moduleDir.'/exec/ShowImage.do.php?idx='.$file['idx'].'&tno='.$table['idx'] : $_ENV['dir'].$file['filepath'];
						$file['download'] = $this->moduleDir.'/exec/FileDownload.do.php?idx='.$file['idx'].'&tno='.$table['idx'];
						$data[$i][$field] = $file;
					} else {
						$data[$i][$field] = array('idx'=>0,'filename'=>'','filepath'=>'','filedir'=>'','filesize'=>0,'filetype'=>'ETC','hit'=>0,'download'=>'');
					}
				} else {
					if ($table['field'][$field]['type'] == 'HTML') {
						$value = '<div class="smartOutput">'.str_replace('{$moduleDir}',$this->moduleDir,$value).'</div>';
					}
					$data[$i][$field] = $value;
				}
			}
		}

		return $data;
	}

	function DBcount($table,$find='') {
		$table = $this->GetTable(0,$table);
		return $this->mDB->DBcount($table['name'],$find,$table['database']);
	}
}
?>