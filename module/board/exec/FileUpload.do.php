<?php
REQUIRE_ONCE '../../../config/default.conf.php';

function ExecFileUpload($temppath,$filename) {
	$mDB = &DB::instance();
	$mBoard = new ModuleBoard();

	$type = Request('type');
	$wysiwyg = Request('wysiwyg');
	$filesize = filesize($temppath);
	$filetype = GetFileType($filename,$temppath);
	$filepath = '/attach/'.date('Ym').'/'.md5_file($temppath).'.'.time().'.'.rand(1000,9999).'.'.GetFileExec($filename);
	
	$result = array();
	
	if (CreateDirectory($_ENV['userfilePath'].$mBoard->userfile.'/attach/'.date('Ym')) == true) {
		@move_uploaded_file($temppath,$_ENV['userfilePath'].$mBoard->userfile.$filepath);
		$idx = $mDB->DBinsert($mBoard->table['file'],array('type'=>$type,'filename'=>$filename,'filepath'=>$filepath,'filesize'=>$filesize,'filetype'=>$filetype,'wysiwyg'=>$wysiwyg,'reg_date'=>GetGMT()));
	
		$result['success'] = true;
		$result['file'] = array(
			'idx'=>$idx,
			'type'=>$filetype,
			'name'=>$filename,
			'size'=>$filesize,
			'thumbnail'=>''
		);
		
		if ($filetype == 'IMG' && CreateDirectory($_ENV['userfilePath'].$mBoard->thumbnail) == true) {
			GetThumbnail($_ENV['userfilePath'].$mBoard->userfile.$filepath,$_ENV['userfilePath'].$mBoard->thumbnail.'/'.$idx.'.thm',150,120,false);
			$result['file']['thumbnail'] = $_ENV['userfileDir'].$mBoard->thumbnail.'/'.$idx.'.thm';
		}
	} else {
		$result['success'] = false;
	}
	
	exit(json_encode($result));
}

if (isset($_FILES['UploaderFile']['tmp_name']) == true) {
	if (is_array($_FILES['UploaderFile']['tmp_name']) == true) {
		ExecFileUpload($_FILES['UploaderFile']['tmp_name'][0],$_FILES['UploaderFile']['name'][0]);
	} else {
		ExecFileUpload($_FILES['UploaderFile']['tmp_name'],$_FILES['UploaderFile']['name']);
	}
}
?>