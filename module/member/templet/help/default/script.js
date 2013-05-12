function FindUserID() {
	var object = document.forms["idForm"];
	var name = object.name.value;
	var email = object.email ? object.email.value : "";
	var jumin = object.jumin1 ? object.jumin1.value+"-"+object.jumin2.value : "";

	document.getElementById("FindPasswordButton").setAttribute("name",name);
	document.getElementById("FindPasswordButton").setAttribute("jumin",jumin);
	document.getElementById("FindPasswordButton").setAttribute("email",email);

	GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=find&get=user_id&name="+GetAjaxParam(name)+"&email="+GetAjaxParam(email)+"&jumin="+GetAjaxParam(jumin),"FindUserIDInner");
}

function FindUserIDInner(XML) {
	if (XML) {
		var result = XML.documentElement.childNodes.item(0);

		if (result.getAttribute("result") == "FALSE") {
			document.getElementById("FindUserIDInfo").innerHTML = result.getAttribute("msg");
			document.getElementById("FindPasswordButton").style.display = "none";
		} else {
			document.getElementById("FindUserIDInfo").innerHTML = '회원님께서는 <span class="blud bold">'+result.getAttribute("user_id")+'</span> 아이디로 가입하셨습니다.<br />계속해서 패스워드를 찾으시려면 아래 패스워드찾기 버튼을 클릭하여 주십시오.';
			document.getElementById("FindPasswordButton").style.display = "";
			document.getElementById("FindPasswordButton").setAttribute("user_id",result.getAttribute("user_id"));
		}
	}
}

function SetFindPassword() {
	var name = document.getElementById("FindPasswordButton").getAttribute("name");
	var jumin = document.getElementById("FindPasswordButton").getAttribute("jumin");
	var email = document.getElementById("FindPasswordButton").getAttribute("email");
	var user_id = document.getElementById("FindPasswordButton").getAttribute("user_id");

	var object = document.forms["passwordForm"];

	object.user_id.value = user_id;
	object.name.value = name;
	if (object.jumin1) {
		var jumin = jumin.split("-");
		object.jumin1.value = jumin[0];
		object.jumin2.value = jumin[1];
	} else {
		object.email.value = email;
	}
}

function FindPassword() {
	var object = document.forms["passwordForm"];
	var user_id = object.user_id.value;
	var name = object.name.value;
	var email = object.email ? object.email.value : "";
	var jumin = object.jumin1 ? object.jumin1.value+"-"+object.jumin2.value : "";

	GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=find&get=password&user_id="+GetAjaxParam(user_id)+"&name="+GetAjaxParam(name)+"&email="+GetAjaxParam(email)+"&jumin="+GetAjaxParam(jumin),"FindPasswordInner");
}

function FindPasswordInner(XML) {
	if (XML) {
		var result = XML.documentElement.childNodes.item(0);

		if (result.getAttribute("result") == "FALSE") {
			document.getElementById("FindPasswordStep1").style.display = "";
			document.getElementById("FindPasswordStep2").style.display = "none";
			document.getElementById("FindPasswordInfo").innerHTML = result.getAttribute("msg");
			document.getElementById("FindPasswordNextButton").style.display = "";
			document.getElementById("SendPasswordButton").style.display = "none";
		} else {
			document.getElementById("FindPasswordStep1").style.display = "none";
			document.getElementById("FindPasswordStep2").style.display = "";
			document.getElementById("FindPasswordQuestion").innerHTML = result.getAttribute("question");
			document.forms["passwordForm"].answer.focus();
			document.getElementById("FindPasswordInfo").innerHTML = '패스워드 찾기 질문에 대한 답을 입력하신 후 패스워드발급 버튼을 누르시면<br> 등록하신 이메일로 변경된 패스워드가 발송됩니다.';
			document.getElementById("FindPasswordNextButton").style.display = "none";
			document.getElementById("SendPasswordButton").style.display = "";
		}
	}
}

function SendFindPassword() {
	var object = document.forms["passwordForm"];
	var user_id = object.user_id.value;
	var name = object.name.value;
	var email = object.email ? object.email.value : "";
	var jumin = object.jumin1 ? object.jumin1.value+"-"+object.jumin2.value : "";
	var answer = object.answer.value;

	document.getElementById("FindPasswordInfo").innerHTML = "패스워드를 요청중입니다.<br />잠시만 기다려주십시오.";
	document.getElementById("SendPasswordButton").style.display = "none";

	GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=find&get=send&answer="+GetAjaxParam(answer)+"&user_id="+GetAjaxParam(user_id)+"&name="+GetAjaxParam(name)+"&email="+GetAjaxParam(email)+"&jumin="+GetAjaxParam(jumin),"SendFindPasswordInner");
}

function SendFindPasswordInner(XML) {
	if (XML) {
		var result = XML.documentElement.childNodes.item(0);

		if (result.getAttribute("result") == "FALSE") {
			document.getElementById("FindPasswordInfo").innerHTML = result.getAttribute("msg");
			document.getElementById("SendPasswordButton").style.display = "none";
		} else {
			document.getElementById("SendPasswordButton").style.display = "";
			document.getElementById("FindPasswordInfo").innerHTML = '회원님의 임시패스워드가 <span class="bold">'+result.getAttribute("email")+'</span>로 발송되었습니다. 이메일을 확인하신 후 로그인하여 주십시오.';
		}
	}
}