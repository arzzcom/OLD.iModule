function MainSearchGetDefaultValues() {
	var form = document.forms["OneroomSearchForm-mainsearch"];
	
	if (form.search_type.value == "region") {
		MainSearchToggleSearchType("Region");
	} else if (form.search_type.value == "university") {
		MainSearchToggleSearchType("University");
	} else if (form.search_type.value == "subway") {
		MainSearchToggleSearchType("Subway");
	}
	
	if (form.region1.value) {
		MainSearchSelectRegion1("",form.region1.value);
	}
	
	if (form.university_parent.value) {
		MainSearchSelectUniversity("",form.university_parent.value);
	}
	
	if (form.subway_parent.value) {
		MainSearchSelectSubway("",form.subway_parent.value);
	}
	
	MainSearchPriceFormToggle();
}

function MainSearchToggleSearchType(type) {
	document.getElementById("TabRegion").className = "tabOff";
	document.getElementById("TabUniversity").className = "tabOff";
	document.getElementById("TabSubway").className = "tabOff";
	
	document.getElementById("TabContentRegion").style.display = "none";
	document.getElementById("TabContentUniversity").style.display = "none";
	document.getElementById("TabContentSubway").style.display = "none";
	
	document.getElementById("TabContent"+type).style.display = "";
	document.getElementById("Tab"+type).className = "tabOn";
	
	document.forms["OneroomSearchForm-mainsearch"].search_type.value = type.toLowerCase();
}

function MainSearchSelectRegion1(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"MainSearchSelectRegion1Inner");
		document.forms["OneroomSearchForm-mainsearch"].region1.value = value;
	}
}

function MainSearchSelectRegion1Inner(XML) {
	if (XML) {
		var region2 = document.forms["OneroomSearchForm-mainsearch"].region2.value;
		var root = XML.documentElement;
		var object = document.getElementById("MainSearchRegionList2").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		var isFind = false;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","MainSearchRegionList2");
			list.setAttribute("onclick","InputSelectBoxSelect('MainSearchRegionList2','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',MainSearchSelectRegion2)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
			
			if (region2 == data.getAttribute("idx")) isFind = true;
		}
		if (isFind == true) {
			MainSearchSelectRegion2("",region2);
		} else {
			document.forms["OneroomSearchForm-mainsearch"].region2.value = "";
			document.forms["OneroomSearchForm-mainsearch"].region3.value = "";
			document.getElementById("MainSearchRegionList2").getElementsByTagName("div")[0].innerHTML = "2차지역선택(읍/면/동)";
		}
	}
}

function MainSearchSelectRegion2(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"MainSearchSelectRegion2Inner");
		document.forms["OneroomSearchForm-mainsearch"].region2.value = value;
	}
}

function MainSearchSelectRegion2Inner(XML) {
	if (XML) {
		var region3 = document.forms["OneroomSearchForm-mainsearch"].region3.value;
		var root = XML.documentElement;
		var object = document.getElementById("MainSearchRegionList3").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		var isFind = false;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","MainSearchRegionList3");
			list.setAttribute("onclick","InputSelectBoxSelect('MainSearchRegionList3','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',MainSearchSelectRegion3)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
			
			if (region3 == data.getAttribute("idx")) isFind = true;
		}
		
		if (isFind == false) {
			document.forms["OneroomSearchForm-mainsearch"].region3.value = "";
		}
		
		if (root.childNodes.length == 0) {
			var list = document.createElement("li");
			list.setAttribute("clicker","MainSearchRegionList3");
			list.setAttribute("onclick","InputSelectBoxSelect('MainSearchRegionList3','3차지역선택','0',MainSearchSelectRegion3)");
			list.innerHTML = '3차지역이 없습니다.';
			object.appendChild(list);
			document.getElementById("MainSearchRegionList3").getElementsByTagName("div")[0].innerHTML = "3차지역선택";
		}
	}
}

function MainSearchSelectRegion3(text,value) {
	document.forms["OneroomSearchForm-mainsearch"].region3.value = value;
}

function MainSearchSelectUniversity(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=university&parent="+value,"MainSearchSelectUniversityInner");
		document.forms["OneroomSearchForm-mainsearch"].university_parent.value = value;
	}
}

function MainSearchSelectUniversityInner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("MainSearchUniversityList");
		object.innerHTML = "";
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("div");
			list.className = "checkboxList";
			list.innerHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="18" /><col width="100%" /><tr><td><input type="checkbox" name="check_university[]" value="'+data.getAttribute("idx")+'" onclick="MainSearchCheckUniversity()" /></td><td>'+data.getAttribute("title")+'<span class="bold blue">('+GetNumberFormat(data.getAttribute("itemnum"))+')</span></td></tr></table>';
			object.appendChild(list);
		}
		
		if (document.forms["OneroomSearchForm-mainsearch"].university_idx.value) {
			var idx = document.forms["OneroomSearchForm-mainsearch"].university_idx.value.split(",");
			var object = document.forms["OneroomSearchForm-mainsearch"].getElementsByTagName("input");
			var findIDX = new Array();
			
			for (var i=0, loop=idx.length;i<loop;i++) {
				for (var j=0, loopj=object.length;j<loopj;j++) {
					if (object[j].name == "check_university[]" && object[j].value == idx[i]) {
						object[j].checked = true;
						findIDX.push(idx[i]);
					}
				}
			}
			
			document.forms["OneroomSearchForm-mainsearch"].university_idx.value = findIDX.join(",");
		}
	}
}

function MainSearchCheckUniversity() {
	var object = document.forms["OneroomSearchForm-mainsearch"].getElementsByTagName("input");
	var checked = new Array();
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].name == "check_university[]" && object[i].checked == true) checked.push(object[i].value);
	}
	document.forms["OneroomSearchForm-mainsearch"].university_idx.value = checked.join(",");
}

function MainSearchSelectSubway(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=subway&parent="+value,"MainSearchSelectSubwayInner");
		document.forms["OneroomSearchForm-mainsearch"].subway_parent.value = value;
	}
}

function MainSearchSelectSubwayInner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("MainSearchSubwayList");
		object.innerHTML = "";
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("div");
			list.className = "checkboxList";
			list.innerHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="18" /><col width="100%" /><tr><td><input type="checkbox" name="check_subway[]" value="'+data.getAttribute("idx")+'" onclick="MainSearchCheckSubway()" /></td><td>'+data.getAttribute("title")+'<span class="bold blue">('+GetNumberFormat(data.getAttribute("itemnum"))+')</span></td></tr></table>';
			object.appendChild(list);
		}
		
		if (document.forms["OneroomSearchForm-mainsearch"].subway_idx.value) {
			var idx = document.forms["OneroomSearchForm-mainsearch"].subway_idx.value.split(",");
			var object = document.forms["OneroomSearchForm-mainsearch"].getElementsByTagName("input");
			var findIDX = new Array();
			
			for (var i=0, loop=idx.length;i<loop;i++) {
				for (var j=0, loopj=object.length;j<loopj;j++) {
					if (object[j].name == "check_subway[]" && object[j].value == idx[i]) {
						object[j].checked = true;
						findIDX.push(idx[i]);
					}
				}
			}
			
			document.forms["OneroomSearchForm-mainsearch"].subway_idx.value = findIDX.join(",");
		}
	}
}

function MainSearchCheckSubway() {
	var object = document.forms["OneroomSearchForm-mainsearch"].getElementsByTagName("input");
	var checked = new Array();
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].name == "check_subway[]" && object[i].checked == true) checked.push(object[i].value);
	}
	document.forms["OneroomSearchForm-mainsearch"].subway_idx.value = checked.join(",");
}

function MainSearchPriceFormToggle() {
	var form = document.forms["OneroomSearchForm-mainsearch"];
	if (form.is_buy.checked == true) {
		document.getElementById("MainSearchPriceBuy").style.display = "";
	} else {
		document.getElementById("MainSearchPriceBuy").style.display = "none";
	}
	
	if (form.is_rent_all.checked == true) {
		document.getElementById("MainSearchPriceRentAll").style.display = "";
	} else {
		document.getElementById("MainSearchPriceRentAll").style.display = "none";
	}
	
	if (form.is_rent_month.checked == true) {
		document.getElementById("MainSearchPriceRentDeposit").style.display = "";
	} else {
		document.getElementById("MainSearchPriceRentDeposit").style.display = "none";
	}
	
	if (form.is_rent_month.checked == true || form.is_rent_short.checked == true) {
		document.getElementById("MainSearchPriceRentMonth").style.display = "";
	} else {
		document.getElementById("MainSearchPriceRentMonth").style.display = "none";
	}
}