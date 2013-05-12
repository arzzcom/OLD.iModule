function SearchOptionCheck(id) {
	var thisKey = document.forms["ModuleBoardSearch"].key.value;
	var option = id.replace("checkbox_","");

	document.getElementById("checkbox_"+thisKey).className = "checkboxoff";
	document.getElementById(id).className = "checkboxon";

	document.forms["ModuleBoardSearch"].key.value = option;
}

function SelectCategory(object,depth,idx) {
	var list = document.getElementById("category"+depth).getElementsByTagName("ul")[0].getElementsByTagName("li");
	for (var i=0, loop=list.length;i<loop;i++) {
		list[i].className = "";
	}
	
	object.className = "select";
	document.forms["ModuleKinPost"]["category"+depth].value = idx;
	
	if (parseInt(depth) == 1) {
		document.forms["ModuleKinPost"]["category2"].value = "";
		document.forms["ModuleKinPost"]["category3"].value = "";
	}
	if (parseInt(depth) == 2) {
		document.forms["ModuleKinPost"]["category3"].value = "";
	}
	if (parseInt(depth) != 3) {
		GetHttpRequestXML(ENV.dir+"/module/kin/exec/Ajax.get.php","action=category&idx="+idx,"SelectCategoryInner",[depth]);
	}
}

function SelectCategoryInner(XML,depth) {
	if (depth == 1) {
		document.getElementById("category2").getElementsByTagName("ul")[0].innerHTML = "";
		document.getElementById("category3").getElementsByTagName("ul")[0].innerHTML = "";
		
		var object = document.getElementById("category2").getElementsByTagName("ul")[0];
	} else if (depth == 2) {
		document.getElementById("category3").getElementsByTagName("ul")[0].innerHTML = "";
		
		var object = document.getElementById("category3").getElementsByTagName("ul")[0];
	}
	
	if (XML) {
		var data = XML.documentElement;
		
		for (var i=0, loop=data.childNodes.length;i<loop;i++) {
			var list = document.createElement("li");
			list.innerHTML = data.childNodes.item(i).getAttribute("category");
			list.setAttribute("idx",data.childNodes.item(i).getAttribute("idx"));
			list.onclick = function(event) {
				SelectCategory(this,parseInt(depth)+1,this.getAttribute("idx"));
			};
			list.onmouseover = function(event) { if (this.className != "select") this.className = "over"; };
			list.onmouseout = function(event) { if (this.className != "select") this.className = ""; };
			object.appendChild(list);
		}
	}
}

function SelectPoint(text,value) {
	document.forms["ModuleKinPost"].point.value = value;
}