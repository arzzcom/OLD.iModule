var WebHardUploaderIsIE = '\v'=='v';

function WebHardUploaderRenderer(id,rendered) {
	WebHardUploaderVars = WebHardUploaderVars + "&id="+id;

	if (WebHardUploaderIsIE == true) {
		var WebHardUploaderHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="'+WebHardUploaderWidth+'" height="'+WebHardUploaderHeight+'" id="'+id+'" align="middle">';
		WebHardUploaderHTML+= '<param name="allowScriptAccess" value="always" />';
		WebHardUploaderHTML+= '<param name="flashVars" value="'+WebHardUploaderVars+'" />';
		WebHardUploaderHTML+= '<param name="movie" value="'+WebHardUploaderURL+'" />';
		WebHardUploaderHTML+= '<param name="quality" value="high" />';
		WebHardUploaderHTML+= '<param name="wmode" value="transparent" />';
		WebHardUploaderHTML+= '<embed src="'+WebHardUploaderURL+'" quality="high" wmode="transparent" style="width:'+WebHardUploaderWidth+'px; height:'+WebHardUploaderHeight+'px;" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+WebHardUploaderVars+'"></embed>';
		WebHardUploaderHTML+= '</object>';

		if (rendered) document.getElementById(rendered).innerHTML = WebHardUploaderHTML;
		else document.write(WebHardUploaderHTML);
		eval("window."+id+" = document.getElementById('"+id+"');");
	} else {
		var WebHardUploaderHTML = '<embed id="'+id+'" src="'+WebHardUploaderURL+'" quality="high" wmode="transparent" style="width:'+WebHardUploaderWidth+'px; height:'+WebHardUploaderHeight+'px;" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+WebHardUploaderVars+'"></embed>';
		if (rendered) document.getElementById(rendered).innerHTML = WebHardUploaderHTML;
		else document.write(WebHardUploaderHTML);
	}
}

function WebHardUploaderUpload(id,dir) {
	document.getElementById(id).FileUpload(encodeURIComponent(dir));
}

// 모듈 리턴부
function WebHardUploaderError(code,id) {
	switch (code) {
		case 101 :
			var msg = "버튼이미지가 정의되지 않았습니다.";
		break;

		case 102 :
			var msg = "버튼이미지를 로딩할 수 없습니다.\n경로를 다시 확인하여 주십시오.";
		break;

		case 201 :
			var msg = "업로드경로가 정의되지 않았습니다.";
		break;

		case 202 :
			var msg = "업로드경로가 유효하지 않습니다.\n경로를 다시 확인하여 주십시오.";
		break;

		case 203 :
			var msg = "업로드경로에 파일을 업로드할 수 없습니다.\n업로드 도메인은 WebHardUploader가 위치한 도메인과 일치하여야 합니다.";
		break;

		case 204 :
			var msg = "업로드할 파일이 없습니다.";
		break;

		case 999 :
			var msg = "WebHardUploader가 로딩되는 도중 문제가 발생하였습니다.";
		break;
	}

	WebHardUploaderErrorByUser(msg,id);
}

function WebHardUploaderSelectedFile(fileList,id) {
	var fileInfor = new Array();
	for (var i=0, loop=fileList.length;i<loop;i++) {
		fileInfor[i] = {};
		fileInfor[i].idx = fileList[i][0];
		fileInfor[i].name = fileList[i][1];
		fileInfor[i].size = fileList[i][2];
	}

	WebHardUploaderSelectedFileByUser(fileInfor,id);
}

function WebHardUploaderUploadedFile(file,id) {
	var fileInfor = {};
	fileInfor.idx = file[0];
	fileInfor.name = file[1];
	fileInfor.size = file[2];
	fileInfor.server = file[3];

	WebHardUploaderUploadedFileByUser(fileInfor,id);
}

function WebHardUploaderDeleteFile(idxs) {
	return WebHardUploader.fileDelete(idxs);
}

function WebHardUploaderUploadedComplete(id) {
	WebHardUploaderUploadedCompleteByUser(id);
}

function WebHardUploaderProgress(infor,id) {
	var fileInfor = {};
	fileInfor.file = {}
	fileInfor.file.idx = infor[0];
	fileInfor.file.name = infor[1];
	fileInfor.file.size = infor[2];

	fileInfor.uploaded = {}
	fileInfor.uploaded.file = infor[3];
	fileInfor.uploaded.total = infor[4];

	fileInfor.time = {}
	fileInfor.time.file = infor[5];
	fileInfor.time.total = infor[6];
	fileInfor.time.remain = infor[7];

	fileInfor.speed = {}
	fileInfor.speed.file = infor[8];
	fileInfor.speed.total = infor[9];

	fileInfor.total = {}
	fileInfor.total.size = infor[10];
	fileInfor.total.count = infor[11];
	fileInfor.total.upload = infor[12];

	WebHardUploaderProgressByUser(fileInfor,id);
}

// 기타 유용한 함수
function WebHardUploaderFileSize(size) {
	if(size < 1024) {
		return size + "B";
	} else if(size < 1048576) {
		return (size/1024).toFixed(2) + "KiB";
	} else {
		return (size/1048576).toFixed(2) + "MiB";
	}
}