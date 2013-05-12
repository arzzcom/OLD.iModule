function DetailGetDefaultValues() {
	var form = document.forms["OneroomSearchForm-detail"];
	
	if (form.search_type.value == "region") {
		DetailToggleSearchType("Region");
	} else if (form.search_type.value == "university") {
		DetailToggleSearchType("University");
	} else if (form.search_type.value == "subway") {
		DetailToggleSearchType("Subway");
	}
	
	if (form.region1.value) {
		DetailSelectRegion1("",form.region1.value);
	}
	
	if (form.university_parent.value) {
		DetailSelectUniversity("",form.university_parent.value);
	}
	
	if (form.subway_parent.value) {
		DetailSelectSubway("",form.subway_parent.value);
	}
	
	DetailPriceFormToggle();
}

function DetailToggleSearchType(type) {
	document.getElementById("TabRegion").className = "tabOff";
	document.getElementById("TabUniversity").className = "tabOff";
	document.getElementById("TabSubway").className = "tabOff";
	
	document.getElementById("TabContentRegion").style.display = "none";
	document.getElementById("TabContentUniversity").style.display = "none";
	document.getElementById("TabContentSubway").style.display = "none";
	
	document.getElementById("TabContent"+type).style.display = "";
	document.getElementById("Tab"+type).className = "tabOn";
	
	document.forms["OneroomSearchForm-detail"].search_type.value = type.toLowerCase();
}

function DetailSelectRegion1(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"DetailSelectRegion1Inner");
		document.forms["OneroomSearchForm-detail"].region1.value = value;
	}
}

function DetailSelectRegion1Inner(XML) {
	if (XML) {
		var region2 = document.forms["OneroomSearchForm-detail"].region2.value;
		var root = XML.documentElement;
		var object = document.getElementById("DetailRegionList2").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		var isFind = false;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","DetailRegionList2");
			list.setAttribute("onclick","InputSelectBoxSelect('DetailRegionList2','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',DetailSelectRegion2)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
			
			if (region2 == data.getAttribute("idx")) isFind = true;
		}
		if (isFind == true) {
			DetailSelectRegion2("",region2);
		} else {
			document.forms["OneroomSearchForm-detail"].region2.value = "";
			document.forms["OneroomSearchForm-detail"].region3.value = "";
			document.getElementById("DetailRegionList2").getElementsByTagName("div")[0].innerHTML = "2차지역선택(읍/면/동)";
		}
	}
}

function DetailSelectRegion2(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"DetailSelectRegion2Inner");
		document.forms["OneroomSearchForm-detail"].region2.value = value;
	}
}

function DetailSelectRegion2Inner(XML) {
	if (XML) {
		var region3 = document.forms["OneroomSearchForm-detail"].region3.value;
		var root = XML.documentElement;
		var object = document.getElementById("DetailRegionList3").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		var isFind = false;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","DetailRegionList3");
			list.setAttribute("onclick","InputSelectBoxSelect('DetailRegionList3','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',DetailSelectRegion3)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
			
			if (region3 == data.getAttribute("idx")) isFind = true;
		}
		
		if (isFind == false) {
			document.forms["OneroomSearchForm-detail"].region3.value = "";
		}
		
		if (root.childNodes.length == 0) {
			var list = document.createElement("li");
			list.setAttribute("clicker","DetailRegionList3");
			list.setAttribute("onclick","InputSelectBoxSelect('DetailRegionList3','3차지역선택','0',DetailSelectRegion3)");
			list.innerHTML = '3차지역이 없습니다.';
			object.appendChild(list);
			document.getElementById("DetailRegionList3").getElementsByTagName("div")[0].innerHTML = "3차지역선택";
		}
	}
}

function DetailSelectRegion3(text,value) {
	document.forms["OneroomSearchForm-detail"].region3.value = value;
}

function DetailSelectUniversity(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=university&parent="+value,"DetailSelectUniversityInner");
		document.forms["OneroomSearchForm-detail"].university_parent.value = value;
	}
}

function DetailSelectUniversityInner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("DetailUniversityList");
		object.innerHTML = "";
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("div");
			list.className = "checkboxList";
			list.innerHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="18" /><col width="100%" /><tr><td><input type="checkbox" name="check_university[]" value="'+data.getAttribute("idx")+'" onclick="DetailCheckUniversity()" /></td><td>'+data.getAttribute("title")+'<span class="bold blue">('+GetNumberFormat(data.getAttribute("itemnum"))+')</span></td></tr></table>';
			object.appendChild(list);
		}
		
		if (document.forms["OneroomSearchForm-detail"].university_idx.value) {
			var idx = document.forms["OneroomSearchForm-detail"].university_idx.value.split(",");
			var object = document.forms["OneroomSearchForm-detail"].getElementsByTagName("input");
			var findIDX = new Array();
			
			for (var i=0, loop=idx.length;i<loop;i++) {
				for (var j=0, loopj=object.length;j<loopj;j++) {
					if (object[j].name == "check_university[]" && object[j].value == idx[i]) {
						object[j].checked = true;
						findIDX.push(idx[i]);
					}
				}
			}
			
			document.forms["OneroomSearchForm-detail"].university_idx.value = findIDX.join(",");
		}
	}
}

function DetailCheckUniversity() {
	var object = document.forms["OneroomSearchForm-detail"].getElementsByTagName("input");
	var checked = new Array();
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].name == "check_university[]" && object[i].checked == true) checked.push(object[i].value);
	}
	document.forms["OneroomSearchForm-detail"].university_idx.value = checked.join(",");
}

function DetailSelectSubway(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=subway&parent="+value,"DetailSelectSubwayInner");
		document.forms["OneroomSearchForm-detail"].subway_parent.value = value;
	}
}

function DetailSelectSubwayInner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("DetailSubwayList");
		object.innerHTML = "";
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("div");
			list.className = "checkboxList";
			list.innerHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="18" /><col width="100%" /><tr><td><input type="checkbox" name="check_subway[]" value="'+data.getAttribute("idx")+'" onclick="DetailCheckSubway()" /></td><td>'+data.getAttribute("title")+'<span class="bold blue">('+GetNumberFormat(data.getAttribute("itemnum"))+')</span></td></tr></table>';
			object.appendChild(list);
		}
		
		if (document.forms["OneroomSearchForm-detail"].subway_idx.value) {
			var idx = document.forms["OneroomSearchForm-detail"].subway_idx.value.split(",");
			var object = document.forms["OneroomSearchForm-detail"].getElementsByTagName("input");
			var findIDX = new Array();
			
			for (var i=0, loop=idx.length;i<loop;i++) {
				for (var j=0, loopj=object.length;j<loopj;j++) {
					if (object[j].name == "check_subway[]" && object[j].value == idx[i]) {
						object[j].checked = true;
						findIDX.push(idx[i]);
					}
				}
			}
			
			document.forms["OneroomSearchForm-detail"].subway_idx.value = findIDX.join(",");
		}
	}
}

function DetailCheckSubway() {
	var object = document.forms["OneroomSearchForm-detail"].getElementsByTagName("input");
	var checked = new Array();
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].name == "check_subway[]" && object[i].checked == true) checked.push(object[i].value);
	}
	document.forms["OneroomSearchForm-detail"].subway_idx.value = checked.join(",");
}

function DetailPriceFormToggle() {
	var form = document.forms["OneroomSearchForm-detail"];
	if (form.is_buy.checked == true) {
		document.getElementById("DetailPriceBuy").style.display = "";
	} else {
		document.getElementById("DetailPriceBuy").style.display = "none";
	}
	
	if (form.is_rent_all.checked == true) {
		document.getElementById("DetailPriceRentAll").style.display = "";
	} else {
		document.getElementById("DetailPriceRentAll").style.display = "none";
	}
	
	if (form.is_rent_month.checked == true) {
		document.getElementById("DetailPriceRentDeposit").style.display = "";
	} else {
		document.getElementById("DetailPriceRentDeposit").style.display = "none";
	}
	
	if (form.is_rent_month.checked == true || form.is_rent_short.checked == true) {
		document.getElementById("DetailPriceRentMonth").style.display = "";
	} else {
		document.getElementById("DetailPriceRentMonth").style.display = "none";
	}
}