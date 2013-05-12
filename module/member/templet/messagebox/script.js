function ToggleMethod(method) {
	document.getElementById("List"+method).style.display = document.getElementById("List"+method).style.display == "none" ? "" : "none";
	document.getElementById("Method"+method).className = document.getElementById("Method"+method).className == "open" ? "close" : "open";
}

function PrintMessage(list,message) {
	var object = document.getElementById("MessageList");
	var type = "";
	for (var i=0, loop=message.length;i<loop;i++) {
		var msg = document.createElement("div");
		msg.className = "viewerMessgeArea viewerMessge"+message[i].type;
		var sHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed">';
		sHTML+= '<tr>';
		var messageContent = '<td class="viewerMessage" style="width:400px;"><div class="talkTop"></div><div class="talkContent"><div class="date">'+message[i].reg_date+'</div>'+message[i].message+(message[i].url ? '<div class="url" onclick="OpenPopup(\''+ENV.dir+message[i].url+'\',700,500,true);">'+message[i].url+'</div>' : '')+'</div><div class="talkBottom"></div></td>';
		var messagePhoto = '<td class="talkPhoto" style="width:100%;"><img src="'+message[i].fromPhoto+'" /></td>';
		
		if (message[i].type == "receive") {
			sHTML+= messagePhoto+messageContent;
		} else {
			sHTML+= messageContent+messagePhoto;
		}
		sHTML+= '</tr>';
		sHTML+= '</table>';
		
		msg.innerHTML = sHTML;
		if (list == "next") {
			object.appendChild(msg);
		} else {
			if (object.getElementsByTagName("div").length > 0) object.insertBefore(msg,object.getElementsByTagName("div")[0]);
			if (message.length < 10) document.getElementById("MessageMore").style.display = "none";
		}
		
		type = message[i].type;
	}
	
	if (list == "next" && message.length > 0) {
		if (type == "receive") document.getElementById("MessageChecker").playSound("received");
		else document.getElementById("MessageChecker").playSound("sent");
		object.parentNode.scrollTop = object.parentNode.scrollHeight;
	}
}

function SelectAll(checked) {
	var object = document.getElementById("MessageList").getElementsByTagName("input");
	for (var i=0, loop=object.length;i<loop;i++) {
		object[i].checked = checked;
	}
}

function DeleteAll() {
	var idxs = new Array();
	var object = document.getElementById("MessageList").getElementsByTagName("input");
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].checked == true) {
			idxs.push(object[i].value);
		}
	}
	
	var idx = idxs.join(",");
	if (idx) {
		if (confirm("선택한 메세지를 삭제하시겠습니까?") == true) {
			GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=deleteMessage&idx="+idx,"DeleteAllInner");
		}
	}
}

function DeleteAllInner(XML) {
	location.href = location.href;
}