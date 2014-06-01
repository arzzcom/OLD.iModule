<?php
class ModuleUploader extends Module {
	protected $mTemplet;
	protected $loadPath;
	protected $uploadPath;
	protected $skinPath;
	protected $skinDir;
	protected $callback;
	protected $callerType;
	protected $caller;
	
	protected $maxSize;
	protected $isMultiple;
	protected $isHeaderIncluded;

	function __construct() {
		parent::__construct('uploader');
		$this->mTemplet = new Templet();
		$this->uploadPath = null;
		$this->loadPath = null;
		$this->skinPath = null;
		$this->skinDir = null;
		$this->type = null;
		$this->callback = 'null';
		
		$this->maxSize = 20;
		$this->isMultiple = true;
		$this->isHeaderIncluded = false;
	}

	function SetUploadPath($path) {
		$this->uploadPath = $path;
	}

	function SetLoadPath($path) {
		$this->loadPath = $path;
	}

	function SetCaller($callerType,$caller) {
		$this->callerType = $callerType;
		$this->caller = $caller;
	}
	
	function SetCallback($callback) {
		$this->callback = $callback;
	}
	
	function SetIsMultiple($isMultiple) {
		$this->isMultiple = $isMultiple;
	}
	
	function SetMaxSize($maxSize) {
		$this->maxSize = $maxSize;
	}

	function PrintScript() {
		if ($this->isHeaderIncluded == false) {
			echo "\n".'<!-- Module AzUploader Start -->'."\n";
			echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/AzUploader.js"></script>';
			echo "\n".'<!-- Module AzUploader End -->'."\n";
			$this->isHeaderIncluded = true;
		}
	}
	
	function SetType($type) {
		$this->type = $type;
	}
	
	function PrintUploader($skin,$id='uploader',$form='',$wysiwyg='') {
		echo "\n".'<!-- Module Uploader Start -->'."\n";
		echo '<script type="text/javascript" src="'.$this->moduleDir.'/script/script.js"></script>'."\n";
		
		$this->skinPath = $this->modulePath.'/templet/'.$skin;
		$this->skinDir = $this->moduleDir.'/templet/'.$skin;
		
		if (file_exists($this->skinPath.'/style.css') == true) echo '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" />'."\n";
		if (file_exists($this->skinPath.'/script.js') == true) echo '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";

		$this->mTemplet = new Templet($this->skinPath.'/uploader.tpl');
		$this->mTemplet->assign('id',$id);
		$this->mTemplet->assign('wysiwyg',$wysiwyg);
		$this->mTemplet->assign('skinDir',$this->skinDir);
		$this->mTemplet->assign('moduleDir',$this->caller->moduleDir);
		$this->mTemplet->PrintTemplet();

		$params = array();
		$params['flashURL'] = $this->moduleDir.'/flash/uploader.swf?rnd='.time();
		$params['uploadURL'] = $this->uploadPath;
		$params['loadURL'] = $this->loadPath;
		$params['buttonURL'] = $this->skinDir.'/images/button.gif';
		$buttonSize = getimagesize($this->skinPath.'/images/button.gif');
		$params['buttonWidth'] = $buttonSize[0];
		$params['buttonHeight'] = $buttonSize[1];
		$params['maxSize'] = $this->maxSize;
		$params['skinDir'] = $this->skinDir;
		$params['moduleDir'] = $this->caller->moduleDir;
		$params['formObject'] = $form;
		$params['wysiwygObject'] = $wysiwyg;
		
		echo '<script type="text/javascript">ModuleUploaderButton("'.$id.'",'.json_encode($params).');</script>';
		
		echo "\n".'<!-- Module Uploader End -->'."\n";
	}

	function GetUploader($skin,$id='uploader',$form='',$wysiwyg='') {
		$this->PrintScript();
		$uploader = '';
		$uploader.= "\n".'<!-- Module AzUploader Start -->'."\n";

		if ($this->uploadPath == null) {
			$uploader.= '[업로더에러] 파일을 업로드받을 경로가 설정되지 않았습니다.';
		} else {
			$this->skinPath = $this->modulePath.'/templet/'.$skin;
			$this->skinDir = $this->moduleDir.'/templet/'.$skin;

			$uploader.= '<link rel="stylesheet" href="'.$this->skinDir.'/style.css" type="text/css" />'."\n";
			$uploader.= '<script type="text/javascript" src="'.$this->skinDir.'/script.js"></script>'."\n";

			$button = '<script type="text/javascript">var uploader = new AzUploader({';
			$button.= 'id:"'.$id.'",flashURL:"'.$this->moduleDir.'/flash/AzUploader.swf",buttonURL:"'.$this->skinDir.'/images/button.gif",';
			$button.= 'uploadURL:"'.$this->uploadPath.'",loadURL:"'.$this->loadPath.'",';
			if ($this->type != null) $button.= 'allowType:"'.$this->type.'",';
			$button.= 'moduleType:"'.$this->callerType.'",moduleDir:"'.$this->caller->moduleDir.'",skinDir:"'.$this->skinDir.'",';
			if ($form) $button.= 'formElement:"'.$form.'",';
			if ($wysiwyg) $button.= 'wysiwygElement:"'.$wysiwyg.'",';
			
			$button.= 'width:75,height:20,maxFileSize:0,maxTotalSize:0,uploadedSize:0,callback:'.$this->callback.',';
			$button.= 'listeners:{onSelect:AzUploaderSkinOnSelect,beforeLoad:AzUploaderSkinBeforeLoad,onLoad:AzUploaderSkinOnLoad,onError:AzUploaderSkinOnError,onDelete:AzUploaderSkinOnSelect,onProgress:AzUploaderSkinOnProgress,onUpload:AzUploaderSkinOnUpload,onComplete:AzUploaderSkinOnComplete}});';
			$button.= '</script>';

			$this->mTemplet = new Templet($this->skinPath.'/uploader.tpl');
			$this->mTemplet->assign('id',$id);
			$this->mTemplet->assign('wysiwyg',$wysiwyg);
			$this->mTemplet->assign('button',$button);
			$this->mTemplet->assign('skinDir',$this->skinDir);
			$this->mTemplet->assign('moduleDir',$this->caller->moduleDir);
			$uploader.= $this->mTemplet->GetTemplet();
		}

		$uploader.= "\n".'<!-- Module AzUploader End -->'."\n";

		return $uploader;
	}
}
?>