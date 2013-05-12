var AzUploadErrorFiles = new Array();
function AzUploaderSkinOnSelect(uploader,fileList) {
	if (fileList.length > 0) {
		WindowDisabled(true);
		document.getElementById("UPBox-"+uploader.id).style.display = "";
		LayerCenter("UPBox-"+uploader.id,true);
		uploader.upload();
	} else {
		AzUploaderSkinShowErrorFile(uploader.id);
	}
}

function AzUploaderSkinOnError(uploader,type,fileList) {
	if (!AzUploadErrorFiles[uploader.id]) AzUploadErrorFiles[uploader.id] = new Array();
	AzUploadErrorFiles[uploader.id].push(fileList);
}

function AzUploaderSkinOnUpload(uploader,file) {
	AzUploaderSkinShowFile(uploader.id,file.server);
}

function AzUploaderSkinBeforeLoad(uploader) {
	if (!uploader.loadURL) return false;
	document.getElementById("UploaderPreviewImage-"+uploader.id).innerHTML = "";
	document.getElementById("UploaderPreviewFile-"+uploader.id).innerHTML = "";
}

function AzUploaderSkinOnLoad(uploader,file) {
	AzUploaderSkinShowFile(uploader.id,file.server);
	
	if (uploader.callback != null) uploader.callback();
}

function AzUploaderSkinOnComplete(uploader) {
	AzUploaderSkinShowErrorFile(uploader.id);
	AzUploadErrorFiles[uploader.id] = new Array();
	WindowDisabled(false);
	document.getElementById("UPBox-"+uploader.id).style.display = "none";
	uploader.flash.style.visibility = "visible";

	if (uploader.callback != null) uploader.callback();
}

function AzUploaderSkinOnProgress(uploader,file,fileUpload,totalUpload,time,speed) {
	document.getElementById("FileNum-"+uploader.id).innerHTML = fileUpload.count;
	document.getElementById("TotalNum-"+uploader.id).innerHTML = totalUpload.count;
	document.getElementById("UPBoxFileText-"+uploader.id).innerHTML = file.name;
	document.getElementById("UPBoxFile-"+uploader.id).style.width = Math.round(100*fileUpload.upload/fileUpload.total)+"%";

	document.getElementById("UPBoxTotalText-"+uploader.id).innerHTML = AzUploaderFileSize(totalUpload.upload)+" / "+AzUploaderFileSize(totalUpload.total);
	document.getElementById("UPBoxTotal-"+uploader.id).style.width = Math.round(100*totalUpload.upload/totalUpload.total)+"%";

	document.getElementById("UPBoxTime-"+uploader.id).innerHTML = "";
	var s = time.total%60 < 10 ? "0"+Math.floor(time.total%60) : Math.floor(time.total%60);
	var m = Math.floor(time.total/60);
	if (m > 60) {
		var h = Math.floor(m/60);
		var m = m%60 < 10 ? "0"+Math.floor(m%60) : Math.floor(m%60);
		document.getElementById("UPBoxTime-"+uploader.id).innerHTML+= h+":"
	}
	document.getElementById("UPBoxTime-"+uploader.id).innerHTML+= m+":"+s;


	document.getElementById("UPBoxRemain-"+uploader.id).innerHTML = "";
	var s = time.remain%60 < 10 ? "0"+Math.floor(time.remain%60) : Math.floor(time.remain%60);
	var m = Math.floor(time.remain/60);
	if (m > 60) {
		var h = Math.floor(m/60);
		var m = m%60 < 10 ? "0"+Math.floor(m%60) : Math.floor(m%60);
		document.getElementById("UPBoxRemain-"+uploader.id).innerHTML+= h+":"
	}
	document.getElementById("UPBoxRemain-"+uploader.id).innerHTML+= m+":"+s;

	document.getElementById("UPBoxSpeed-"+uploader.id).innerHTML = AzUploaderFileSize(parseInt(speed.total)*1024);
}

function AzUploaderSkinShowErrorFile(id) {
	if (AzUploadErrorFiles[id] && AzUploadErrorFiles[id].length) {
		alert("제외된 파일이 있습니다.");
	}
}

function AzUploaderSkinShowFile(id,file) {
	var uploader = AzUploaderComponent.get(id);
	var data = file.split("|");

	var sHTML = '<input type="hidden" name="file[]" value="'+file+'" />';

	if (data[1] == "IMG") {
		var object = document.getElementById("UploaderPreviewImage-"+uploader.id);
		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="previewImage">';

		if (uploader.formElement && document.forms[uploader.formElement].image) {
			if (!document.forms[uploader.formElement].image.value) {
				document.forms[uploader.formElement].image.value = data[0];
				sHTML+= '<div id="AzUploadedFileImage-'+data[0]+'" class="imageon"><img src="'+data[4]+'" alt="'+data[2]+'" style="width:71px;" onclick="AzUploaderSetDefaultImage(\''+uploader.id+'\','+data[0]+');" /></div>';
			} else {
				sHTML+= '<div id="AzUploadedFileImage-'+data[0]+'" class="imageoff"><img src="'+data[4]+'" alt="'+data[2]+'" style="width:71px;" onclick="AzUploaderSetDefaultImage(\''+uploader.id+'\','+data[0]+');" /></div>';
			}
		} else {
			sHTML+= '<div class="imageoff"><img src="'+data[4]+'" alt="'+data[2]+'" style="width:71px;" /></div>';
		}
		if (uploader.wysiwygElement) sHTML+= '<div class="button"><img src="'+uploader.skinDir+'/images/btn_insert.gif" alt="본문삽입" onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\','+data[0]+');" class="pointer" /></div>';
		sHTML+= '<div class="text">';
		sHTML+= '<div class="size">'+AzUploaderFileSize(data[3])+'</div>';
		sHTML+= '<img src="'+uploader.skinDir+'/images/btn_delete.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\','+data[0]+');" class="pointer" />';
		sHTML+= '</div>';
		sHTML+= '</div>';
	} else if (data[1] == "MOV") {
		var object = document.getElementById("UploaderPreviewImage-"+uploader.id);
		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="previewMovie">';
		sHTML+= '<img src="'+uploader.skinDir+'/images/movie.gif" style="width:75px;" />';

		if (uploader.wysiwygElement) sHTML+= '<div class="button"><img src="'+uploader.skinDir+'/images/btn_insert.gif" alt="본문삽입" onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\','+data[0]+');" class="pointer" /></div>';
		sHTML+= '<div class="text">';
		sHTML+= '<div class="size">'+AzUploaderFileSize(data[3])+'</div>';
		sHTML+= '<img src="'+uploader.skinDir+'/images/btn_delete.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\','+data[0]+');" class="pointer" />';
		sHTML+= '</div>';
		sHTML+= '</div>';
	} else {
		var object = document.getElementById("UploaderPreviewFile-"+uploader.id);
		sHTML+= '<div id="AzUploadedFile-'+data[0]+'" class="previewFile"><span class="text">'+data[2]+'</span> <span class="size">('+AzUploaderFileSize(data[3])+')</span>';
		if (uploader.wysiwygElement) sHTML+= '<span class="button"><img src="'+uploader.skinDir+'/images/btn_insert_thin.gif" alt="본문삽입" onclick="AzUploaderInsertWysiwyg(\''+uploader.id+'\','+data[0]+');" class="pointer" />';
		sHTML+= '<img src="'+uploader.skinDir+'/images/btn_delete_thin.gif" alt="삭제" onclick="AzUploaderDeleteFile(\''+uploader.id+'\','+data[0]+');" class="pointer" /></span></div>';
	}

	object.innerHTML+= sHTML;
}

function AzUploaderSetDefaultImage(id,file) {
	if (confirm("해당 이미지를 대표이미지로 선택하시겠습니까?") == true) {
		var uploader = AzUploaderComponent.get(id);

		if (document.getElementById("AzUploadedFileImage-"+document.forms[uploader.formElement].image.value)) {
			document.getElementById("AzUploadedFileImage-"+document.forms[uploader.formElement].image.value).className = "imageoff";
		}

		document.forms[uploader.formElement].image.value = file;
		document.getElementById("AzUploadedFileImage-"+file).className = "imageon";
	}
}

function AzUploaderInsertWysiwyg(id,file) {
	var uploader = AzUploaderComponent.get(id);
	var data = new Array();
	
	if (uploader.formElement) {
		var fileInput = document.forms[uploader.formElement].getElementsByTagName("input");
		for (var i=0, loop=fileInput.length;i<loop;i++) {
			if (fileInput[i].getAttribute("name") == "file[]" && fileInput[i].value.split("|").shift() == file) {
				data = fileInput[i].value.split("|");
				break;
			}
		}
	}
	
	if (data.length == 0) return;

	var sHTML = "";
	if (data[1] == "IMG") {
		sHTML+= '<img name="InsertFile" file="'+data[0]+'" src="'+uploader.moduleDir+'/exec/ShowImage.do.php?idx='+data[0]+'" />';
	} else if (data[1] == "MOV") {
		sHTML+= '<img name="InsertFile" src="'+uploader.skinDir+'/images/t.gif" file="'+data[0]+'" movie="'+uploader.moduleDir+'/exec/FileDownload.do.php?idx='+data[0]+'" class="movie" style="width:320px; height:240px;" />';
	} else {
		sHTML+= '<a name="InsertFile" file="'+data[0]+'" href="'+uploader.moduleDir+'/exec/FileDownload.do.php?idx='+data[0]+'" />'+data[2]+' ('+AzUploaderFileSize(data[3])+')</a>';
	}
	oEditors.getById[uploader.wysiwygElement].exec("PASTE_HTML",[sHTML]);
}

function AzUploaderDeleteFile(id,file) {
	var uploader = AzUploaderComponent.get(id);
	if (uploader.wysiwygElement && confirm("해당 파일을 정말 삭제하시겠습니까?\n본문에 삽입되어있는 파일을 삭제할 경우, 본문에서도 삭제됩니다.") == false) return;
	
	if (uploader.formElement) {
		var fileInput = document.forms[uploader.formElement].getElementsByTagName("input");
		for (var i=0, loop=fileInput.length;i<loop;i++) {
			if (fileInput[i].getAttribute("name") == "file[]" && fileInput[i].value.split("|").shift() == file) {
				var data = fileInput[i].value.split("|");
				fileInput[i].value = file;
				break;
			}
		}
	}

	if (data[1] == "IMG" && uploader.formElement && document.forms[uploader.formElement].image) {
		if (document.forms[uploader.formElement].image.value == data[0]) {
			document.forms[uploader.formElement].image.value = "";
			for (var i=0, loop=fileInput.length;i<loop;i++) {
				if (fileInput[i].getAttribute("name") == "file[]" && fileInput[i].value.split("|").length != 1) {
					var temp = fileInput[i].value.split("|");
					if (temp[1] == "IMG") {
						document.forms[uploader.formElement].image.value = temp[0];
						document.getElementById("AzUploadedFileImage-"+temp[0]).className = "imageon";
						break;
					}
				}
			}
		}
	}

	if (uploader.wysiwygElement) {
		var content = oEditors.getById[uploader.wysiwygElement].getWYSIWYGDocument().body;
		if (data[1] == "IMG") {
			var insertFiles = content.getElementsByTagName("img");
		} else {
			var insertFiles = content.getElementsByTagName("a");
		}
		for (var i=insertFiles.length-1;i>=0;i--) {
			if (insertFiles[i].getAttribute("name") == "InsertFile" && insertFiles[i].getAttribute("file") == data[0]) {
				content.appendChild(insertFiles[i]);
				content.removeChild(insertFiles[i]);
			}
		}
	}
	
	document.getElementById("AzUploadedFile-"+data[0]).style.display = "none";
	
	if (uploader.callback != null) {
		uploader.callback();
	}
}