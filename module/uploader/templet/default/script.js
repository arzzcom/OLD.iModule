function ModuleUploaderDoneBySkin(id,o,f) {
	var objectData = {id:id,o:o,f:f};

	var file = $("<div>").attr("id",id+"File"+f.idx).addClass("item");
	file.append($("<input>").attr("type","hidden").attr("name","file[]").attr("value",f.idx));
	file.data("file",objectData);
	
	if (f.type == "IMG") {
		var target = $("#"+id+"ImageList");
		
		if (o.formObject && $("form[name="+o.formObject+"] > input[name=image]").length > 0) {
			if ($("form[name="+o.formObject+"] > input[name=image]").val() == "" || $("form[name="+o.formObject+"] > input[name=image]").val() == "0" || $("form[name="+o.formObject+"] > input[name=image]").val() == f.idx) {
				file.addClass("imageon");
				$("form[name="+o.formObject+"] > input[name=image]").val(f.idx);
			} else {
				file.addClass("imageoff");
			}
			
			file.append($("<img>").attr("src",f.thumbnail).on("click",ModuleUploaderSetDefaultImage));
		} else {
			var file = $("<div>").append($("<img>").attr("src",f.thumbnail));
		}
		
		if (o.wysiwygObject) {
			file.append($("<div>").addClass("insertButton").data("file",objectData).on("click",ModuleUploaderInsertWysiwyg));
		}
		
		var info = $("<div>").addClass("text");
		info.append($("<div>").addClass("size").text(ModuleUploaderFileSize(f.size)));
		info.append($("<div>").addClass("deleteButton").data("file",objectData).on("click",ModuleUploaderFileDelete));
		
		file.append(info);
	} else {
		var target = $("#"+id+"FileList");
		file.append($("<span>").html(f.name+" ("+ModuleUploaderFileSize(f.size)+")"));
		if (o.wysiwygObject) {
			file.append($("<span>").addClass("insertButton").data("file",objectData).on("click",ModuleUploaderInsertWysiwyg));
		}
		file.append($("<span>").addClass("deleteButton").data("file",objectData).on("click",ModuleUploaderFileDelete));
	}
	
	target.append(file);
}

function ModuleUploaderSetDefaultImage() {
	if (confirm("해당 이미지를 대표이미지로 선택하시겠습니까?") == true) {
		var file = $(this).parent().data("file");
		
		$($("#"+file.id+"ImageList").find(".imageon")).removeClass("imageon").addClass("imageoff");
		$("form[name="+file.o.formObject+"] > input[name=image]").val(file.f.idx);
		
		$(this).parent().removeClass("imageoff").addClass("imageon");
	}
}

function ModuleUploaderInsertWysiwyg() {
	var file = $(this).parent().data("file");
	
	var sHTML = "";
	if (file.f.type == "IMG") {
		sHTML+= '<img name="InsertFile" file="'+file.f.idx+'" src="'+file.o.moduleDir+'/exec/ShowImage.do.php?idx='+file.f.idx+'" />';
	} else {
		sHTML+= '<a name="InsertFile" file="'+file.f.idx+'" href="'+file.o.moduleDir+'/exec/FileDownload.do.php?idx='+file.f.idx+'" class="btn btn-sm btn-primary" />'+file.f.name+' ('+ModuleUploaderFileSize(file.f.size)+')</a>';
	}
	oEditors.getById[file.o.wysiwygObject].exec("PASTE_HTML",[sHTML]);
}

function ModuleUploaderFileDelete() {
	var file = $(this).data("file");
	
	if (confirm("해당 파일을 정말 삭제하시겠습니까?\n본문에 삽입되어있는 파일을 삭제할 경우, 본문에서도 삭제됩니다.") == false) return;
	
	$("#"+file.id+"File"+file.f.idx).hide();
	
	if (file.o.formObject) {
		var fileInput = $("form[name="+file.o.formObject+"]").find("input[name='file[]']");
		for (var i=0, loop=fileInput.length;i<loop;i++) {
			if (fileInput[i].value == file.f.idx) {
				fileInput[i].value = "@"+file.f.idx;
			}
		}
	}
	
	if (file.f.type == "IMG" && file.o.formObject && $("form[name="+file.o.formObject+"] > input[name=image]").length > 0 && $("form[name="+file.o.formObject+"] > input[name=image]").val() == file.f.idx) {
		$("form[name="+file.o.formObject+"] > input[name=image]").val("");
		var imageList = $("#"+file.id+"ImageList > DIV");
		for (var i=0, loop=imageList.length;i<loop;i++) {
			if ($(imageList[i]).css("display") != "none") {
				var data = $(imageList[i]).data("file");
				$("form[name="+file.o.formObject+"] > input[name=image]").val(data.f.idx);
				$(imageList[i]).removeClass("imageoff").addClass("imageon");
				break;
			}
		}
	}
	
	if (file.o.wysiwygObject) {
		var content = $($(oEditors.getById[file.o.wysiwygObject].getWYSIWYGDocument().body).find("*[file="+file.f.idx+"]")).remove();
	}
}