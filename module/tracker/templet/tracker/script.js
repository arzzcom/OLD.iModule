function TrackerListSelectCategory1(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=category&parent="+value,"TrackerListSelectCategoryInner",[1]);
		innerForm.location.href = ENV.dir+"/module/tracker/InnerForm.php?category="+value;
	}
	document.forms["TrackerOuter"].category1.value = value;
}

function TrackerListSelectCategory2(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/tracker/exec/Ajax.get.php","action=category&parent="+value,"TrackerListSelectCategoryInner",[2]);
	}
	document.forms["TrackerOuter"].category2.value = value;
}

function TrackerListSelectCategory3(text,value) {
	document.forms["TrackerOuter"].category3.value = value;
}

function TrackerListSelectCategoryInner(XML,depth) {
	if (depth == 1) var object = document.getElementById("TrackerCategory2");
	else if (depth == 2) var object = document.getElementById("TrackerCategory3");
	if (XML) {
		var root = XML.documentElement;
		
		object.getElementsByTagName("ul")[0].innerHTML = "";
		
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var idx = root.childNodes.item(i).getAttribute("idx");
			var title = root.childNodes.item(i).getAttribute("title");
			
			var list = document.createElement("li");
			list.setAttribute("idx",idx);
			list.setAttribute("title",title);
			list.innerHTML = title;
			list.onclick = function(e) {
				if (depth == 1) {
					InputSelectBoxSelect("TrackerCategory2",this.getAttribute("title"),this.getAttribute("idx"),TrackerListSelectCategory2);
				}
			};
			object.getElementsByTagName("ul")[0].appendChild(list);
		}
		
		if (root.childNodes.length > 0) {
			object.style.display = "";
		} else {
			object.style.display = "none";
			document.forms["TrackerOuter"].category2 = "";
			document.forms["TrackerOuter"].category3 = "";
		}

		if (depth == 2) {
			document.forms["TrackerOuter"].category2 = "";
			document.forms["TrackerOuter"].category3 = "";
		} else if (depth == 3) {
			document.forms["TrackerOuter"].category3 = "";
		}
	}
}

function TrackerListSelectEdition(text,value) {
	document.forms["TrackerPost"].edition.value = value;
}

function TrackerListSelectSubtitles(text,value) {
	if (document.forms["TrackerPost"]) {
		document.forms["TrackerPost"].subtitles.value = value;
	} else if (document.forms["TrackerTorrentSearchForm"]) {
		document.forms["TrackerTorrentSearchForm"].subtitles.value = value;
	}
}

function TrackerListSelectResolution(text,value) {
	if (document.forms["TrackerPost"]) {
		document.forms["TrackerPost"].resolution.value = value;
	} else if (document.forms["TrackerTorrentSearchForm"]) {
		document.forms["TrackerTorrentSearchForm"].resolution.value = value;
	}
}

function TrackerListSelectCodec(text,value) {
	if (document.forms["TrackerPost"]) {
		document.forms["TrackerPost"].codec.value = value;
	} else if (document.forms["TrackerTorrentSearchForm"]) {
		document.forms["TrackerTorrentSearchForm"].codec.value = value;
	}
}

function TrackerListSelectSource(text,value) {
	if (document.forms["TrackerPost"]) {
		document.forms["TrackerPost"].source.value = value;
	} else if (document.forms["TrackerTorrentSearchForm"]) {
		document.forms["TrackerTorrentSearchForm"].source.value = value;
	}
}

function TrackerListSelectFormat(text,value) {
	if (document.forms["TrackerPost"]) {
		document.forms["TrackerPost"].format.value = value;
	} else if (document.forms["TrackerTorrentSearchForm"]) {
		document.forms["TrackerTorrentSearchForm"].format.value = value;
	}
}

function TrackerListSelectGender(text,value) {
	document.forms["TrackerPost"].gender.value = value;
}

function TrackerSelectVote() {
	if (document.getElementById("TrackerSelectVoteList").style.display == "none") {
		document.getElementById("TrackerSelectVoteList").style.display = "";
	} else {
		document.getElementById("TrackerSelectVoteList").style.display = "none";
	}
}

function TrackerSelectVotePoint(point) {
	var sHTML = '';
	if (point != -1) {
		sHTML+= '<div class="star"><div class="on" style="width:'+(point*10)+'%"></div></div><div class="point">'+point+'.0</div>';
	} else {
		sHTML+= '선택안함';
	}
	document.getElementById("TrackerSelectVotePreview").innerHTML = sHTML;
	document.getElementById("TrackerSelectVoteList").style.display = "none";
	document.forms["TrackerPost"].vote.value = point;
}

function TrackerListEpisodePrint(episode) {
	if (episode.length > 0) {
		document.getElementById("TrackerEpisode").style.display = "";
		var object = document.getElementById("TrackerEpisode").getElementsByTagName("ul")[0];
		object.innerHTML = "";
		for (var i=0, loop=episode.length;i<loop;i++) {
			var list = document.createElement("li");

			if (episode[i].is_pack) {
				var temp = episode[i].episode_title.split("~");
				var text = "E"+(1000+parseInt(temp[0])).toString().substr(1,3)+"~"+"E"+(1000+parseInt(temp[1])).toString().substr(1,3)+".Pack";
			} else {
				var text = "";
				if (episode[i].episode) text+= "E"+(1000+parseInt(episode[i].episode)).toString().substr(1,3);
				if (episode[i].episode && episode[i].episode_title) text+= " : ";
				if (episode[i].episode_title) text+= episode[i].episode_title;
				if (episode[i].date) text+= ' ('+episode[i].date+')';
			}
			
			list.setAttribute("idx",episode[i].idx);
			list.setAttribute("date",episode[i].date);
			list.setAttribute("year",episode[i].year);
			list.setAttribute("episode",episode[i].episode);
			list.setAttribute("episode_title",episode[i].episode_title);
			list.setAttribute("intro",episode[i].intro);
			list.setAttribute("is_pack",episode[i].is_pack == true ? "TRUE" : "FALSE");
			
			list.onclick = function() {
				InputSelectBoxSelect('TrackerEpisode',this.innerHTML,this,TrackerListSelectEpisode);
			}
			
			list.innerHTML = text;
			object.appendChild(list);
		}
	} else {
		document.getElementById("TrackerEpisode").style.display = "none";
	}
}

function TrackerListSelectEpisode(text,object) {
	var form = document.forms["TrackerPost"];
	if (object.getAttribute("is_pack") == "TRUE") {
		form.is_pack.checked = "checked";
		form.episode.disabled = "disabled";
	} else {
		form.is_pack.checked = "";
		form.episode.disabled = "";
		form.episode.value = object.getAttribute("episode");
	}

	if (form.episode_date) form.episode_date.value = object.getAttribute("date");
	if (form.episode_year) form.episode_date.value = object.getAttribute("year");
	form.episode_title.value = object.getAttribute("episode_title");
	form.episode_intro.value = object.getAttribute("intro");
}

function TrackerViewToggleArea(object) {
	if (document.getElementById(object.getAttribute("for")).style.display == "none") {
		document.getElementById(object.getAttribute("for")).style.display = "";
		object.className = "toggle on";
	} else {
		document.getElementById(object.getAttribute("for")).style.display = "none";
		object.className = "toggle off";
	}
}

var gGetTorrentFiles = new Array();
function TrackerViewToggleTorrentFile(object,idx) {
	if (document.getElementById(object.getAttribute("for")).style.display == "none") {
		document.getElementById(object.getAttribute("for")).style.display = "";
		object.className = "toggle on";
		if (in_array(idx,gGetTorrentFiles) == false) {
			TrackerTorrentFileSearch(idx,'TrackerPrintTorrentFile');
		}
	} else {
		document.getElementById(object.getAttribute("for")).style.display = "none";
		object.className = "toggle off";
	}
}

function TrackerPrintTorrentFile(idx,file) {
	var object = document.getElementById("TrackerFileListArea-"+idx);
	var sHTML = '<table cellpadding="0" cellspacing="1" class="torrentList"><col width="100%" /><col width="100" /><tr class="tHead"><td><div>Filename</div></td><td><div>Filesize</div></td></tr>';
	for (var i=0, loop=file.length;i<loop;i++) {
		var temp = file[i].filename.split("/");
		var filename = temp.pop();
		var filepath = temp.join("/") ? temp.join("/")+"/" : "";
		sHTML+= '<tr class="tBody"><td style="padding-left:5px;"><span style="color:#CCCCCC;">'+filepath+'</span>'+filename+'</td><td class="right" style="padding-right:5px;">'+file[i].filesize+'</td></tr>';
	}
	sHTML+= '</table>';
	
	object.innerHTML = sHTML;
}

var gGetTorrentPeers = new Array();
function TrackerViewToggleTorrentPeer(object,idx) {
	if (document.getElementById(object.getAttribute("for")).style.display == "none") {
		document.getElementById(object.getAttribute("for")).style.display = "";
		object.className = "toggle on";
		if (in_array(idx,gGetTorrentPeers) == false) {
			TrackerTorrentPeerSearch(idx,'TrackerPrintTorrentPeer');
		}
	} else {
		document.getElementById(object.getAttribute("for")).style.display = "none";
		object.className = "toggle off";
	}
}

function TrackerPrintTorrentPeer(idx,peer) {
	var object = document.getElementById("TrackerPeerListArea-"+idx);
	var sHTML = '<table cellpadding="0" cellspacing="1" class="torrentList"><col width="100" /><col width="50" /><col width="70" /><col width="70" /><col width="60" /><col width="50" /><col width="150" /><col width="100%" /><tr class="tHead"><td><div>User</div></td><td><div>Active</div></td><td><div>Upload</div></td><td><div>Download</div></td><td><div>Ratio</div></td><td><div>%</div></td><td><div>Client</div></td><td><div>Last Connect</div></td></tr>';
	for (var i=0, loop=peer.length;i<loop;i++) {
		sHTML+= '<tr class="tBody">';
		sHTML+= '<td class="center tahoma f11">'+peer[i].user_id+'</td>';
		sHTML+= '<td class="center tahoma f11" style="color:'+(peer[i].connectable == "true" ? 'green' : 'red')+';">'+(peer[i].connectable == "true" ? 'YES' : 'NO')+'</td>';
		sHTML+= '<td class="right tahoma f11" style="padding-right:5px;">'+peer[i].upload+'</td>';
		sHTML+= '<td class="right tahoma f11" style="padding-right:5px;">'+peer[i].download+'</td>';
		sHTML+= '<td class="center tahoma f11" style="color:'+(parseFloat(peer[i].ratio) <= 1 ? 'red' : 'green')+';">'+peer[i].ratio+'</td>';
		sHTML+= '<td class="center tahoma f11">'+peer[i].percent+'%</td>';
		sHTML+= '<td class="center center f11">'+peer[i].client+'</td>';
		sHTML+= '<td class="center center f11">'+peer[i].last_connect+'</td>';
		sHTML+= '</tr>';
	}
	sHTML+= '</table>';
	
	object.innerHTML = sHTML;
}

function TrackerViewToggleTorrent(idx) {
	if (document.getElementById("TrackerViewTorrent-"+idx).style.display == "none") document.getElementById("TrackerViewTorrent-"+idx).style.display = "";
	else document.getElementById("TrackerViewTorrent-"+idx).style.display = "none";
}