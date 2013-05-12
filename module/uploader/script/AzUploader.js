if (isIncludeAzUploader === undefined) {
	var isIncludeAzUploader = true;

	var AzUploaderComponent = {
		components:new Array(),
		get:function(id) {
			if (this.components[id]) {
				return this.components[id];
			} else {
				this.errormsg(id+"로 정의된 AzUploader를 찾을 수 없습니다.");
			}
		},
		register:function(component) {
			this.components[component.id] = component;
		},
		load:function(params) {
			for (id in this.components) {
				if (this.get(id).isReady == false) {
					this.get(id).readyQueue.push("AzUploaderComponent.get(\""+id+"\").setLoadURLParams(\""+params+"\"); AzUploaderComponent.get(\""+id+"\").load();");
				} else {
					this.get(id).setLoadURLParams(params); this.get(id).load();
				}
			}
		},
		error:function(code) {
			switch (code) {
				case 101 :
					this.errormsg("버튼이미지를 로딩하지 못하였습니다.");
				break;

				case 201 :
					this.errormsg("업로드할 파일이 없습니다.");
				break;

				case 202 :
					this.errormsg("업로드경로가 잘못지정되었거나, 해당경로에 파일이 없습니다.");
				break;

				case 203 :
					this.errormsg("업로드경로는 업로더가 올려진 도메인내의 경로만 가능합니다.");
				break;

				case 999 :
					this.errormsg("AzUploader가 로딩되지 않았습니다.");
				break;
			}
		},
		errormsg:function(msg) {
			alert(msg);
		}
	}

	var AzUploaderEventListener = function(type,id,data) {
		var AzUploader = AzUploaderComponent.get(id);
		switch (type) {
			case "onReady" :
				AzUploader.flash = document.getElementById(id);
				AzUploader.isReady = true;
				for (var i=0, loop=AzUploader.readyQueue.length;i<loop;i++) {
					eval(AzUploader.readyQueue[i]);
				}
				AzUploader.readyQueue = new Array();

				if (AzUploader.autoLoad == true) {
					AzUploader.load();
				}

				try { AzUploader.listeners.onReady(AzUploader); } catch(e) {}
			break;

			case "onSelect" :
				var fileList = new Array();
				for (var i=0, loop=data.length;i<loop;i++) {
					fileList[i] = {};
					fileList[i].idx = data[i][0];
					fileList[i].name = data[i][1];
					fileList[i].size = data[i][2];
				}
				try { AzUploader.listeners.onSelect(AzUploader,fileList); } catch(e) {}
			break;

			case "onError" :
				file = {};
				file.idx = data[0];
				file.name = data[1];
				file.size = data[2];
				file.msg = data[3];

				AzUploader.errorFiles.push(file);
				try { AzUploader.listeners.onError(AzUploader,data[3],file); } catch(e) {}
			break;

			case "onDelete" :
				file = {};
				file.idx = data[0];
				file.name = data[1];
				file.size = data[2];
				try { AzUploader.listeners.onDelete(AzUploader,file); } catch(e) {}
			break;

			case "onUpload" :
				var file = {};
				file.idx = data[0];
				file.name = data[1];
				file.size = data[2];
				file.server = data[3];
				try { AzUploader.listeners.onUpload(AzUploader,file); } catch(e) {}
			break;

			case "onLoad" :
				var file = {};
				file.idx = data[0];
				file.name = data[1];
				file.size = data[2];
				file.server = data[3];
				try { AzUploader.listeners.onLoad(AzUploader,file); } catch(e) {}
			break;

			case "onProgress" :
				var file = {};
				file.idx = data[0];
				file.name = data[1];
				file.size = data[2];

				var fileUpload = {};
				fileUpload.count = parseInt(data[3]);
				fileUpload.upload = parseInt(data[4]);
				fileUpload.total = parseInt(data[5]);

				var totalUpload = {};
				totalUpload.count = parseInt(data[6]);
				totalUpload.upload = parseInt(data[7]);
				totalUpload.total = parseInt(data[8]);

				var time = {};
				time.file = parseInt(data[9]);
				time.total = parseInt(data[10]);
				time.remain = parseInt(data[11]);

				var speed = {};
				speed.file = parseInt(data[12]);
				speed.total = parseInt(data[13]);

				try { AzUploader.listeners.onProgress(AzUploader,file,fileUpload,totalUpload,time,speed); } catch(e) {}
			break;

			case "onComplete" :
				try { AzUploader.listeners.onComplete(AzUploader,file); } catch(e) {}
				AzUploader.errorFiles = new Array();
			break;
		}
	}

	var AzUploader = function(opt) {
		this.isReady = false;
		this.autoRender = true;
		this.autoLoad = false;
		this.readyQueue = new Array();
		this.id = "AzUploader-"+(Math.floor(Math.random()*100000)%100000);
		this.flashURL = "AzUploader.swf";
		this.buttonURL = "button.gif";
		this.uploadURL = "upload.php";
		this.loadURL = "load.php";
		this.moduleType = "";
		this.moduleDir = "";
		this.skinDir = "";
		this.formElement = "";
		this.panelElement = "";
		this.wysiwygElement = "";
		this.width = 75;
		this.height = 20;
		this.fieldName = "FileData";
		this.maxFileSize = 0;
		this.maxTotalSize = 0;
		this.uploadedSize = 0;
		this.allowType = "";
		this.errorFiles = new Array();
		this.listeners = {};
		this.callback = opt.callback ? opt.callback : null;

		if (opt.autoRender !== undefined) this.autoRender = opt.autoRender;
		if (opt.autoLoad !== undefined) this.autoLoad = opt.autoLoad;
		if (opt.id) this.id = opt.id;
		if (opt.renderElement) this.renderElement = opt.renderElement;
		if (opt.flashURL) this.flashURL = opt.flashURL;
		if (opt.buttonURL) this.buttonURL = opt.buttonURL;
		if (opt.uploadURL) this.uploadURL = opt.uploadURL;
		if (opt.loadURL) this.loadURL = opt.loadURL;
		if (opt.moduleType) this.moduleType = opt.moduleType;
		if (opt.moduleDir) this.moduleDir = opt.moduleDir;
		if (opt.skinDir) this.skinDir = opt.skinDir;
		if (opt.formElement) this.formElement = opt.formElement;
		if (opt.wysiwygElement) this.wysiwygElement = opt.wysiwygElement;
		if (opt.panelElement) this.panelElement = opt.panelElement;
		if (opt.width) this.width = opt.width;
		if (opt.height) this.height = opt.height;
		if (opt.fieldName) this.fieldName = opt.fieldName;
		if (opt.maxFileSize) this.maxFileSize = opt.maxFileSize;
		if (opt.maxTotalSize) this.maxTotalSize = opt.maxTotalSize;
		if (opt.uploadedSize) this.uploadedSize = opt.uploadedSize;
		if (opt.allowType) this.allowType = opt.allowType;

		if (opt.listeners) {
			if (opt.listeners.onReady) this.listeners.onReady = opt.listeners.onReady;
			if (opt.listeners.onSelect) this.listeners.onSelect = opt.listeners.onSelect;
			if (opt.listeners.onError) this.listeners.onError = opt.listeners.onError;
			if (opt.listeners.onDelete) this.listeners.onDelete = opt.listeners.onDelete;
			if (opt.listeners.onProgress) this.listeners.onProgress = opt.listeners.onProgress;
			if (opt.listeners.onUpload) this.listeners.onUpload = opt.listeners.onUpload;
			if (opt.listeners.beforeLoad) this.listeners.beforeLoad = opt.listeners.beforeLoad;
			if (opt.listeners.onLoad) this.listeners.onLoad = opt.listeners.onLoad;
			if (opt.listeners.onComplete) this.listeners.onComplete = opt.listeners.onComplete;
		}

		this.flashURL+= "?rnd="+Math.random();
		this.flashVars = "id="+this.id+"&maxFileSize="+this.maxFileSize+"&maxTotalSize="+this.maxTotalSize+"&uploadedSize="+this.uploadedSize+"&allowType="+this.allowType+"&buttonURL="+encodeURIComponent(this.buttonURL)+"&uploadURL="+encodeURIComponent(this.uploadURL)+"&loadURL="+encodeURIComponent(this.loadURL);

		this.isIE = navigator.appName == "Microsoft Internet Explorer";
		if (this.isIE) {
			this.AzUploaderHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="'+this.width+'" height="'+this.height+'" id="'+this.id+'" align="middle">';
			this.AzUploaderHTML+= '<param name="allowScriptAccess" value="always" />';
			this.AzUploaderHTML+= '<param name="flashVars" value="'+this.flashVars+'" />';
			this.AzUploaderHTML+= '<param name="movie" value="'+this.flashURL+'" />';
			this.AzUploaderHTML+= '<param name="quality" value="high" />';
			this.AzUploaderHTML+= '<param name="wmode" value="transparent" />';
			this.AzUploaderHTML+= '<param name="base" value="." />';
			this.AzUploaderHTML+= '<embed src="'+this.flashURL+'" quality="high" wmode="transparent" style="width:'+this.width+'px; height:'+this.height+'px;" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+this.flashVars+'"></embed>';
			this.AzUploaderHTML+= '</object>';

			if (this.autoRender == true) {
				if (this.renderElement) document.getElementById(this.renderElement).innerHTML = this.AzUploaderHTML;
				else document.write(this.AzUploaderHTML);
				eval("window."+this.id+" = document.getElementById('"+this.id+"');");
			}
		} else {
			this.AzUploaderHTML = '<embed id="'+this.id+'" src="'+this.flashURL+'" quality="high" wmode="transparent" style="width:'+this.width+'px; height:'+this.height+'px;" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" base="." flashvars="'+this.flashVars+'"></embed>';

			if (this.autoRender == true) {
				if (this.renderElement) document.getElementById(this.renderElement).innerHTML = this.AzUploaderHTML;
				else document.write(this.AzUploaderHTML);
			}
		}

		this.render = function(renderElement) { if (renderElement) document.getElementById(renderElement).innerHTML = this.AzUploaderHTML; else document.write(this.AzUploaderHTML);  this.flash = document.getElementById(this.id); }
		this.upload = function() { if (this.isReady == false) return; this.flash.upload(); }
		this.remove = function(idx) { if (this.isReady == false) return; this.flash.remove(idx); }
		this.load = function() { if (this.isReady == false) return; if (this.listeners.beforeLoad && this.listeners.beforeLoad(this) == false) return; this.flash.load(); }
		this.setLoadURLParams = function(params) { if (this.isReady == false) return; this.loadURL+= this.loadURL.indexOf("?") == -1 ? "?"+params : "&"+params; this.flash.setLoadURL(this.loadURL); }
		this.setAllowType = function(allowType) { if (this.isReady == false) return; this.flash.setAllowType(allowType); }
		this.setMaxFileSize = function(maxFileSize) { if (this.isReady == false) return; this.flash.setMaxFileSize(maxFileSize); }
		this.setMaxTotalSize = function(maxTotalSize) { if (this.isReady == false) return; this.flash.setMaxTotalSize(maxTotalSize); }

		AzUploaderComponent.register(this);
	}

	// 기타 유용한 함수
	function AzUploaderFileSize(size) {
		if(size < 1024) {
			return size + "B";
		} else if(size < 1048576) {
			return (size/1024).toFixed(2) + "KiB";
		} else {
			return (size/1048576).toFixed(2) + "MiB";
		}
	}
}