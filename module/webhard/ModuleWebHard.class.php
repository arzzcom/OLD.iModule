<?php
class ModuleWebHard extends Module {
	public $table;

	function __construct() {
		parent::__construct('webhard');

		$this->table['file'] = $_ENV['code'].'_webhard_file_table';
	}

	function DirFileSize($dir,$filesize) {
		$filesize = $filesize > 0 ? '+'.$filesize : $filesize;
		if ($dir != '/') {
			$dirlist = explode('/',$dir);
			$thisdir = '/';

			for ($i=1, $loop=sizeof($dirlist);$i<$loop;$i++) {
				$this->mDB->DBupdate($this->table['file'],array('modify_date'=>GetGMT()),array('filesize'=>'`filesize`'.$filesize),"where `dir`='$thisdir' and `filename`='$dirlist[$i]' and `type`='DIR'");

				$thisdir.= $dirlist[$i].'/';
			}
		}
	}
}
?>