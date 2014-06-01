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
	$result['success'] = true;
	$result['file'] = array(
		'idx'=>rand(1000,2000),
		'type'=>$filetype,
		'name'=>'kor.jpg',
		'size'=>1731,
		'thumbnail'=>$_ENV['userfileDir'].$mBoard->thumbnail.'/500691.thm'
	);
	
	exit(json_encode($result));
	
	
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
/*
	$file = $_FILES['Filedata'];
	$check = @getimagesize($file['tmp_name']);
	
	$type = Request('type');
	$wysiwyg = Request('wysiwyg');
	$filename = $file['name'];
	$temppath = $file['tmp_name'];
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
		
		exit(json_encode($result));
	} else {
		
	}
} else {
	echo 'FALSE';
}

if (isset($_FILES['files']['tmp_name'][0]) == true) {
	$check = getimagesize($_FILES['files']['tmp_name'][0]);

	if (in_array($check[2],array(1,2,3)) == true) {
		$exeName = array('','gif','jpg','png');
		$filename = md5_file($_FILES['files']['tmp_name'][0]).'.'.strrev(sprintf('%011d',time()));
		$fileexe = $exeName[$check[2]];
		$filetype = $_FILES['files']['type'][0];
		$filesize = filesize($_FILES['files']['tmp_name'][0]);
		$dirname = strtoupper(substr($filename,0,1));
		
		if (is_dir($_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname) == false) {
			@mkdir($_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname);
			@chmod($_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname,0707);
		}
		
		@move_uploaded_file($_FILES['files']['tmp_name'][0],$_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname.'/'.$filename.'.'.$fileexe);
		GetThumbnail($_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname.'/'.$filename.'.'.$fileexe,$_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname.'/'.$filename.'.thm',300,0);
		@chmod($_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname.'/'.$filename.'.'.$fileexe,0707);
		@chmod($_SERVER['DOCUMENT_ROOT'].'/attach/portfolio/'.$dirname.'/'.$filename.'.thm',0707);
		
		$this->mDB->DBinsert('portfolio_files',array('name'=>$filename.'.'.$fileexe,'pidx'=>$pidx,'dirname'=>$dirname,'filetype'=>$filetype,'filesize'=>$filesize,'width'=>$check[0],'height'=>$check[1],'reg_date'=>GetGMT()));

		$result = null;
		$result->files = array();
		$result->files[0] = array(
			'name'=>$filename.'.'.$fileexe,
			'size'=>$filesize,
			'type'=>$filetype,
			'url'=>'/attach/portfolio/'.$dirname.'/'.$filename.'.'.$fileexe,
			'thumbnailUrl'=>'/attach/portfolio/'.$dirname.'/'.$filename.'.thm'
		);
		exit(json_encode($result));
	}
}
*/