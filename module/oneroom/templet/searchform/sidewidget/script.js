function SideWidgetSelectRegion1(text,value) {
	if (value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=region&parent="+value,"SideWidgetSelectRegion1Inner");
		document.forms["OneroomSearchForm-sidewidget"].region1.value = value;
		document.forms["OneroomSearchForm-sidewidget"].region2.value = "";
	}
}

function SideWidgetSelectRegion1Inner(XML) {
	if (XML) {
		var root = XML.documentElement;
		var object = document.getElementById("SideWidgetRegionList2").getElementsByTagName("ul")[0];
		object.innerHTML = "";

		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			var data = root.childNodes.item(i);
			var list = document.createElement("li");
			list.setAttribute("clicker","SideWidgetRegionList2");
			list.setAttribute("onclick","InputSelectBoxSelect('SideWidgetRegionList2','"+data.getAttribute("title")+"','"+data.getAttribute("idx")+"',SideWidgetSelectRegion2)");
			list.innerHTML = data.getAttribute("title");
			object.appendChild(list);
		}
		document.getElementById("SideWidgetRegionList2").getElementsByTagName("div")[0].innerHTML = "2차지역선택(읍/면/동)";
	}
}

function SideWidgetSelectRegion2(text,value) {
	document.forms["OneroomSearchForm-sidewidget"].region2.value = value;
}

function SideWidgetSelectPriceType(text,value) {
	document.getElementById("SideWidgetPriceArea1").style.display = "none";
	document.getElementById("SideWidgetPriceArea2").style.display = "none";
	document.getElementById("SideWidgetPriceArea3").style.display = "none";
	document.getElementById("SideWidgetPriceAreaBar").style.display = "";
	
	if (value) {
		document.getElementById("SideWidgetPriceArea"+value).style.display = "";
		document.getElementById("SideWidgetPriceAreaBar").style.display = "";
	}
	
	document.forms["OneroomSearchForm-sidewidget"].price_type.value = value;
	document.forms["OneroomSearchForm-sidewidget"].price1.value = "";
	document.forms["OneroomSearchForm-sidewidget"].price2.value = "";

}

function SideWidgetSelectPrice1(text,value) {
	document.forms["OneroomSearchForm-sidewidget"].price1.value = value;
	document.forms["OneroomSearchForm-sidewidget"].price2.value = "";
}

function SideWidgetSelectPrice2(text,value) {
	document.forms["OneroomSearchForm-sidewidget"].price1.value = value;
	document.forms["OneroomSearchForm-sidewidget"].price2.value = "";
}

function SideWidgetSelectPrice3(text,value) {
	document.forms["OneroomSearchForm-sidewidget"].price1.value = value;
}

function SideWidgetSelectPrice4(text,value) {
	document.forms["OneroomSearchForm-sidewidget"].price2.value = value;
}