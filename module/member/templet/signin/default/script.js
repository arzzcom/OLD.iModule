function SelectPasswordQuestionBySkin(text,value) {
	document.forms["MemberSignIn"]["password_question"].value = value;
	document.forms["MemberSignIn"]["password_answer"].focus();
}

function SelectCellPhoneProviderBySkin(text,value) {
	document.forms["MemberSignIn"]["provider"].value = value;
}

function SelectCellPhonePnoBySkin(text,value) {
	document.forms["MemberSignIn"]["cellphone1"].value = value;
	document.forms["MemberSignIn"]["cellphone2"].focus();
}

function SelectTelePhonePnoBySkin(text,value) {
	document.forms["MemberSignIn"]["telephone1"].value = value;
	document.forms["MemberSignIn"]["telephone2"].focus();
}

function SelectBirthdayYearBySkin(text,value) {
	document.forms["MemberSignIn"]["birthday1"].value = value;
}

function SelectBirthdayMonthBySkin(text,value) {
	document.forms["MemberSignIn"]["birthday2"].value = value;
}

function SelectBirthdayDayBySkin(text,value) {
	document.forms["MemberSignIn"]["birthday3"].value = value;
}

function SearchAddressBySkin(e,object) {
	if (e === false) {
		if (!document.getElementById(object).value) {
			document.getElementById(document.getElementById(object).getAttribute("address")).getElementsByTagName("div")[0].innerHTML = "읍.면.동을 입력 후 우편번호검색버튼을 클릭하여 주십시오.";
			document.getElementById("SearchZipcode").focus();
		} else {
			GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=address&keyword="+GetAjaxParam(document.getElementById(object).value),"SearchAddressBySkinInner",[object]);
		}
		return false;
	} else {
		if (e.keyCode) {
			var pressKey = e.keyCode;
		} else {
			var pressKey = e.which;
		}

		if (pressKey == 13) {
			return SearchAddressBySkin(false,object);
		} else {
			return true;
		}
	}
}

function SearchAddressBySkinInner(XML,field) {
	if (XML) {
		var root = XML.documentElement;

		if (root.childNodes.length > 0) {
			var sHTML = "";
			var object = document.getElementById(document.getElementById(field).getAttribute("address")).getElementsByTagName("ul")[0];
			object.innerHTML = "";

			for (var i=0, loop=root.childNodes.length;i<loop;i++) {
				var data = root.childNodes.item(i);

				var list = document.createElement("li");
				
				list.className = "item";
				list.setAttribute("clicker",document.getElementById(field).getAttribute("address"));
				list.setAttribute("onclick","InputSelectBoxSelect('"+document.getElementById(field).getAttribute("address")+"','<span class=\"bold\">"+data.getAttribute("zipcode")+"</span> "+data.getAttribute("address")+"','"+data.getAttribute("value")+"',function(text,value) { SearchAddressSelectBySkin(\'"+field+"\',text,value); })");
				list.innerHTML = '<span class="bold">'+data.getAttribute("zipcode")+'</span> '+data.getAttribute("address");

				object.appendChild(list);
			}

			document.getElementById(document.getElementById(field).getAttribute("address")).getElementsByTagName("div")[0].innerHTML = "기본주소를 선택하세요.";
		} else {
			document.getElementById(document.getElementById(field).getAttribute("address")).getElementsByTagName("div")[0].innerHTML = "주소를 검색하지 못하였습니다.";
			document.getElementById(field).focus();
		}
	}
}

function SearchAddressSelectBySkin(field,text,value) {
	var value = value.split("|");

	document.forms["MemberSignIn"][document.getElementById(field).getAttribute("zipcode")].value = value[0];
	document.forms["MemberSignIn"][document.getElementById(field).getAttribute("address1")].value = value[1];
	document.forms["MemberSignIn"][document.getElementById(field).getAttribute("address2")].focus();
}

function SelectGenderBySkin(text,value) {
	document.forms["MemberSignIn"].gender.value = value;
}