if (isIncludeBoard === undefined) {
	var isIncludeBoard = true;

	var UsedWysiwyg = new Array();
	var ObserveData = new Array();

	function AutoSaveSendData(geter) {
		switch (geter) {
			case "url" :
				return "../exec/Board.do.php?action=autosave";
			break;

			case "data" :
				var isChange = false;
				var data = new Array();
				var object = document.forms["ModuleBoardPost"];
				for (var i=0, loop=UsedWysiwyg.length;i<loop;i++) {
					oEditors.getById[UsedWysiwyg[i]].exec("UPDATE_IR_FIELD",[]);
				}

				var file = new Array();

				for (var i=0, loop=object.length;i<loop;i++) {
					if (object[i].getAttribute("opserve") == "true") {
						if (!ObserveData[object[i].getAttribute("name")]) ObserveData[object[i].getAttribute("name")] = object[i].value;
						if (ObserveData[object[i].getAttribute("name")] != object[i].value) {
							isChange = true;
						}
						ObserveData[object[i].getAttribute("name")] = object[i].value;
					}

					if (object[i].getAttribute("autosave") == "true") {
						var temp = [object[i].getAttribute("name"),object[i].value];
						data.push(temp);
					}

					if (object[i].getAttribute("name") == "file[]") {
						file.push(object[i].value);
					}
				}

				data.push(["file",file.join(",")]);
				data.push(["repto",document.forms["ModuleBoardPost"].idx.value],["bid",document.forms["ModuleBoardPost"].bid.value]);
				if (isChange == true) document.getElementById("ModuleBoardAutoSaver").save(data);
			break;
		}
	}

	function AutoSaveComplete(result) {
		var object = document.getElementById("AutoSaverAlertBox");

		if (result != "FALSE") {
			object.style.top = document.documentElement.scrollTop+"px";
			object.style.right = "0px";
			object.innerHTML = "<span class=bold>"+result+"</span>에 자동으로 저장되었습니다.";
			object.style.display = "";

			setTimeout("AutoSaveComplete('FALSE')",10000);
		} else {
			object.style.display = "none";
		}
	}

	function GetAutoSave(bid,tid,date,path,use_uploader) {
		if (confirm(date+"에 자동저장된 임시저장본이 있습니다.\n해당 내용을 불러오시겠습니까?\n취소할 경우, 자동저장된 임시저장본은 삭제됩니다.") == true) {
			var InnerFunctionValue = new Array();
			InnerFunctionValue[0] = tid;
			InnerFunctionValue[1] = use_uploader;
			GetHttpRequestXML(path,"action=autosave&bid="+bid+"&mode=get&tid="+tid,"GetAutoSaveInner",InnerFunctionValue);
		} else {
			GetHttpRequestXML(path,"action=autosave&bid="+bid+"&mode=delete&tid="+tid);
		}
	}

	function GetAutoSaveInner(XML,tid,use_uploader) {
		if (XML) {
			var object = document.forms["ModuleBoardPost"];
			var root = XML.documentElement;

			if (root.childNodes.length > 0) {
				for (var i=0, loop=root.childNodes.length;i<loop;i++) {
					var data = root.childNodes.item(i);

					if (object[data.getAttribute("field")]) {
						object[data.getAttribute("field")].value = data.getAttribute("value");
					}
				}

				if (use_uploader == "TRUE") {
					AzUploaderComponent.load("autosave="+tid);
				} else {
					if (data.getAttribute("field") == "file") {
						GetSaveFile(data.getAttribute("value"),"ModuleBoardPost");
					}
				}
			}

			for (var i=0, loop=UsedWysiwyg.length;i<loop;i++) {
				oEditors.getById[UsedWysiwyg[i]].exec("SET_IR",[document.getElementById(UsedWysiwyg[i]).value]);
			}
		}
	}

	function CheckPost(object) {
		for (var i=0, loop=UsedWysiwyg.length;i<loop;i++) {
			oEditors.getById[UsedWysiwyg[i]].exec("UPDATE_IR_FIELD",[]);
		}

		for (var i=0, loop=object.length;i<loop;i++) {
			if (object[i].getAttribute("blank") && !object[i].value) {
				alert(object[i].getAttribute("blank"));
				object[i].focus();
				return false;
			}
		}

		return true;
	}

	function CheckMent(object) {
		for (var i=0, loop=UsedWysiwyg.length;i<loop;i++) {
			oEditors.getById[UsedWysiwyg[i]].exec("UPDATE_IR_FIELD",[]);
		}

		for (var i=0, loop=object.length;i<loop;i++) {
			if (object[i].getAttribute("blank") && !object[i].value) {
				alert(object[i].getAttribute("blank"));
				object[i].focus();
				return false;
			}
		}

		return true;
	}

	function PostVote(idx) {
		GetHttpRequestXML(ENV.dir+"/module/board/exec/Board.do.php","action=vote&idx="+idx,"PostVoteInner");
	}

	function PostVoteInner(XML) {
		if (XML) {
			var result = XML.documentElement.childNodes.item(0);
			alert(result.getAttribute("msg"));
		}
	}

	function ReplyMent(idx,midx) {
		var reply = document.forms["ModuleBoardMent"+idx]["parent"].value;
		if (!reply) var oForm = document.getElementById("PostMentForm"+idx);
		else var oForm = document.getElementById("MentReplyForm"+reply);
		var rForm = document.getElementById("MentReplyForm"+midx);

		var oTextarea = document.getElementById("MentWrite"+idx);
		var oTextareaWidth = oTextarea.style.width;
		var oTextareaHeight = oTextarea.style.height;
		document.getElementById("WrapMentWrite"+idx).innerHTML = '<textarea name="content" id="MentWrite'+idx+'" style="width:'+oTextareaWidth+'; height:'+oTextareaHeight+'" blank="'+oTextarea.getAttribute("blank")+'"></textarea>';

		if (document.getElementById("WrapMentUploader"+idx)) document.getElementById("WrapMentUploader"+idx).innerHTML = '';

		if (midx == reply) {
			document.getElementById("PostMentForm"+idx).innerHTML = oForm.innerHTML;
			document.forms["ModuleBoardMent"+idx]["parent"].value = "";
		} else {
			rForm.innerHTML = oForm.innerHTML;
			document.forms["ModuleBoardMent"+idx]["parent"].value = midx;
		}
		oForm.innerHTML = "";

		nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"MentWrite"+idx,sSkinURI:ENV.dir+"/module/wysiwyg/wysiwyg.php",fCreator:"createSEditorInIFrame"});
		if (document.getElementById("WrapMentUploader"+idx)) {
			AzUploaderComponent.get("MentUploader"+idx).render("WrapMentUploader"+idx);
			//AzUploaderRenderer("MentUploader"+idx,"WrapMentUploader"+idx);
		}
	}

	function SelectMent(idx) {
		if (confirm("답변을 채택하면, 채택자 및 답변자에게 소정의 포인트가 지급됩니다.\n답변채택은 하나의 질문에 하나의 답변밖에 하지 못합니다. 채택하시겠습니까?") == true) {
			GetHttpRequestXML(ENV.dir+"/module/board/exec/Board.do.php","action=select&idx="+idx,"SelectMentInner");
		}
	}
	
	function CompletePost(idx) {
		if (confirm("해당 글을 미해결완료로 처리하시겠습니까?") == true) {
			GetHttpRequestXML(ENV.dir+"/module/board/exec/Board.do.php","action=complete&idx="+idx,"CompletePostInner");
		}
	}

	function SelectMentInner(XML) {
		if (XML) {
			var result = XML.documentElement.childNodes.item(0);
			alert(result.getAttribute("msg"));
			if (result.getAttribute("success") == "TRUE") {
				location.href = location.href;
			}
		}
	}
	
	function CompletePostInner(XML) {
		if (XML) {
			var result = XML.documentElement.childNodes.item(0);
			alert(result.getAttribute("msg"));
			if (result.getAttribute("success") == "TRUE") {
				location.href = location.href;
			}
		}
	}

	function ReplyMentPosition(pidx,idx) {
		var pArea = document.getElementById("MentReplyList"+pidx);
		var rArea = document.getElementById("ReplyMent"+idx);

		pArea.innerHTML+= rArea.innerHTML;
		rArea.innerHTML = "";
	}

	function ListSelectCategory(text,value) {
		if (!document.forms["ModuleBoardSearch"]["category"]) {
			var form = document.forms["ModuleBoardSearch"];
			var input = document.createElement("input");
			input.setAttribute("name","category");
			input.setAttribute("type","hidden");
			form.appendChild(input);
		}
		document.forms["ModuleBoardSearch"]["category"].value = value;
		document.forms["ModuleBoardSearch"].submit();
	}
	
	function ListSelectSelectAnswer(text,value) {
		if (!document.forms["ModuleBoardSearch"]["select"]) {
			var form = document.forms["ModuleBoardSearch"];
			var input = document.createElement("input");
			input.setAttribute("name","select");
			input.setAttribute("type","hidden");
			form.appendChild(input);
		}
		document.forms["ModuleBoardSearch"]["select"].value = value;
		document.forms["ModuleBoardSearch"].submit();
	}

	function WriteSelectCategory(text,value) {
		document.forms["ModuleBoardPost"]["category"].value = value;
	}

	function ToggleUserMenu(id,object,event) {
		var e = event ? event : window.event;
		var userMenu = document.getElementById(id).getElementsByTagName("div")[0];
		userMenu.innerHTML = "";

		if (object.idx) {
			var menu = document.createElement("div");
			menu.className = "UserMenuItem SendMessage";
			menu.innerHTML = "쪽지보내기";
			menu.setAttribute("mno",object.idx);
			menu.onclick = function() { OpenMessage(this.getAttribute("mno")); }

			userMenu.appendChild(menu);

			var menu = document.createElement("div");
			menu.className = "UserMenuItem PointGift";
			menu.innerHTML = "포인트선물하기";
			menu.setAttribute("mno",object.idx);
			menu.onclick = function() { OpenPointGift(this.getAttribute("mno")); }

			userMenu.appendChild(menu);
		}

		if (object.email) {
			var menu = document.createElement("div");
			menu.className = "UserMenuItem SendEmail";
			menu.innerHTML = "이메일보내기";
			menu.setAttribute("email",object.email);
			menu.onclick = function() { location.href = "mailto:"+this.getAttribute("email"); }

			userMenu.appendChild(menu);
		}

		if (object.homepage) {
			var menu = document.createElement("div");
			menu.className = "UserMenuItem GoHomepage";
			menu.innerHTML = "홈페이지가기";
			menu.setAttribute("homepage",object.homepage);
			menu.onclick = function() { window.open(this.getAttribute("homepage")); }

			userMenu.appendChild(menu);
		}

		var scrollTop = Math.max(document.documentElement.scrollTop,document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft,document.body.scrollLeft);

		var offsetTop = GetRealOffsetTop(userMenu.parentNode);
		var offsetLeft = GetRealOffsetLeft(userMenu.parentNode);
		var top = e.clientY+scrollTop-offsetTop;
		var left = e.clientX+scrollLeft-offsetLeft;

		userMenu.style.display = "";
		userMenu.style.top = top+"px";
		userMenu.style.left = left+"px";

		GlobalToggleList[id] = userMenu;
	}

	/***********************************************************************************
	 * Event Listeners
	 ***********************************************************************************/
	$(document).ready(function() {
		var contentSection = $(".smartOutput");
		var contentSectionMobile = $(".smartOutputMobile");
		
		for (var i=0, loop=contentSection.length;i<loop;i++) {
			$($(contentSection[i]).find("img")).attr("maxWidth",$(contentSection[i]).innerWidth());
			$(contentSection[i]).find("img").load(function() {
				if ($(this).width() > $(this).attr("maxWidth")) {
					$(this).css("width",$(this).attr("maxWidth"));
				}
			});
			
			$($(contentSection[i]).find("a")).attr("target","_blank");
		}
		
		for (var i=0, loop=contentSectionMobile.length;i<loop;i++) {
			$($(contentSectionMobile[i]).find("img")).attr("maxWidth",$(contentSectionMobile[i]).innerWidth());
			$(contentSectionMobile[i]).find("img").load(function() {
				if ($(this).width() > $(this).attr("maxWidth")) {
					$(this).css("width",$(this).attr("maxWidth"));
					$(this).css("cursor","pointer");
					$(this).parent().css("lineHeight",1);
					$(this).parent().append($("<div>").css("fontSize","12px").css("color","gray").html("이미지 클릭시 원래크기로 볼 수 있습니다."));
					$(this).click(function() {
						window.open($(this).attr("src"));
					});
				}
			});
			
			$($(contentSectionMobile[i]).find("a")).attr("target","_blank");
		}
	});
}