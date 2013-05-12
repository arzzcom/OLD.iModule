function TrackerMemberSignInType(checked) {
	if (checked == true) {
		document.getElementById("TrackerSignInLogin").style.display = "";
		document.getElementById("TrackerSignInForm").style.display = "none";
	} else {
		document.getElementById("TrackerSignInLogin").style.display = "none";
		document.getElementById("TrackerSignInForm").style.display = "";
	}
}

function TrackerWriteInnerFormHeight() {
	var height = Math.max(document.documentElement.offsetHeight,document.documentElement.scrollHeight,document.documentElement.clientHeight);
	if (parent.document.getElementById("TrackerWriteInnerForm")) {
		parent.document.getElementById("TrackerWriteInnerForm").style.height = height+"px";
	}
}

function TrackerMentInnerFormHeight() {
	var height = Math.max(document.documentElement.offsetHeight,document.documentElement.scrollHeight,document.documentElement.clientHeight);
	var temp = location.href.split("?").pop().split("&");
	for (var i=0, loop=temp.length;i<loop;i++) {
		var token = temp[i].split("=");
		if (token[0] == "mode") {
			var mode = token[1];
		} else if (token[0] == "repto") {
			var repto = token[1];
		}
	}
	
	if (mode == "torrent") {
		parent.document.getElementById("TrackerTorrentMentList-"+repto).style.height = height+"px";
	} else if (mode == "group") {
		parent.document.getElementById("TrackerGroupMentList-"+repto).style.height = height+"px";
	}
}

function TrackerWriteNumberOnly(object) {
	if (object.value && object.value != parseInt(object.value)) {
		alert("숫자로만 입력하여 주십시오.\nPlease, Insert numberic only.");
		object.value = "";
		object.focus();
	}
}

function TrackerWriteIsPackage(object) {
	if (object.checked == true) {
		document.forms["TrackerPost"].episode.value = "";
		document.forms["TrackerPost"].episode.disabled = "disabled";
		document.getElementById("TrackerListTitleLabel").innerHTML = object.getAttribute("is_pack_title");
	} else {
		document.forms["TrackerPost"].episode.value = "";
		document.forms["TrackerPost"].episode.disabled = "";
		document.getElementById("TrackerListTitleLabel").innerHTML = object.getAttribute("is_unpack_title");
	}
}

function TrackerTorrentSearch(form) {
	var isSubmit = false;
	if (form === undefined) {
		form = document.forms["TrackerTorrentSearchForm"];
		isSubmit = true;
	}
	var category1 = new Array();
	var category2 = new Array();
	var category3 = new Array();
	var tag = new Array();
	
	var input = form.getElementsByTagName("input");
	
	for (var i=0, loop=input.length;i<loop;i++) {
		if (input[i].type == "checkbox") {
			if (input[i].checked == true) {
				switch (input[i].name) {
					case "category1[]" :
						category1.push(input[i].value);
						break;
						
					case "tag[]" :
						tag.push(input[i].value);
						break;
				}
			}
			input[i].disabled = "disabled";
		}
	}
	
	form.category1.value = category1.join(",");
	
	form.tag.value = tag.join(",");

	for (var i=0, loop=input.length;i<loop;i++) {
		if (input[i].type == "text" && !input[i].value) {
			input[i].disabled = true;
		}
	}

	if (isSubmit == true) form.submit();
}

function TrackerTorrentSearchForCheckbox(field,idx) {
	var form = document.forms["TrackerTorrentSearchForm"];
	var input = form.getElementsByTagName("input");
	
	if (field.indexOf("category") == 0) {
		for (var i=0, loop=input.length;i<loop;i++) {
			if (input[i].type == "checkbox" && input[i].name == "tag[]") {
				input[i].checked = "";
			}
		}
	}
	
	for (var i=0, loop=input.length;i<loop;i++) {
		if (input[i].type == "checkbox" && input[i].name == field+"[]") {
			input[i].checked = "";
			if (input[i].value == idx) input[i].checked = "checked";
		}
	}
	
	TrackerTorrentSearch();
}

/****************************************************************************************
 * Live Keyword
 ***************************************************************************************/



/****************************************************************************************
 * Group Search API
 ***************************************************************************************/
 
var gGroupLiveSearchTimeout = null;
var gGroupLiveSearchLastKeyword = null;
var gGroupLiveSearchListPoint = null;
var gGroupLiveSearchXML = null;

function TrackerGroupLiveSearchStart(category,addon) {
	TrackerGroupLiveSearch(category,(addon ? addon : ''));
}

function TrackerGroupLiveSearchEnd() {
	if (gGroupLiveSearchTimeout != null) {
		clearTimeout(gGroupLiveSearchTimeout);
		gGroupLiveSearchTimeout = null;
	}

	gGroupLiveSearchListPoint = null;
	
	setTimeout('document.getElementById("TrackerGroupSearchList").style.display = "none"',100);
}

function TrackerGroupLiveSearch(category,addon) {
	var object = document.getElementById("TrackerGroupSearchInput");
	
	if (object.value && object.value != gGroupLiveSearchLastKeyword) {
		gGroupLiveSearchLastKeyword = object.value;
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=group&category="+category+"&addon="+addon+"&keyword="+GetAjaxParam(object.value),"TrackerGroupLiveSearchInner");
	} else if (!object.value) {
		gGroupLiveSearchLastKeyword = null;
	}
	
	if (!object.value) TrackerGroupLiveSearchInner();
	
	gGroupLiveSearchTimeout = setTimeout("TrackerGroupLiveSearch("+category+",'"+addon+"')",500);
}

function TrackerGroupLiveSearchInner(XML) {
	var object = document.getElementById("TrackerGroupSearchList");
	var input = document.getElementById("TrackerGroupSearchInput");
	object.innerHTML = "";
	
	if (XML) {
		var root = XML.documentElement;
		
		var list = document.createElement("ul");
		
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var item = document.createElement("li");
			item.setAttribute("title",data.getAttribute("title")+(data.getAttribute("season") != "0" ? " 시즌 "+data.getAttribute("season") : "")+(data.getAttribute("eng_title") ? " ("+data.getAttribute("eng_title")+")" : "")+", "+data.getAttribute("year"));
			item.setAttribute("idx",i);
			var sHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="60" /><col width="100%" /><tr><td><img src="'+data.getAttribute("thumbnail")+'" class="thumbnail" /></td><td class="vTop">';
			sHTML+= '<div class="title">';
			sHTML+= data.getAttribute("title");
			if (data.getAttribute("season") != "0") sHTML+= ' 시즌 '+data.getAttribute("season");
			if (data.getAttribute("eng_title").length > 0) sHTML+= ' <span class="eng">('+data.getAttribute("eng_title")+')</span>';
			sHTML+= '<span class="eng">, '+data.getAttribute("year")+'</span>';
			sHTML+= '</div>';
			sHTML+= '<div class="info">제작국가 <span class="value">'+data.getAttribute("nation")+'</span></div>';
			sHTML+= '<div class="info">'+input.getAttribute("artist")+' <span class="value">'+data.getAttribute("artist")+'</span></div>';
			sHTML+= '<div class="info">'+input.getAttribute("subartist")+' <span class="value">'+data.getAttribute("subartist")+'</span></div>';
			sHTML+= '</td></tr></table>';
			
			if (data.getAttribute("addon") == "false") {
				sHTML+= '<div class="registed"></div>';
			}
			item.innerHTML = sHTML;
			
			item.onclick = function() {
				var idx = parseInt(this.getAttribute("idx"));
				TrackerGroupLiveSearchListSelect(idx);
				document.getElementById("TrackerGroupSearchList").style.display = "none";
			}
			
			item.onmouseover = function() { this.className = "select"; }
			item.onmouseout = function() { this.className = ""; }
			list.appendChild(item);
		}
		
		object.appendChild(list);
		gGroupLiveSearchXML = root.childNodes;
		if (loop > 0) object.style.display = "";
		else object.style.display = "none";
	} else {
		object.style.display = "none";
	}
	
	gGroupLiveSearchListPoint = null;
}

function TrackerGroupLiveSearchListMove(event) {
	var input = document.getElementById("TrackerGroupSearchInput");
	var object = document.getElementById("TrackerGroupSearchList");
	var list = object.getElementsByTagName("li");
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;

	if (keycode == 38) {
		if (gGroupLiveSearchListPoint == null) gGroupLiveSearchListPoint = list.length;
		if (gGroupLiveSearchListPoint == 0) return;
		if (gGroupLiveSearchListPoint != list.length) {
			list[gGroupLiveSearchListPoint].className = "";
		}
		gGroupLiveSearchListPoint--;
		list[gGroupLiveSearchListPoint].className = "select";

		gGroupLiveSearchLastKeyword = list[gGroupLiveSearchListPoint].getAttribute("title");
		input.value = gGroupLiveSearchLastKeyword;

		var basePosition = gGroupLiveSearchListPoint - 2 < 0 ? 0 : gGroupLiveSearchListPoint - 2;
		var baseScrollPosition = gGroupLiveSearchListPoint * 79;
		if (object.scrollTop > baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 40) {
		if (gGroupLiveSearchListPoint == null) gGroupLiveSearchListPoint = -1;
		if (gGroupLiveSearchListPoint == list.length - 1) return;
		if (gGroupLiveSearchListPoint != -1) {
			list[gGroupLiveSearchListPoint].className = "";
		}
		gGroupLiveSearchListPoint++;
		list[gGroupLiveSearchListPoint].className = "select";

		gGroupLiveSearchLastKeyword = list[gGroupLiveSearchListPoint].getAttribute("title");
		input.value = gGroupLiveSearchLastKeyword;
		
		var basePosition = gGroupLiveSearchListPoint - 2 < 0 ? 0 : gGroupLiveSearchListPoint - 2;
		var baseScrollPosition = basePosition * 79;
		if (object.scrollTop < baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 13) {
		object.style.display = "none";
		TrackerGroupLiveSearchListSelect(gGroupLiveSearchListPoint);
		return false;
	}
}

function TrackerGroupLiveSearchListSelect(idx) {
	var input = document.getElementById("TrackerGroupSearchInput");
	var data = gGroupLiveSearchXML.item(idx);
	var form = document.forms["TrackerPost"];
	
	gGroupLiveSearchLastKeyword = data.getAttribute("title")+(data.getAttribute("season") != "0" ? " 시즌 "+data.getAttribute("season") : "")+(data.getAttribute("eng_title") ? " ("+data.getAttribute("eng_title")+")" : "")+", "+data.getAttribute("year");
	input.value = gGroupLiveSearchLastKeyword;
	input.disabled = "disabled";
	input.blur();

	document.getElementById("TrackerGroupSearchCancel").style.display = "";
	
	if (data.getAttribute("addon") != "false") {
		form.title.value = data.getAttribute("title");
		form.title.disabled = "disabled";
		if (form.eng_title) {
			form.eng_title.value = data.getAttribute("eng_title");
			if (form.eng_title.value) form.eng_title.disabled = "disabled";
		}
		if (form.artist) {
			for (var i=0,loop=data.getAttribute("artist").split(", ").length;i<loop;i++) {
				TrackerArtistItemAdd(data.getAttribute("artist").split(", ")[i]);
			}
			if (data.getAttribute("artist")) document.getElementById("TrackerArtistSearchInputArea").style.display = "none";
		}
		if (form.subartist) {
			for (var i=0,loop=data.getAttribute("subartist").split(", ").length;i<loop;i++) {
				TrackerSubArtistItemAdd(data.getAttribute("subartist").split(", ")[i]);
			}
			if (data.getAttribute("subartist")) document.getElementById("TrackerSubArtistSearchInputArea").style.display = "none";
		}
		if (form.season) {
			form.season.value = data.getAttribute("season");
			if (form.season.value) form.season.disabled = "disabled";
		}
		if (form.year) {
			form.year.value = data.getAttribute("year");
			if (form.year.value) form.year.disabled = "disabled";
		}
		if (form.nation) {
			for (var i=0,loop=data.getAttribute("nation").split(", ").length;i<loop;i++) {
				TrackerNationItemAdd(data.getAttribute("nation").split(", ")[i]);
			}
			if (data.getAttribute("nation")) document.getElementById("TrackerNationInputArea").style.display = "none";
		}
		if (form.date) {
			if (data.getAttribute("date") != "0000-00-00") form.date.value = data.getAttribute("date");
			if (form.date.value) form.date.disabled = "disabled";
		}
		
		form[data.getAttribute("addon")].value = data.getAttribute("addidx");
	} else {
		form.groupno.value = data.getAttribute("idx");
		document.getElementById("TrackerPostGroup").style.display = "none";
		TrackerWriteInnerFormHeight();
		TrackerEpisodeSearch(data.getAttribute("idx"));
	}
	
	gGroupLiveSearchListPoint = null;
	
	document.getElementById("TrackerGroupSearchList").style.display = "none"
}

function TrackerGroupLiveSearchCancel() {
	var input = document.getElementById("TrackerGroupSearchInput");
	var form = document.forms["TrackerPost"];
	
	input.value = "";
	input.disabled = "";
	input.focus();

	form.groupno.value = "";
	form.daummovie.value = "";
	form.title.value = "";
	form.title.disabled = "";
	if (form.eng_title) {
		form.eng_title.value = "";
		form.eng_title.disabled = "";
	}
	if (form.season) {
		form.season.value = "";
		if (form.season.value) form.season.disabled = "";
	}
	if (form.artist) {
		TrackerArtistItemDeleteAll();
		document.getElementById("TrackerArtistSearchInputArea").style.display = "";
	}
	if (form.subartist) {
		TrackerSubArtistItemDeleteAll();
		document.getElementById("TrackerSubArtistSearchInputArea").style.display = "";
	}
	if (form.year) {
		form.year.value = "";
		form.year.disabled = "";
	}
	if (form.nation) {
		TrackerNationItemDeleteAll();
		document.getElementById("TrackerNationInputArea").style.display = "";
	}
	if (form.date) {
		form.date.value = "";
		form.date.disabled = "";
	}
	
	document.getElementById("TrackerGroupSearchCancel").style.display = "none";
	document.getElementById("TrackerPostGroup").style.display = "";

	TrackerWriteInnerFormHeight();
	TrackerEpisodeSearch(0);
}

/****************************************************************************************
 * Daum Movie API
 ***************************************************************************************/
 /*
var gDaumMovieLiveSearchTimeout = null;
var gDaumMovieLiveSearchLastKeyword = null;
var gDaumMovieLiveSearchListPoint = null;
var gDaumMovieLiveSearchXML = null;

function TrackerDaumMovieLiveSearchStart() {
	TrackerDaumMovieLiveSearch();
}

function TrackerDaumMovieLiveSearchEnd() {
	if (gDaumMovieLiveSearchTimeout != null) {
		clearTimeout(gDaumMovieLiveSearchTimeout);
		gDaumMovieLiveSearchTimeout = null;
	}
	
	setTimeout('document.getElementById("TrackerDaumMovieSearchList").style.display = "none"',100);
}

function TrackerDaumMovieLiveSearch() {
	var object = document.getElementById("TrackerDaumMovieSearchInput");
	
	if (object.value && object.value != gDaumMovieLiveSearchLastKeyword) {
		gDaumMovieLiveSearchLastKeyword = object.value;
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=daummovie&keyword="+GetAjaxParam(object.value),"TrackerDaumMovieLiveSearchInner");
	}
	
	if (!object.value) TrackerDaumMovieLiveSearchInner();
	
	gDaumMovieLiveSearchTimeout = setTimeout(TrackerDaumMovieLiveSearch,500);
}

function TrackerDaumMovieLiveSearchInner(XML) {
	var object = document.getElementById("TrackerDaumMovieSearchList");
	object.innerHTML = "";
	
	if (XML) {
		var root = XML.documentElement;
		
		var list = document.createElement("ul");
		
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var item = document.createElement("li");
			item.setAttribute("title",data.getAttribute("title")+(data.getAttribute("eng_title") ? " ("+data.getAttribute("eng_title")+")" : "")+", "+data.getAttribute("year"));
			item.setAttribute("idx",i);
			var sHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="60" /><col width="100%" /><tr><td><img src="'+data.getAttribute("thumbnail")+'" class="thumbnail" /></td><td class="vTop">';
			sHTML+= '<div class="title">';
			sHTML+= data.getAttribute("title");
			if (data.getAttribute("eng_title").length > 0) sHTML+= ' <span class="eng">('+data.getAttribute("eng_title")+')</span>';
			sHTML+= '<span class="eng">, '+data.getAttribute("year")+'</span>';
			sHTML+= '</div>';
			sHTML+= '<div class="info">제작/장르 <span class="value">'+data.getAttribute("nation")+'/'+data.getAttribute("genre")+'</span></div>';
			sHTML+= '<div class="info">감독 <span class="value">'+data.getAttribute("director")+'</span></div>';
			sHTML+= '<div class="info">출연 <span class="value">'+data.getAttribute("actor")+'</span></div>';
			sHTML+= '</td></tr></table>';
			item.innerHTML = sHTML;
			
			item.onclick = function() {
				var idx = parseInt(this.getAttribute("idx"));
				TrackerDaumMovieLiveSearchListSelect(idx);
				document.getElementById("TrackerDaumMovieSearchList").style.display = "none";
			}
			
			item.onmouseover = function() { this.className = "select"; }
			item.onmouseout = function() { this.className = ""; }
			list.appendChild(item);
		}
		
		object.appendChild(list);
		gDaumMovieLiveSearchXML = root.childNodes;
		object.style.display = "";
	} else {
		object.style.display = "none";
	}
}

function TrackerDaumMovieLiveSearchListMove(event) {
	var input = document.getElementById("TrackerDaumMovieSearchInput");
	var object = document.getElementById("TrackerDaumMovieSearchList");
	var list = object.getElementsByTagName("li");
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;

	if (keycode == 38) {
		if (gDaumMovieLiveSearchListPoint == null) gDaumMovieLiveSearchListPoint = list.length;
		if (gDaumMovieLiveSearchListPoint == 0) return;
		if (gDaumMovieLiveSearchListPoint != list.length) {
			list[gDaumMovieLiveSearchListPoint].className = "";
		}
		gDaumMovieLiveSearchListPoint--;
		list[gDaumMovieLiveSearchListPoint].className = "select";

		gDaumMovieLiveSearchLastKeyword = list[gDaumMovieLiveSearchListPoint].getAttribute("title");
		input.value = gDaumMovieLiveSearchLastKeyword;

		var basePosition = gDaumMovieLiveSearchListPoint - 2 < 0 ? 0 : gDaumMovieLiveSearchListPoint - 2;
		var baseScrollPosition = gDaumMovieLiveSearchListPoint * 79;
		if (object.scrollTop > baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 40) {
		if (gDaumMovieLiveSearchListPoint == null) gDaumMovieLiveSearchListPoint = -1;
		if (gDaumMovieLiveSearchListPoint == list.length - 1) return;
		if (gDaumMovieLiveSearchListPoint != -1) {
			list[gDaumMovieLiveSearchListPoint].className = "";
		}
		gDaumMovieLiveSearchListPoint++;
		list[gDaumMovieLiveSearchListPoint].className = "select";

		gDaumMovieLiveSearchLastKeyword = list[gDaumMovieLiveSearchListPoint].getAttribute("title");
		input.value = gDaumMovieLiveSearchLastKeyword;
		
		var basePosition = gDaumMovieLiveSearchListPoint - 2 < 0 ? 0 : gDaumMovieLiveSearchListPoint - 2;
		var baseScrollPosition = basePosition * 79;
		if (object.scrollTop < baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 13) {
		object.style.display = "none";
		TrackerDaumMovieLiveSearchListSelect(gDaumMovieLiveSearchListPoint);
		return false;
	}
}

function TrackerDaumMovieLiveSearchListSelect(idx) {
	var input = document.getElementById("TrackerDaumMovieSearchInput");
	var data = gDaumMovieLiveSearchXML.item(idx);
	var form = document.forms["TrackerPost"];
	
	gDaumMovieLiveSearchLastKeyword = data.getAttribute("title")+(data.getAttribute("eng_title") ? " ("+data.getAttribute("eng_title")+")" : "")+", "+data.getAttribute("year");
	input.value = gDaumMovieLiveSearchLastKeyword;
	input.disabled = "disabled";
	
	form.daummovie.value = data.getAttribute("idx");
	form.title.value = data.getAttribute("title");
	form.title.disabled = "disabled";
	form.eng_title.value = data.getAttribute("eng_title");
	form.eng_title.disabled = "disabled";
	for (var i=0,loop=data.getAttribute("director").split(", ").length;i<loop;i++) {
		TrackerArtistItemAdd(data.getAttribute("director").split(", ")[i]);
	}
	for (var i=0,loop=data.getAttribute("actor").split(", ").length;i<loop;i++) {
		TrackerSubArtistItemAdd(data.getAttribute("actor").split(", ")[i]);
	}
	form.year.value = data.getAttribute("year");
	form.year.disabled = "disabled";
	for (var i=0,loop=data.getAttribute("nation").split(", ").length;i<loop;i++) {
		TrackerNationItemAdd(data.getAttribute("nation").split(", ")[i]);
	}
	if (data.getAttribute("date") != "0000-00-00") form.date.value = data.getAttribute("date");
	form.date.disabled = "disabled";
	
	document.getElementById("TrackerDaumMovieSearchCancel").style.display = "";
	document.getElementById("TrackerArtistSearchInputArea").style.display = "none";
	document.getElementById("TrackerSubArtistSearchInputArea").style.display = "none";
	document.getElementById("TrackerNationInputArea").style.display = "none";
	
	if (data.getAttribute("groupno") != "0") {
		document.getElementById("TrackerPostGroup").style.display = "none";
	}
}

function TrackerDaumMovieLiveSearchCancel() {
	var input = document.getElementById("TrackerDaumMovieSearchInput");
	var form = document.forms["TrackerPost"];
	
	input.value = "";
	input.disabled = "";
	input.focus();

	form.daummovie.value = "";
	form.title.value = "";
	form.title.disabled = "";
	form.eng_title.value = "";
	form.eng_title.disabled = "";
	
	TrackerArtistItemDeleteAll();
	TrackerSubArtistItemDeleteAll();
	
	form.year.value = "";
	form.year.disabled = "";

	TrackerNationItemDeleteAll();

	form.date.value = "";
	form.date.disabled = "";
	
	document.getElementById("TrackerDaumMovieSearchCancel").style.display = "none";
	document.getElementById("TrackerArtistSearchInputArea").style.display = "";
	document.getElementById("TrackerSubArtistSearchInputArea").style.display = "";
	document.getElementById("TrackerNationInputArea").style.display = "";
	
	document.getElementById("TrackerPostGroup").style.display = "";
}
*/
/****************************************************************************************
 * Daum TV API
 ***************************************************************************************/
/*
var gDaumTVLiveSearchTimeout = null;
var gDaumTVLiveSearchLastKeyword = null;
var gDaumTVLiveSearchListPoint = null;
var gDaumTVLiveSearchXML = null;

function TrackerDaumTVLiveSearchStart() {
	TrackerDaumTVLiveSearch();
}

function TrackerDaumTVLiveSearchEnd() {
	if (gDaumTVLiveSearchTimeout != null) {
		clearTimeout(gDaumTVLiveSearchTimeout);
		gDaumTVLiveSearchTimeout = null;
	}
	
	setTimeout('document.getElementById("TrackerDaumTVSearchList").style.display = "none"',100);
}

function TrackerDaumTVLiveSearch() {
	var object = document.getElementById("TrackerDaumTVSearchInput");
	
	if (object.value && object.value != gDaumTVLiveSearchLastKeyword) {
		gDaumTVLiveSearchLastKeyword = object.value;
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=daumtv&keyword="+GetAjaxParam(object.value),"TrackerDaumTVLiveSearchInner");
	}
	
	if (!object.value) TrackerDaumTVLiveSearchInner();
	
	gDaumTVLiveSearchTimeout = setTimeout(TrackerDaumTVLiveSearch,500);
}

function TrackerDaumTVLiveSearchInner(XML) {
	var object = document.getElementById("TrackerDaumTVSearchList");
	object.innerHTML = "";
	
	if (XML) {
		var root = XML.documentElement;
		
		var list = document.createElement("ul");
		
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var item = document.createElement("li");
			item.setAttribute("title",data.getAttribute("title")+(data.getAttribute("eng_title") ? " ("+data.getAttribute("eng_title")+")" : "")+", "+data.getAttribute("year"));
			item.setAttribute("idx",i);
			var sHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="60" /><col width="100%" /><tr><td><img src="'+data.getAttribute("thumbnail")+'" class="thumbnail" /></td><td class="vTop">';
			sHTML+= '<div class="title">';
			sHTML+= data.getAttribute("title");
			if (data.getAttribute("eng_title").length > 0) sHTML+= ' <span class="eng">('+data.getAttribute("eng_title")+')</span>';
			sHTML+= '<span class="eng">, '+(data.getAttribute("year") ? data.getAttribute("year") : 'Unknown')+'</span>';
			sHTML+= '</div>';
			sHTML+= '<div class="info">제작 <span class="value">'+(data.getAttribute("nation") ? data.getAttribute("nation") : '정보없음')+'</span></div>';
			sHTML+= '<div class="info">연출 <span class="value">'+(data.getAttribute("director") ? data.getAttribute("director") : '정보없음')+'</span></div>';
			sHTML+= '<div class="info">출연 <span class="value">'+(data.getAttribute("actor") ? data.getAttribute("actor") : '정보없음')+'</span></div>';
			sHTML+= '</td></tr></table>';
			item.innerHTML = sHTML;
			
			item.onclick = function() {
				var idx = parseInt(this.getAttribute("idx"));
				TrackerDaumTVLiveSearchListSelect(idx);
				document.getElementById("TrackerDaumTVSearchList").style.display = "none";
			}
			
			item.onmouseover = function() { this.className = "select"; }
			item.onmouseout = function() { this.className = ""; }
			list.appendChild(item);
		}
		
		object.appendChild(list);
		gDaumTVLiveSearchXML = root.childNodes;
		object.style.display = "";
	} else {
		object.style.display = "none";
	}
}

function TrackerDaumTVLiveSearchListMove(event) {
	var input = document.getElementById("TrackerDaumTVSearchInput");
	var object = document.getElementById("TrackerDaumTVSearchList");
	var list = object.getElementsByTagName("li");
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;

	if (keycode == 38) {
		if (gDaumTVLiveSearchListPoint == null) gDaumTVLiveSearchListPoint = list.length;
		if (gDaumTVLiveSearchListPoint == 0) return;
		if (gDaumTVLiveSearchListPoint != list.length) {
			list[gDaumTVLiveSearchListPoint].className = "";
		}
		gDaumTVLiveSearchListPoint--;
		list[gDaumTVLiveSearchListPoint].className = "select";

		gDaumTVLiveSearchLastKeyword = list[gDaumTVLiveSearchListPoint].getAttribute("title");
		input.value = gDaumTVLiveSearchLastKeyword;

		var basePosition = gDaumTVLiveSearchListPoint - 2 < 0 ? 0 : gDaumTVLiveSearchListPoint - 2;
		var baseScrollPosition = gDaumTVLiveSearchListPoint * 79;
		if (object.scrollTop > baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 40) {
		if (gDaumTVLiveSearchListPoint == null) gDaumTVLiveSearchListPoint = -1;
		if (gDaumTVLiveSearchListPoint == list.length - 1) return;
		if (gDaumTVLiveSearchListPoint != -1) {
			list[gDaumTVLiveSearchListPoint].className = "";
		}
		gDaumTVLiveSearchListPoint++;
		list[gDaumTVLiveSearchListPoint].className = "select";

		gDaumTVLiveSearchLastKeyword = list[gDaumTVLiveSearchListPoint].getAttribute("title");
		input.value = gDaumTVLiveSearchLastKeyword;
		
		var basePosition = gDaumTVLiveSearchListPoint - 2 < 0 ? 0 : gDaumTVLiveSearchListPoint - 2;
		var baseScrollPosition = basePosition * 79;
		if (object.scrollTop < baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 13) {
		object.style.display = "none";
		TrackerDaumTVLiveSearchListSelect(gDaumTVLiveSearchListPoint);
		return false;
	}
}

function TrackerDaumTVLiveSearchListSelect(idx) {
	var input = document.getElementById("TrackerDaumTVSearchInput");
	var data = gDaumTVLiveSearchXML.item(idx);
	var form = document.forms["TrackerPost"];
	
	gDaumTVLiveSearchLastKeyword = data.getAttribute("title")+(data.getAttribute("eng_title") ? " ("+data.getAttribute("eng_title")+")" : "")+", "+data.getAttribute("year");
	input.value = gDaumTVLiveSearchLastKeyword;
	input.disabled = "disabled";
	
	form.daummovie.value = data.getAttribute("idx");
	form.title.value = data.getAttribute("title");
	form.title.disabled = "disabled";
	form.eng_title.value = data.getAttribute("eng_title");
	form.eng_title.disabled = "disabled";
	for (var i=0,loop=data.getAttribute("director").split(", ").length;i<loop;i++) {
		TrackerArtistItemAdd(data.getAttribute("director").split(", ")[i]);
	}
	for (var i=0,loop=data.getAttribute("actor").split(", ").length;i<loop;i++) {
		TrackerSubArtistItemAdd(data.getAttribute("actor").split(", ")[i]);
	}
	form.year.value = data.getAttribute("year");
	form.year.disabled = "disabled";
	for (var i=0,loop=data.getAttribute("nation").split(", ").length;i<loop;i++) {
		TrackerNationItemAdd(data.getAttribute("nation").split(", ")[i]);
	}
	if (data.getAttribute("date") != "0000-00-00") form.date.value = data.getAttribute("date");
	form.date.disabled = "disabled";
	
	document.getElementById("TrackerDaumTVSearchCancel").style.display = "";
	document.getElementById("TrackerArtistSearchInputArea").style.display = "none";
	document.getElementById("TrackerSubArtistSearchInputArea").style.display = "none";
	document.getElementById("TrackerNationInputArea").style.display = "none";
	
	if (data.getAttribute("groupno") != "0") {
		document.getElementById("TrackerPostGroup").style.display = "none";
	}
}

function TrackerDaumTVLiveSearchCancel() {
	var input = document.getElementById("TrackerDaumTVSearchInput");
	var form = document.forms["TrackerPost"];
	
	input.value = "";
	input.disabled = "";
	input.focus();

	form.daummovie.value = "";
	form.title.value = "";
	form.title.disabled = "";
	form.eng_title.value = "";
	form.eng_title.disabled = "";
	
	TrackerArtistItemDeleteAll();
	TrackerSubArtistItemDeleteAll();
	
	form.year.value = "";
	form.year.disabled = "";

	TrackerNationItemDeleteAll();

	form.date.value = "";
	form.date.disabled = "";
	
	document.getElementById("TrackerDaumTVSearchCancel").style.display = "none";
	document.getElementById("TrackerArtistSearchInputArea").style.display = "";
	document.getElementById("TrackerSubArtistSearchInputArea").style.display = "";
	document.getElementById("TrackerNationInputArea").style.display = "";
	
	document.getElementById("TrackerPostGroup").style.display = "";
}
*/
/****************************************************************************************
 * 주 아티스트 입력 스크립트
 ***************************************************************************************/

function TrackerArtistItemInsert(event) {
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;
	
	var input = document.getElementById("TrackerArtistSearchInput");
	
	if (keycode == 188) {
		var str = input.value.replace(/^\s*/,"").replace(/\s*$/,"").replace(",","");
		if (str.length > 0) {
			TrackerArtistItemAdd(str);
			input.value = "";
		}
	}
}

function TrackerArtistItemAdd(str) {
	if (str.length == 0) return;
	var inputlist = document.getElementById("TrackerArtistSearchInputList");
	var inputarea = document.getElementById("TrackerArtistSearchInputArea");
	var input = document.getElementById("TrackerArtistSearchInput");

	for (var i=0, loop=inputlist.getElementsByTagName("div").length;i<loop;i++) {
		if (inputlist.getElementsByTagName("div")[i] === inputarea) break;
		if (inputlist.getElementsByTagName("div")[i].getAttribute("value") == str) return false;
	}
	
	var item = document.createElement("div");
	item.setAttribute("value",str);
	item.className = "item";
	item.innerHTML = str;
	item.onclick = function() {
		if (document.forms["TrackerPost"].mode.value == "write" && (document.forms["TrackerPost"].daummovie.value != "" || document.forms["TrackerPost"].groupno.value != "")) {
			alert("기존 자료를 검색하여 자동으로 입력된 데이터는 삭제할 수 없습니다.\nYou can not delete data entered automatically by searching existing data.");
		} else {
			inputlist.removeChild(this);
		}
		TrackerArtistItemComplete();
	}
	
	inputlist.insertBefore(item,inputarea);
}

function TrackerArtistItemComplete() {
	setTimeout("TrackerArtistItemCompleteInner(false)",300);
}

function TrackerArtistItemCompleteInner(isFocus) {
	var input = document.getElementById("TrackerArtistSearchInput");
	var str = input.value.replace(/^\s*/,"").replace(/\s*$/,"").replace(",","");
	if (str.length > 0) TrackerArtistItemAdd(str);
	input.value = "";
	
	var form = document.forms["TrackerPost"];
	var inputarea = document.getElementById("TrackerArtistSearchInputArea");
	var inputlist = document.getElementById("TrackerArtistSearchInputList");
	
	var values = new Array();
	for (var i=0, loop=inputlist.getElementsByTagName("div").length;i<loop;i++) {
		if (inputlist.getElementsByTagName("div")[i] === inputarea) break;
		values[i] = inputlist.getElementsByTagName("div")[i].getAttribute("value");
	}
	form.artist.value = values.join(",");
	
	if (gArtistLiveSearchTimeout != null) {
		clearTimeout(gArtistLiveSearchTimeout);
		gArtistLiveSearchTimeout = null;
	}
	
	if (isFocus == true) {
		input.focus();
		fireEvent(input,"focus");
	}
	setTimeout('document.getElementById("TrackerArtistSearchList").style.display = "none"',100);
}

function TrackerArtistItemDeleteAll() {
	var input = document.getElementById("TrackerArtistSearchInput");
	var inputarea = document.getElementById("TrackerArtistSearchInputArea");
	var inputlist = document.getElementById("TrackerArtistSearchInputList");
	var item = inputlist.getElementsByTagName("div");
	
	for (var i=0, loop=item.length;i<loop;i++) {
		if (item[0] === inputarea) break;
		inputlist.removeChild(item[0]);
	}
	input.style.display = "";
}

// Live Search
var gArtistLiveSearchTimeout = null;
var gArtistLiveSearchLastKeyword = null;
var gArtistLiveSearchListPoint = null;

function TrackerArtistLiveSearchStart(category) {
	gArtistLiveSearchLastKeyword = null;
	gArtistLiveSearchListPoint = null;

	TrackerArtistLiveSearch(category);
}

function TrackerArtistLiveSearch(category) {
	var object = document.getElementById("TrackerArtistSearchInput");
	
	if (object.value && object.value != gArtistLiveSearchLastKeyword) {
		gArtistLiveSearchLastKeyword = object.value;
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=artist&type=MAIN&category="+category+"&keyword="+GetAjaxParam(object.value),"TrackerArtistLiveSearchInner");
	} else if (!object.value) {
		gArtistLiveSearchLastKeyword = null;
	}
	
	if (!object.value) TrackerArtistLiveSearchInner();
	
	gArtistLiveSearchTimeout = setTimeout("TrackerArtistLiveSearch("+category+")",300);
}

function TrackerArtistLiveSearchInner(XML) {
	var object = document.getElementById("TrackerArtistSearchList");
	object.innerHTML = "";
	
	if (XML) {
		var root = XML.documentElement;
		
		var list = document.createElement("ul");
		
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var item = document.createElement("li");
			item.setAttribute("title",data.getAttribute("name"));
			item.innerHTML = data.getAttribute("name");
			item.onclick = function() {
				TrackerArtistItemAdd(this.getAttribute("title"));
				document.getElementById("TrackerArtistSearchInput").value = "";
				setTimeout('document.getElementById("TrackerArtistSearchInput").focus()',200);
				document.getElementById("TrackerArtistSearchList").style.display = "none";
			}
			
			item.onmouseover = function() { this.className = "select"; }
			item.onmouseout = function() { this.className = ""; }
			list.appendChild(item);
		}
		object.appendChild(list);
		gArtistLiveSearchXML = root.childNodes;
		if (loop > 0) object.style.display = "";
		else object.style.display = "none";
	} else {
		object.style.display = "none";
	}
	
	gArtistLiveSearchListPoint = null;
}

function TrackerArtistLiveSearchListMove(event) {
	var input = document.getElementById("TrackerArtistSearchInput");
	var object = document.getElementById("TrackerArtistSearchList");
	var list = object.getElementsByTagName("li");
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;

	if (keycode == 38) {
		if (gArtistLiveSearchListPoint == null) gArtistLiveSearchListPoint = list.length;
		if (gArtistLiveSearchListPoint == 0) return;
		if (gArtistLiveSearchListPoint != list.length) {
			list[gArtistLiveSearchListPoint].className = "";
		}
		gArtistLiveSearchListPoint--;
		list[gArtistLiveSearchListPoint].className = "select";

		gArtistLiveSearchLastKeyword = list[gArtistLiveSearchListPoint].getAttribute("title");
		input.value = gArtistLiveSearchLastKeyword;

		var basePosition = gArtistLiveSearchListPoint - 2 < 0 ? 0 : gArtistLiveSearchListPoint - 2;
		var baseScrollPosition = gArtistLiveSearchListPoint * 79;
		if (object.scrollTop > baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 40) {
		if (gArtistLiveSearchListPoint == null) gArtistLiveSearchListPoint = -1;
		if (gArtistLiveSearchListPoint == list.length - 1) return;
		if (gArtistLiveSearchListPoint != -1) {
			list[gArtistLiveSearchListPoint].className = "";
		}
		gArtistLiveSearchListPoint++;
		list[gArtistLiveSearchListPoint].className = "select";

		gArtistLiveSearchLastKeyword = list[gArtistLiveSearchListPoint].getAttribute("title");
		input.value = gArtistLiveSearchLastKeyword;
		
		var basePosition = gArtistLiveSearchListPoint - 2 < 0 ? 0 : gArtistLiveSearchListPoint - 2;
		var baseScrollPosition = basePosition * 79;
		if (object.scrollTop < baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 13) {
		object.style.display = "none";
		TrackerArtistItemCompleteInner(true);
		return false;
	}
}

/****************************************************************************************
 * 서브 아티스트 입력 스크립트
 ***************************************************************************************/

function TrackerSubArtistItemInsert(event) {
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;
	
	var input = document.getElementById("TrackerSubArtistSearchInput");
	
	if (keycode == 188) {
		var str = input.value.replace(/^\s*/,"").replace(/\s*$/,"").replace(",","");
		if (str.length > 0) {
			TrackerSubArtistItemAdd(str);
			input.value = "";
		}
	}
}

function TrackerSubArtistItemAdd(str) {
	if (str.length == 0) return;
	var inputlist = document.getElementById("TrackerSubArtistSearchInputList");
	var inputarea = document.getElementById("TrackerSubArtistSearchInputArea");
	var input = document.getElementById("TrackerSubArtistSearchInput");
	
	for (var i=0, loop=inputlist.getElementsByTagName("div").length;i<loop;i++) {
		if (inputlist.getElementsByTagName("div")[i] === inputarea) break;
		if (inputlist.getElementsByTagName("div")[i].getAttribute("value") == str) return false;
	}
	
	var item = document.createElement("div");
	item.setAttribute("value",str);
	item.className = "item";
	item.innerHTML = str;
	item.onclick = function() {
		if (document.forms["TrackerPost"].mode.value == "write" && (document.forms["TrackerPost"].daummovie.value != "" || document.forms["TrackerPost"].groupno.value != "")) {
			alert("기존 자료를 검색하여 자동으로 입력된 데이터는 삭제할 수 없습니다.\nYou can not delete data entered automatically by searching existing data.");
		} else {
			inputlist.removeChild(this);
		}
		TrackerSubArtistItemComplete();
	}
	
	inputlist.insertBefore(item,inputarea);
}

function TrackerSubArtistItemComplete() {
	setTimeout("TrackerSubArtistItemCompleteInner(false)",300);
}

function TrackerSubArtistItemCompleteInner(isFocus) {
	var input = document.getElementById("TrackerSubArtistSearchInput");
	var str = input.value.replace(/^\s*/,"").replace(/\s*$/,"").replace(",","");
	if (str.length > 0) TrackerSubArtistItemAdd(str);
	input.value = "";
	
	var form = document.forms["TrackerPost"];
	var inputarea = document.getElementById("TrackerSubArtistSearchInputArea");
	var inputlist = document.getElementById("TrackerSubArtistSearchInputList");
	
	var values = new Array();
	for (var i=0, loop=inputlist.getElementsByTagName("div").length;i<loop;i++) {
		if (inputlist.getElementsByTagName("div")[i] === inputarea) break;
		values[i] = inputlist.getElementsByTagName("div")[i].getAttribute("value");
	}
	form.subartist.value = values.join(",");
	
	if (gSubArtistLiveSearchTimeout != null) {
		clearTimeout(gSubArtistLiveSearchTimeout);
		gSubArtistLiveSearchTimeout = null;
	}
	
	if (isFocus == true) {
		input.focus();
		fireEvent(input,"focus");
	}
	setTimeout('document.getElementById("TrackerSubArtistSearchList").style.display = "none"',100);
}

function TrackerSubArtistItemDeleteAll() {
	var input = document.getElementById("TrackerSubArtistSearchInput");
	var inputarea = document.getElementById("TrackerSubArtistSearchInputArea");
	var inputlist = document.getElementById("TrackerSubArtistSearchInputList");
	var item = inputlist.getElementsByTagName("div");
	
	for (var i=0, loop=item.length;i<loop;i++) {
		if (item[0] === inputarea) break;
		inputlist.removeChild(item[0]);
	}
	input.style.display = "";
}

// Live Search
var gSubArtistLiveSearchTimeout = null;
var gSubArtistLiveSearchLastKeyword = null;
var gSubArtistLiveSearchListPoint = null;

function TrackerSubArtistLiveSearchStart(category) {
	gSubArtistLiveSearchLastKeyword = null;
	gSubArtistLiveSearchListPoint = null;
	
	TrackerSubArtistLiveSearch(category);
}

function TrackerSubArtistLiveSearch(category) {
	var object = document.getElementById("TrackerSubArtistSearchInput");
	
	if (object.value && object.value != gSubArtistLiveSearchLastKeyword) {
		gSubArtistLiveSearchLastKeyword = object.value;
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=artist&type=SUB&category="+category+"&keyword="+GetAjaxParam(object.value),"TrackerSubArtistLiveSearchInner");
	} else if (!object.value) {
		gSubArtistLiveSearchLastKeyword = null;
	}
	
	if (!object.value) TrackerSubArtistLiveSearchInner();
	
	gSubArtistLiveSearchTimeout = setTimeout("TrackerSubArtistLiveSearch("+category+")",300);
}

function TrackerSubArtistLiveSearchInner(XML) {
	var object = document.getElementById("TrackerSubArtistSearchList");
	object.innerHTML = "";
	
	if (XML) {
		var root = XML.documentElement;
		
		var list = document.createElement("ul");
		
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var item = document.createElement("li");
			item.setAttribute("title",data.getAttribute("name"));
			item.innerHTML = data.getAttribute("name");
			item.onclick = function() {
				TrackerSubArtistItemAdd(this.getAttribute("title"));
				document.getElementById("TrackerSubArtistSearchInput").value = "";
				setTimeout('document.getElementById("TrackerSubArtistSearchInput").focus()',200);
				document.getElementById("TrackerSubArtistSearchList").style.display = "none";
			}
			
			item.onmouseover = function() { this.className = "select"; }
			item.onmouseout = function() { this.className = ""; }
			list.appendChild(item);
		}
		object.appendChild(list);
		gSubArtistLiveSearchXML = root.childNodes;
		if (loop > 0) object.style.display = "";
		else object.style.display = "none";
	} else {
		object.style.display = "none";
	}
	gSubArtistLiveSearchListPoint = null;
}

function TrackerSubArtistLiveSearchListMove(event) {
	var input = document.getElementById("TrackerSubArtistSearchInput");
	var object = document.getElementById("TrackerSubArtistSearchList");
	var list = object.getElementsByTagName("li");
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;

	if (keycode == 38) {
		if (gSubArtistLiveSearchListPoint == null) gSubArtistLiveSearchListPoint = list.length;
		if (gSubArtistLiveSearchListPoint == 0) return;
		if (gSubArtistLiveSearchListPoint != list.length) {
			list[gSubArtistLiveSearchListPoint].className = "";
		}
		gSubArtistLiveSearchListPoint--;
		list[gSubArtistLiveSearchListPoint].className = "select";

		gSubArtistLiveSearchLastKeyword = list[gSubArtistLiveSearchListPoint].getAttribute("title");
		input.value = gSubArtistLiveSearchLastKeyword;

		var basePosition = gSubArtistLiveSearchListPoint - 2 < 0 ? 0 : gSubArtistLiveSearchListPoint - 2;
		var baseScrollPosition = gSubArtistLiveSearchListPoint * 79;
		if (object.scrollTop > baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 40) {
		if (gSubArtistLiveSearchListPoint == null) gSubArtistLiveSearchListPoint = -1;
		if (gSubArtistLiveSearchListPoint == list.length - 1) return;
		if (gSubArtistLiveSearchListPoint != -1) {
			list[gSubArtistLiveSearchListPoint].className = "";
		}
		gSubArtistLiveSearchListPoint++;
		list[gSubArtistLiveSearchListPoint].className = "select";

		gSubArtistLiveSearchLastKeyword = list[gSubArtistLiveSearchListPoint].getAttribute("title");
		input.value = gSubArtistLiveSearchLastKeyword;
		
		var basePosition = gSubArtistLiveSearchListPoint - 2 < 0 ? 0 : gSubArtistLiveSearchListPoint - 2;
		var baseScrollPosition = basePosition * 79;
		if (object.scrollTop < baseScrollPosition) object.scrollTop = baseScrollPosition;
	} else if (keycode == 13) {
		object.style.display = "none";
		TrackerSubArtistItemCompleteInner(true);
		return false;
	}
}

/****************************************************************************************
 * 국가 입력 스크립트
 ***************************************************************************************/

function TrackerNationItemInsert(event) {
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;
	
	var input = document.getElementById("TrackerNationInput");
	
	if (keycode == 188) {
		var str = input.value.replace(/^\s*/,"").replace(/\s*$/,"").replace(",","");
		if (str.length > 0) {
			TrackerNationItemAdd(str);
			input.value = "";
		}
	}
}

function TrackerNationItemAdd(str) {
	var inputlist = document.getElementById("TrackerNationInputList");
	var inputarea = document.getElementById("TrackerNationInputArea");
	var input = document.getElementById("TrackerNationInput");
	
	var item = document.createElement("div");
	item.setAttribute("value",str);
	item.className = "item";
	item.innerHTML = str;
	item.onclick = function() {
		if (document.forms["TrackerPost"].mode.value == "write" && (document.forms["TrackerPost"].daummovie.value != "" || document.forms["TrackerPost"].groupno.value != "")) {
			alert("기존 자료를 검색하여 자동으로 입력된 데이터는 삭제할 수 없습니다.\nYou can not delete data entered automatically by searching existing data.");
		} else {
			inputlist.removeChild(this);
		}
		TrackerNationItemComplete();
	}
	
	inputlist.insertBefore(item,inputarea);
}

function TrackerNationItemComplete() {
	var input = document.getElementById("TrackerNationInput");
	var str = input.value.replace(/^\s*/,"").replace(/\s*$/,"").replace(",","");
	if (str.length > 0) TrackerNationItemAdd(str);
	input.value = "";
	
	var form = document.forms["TrackerPost"];
	var inputarea = document.getElementById("TrackerNationInputArea");
	var inputlist = document.getElementById("TrackerNationInputList");
	
	var values = new Array();
	for (var i=0, loop=inputlist.getElementsByTagName("div").length;i<loop;i++) {
		if (inputlist.getElementsByTagName("div")[i] === inputarea) break;
		values[i] = inputlist.getElementsByTagName("div")[i].getAttribute("value");
	}
	form.nation.value = values.join(",");
}

function TrackerNationItemDeleteAll() {
	var input = document.getElementById("TrackerNationInput");
	var inputarea = document.getElementById("TrackerNationInputArea");
	var inputlist = document.getElementById("TrackerNationInputList");
	var item = inputlist.getElementsByTagName("div");
	
	for (var i=0, loop=item.length;i<loop;i++) {
		if (item[0] === inputarea) break;
		inputlist.removeChild(item[0]);
	}
	input.style.display = "";
}

/****************************************************************************************
 * 에피소드 처리
 ***************************************************************************************/

function TrackerEpisodeSearch(groupno) {
	var form = document.forms["TrackerPost"];
	
	if (form.episode) {
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=episode&groupno="+groupno,"TrackerEpisodeSearchInner");
	}
}

function TrackerEpisodeSearchInner(XML) {
	var form = document.forms["TrackerPost"];
	var episode = [];
	if (XML) {
		var root = XML.documentElement;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			episode[i] = {"episodeno":root.childNodes.item(i).getAttribute("idx"),"date":root.childNodes.item(i).getAttribute("date"),"year":root.childNodes.item(i).getAttribute("year"),"episode":root.childNodes.item(i).getAttribute("episode"),"episode_title":root.childNodes.item(i).getAttribute("episode_title"),"is_pack":(root.childNodes.item(i).getAttribute("is_pack") == "TRUE"),"intro":root.childNodes.item(i).textContent ? root.childNodes.item(i).textContent : root.childNodes.item(i).text};
		}
	}
	
	if (form.episode.getAttribute("searchFunction")) {
		eval(form.episode.getAttribute("searchFunction")+"(episode)");
	}
}

/****************************************************************************************
 * 토렌트 파일처리
 ***************************************************************************************/

function TrackerTorrentFileSearch(torrentno,callback) {
	GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=file&torrentno="+torrentno,"TrackerTorrentFileSearchInner",[torrentno,callback]);
}

function TrackerTorrentFileSearchInner(XML,torrentno,callback) {
	var file = [];
	if (XML) {
		var root = XML.documentElement;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			file[i] = {"filename":root.childNodes.item(i).getAttribute("filename"),"filesize":root.childNodes.item(i).getAttribute("filesize")};
		}
	}
	
	eval(callback+"(torrentno,file)");
}

function TrackerTorrentPeerSearch(torrentno,callback) {
	GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=peer&torrentno="+torrentno,"TrackerTorrentPeerSearchInner",[torrentno,callback]);
}

function TrackerTorrentPeerSearchInner(XML,torrentno,callback) {
	var peer = [];
	if (XML) {
		var root = XML.documentElement;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			peer[i] = {"user_id":root.childNodes.item(i).getAttribute("user_id"),"upload":root.childNodes.item(i).getAttribute("upload"),"download":root.childNodes.item(i).getAttribute("download"),"ratio":root.childNodes.item(i).getAttribute("ratio"),"last_connect":root.childNodes.item(i).getAttribute("last_connect"),"percent":root.childNodes.item(i).getAttribute("percent"),"client":root.childNodes.item(i).getAttribute("client"),"connectable":root.childNodes.item(i).getAttribute("connectable")};
		}
	}
	
	eval(callback+"(torrentno,peer)");
}

/****************************************************************************************
 * 폼 처리
 ***************************************************************************************/

function TrackerCheckPostForm() {
	var form = document.forms["TrackerPost"];
	form.category2.value = parent.document.forms["TrackerOuter"].category2.value;
	form.category3.value = parent.document.forms["TrackerOuter"].category3.value;
	if (form.artist) TrackerArtistItemCompleteInner();
	if (form.subartist) TrackerSubArtistItemCompleteInner();
	if (form.nation) TrackerNationItemComplete();
	
	for (var i=0, loop=form.length;i<loop;i++) {
		if (form[i].getAttribute("allowBlank") == "false" && form[i].value == "") {
			alert("필수항목을 모두 입력하여 주세요.\nPlease complete all required fields.("+form[i].name+")");
			return false;
		}
	}

}