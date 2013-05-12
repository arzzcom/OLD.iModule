function ItemlistGetDefaultValues() {
	var form = document.forms["OneroomSearchForm-itemlist"];
	
	if (form.search_type.value == "region") {
		ItemlistToggleSearchType("Region");
	} else if (form.search_type.value == "university") {
		ItemlistToggleSearchType("University");
	} else if (form.search_type.value == "subway") {
		ItemlistToggleSearchType("Subway");
	}
	
	if (form.region1.value) {
		ItemlistSelectRegion1("",form.region1.value);
	}
	
	if (form.university_parent.value) {
		ItemlistSelectUniversity("",form.university_parent.value);
	}
	
	if (form.subway_parent.value) {
		ItemlistSelectSubway("",form.subway_parent.value);
	}
	
	ItemlistPriceFormToggle();
}

function ItemlistToggleSearchType(type) {
	document.getElementById("TabRegion").className = "tabOff";
	document.getElementById("TabUniversity").className = "tabOff";
	document.getElementById("TabSubway").className = "tabOff";
	
	document.getElementById("TabContentRegion").style.display = "none";
	document.getElementById("TabContentUniversity").style.display = "none";
	document.getElementById("TabContentSubway").style.display = "none";
	
	document.getElementById("TabContent"+type).style.display = "";
	document.getElementById("Tab"+type).className = "tabOn";
	
	document.forms["OneroomSearchForm-itemlist"].search_type.value = type.toLowerCase();
}

function ItemlistSelectRegion1(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"ItemlistSelectRegion1Inner");
		document.forms["OneroomSearchForm-itemlist"].region1.value = value;
	}
}

function ItemlistSelectRegion1Inner(XML) {
	if (XML) {
		var region2 = document.forms["OneroomSearchForm-itemlist"].region2.value;
		var root = XML.documentElement;
		var object = document.getElementById("ItemlistRegionList2").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		var isFind = false;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","ItemlistRegionList2");
			list.setAttribute("onclick","InputSelectBoxSelect('ItemlistRegionList2','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',ItemlistSelectRegion2)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
			
			if (region2 == data.getAttribute("idx")) isFind = true;
		}
		if (isFind == true) {
			ItemlistSelectRegion2("",region2);
		} else {
			document.forms["OneroomSearchForm-itemlist"].region2.value = "";
			document.forms["OneroomSearchForm-itemlist"].region3.value = "";
			document.getElementById("ItemlistRegionList2").getElementsByTagName("div")[0].innerHTML = "2차지역선택(읍/면/동)";
		}
	}
}

function ItemlistSelectRegion2(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"ItemlistSelectRegion2Inner");
		document.forms["OneroomSearchForm-itemlist"].region2.value = value;
	}
}

function ItemlistSelectRegion2Inner(XML) {
	if (XML) {
		var region3 = document.forms["OneroomSearchForm-itemlist"].region3.value;
		var root = XML.documentElement;
		var object = document.getElementById("ItemlistRegionList3").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		var isFind = false;
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","ItemlistRegionList3");
			list.setAttribute("onclick","InputSelectBoxSelect('ItemlistRegionList3','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',ItemlistSelectRegion3)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
			
			if (region3 == data.getAttribute("idx")) isFind = true;
		}
		
		if (isFind == false) {
			document.forms["OneroomSearchForm-itemlist"].region3.value = "";
		}
		
		if (root.childNodes.length == 0) {
			var list = document.createElement("li");
			list.setAttribute("clicker","ItemlistRegionList3");
			list.setAttribute("onclick","InputSelectBoxSelect('ItemlistRegionList3','3차지역선택','0',ItemlistSelectRegion3)");
			list.innerHTML = '3차지역이 없습니다.';
			object.appendChild(list);
			document.getElementById("ItemlistRegionList3").getElementsByTagName("div")[0].innerHTML = "3차지역선택";
		}
	}
}

function ItemlistSelectRegion3(text,value) {
	document.forms["OneroomSearchForm-itemlist"].region3.value = value;
}

function ItemlistSelectUniversity(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=university&parent="+value,"ItemlistSelectUniversityInner");
		document.forms["OneroomSearchForm-itemlist"].university_parent.value = value;
	}
}

function ItemlistSelectUniversityInner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("ItemlistUniversityList");
		object.innerHTML = "";
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("div");
			list.className = "checkboxList";
			list.innerHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="18" /><col width="100%" /><tr><td><input type="checkbox" name="check_university[]" value="'+data.getAttribute("idx")+'" onclick="ItemlistCheckUniversity()" /></td><td>'+data.getAttribute("title")+'<span class="bold blue">('+GetNumberFormat(data.getAttribute("itemnum"))+')</span></td></tr></table>';
			object.appendChild(list);
		}
		
		if (document.forms["OneroomSearchForm-itemlist"].university_idx.value) {
			var idx = document.forms["OneroomSearchForm-itemlist"].university_idx.value.split(",");
			var object = document.forms["OneroomSearchForm-itemlist"].getElementsByTagName("input");
			var findIDX = new Array();
			
			for (var i=0, loop=idx.length;i<loop;i++) {
				for (var j=0, loopj=object.length;j<loopj;j++) {
					if (object[j].name == "check_university[]" && object[j].value == idx[i]) {
						object[j].checked = true;
						findIDX.push(idx[i]);
					}
				}
			}
			
			document.forms["OneroomSearchForm-itemlist"].university_idx.value = findIDX.join(",");
		}
	}
}

function ItemlistCheckUniversity() {
	var object = document.forms["OneroomSearchForm-itemlist"].getElementsByTagName("input");
	var checked = new Array();
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].name == "check_university[]" && object[i].checked == true) checked.push(object[i].value);
	}
	document.forms["OneroomSearchForm-itemlist"].university_idx.value = checked.join(",");
}

function ItemlistSelectSubway(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=subway&parent="+value,"ItemlistSelectSubwayInner");
		document.forms["OneroomSearchForm-itemlist"].subway_parent.value = value;
	}
}

function ItemlistSelectSubwayInner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("ItemlistSubwayList");
		object.innerHTML = "";
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("div");
			list.className = "checkboxList";
			list.innerHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="18" /><col width="100%" /><tr><td><input type="checkbox" name="check_subway[]" value="'+data.getAttribute("idx")+'" onclick="ItemlistCheckSubway()" /></td><td>'+data.getAttribute("title")+'<span class="bold blue">('+GetNumberFormat(data.getAttribute("itemnum"))+')</span></td></tr></table>';
			object.appendChild(list);
		}
		
		if (document.forms["OneroomSearchForm-itemlist"].subway_idx.value) {
			var idx = document.forms["OneroomSearchForm-itemlist"].subway_idx.value.split(",");
			var object = document.forms["OneroomSearchForm-itemlist"].getElementsByTagName("input");
			var findIDX = new Array();
			
			for (var i=0, loop=idx.length;i<loop;i++) {
				for (var j=0, loopj=object.length;j<loopj;j++) {
					if (object[j].name == "check_subway[]" && object[j].value == idx[i]) {
						object[j].checked = true;
						findIDX.push(idx[i]);
					}
				}
			}
			
			document.forms["OneroomSearchForm-itemlist"].subway_idx.value = findIDX.join(",");
		}
	}
}

function ItemlistCheckSubway() {
	var object = document.forms["OneroomSearchForm-itemlist"].getElementsByTagName("input");
	var checked = new Array();
	for (var i=0, loop=object.length;i<loop;i++) {
		if (object[i].name == "check_subway[]" && object[i].checked == true) checked.push(object[i].value);
	}
	document.forms["OneroomSearchForm-itemlist"].subway_idx.value = checked.join(",");
}

function ItemlistPriceFormToggle() {
	var form = document.forms["OneroomSearchForm-itemlist"];
	if (form.is_buy.checked == true) {
		document.getElementById("ItemlistPriceBuy").style.display = "";
	} else {
		document.getElementById("ItemlistPriceBuy").style.display = "none";
	}
	
	if (form.is_rent_all.checked == true) {
		document.getElementById("ItemlistPriceRentAll").style.display = "";
	} else {
		document.getElementById("ItemlistPriceRentAll").style.display = "none";
	}
	
	if (form.is_rent_month.checked == true) {
		document.getElementById("ItemlistPriceRentDeposit").style.display = "";
	} else {
		document.getElementById("ItemlistPriceRentDeposit").style.display = "none";
	}
	
	if (form.is_rent_month.checked == true || form.is_rent_short.checked == true) {
		document.getElementById("ItemlistPriceRentMonth").style.display = "";
	} else {
		document.getElementById("ItemlistPriceRentMonth").style.display = "none";
	}
}