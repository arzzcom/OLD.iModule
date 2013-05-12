function CheckRegisterNumber(type) {
	var object = document.forms["RegisterForm"];
	
	if (object.register_number1.value && object.register_number2.value && object.register_number3.value) {
		GetHttpRequestXML(ENV.dir+"/module/oneroom/exec/Ajax.get.php","action=check_register_number&type="+type+"&register_number="+GetAjaxParam(object.register_number1.value+"-"+object.register_number2.value+"-"+object.register_number3.value),"CheckRegisterNumberInner");
	}
}

function CheckRegisterNumberInner(XML) {
	if (XML) {
		var result = XML.documentElement.childNodes.item(0);

		if (result.getAttribute("result") == "FALSE") {
			document.getElementById("DuplicationCheck").className = "msg red";
		} else {
			document.getElementById("DuplicationCheck").className = "msg blue";
		}
		
		document.getElementById("DuplicationCheck").innerHTML = result.getAttribute("msg");
	}
}