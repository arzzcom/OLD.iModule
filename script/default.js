document.writeln('<div id="WindowDisabledLayer" style="display:none;"></div>');
document.writeln('<div id="TipLayer" style="display:none;"></div>');
document.writeln('<div id="ShowImageLayer" style="display:none;"></div>');
var GlobalToggleList = new Array();
var ShowImageOriginalSize = new Array();

var isIE = '\v'=='v';

function OpenPopup(url,width,height,scroll,name) {
	var windowLeft = (screen.width-width)/2;
	var windowTop = (screen.height-height)/2;
	windowTop = windowTop>20 ? windowTop-20 : windowTop;
	var opener = window.open(url,name !== undefined ? name : "","top="+windowTop+",left="+windowLeft+",width="+width+",height="+height+",scrollbars="+(scroll == true ? "1" : "0"));

	if (!opener) {
		alert("팝업이 차단되었습니다.");
	}
}

function GetEmbed(id,swf,width,height,vars) {
	var sHTML = '';

	if (isIE == true) {
		sHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="'+width+'" height="'+height+'" id="'+id+'" align="middle">';
		sHTML+= '<param name="allowScriptAccess" value="always" />';
		sHTML+= '<param name="base" value=".">';
		sHTML+= '<param name="flashVars" value="'+vars+'" />';
		sHTML+= '<param name="movie" value="'+swf+'" />';
		sHTML+= '<param name="quality" value="high" />';
		sHTML+= '<param name="wmode" value="transparent" />';
		sHTML+= '<embed src="'+swf+'" quality="high" wmode="transparent" style="width:'+width+'px; height:'+height+'px;" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+vars+'"></embed>';
		sHTML+= '</object>';

		document.writeln(sHTML);
		eval("window."+id+" = document.getElementById('"+id+"');");
	} else {
		sHTML = '<embed id="'+id+'" src="'+swf+'" quality="high" base="." wmode="transparent" style="width:'+width+'px; height:'+height+'px;" align="absmiddle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+vars+'"></embed>';
		document.writeln(sHTML);
	}
}

// Cookie
function SetCookie(name,value,expire,path) {
	path = !path ? "/" : path;
	var todaydate = new Date();
	unixtime = todaydate.getTime();

	if (value == null) {
		extime = unixtime-3600;
		todaydate.setTime(extime);
		expiretime = " expires=" + todaydate.toUTCString() +";";
	} else {
		extime = unixtime+(expire*1000);
		todaydate.setTime(extime);
		if (expire) expiretime = " expires=" + todaydate.toUTCString() +";";
		else expiretime = "";
	}

	document.cookie = name + "=" + escape(value) + "; path="+path+";"+expiretime;
}

function GetCookie(name) {
	var cookies = document.cookie.split(";");
	var values = "";

	for (var i=0, total=cookies.length;i<total;i++) {
		if (cookies[i].indexOf(name+"=")!=-1) {
			var temp = cookies[i].split("=");
			values = temp[1];
			break;
		}
	}

	return values;
}

// TipLayer
function Tip(mode,text,event) {
	var event = event ? event : window.event;
	var object = document.getElementById("TipLayer");

	if (mode == true) {
		if (event.clientY + 50 > document.documentElement.clientHeight) {
			var top = event.clientY - 58;
		} else {
			var top = event.clientY + 8;
		}
		var left = event.clientX+8;

		object.style.top = top+"px";
		object.style.left = left+"px";
		object.innerHTML = text;
		object.style.visibility = "visible";
	} else {
		object.style.visibility = "hidden";
	}
}

function WindowDisabled(mode) {
	var object = document.getElementById("WindowDisabledLayer");

	if (mode == true) {
		var width = Math.max(Math.max(document.body.scrollWidth, document.documentElement.scrollWidth),Math.max(document.body.offsetWidth,document.documentElement.offsetWidth),Math.max(document.body.clientWidth, document.documentElement.clientWidth));
		var height = Math.max(Math.max(document.body.scrollHeight, document.documentElement.scrollHeight),Math.max(document.body.offsetHeight,document.documentElement.offsetHeight),Math.max(document.body.clientHeight, document.documentElement.clientHeight));
		object.style.width = width+"px";
		object.style.height = height+"px";
		object.style.display = "";
	} else {
		object.style.display = "none";
	}
}

function LayerCenter(id,isFixed) {
	var top = Math.round((document.documentElement.clientHeight-document.getElementById(id).scrollHeight)/2);
	var left = Math.round((document.documentElement.clientWidth-document.getElementById(id).scrollWidth)/2);

	if (isFixed == false) top = top + document.documentElement.scrollTop;
	document.getElementById(id).style.top = top+"px";
	document.getElementById(id).style.left = left+"px";
}

// AIR
function ShowAIRInstallLayer(appID,appPath,siteURL,loginAuth) {
	WindowDisabled(true);

	var id = "InstallAIR";
	var width = 300;
	var height = 193;
	var swf = ENV.dir+"/flash/InstallAIR.swf";
	var layer = document.createElement("div");
	layer.setAttribute("id",id+"Layer");
	layer.style.width = width+"px";
	layer.style.height = height+"px";
	layer.style.position = "absolute";
	layer.style.zIndex = "100000";

	var vars = "appID="+appID+"&appPath="+appPath+"&siteURL="+siteURL+"&loginAuth="+loginAuth;
	
	if (isIE == true) {
		sHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="'+width+'" height="'+height+'" id="'+id+'" align="middle">';
		sHTML+= '<param name="allowScriptAccess" value="always" />';
		sHTML+= '<param name="base" value=".">';
		sHTML+= '<param name="flashVars" value="'+vars+'" />';
		sHTML+= '<param name="movie" value="'+swf+'" />';
		sHTML+= '<param name="quality" value="high" />';
		sHTML+= '<param name="wmode" value="transparent" />';
		sHTML+= '<embed src="'+swf+'" quality="high" wmode="transparent" style="width:'+width+'px; height:'+height+'px;" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+vars+'"></embed>';
		sHTML+= '</object>';
	} else {
		sHTML = '<embed id="'+id+'" src="'+swf+'" quality="high" base="." wmode="transparent" style="width:'+width+'px; height:'+height+'px;" align="absmiddle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+vars+'"></embed>';
	}
	
	layer.innerHTML = sHTML;
	document.body.appendChild(layer);
	LayerCenter(id+"Layer",false);
}

function HideAIRInstallLayer() {
	document.body.removeChild(document.getElementById("InstallAIRLayer"));
	WindowDisabled(false);
}

// Ajax
function GetAjaxParam(str) {
	if (str) {
		var paramReg = new RegExp("\\+","gi");
		str = str.replace(paramReg,"#*plus*#");
		return encodeURIComponent(str);
	} else {
		return "";
	}
}

// Text
function GetStripTag(str) {
	return str.replace(/<\/?[^>]+>/gi,'');
}

function GetNumberFormat(value) {
	var str = value.toString();
	str = str.replace(/[^0-9\-\.]+/gi,"");

	var isMinus = false;
	if (str.substr(0,1) == "-") {
		str = str.replace("-","");
		isMinus = true;
	}
	str = parseInt(str.replace(/[^\d]+/g,""));
	str = str.toString();

	var k = 0;
	var getNumber = "";

	for (i=str.length;i>0;i--) {
		getNumber+= str.substr(i-1,1);
		k++;
		if (k%3 == 0 && i != 1) {
			getNumber+= ",";
		}
	}

	var returnValue = "";
	k = getNumber.length-1;
	for (i=0;i<getNumber.length;i++) {
		returnValue+= getNumber.substr(k,1);
		k--;
	}

	if (isMinus == true) returnValue = "-"+returnValue;
	return returnValue;
}

function GetHttpRequestXML(url,query,inFunction,inValues) {
	var selectVar = Math.random();
	var XMLHttp = null;
	if (inValues === undefined) inValues = new Array();

	if (window.XMLHttpRequest) {
		XMLHttp = new XMLHttpRequest();
	} else {
		XMLHttp = new ActiveXObject("Microsoft.XMLHttp");
	}

	XMLHttp.open("post",url,true);
	XMLHttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	XMLHttp.onreadystatechange = function() {
		if (XMLHttp.readyState == 4) {
			if (XMLHttp.status == 200) {
				if (inFunction) {
					inValue = "XMLHttp.responseXML";
					for (var i=0;i<inValues.length;i++) {
						inValue+= ",'"+inValues[i]+"'";
					}

					eval(inFunction+"("+inValue+")");
				}
			}
		}
	}

	query = query!="" ? "&"+query : "";

	XMLHttp.send("rnd="+selectVar+query);
}

function GetElementsByClassName(className,tagName) {
	var result = new Array();
	var temp = document.getElementsByTagName(tagName);
	var regexp = new RegExp('\\b'+className+'\\b');

	for(var i=0,loop=temp.length;i<loop;i++) {
		if(regexp.test(temp[i].className)) result.push(temp[i]);
	}
	
	return result;
}

function ShowImage(path) {
	ShowImageIsResize = false;
	var image = path.split(",");
	WindowDisabled(true);

	var layer = document.getElementById("ShowImageLayer");

	var sHTML = '<table cellpadding="0" cellspacing="0" class="layoutfixed"><col width="8" /><col width="100%" /><col width="8" /><tr><td class="LayerTopLeft"></td><td class="LayerTopBg"><div class="ButtonClose" onmouseover="this.className=\'ButtonCloseOver\'" onmouseout="this.className=\'ButtonClose\'" onclick="ShowImageAction(\'close\')"></div><div class="ButtonFullSize" onmouseover="this.className=\'ButtonFullSizeOver\'" onmouseout="this.className=\'ButtonFullSize\'" onclick="ShowImageAction(\'fullsize\')"></div></td><td class="LayerTopRight"></td></tr>';
	sHTML+= '<tr><td class="LayerBodyLeft"></td><td><div id="ShowImageLayerLoading"><div>이미지를 로딩중입니다.</div></div></td><td class="LayerBodyRight"></td></tr>';
	sHTML+= '<tr><td class="LayerBottomLeft"></td><td class="LayerBottomBg"></td><td class="LayerBottomRight"></td></tr>';
	sHTML+= '</table>';

	layer.innerHTML = sHTML;
	layer.style.width = "416px";
	layer.style.display = "";

	LayerCenter("ShowImageLayer",false);

	var sIMG = document.createElement("IMG");
	sIMG.setAttribute("name","ShowImageObject");
	sIMG.src = image[0];
	sIMG.onload = function(event) {
		var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth;
		var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight;

		var width = sIMG.width;
		var height = sIMG.height;

		ShowImageOriginalSize[0] = width;
		ShowImageOriginalSize[1] = height;

		if ((width+16)*screenHeight/screenWidth < height+59) {
			if (height+59 > screenHeight) {
				width = width*(screenHeight-100)/height;
				height = screenHeight-100;
				ShowImageIsResize = true;
			}
		} else {
			if (width+16 > screenWidth) {
				height = height*(screenWidth-100)/width;
				width = screenWidth-100;
				ShowImageIsResize = true;
			}
		}

		sIMG.width = width;
		sIMG.height = height;

		document.getElementById("ShowImageLayerLoading").innerHTML = "";
		document.getElementById("ShowImageLayer").style.width = (width+16)+"px";
		document.getElementById("ShowImageLayer").style.height = (height+59)+"px";
		document.getElementById("ShowImageLayerLoading").appendChild(sIMG);

		LayerCenter("ShowImageLayer",false);
	}
	sIMG.onerror = function(event) {
		document.getElementById("ShowImageLayerLoading").innerHTML = "<div>이미지를 로딩하지 못하였습니다.</div>";
	}
}

function ShowImageAction(type) {
	if (type == "close") {
		document.getElementById("ShowImageLayer").style.display = "none";
		WindowDisabled(false);
	} else if (type == "fullsize") {
		if (document.images["ShowImageObject"].width != ShowImageOriginalSize[0] || document.images["ShowImageObject"].height != ShowImageOriginalSize[1]) {
			document.images["ShowImageObject"].width = ShowImageOriginalSize[0];
			document.images["ShowImageObject"].height = ShowImageOriginalSize[1];

			document.getElementById("ShowImageLayer").style.width = (ShowImageOriginalSize[0]+16)+"px";
			document.getElementById("ShowImageLayer").style.height = (ShowImageOriginalSize[1]+59)+"px";
			document.getElementById("ShowImageLayer").style.top = "0px";
			document.getElementById("ShowImageLayer").style.left = "0px";
			document.documentElement.scrollTop = "0px";
		} else {
			var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth;
			var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight;

			var width = ShowImageOriginalSize[0];
			var height = ShowImageOriginalSize[1];

			if ((width+16)*screenHeight/screenWidth < height+59) {
				if (height+59 > screenHeight) {
					width = width*(screenHeight-100)/height;
					height = screenHeight-100;
					ShowImageIsResize = true;
				}
			} else {
				if (width+16 > screenWidth) {
					height = height*(screenWidth-100)/width;
					width = screenWidth-100;
					ShowImageIsResize = true;
				}
			}

			document.images["ShowImageObject"].width = width;
			document.images["ShowImageObject"].height = height;

			document.getElementById("ShowImageLayer").style.width = (width+16)+"px";
			document.getElementById("ShowImageLayer").style.height = (height+59)+"px";

			LayerCenter("ShowImageLayer",false);
		}
	}
}

function GetRealOffsetTop(o) { return o ? o.offsetTop + GetRealOffsetTop(o.offsetParent) : 0; }
function GetRealOffsetLeft(o) { return o ? o.offsetLeft + GetRealOffsetLeft(o.offsetParent) : 0; }

/*****************************************************************************************
 * Member
 *****************************************************************************************/
function MemberLoginAutoLogin(id) {
	if (document.getElementById(id).checked == true) {
		if (confirm(document.getElementById(id).getAttribute("msg").replace(/<br \/>/gi,"\n")) == false) {
			document.getElementById(id).checked = false;
		}
	}
}

function MemberLoginCheck(name) {
	var object = document.forms[name];
	if (!object.user_id.value) {
		alert(object.user_id.getAttribute("msg"));
		object.user_id.focus();
		return false;
	}

	if (!object.password.value) {
		alert(object.password.getAttribute("msg"));
		object.password.focus();
		return false;
	}
}

function MemberSignInCheck(step) {
	var object = document.forms["MemberSignIn"];

	if (step == 1) {
		if (object.agreement && object.agreement.checked == false) {
			alert("이용약관에 동의하여 주십시오.");
			return false;
		}

		if (object.privacy && object.privacy.checked == false) {
			alert("개인정보 보호정책에 동의하여 주십시오.");
			return false;
		}

		if (object.youngpolicy && object.youngpolicy.checked == false) {
			alert("청소년 보호정책에 동의하여 주십시오.");
			return false;
		}
	}

	if (step == 2) {
		if (!object.realname.value) {
			alert("실명을 입력하여 주십시오.");
			object.realname.focus();
			return false;
		}

		if (object.jumin1.value.length != 6 || object.jumin2.value.length != 7) {
			alert("주민등록번호를 정확하게 입력하여 주십시오.");
			object.jumin1.value = object.jumin2.value = "";
			object.jumin1.focus();
			return false;
		}
	}

	if (step == 3) {
		if (object.checkname) {
			if (!object.checkname.value) {
				alert("실명을 입력하여 주십시오.");
				object.checkname.focus();
				return false;
			}

			if (object.jumin1) {
				if (object.jumin1.value.length != 6 || object.jumin2.value.length != 7) {
					alert("주민등록번호를 정확하게 입력하여 주십시오.");
					object.jumin1.value = object.jumin2.value = "";
					object.jumin1.focus();
					return false;
				} else {
					var QueryString = "jumin="+object.jumin1.value+"-"+object.jumin2.value;
					object.jumin1.readOnly = true;
					object.jumin2.readOnly = true;
				}
			} else if (object.companyno1) {
				if (object.companyno1.value.length != 3 || object.companyno2.value.length != 2 || object.companyno3.value.length != 5) {
					alert("사업자등록번호 정확하게 입력하여 주십시오.");
					object.companyno1.value = object.companyno2.value = object.companyno3.value = "";
					object.companyno1.focus();
					return false;
				} else {
					var QueryString = "companyno="+object.companyno1.value+"-"+object.companyno2.value+"-"+object.companyno3.value;
					object.companyno1.readOnly = true;
					object.companyno2.readOnly = true;
					object.companyno3.readOnly = true;
				}
			} else {
				if (!object.email.value) {
					alert("이메일 주소를 정확하게 입력하여 주십시오.");
					object.email.focus();
					return false;
				} else {
					var QueryString = "email="+object.email.value;
					object.email.readOnly = true;
				}
			}

			object.checkname.readOnly = true;

			var InnerFunctionValue = new Array();
			InnerFunctionValue.push(step);
			GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=membercheck&"+QueryString+"&name="+object.checkname.value,"MemberSignInCheckInner",InnerFunctionValue);

			return false;
		}
	}

	return true;
}

function MemberSignInCheckInner(XML,step) {
	var object = document.forms["MemberSignIn"];
	var result = XML.documentElement.childNodes.item(0);

	if (result.getAttribute("result") == "false") {
		alert(result.getAttribute("msg"));
		object.checkname.readOnly = false;
		if (result.getAttribute("field") == "jumin") {
			object.jumin1.value = object.jumin2.value = "";
			object.jumin1.readOnly = false;
			object.jumin2.readOnly = false;
			object.jumin1.focus();
		} else {
			object.email.value = "";
			object.email.readOnly = false;
			object.email.focus();
		}
	} else {
		if (result.getAttribute("find") == "true") {
			alert("회원님께서는 "+result.getAttribute("reg_date")+"에 "+result.getAttribute("user_id")+" 아이디로 가입하신 이력이 있습니다.\n해당 아이디로 로그인하시거나, 아이디 또는 비밀번호가 기억나지 않으신다면, 아래의 아이디/비밀번호 찾기를 이용하여 주십시오.");
			object.checkname.readOnly = false;
			if (result.getAttribute("field") == "jumin") {
				object.jumin1.readOnly = false;
				object.jumin2.readOnly = false;
				object.jumin1.focus();
			} else {
				object.email.readOnly = false;
				object.email.focus();
			}
		} else {
			alert("회원님께서는 가입하신 이력이 없습니다.\n가입절차를 계속 진행합니다.");
			object.submit();
		}
	}
}

function MemberDuplicationCheck(field) {
	var object = document.forms["MemberSignIn"];

	if (field != "password") {
		if (field == "user_id") {
			if (!object.user_id.value) {
				document.getElementById("DuplicationCheck_user_id").innerHTML = "아이디를 입력하세요.";
				document.getElementById("DuplicationCheck_user_id").className = "msgerror";
				return false;
			}
			var QueryString = "check=user_id&value="+object.user_id.value;
		}

		if (field == "email") {
			if (!object.email.value) {
				document.getElementById("DuplicationCheck_email").innerHTML = "이메일주소를 입력하세요.";
				document.getElementById("DuplicationCheck_email").className = "msgerror";
				return false;
			}
			var QueryString = "check=email&value="+object.email.value;
		}

		if (field == "nickname") {
			if (!object.nickname.value) {
				document.getElementById("DuplicationCheck_nickname").innerHTML = "닉네임을 입력하세요.";
				document.getElementById("DuplicationCheck_nickname").className = "msgerror";
				return false;
			}
			var QueryString = "check=nickname&value="+object.nickname.value;
		}

		if (field == "voter") {
			if (object.voter.value) {
				var QueryString = "check=voter&value="+object.voter.value;
			} else {
				return false;
			}
		}

		var InnerFunctionValue = new Array();
		InnerFunctionValue.push(field);

		GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=duplication&"+QueryString,"MemberDuplicationCheckInner",InnerFunctionValue);
	} else {
		if (object.password1.value && object.password2.value) {
			if (object.password1.value != object.password2.value) {
				document.getElementById("DuplicationCheck_password").innerHTML = "패스워드가 서로 일치하지 않습니다.";
				document.getElementById("DuplicationCheck_password").className = "msgerror";
				return false;
			} else {
				document.getElementById("DuplicationCheck_password").innerHTML = "패스워드가 확인되었습니다.";
				document.getElementById("DuplicationCheck_password").className = "msgtrue";
				return false;
			}
		}
	}
}

function MemberDuplicationCheckInner(XML,field) {
	var result = XML.documentElement.childNodes.item(0);

	if (result.getAttribute("result") == "true") {
		document.getElementById("DuplicationCheck_"+field).className = "msgtrue";
	} else {
		document.getElementById("DuplicationCheck_"+field).className = "msgerror";
	}

	document.getElementById("DuplicationCheck_"+field).innerHTML = result.getAttribute("msg");
}

function MemberCellPhoneCheck() {
	var object = document.forms["MemberSignIn"];

	if (object.cellphone1) {
		if (object.provider && !object.provider.value) {
			alert("통신사를 선택하여 주십시오.");
			return false;
		}
		if (!object.cellphone1.value || !object.cellphone2.value || !object.cellphone3.value) {
			alert("휴대전화번호를 정확하게 입력하여 주십시오.");
		} else {
			var phone = object.cellphone1.value+"-"+object.cellphone2.value+"-"+object.cellphone3.value;
			var InnerFunctionValue = new Array();
			InnerFunctionValue.push(phone);
			GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=phonecheck&phone="+phone,"MemberCellPhoneCheckInner",InnerFunctionValue);
		}
	}
}

function MemberCellPhoneCheckInner(XML,phone) {
	var object = document.forms["MemberSignIn"];
	var result = XML.documentElement.childNodes.item(0).getAttribute("result");

	if (result == "true") {
		alert("인증번호가 성공적으로 발송되었습니다.\n인증코드 입력란에 인증코드를 입력하여 주십시오.");
		document.getElementById("MemberPhoneInsert").style.display = "";
		object.pcode.value = "";
		object.pcode.focus();
	} else if (result == "false") {
		alert("인증번호발송이 실패하였습니다.\n휴대전화번호를 정확하게 확인하신 후 다시 시도하시거나, 관리자에게 연락주십시오.");
		document.getElementById("MemberPhoneInsert").style.display = "none";
		object.pcode.value = "";
	} else if (result == "notmodify") {
		alert("이미 인증을 받은 휴대전화번호입니다.\n재인증을 받지 않아도 됩니다.");
	} else {
		alert("인증번호를 발송한지 3분이 경과되지 않았습니다.\n3분 후에도 인증번호가 도착하지 않는다면, 그 때 시도하여 주십시오.");
		document.getElementById("MemberPhoneInsert").style.display = "";
		object.pcode.value = "";
		object.pcode.focus();
	}
}

function MemberPasswordModify() {
	var object = document.getElementById("MemberPasswordModifyCheck");
	document.getElementById("MemberPasswordInsert").style.display = object.checked == true ? "" : "none";
	if (object.checked == true) document.forms["MemberSignIn"].password.focus();
}

/*****************************************************************************************
 * Message
 *****************************************************************************************/
function OpenPointGift(mno) {
	if (mno) {
		if (parseInt(mno).toString() == mno) {
			var params = "?mno="+mno;
		} else {
			var params = "?user_id="+mno;
		}
	} else {
		var params = "";
	}

	var width = 400;
	var height = 380;
	var url = ENV.dir+"/module/member/PointGift.php"+params
	var windowLeft = (screen.width-width)/2;
	var windowTop = (screen.height-height)/2;
	windowTop = windowTop>20 ? windowTop-20 : windowTop;
	var opener = window.open(url,"","top="+windowTop+",left="+windowLeft+",width="+width+",height="+height+",resize=0&scrollbars=0");

	if (!opener) {
		alert("팝업이 차단되었습니다.");
	}
}

function OpenMessage(mno) {
	var width = 500;
	var height = 600;
	var url = ENV.dir+"/module/member/SendMessage.php?mno="+mno;
	var windowLeft = (screen.width-width)/2;
	var windowTop = (screen.height-height)/2;
	windowTop = windowTop>20 ? windowTop-20 : windowTop;
	var opener = window.open(url,"","top="+windowTop+",left="+windowLeft+",width="+width+",height="+height+",resize=0&scrollbars=0");

	if (!opener) {
		alert("팝업이 차단되었습니다.");
	}
}

function SendMessage() {
	oEditors.getById["message"].exec("UPDATE_IR_FIELD",[]);

	if (!document.getElementById("message").value || document.getElementById("message").value == '<br>') {
		alert("내용을 입력하세요.");
		return false;
	}

	var InnerFunctionValue = new Array();
	InnerFunctionValue.push("next");
	GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=message&list=next&prev="+document.getElementById("PrevTime").value+"&next="+document.getElementById("NextTime").value+"&mno="+document.getElementById("mno").value+"&message="+GetAjaxParam(document.getElementById("message").value),"GetMessageInner",InnerFunctionValue);

	oEditors.getById["message"].exec("SET_IR",[""]);
	oEditors.getById["message"].exec("FOCUS",[]);
	return false;
}

function GetMessage(list,message) {
	var InnerFunctionValue = new Array();
	InnerFunctionValue.push(list);
	GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=message&list="+list+"&prev="+document.getElementById("PrevTime").value+"&next="+document.getElementById("NextTime").value+"&mno="+document.getElementById("mno").value,"GetMessageInner",InnerFunctionValue);
}

function GetMessageInner(XML,list) {
	if (XML) {
		var root = XML.documentElement;

		var message = new Array();
		for (var i=0, loop=root.childNodes.length;i<loop;i++) {
			if (parseInt(root.childNodes.item(i).getAttribute("time")) < parseInt(document.getElementById("PrevTime").value)) {
				document.getElementById("PrevTime").value = root.childNodes.item(i).getAttribute("time");
			}
			if (parseInt(root.childNodes.item(i).getAttribute("time")) > parseInt(document.getElementById("NextTime").value)) {
				document.getElementById("NextTime").value = root.childNodes.item(i).getAttribute("time");
			}
			var data = {"type":root.childNodes.item(i).getAttribute("type"),"fromPhoto":root.childNodes.item(i).getAttribute("fromPhoto"),"message":root.childNodes.item(i).getAttribute("message"),"reg_date":root.childNodes.item(i).getAttribute("reg_date"),"url":root.childNodes.item(i).getAttribute("url")};
			if (list == "next") {
				message.unshift(data);
			} else {
				message.push(data)
			}
		}

		PrintMessage(list,message);
	}
}

function CheckMessageCount(id,newMsg,allMsg) {
	document.getElementById(id+"New").innerHTML = GetNumberFormat(newMsg);
	document.getElementById(id+"All").innerHTML = GetNumberFormat(allMsg);
}

/*****************************************************************************************
 * LiveSearch
 *****************************************************************************************/
var LiveSearchKeyword = null;
var LiveSearchInterval = null;
var LiveSearchListPoint = null;

function LiveSearchStart(id) {
	if (LiveSearchInterval == null) LiveSearchInterval = setInterval("LiveSearch('"+id+"')",200);
}

function LiveSearchStop(id) {
	setTimeout("LiveSearchStopInner('"+id+"')",100);
}

function LiveSearchStopInner(id) {
	clearInterval(LiveSearchInterval);
	LiveSearchInterval = null;
	LiveSearchKeyword = null;
	LiveSearchListPoint = null;
	document.getElementById(id+"-live-list").innerHTML = "";
	document.getElementById(id+"-live-list").style.display = "none";
}

function LiveSearchListMove(event,id) {
	var input = document.getElementById(id);
	var object = document.getElementById(id+"-live-list");
	var list = object.getElementsByTagName("div");
	var e = event ? event : window.event;
	var keycode = e.which ? e.which : e.keyCode;

	if (list.length == 0) return;

	if (keycode == 38) {
		if (LiveSearchListPoint == null) LiveSearchListPoint = list.length;
		if (LiveSearchListPoint != list.length) {
			list[LiveSearchListPoint].setAttribute("style",object.getAttribute("unselect"));
		}
		LiveSearchListPoint--;
		if (LiveSearchListPoint == -1) LiveSearchListPoint = list.length - 1;
		list[LiveSearchListPoint].setAttribute("style",object.getAttribute("select"));

		LiveSearchKeyword = GetStripTag(list[LiveSearchListPoint].innerHTML);
		input.value = LiveSearchKeyword;
	} else if (keycode == 40) {
		if (LiveSearchListPoint == null) LiveSearchListPoint = -1;
		if (LiveSearchListPoint != -1) {
			list[LiveSearchListPoint].setAttribute("style",object.getAttribute("unselect"));
		}
		LiveSearchListPoint++;
		if (LiveSearchListPoint == list.length) LiveSearchListPoint = 0;
		list[LiveSearchListPoint].setAttribute("style",object.getAttribute("select"));

		LiveSearchKeyword = GetStripTag(list[LiveSearchListPoint].innerHTML);
		input.value = LiveSearchKeyword;
	}
}

function LiveSearch(id) {
	var keyword = document.getElementById(id).value;
	if (keyword) {
		if (LiveSearchKeyword != keyword) {
			LiveSearchKeyword = keyword;

			var InnerFunctionValue = new Array();
			InnerFunctionValue.push(id);
			GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=keyword&keyword="+GetAjaxParam(keyword),"LiveSearchInner",InnerFunctionValue);
		}
	} else {
		var list = document.getElementById(id+"-live-list");
		var arrow = document.getElementById(id+"-live-arrow");

		list.style.display = "none";
		if (arrow && arrow.getAttribute("show") && arrow.getAttribute("hide")) {
			arrow.setAttribute("style",arrow.getAttribute("hide"));
		}
	}
}

function LiveSearchInner(XML,id) {
	if (XML) {
		var root = XML.documentElement;
		var list = document.getElementById(id+"-live-list");
		var arrow = document.getElementById(id+"-live-arrow");
		list.innerHTML = "";

		if (root.childNodes.length > 0) {
			for (var i=0, loop=root.childNodes.length;i<loop;i++) {
				var object = document.createElement("div");
				object.setAttribute("parent",id);
				object.innerHTML = root.childNodes.item(i).getAttribute("viewword");
				object.onclick = function(event) {
					var e = event ? event : window.event;
					var object = e.target ? e.target : e.srcElement;

					document.getElementById(object.getAttribute("parent")).value = GetStripTag(object.innerHTML);
				}

				list.appendChild(object);
			}

			list.style.display = "";
			if (arrow && arrow.getAttribute("show") && arrow.getAttribute("hide")) {
				arrow.setAttribute("style",arrow.getAttribute("show"));
			}
		} else {
			list.style.display = "none";
			if (arrow && arrow.getAttribute("show") && arrow.getAttribute("hide")) {
				arrow.setAttribute("style",arrow.getAttribute("hide"));
			}
		}
	}
}

function GetLiveSearchKeyword(id,type,nums,limit,time,clicker) {
	var InnerFunctionValue = new Array();
	InnerFunctionValue.push(id);
	InnerFunctionValue.push(type);
	InnerFunctionValue.push(nums);
	InnerFunctionValue.push(limit);
	InnerFunctionValue.push(time);
	InnerFunctionValue.push(clicker);
	GetHttpRequestXML(ENV.dir+"/exec/Ajax.get.php","action=liveKeyword&type="+type+"&nums="+nums+"&limit="+limit,"GetLiveSearchKeywordInner",InnerFunctionValue);
}

function GetLiveSearchKeywordInner(XML,id,type,nums,limit,time,clicker) {
	if (XML) {
		var root = XML.documentElement;
		var list = document.getElementById(id);
		list.innerHTML = "";

		if (root.childNodes.length > 0) {
			if (root.childNodes.length > 1) {
				for (var i=1, loop=root.childNodes.length;i<loop;i++) {
					var object = document.createElement("div");
					object.className = list.getAttribute("lineStyle");

					var keyword = document.createElement("div");
					keyword.className = list.getAttribute("keywordStyle");
					keyword.innerHTML = root.childNodes.item(i).getAttribute("viewword");
					keyword.setAttribute("keyword",root.childNodes.item(i).getAttribute("keyword"));
					keyword.onclick = function(event) {
						var e = event ? event : window.event;
						var object = e.target ? e.target : e.srcElement;

						var text = object.getAttribute("keyword");
						if (text) eval(clicker+"(text)");
					}

					var timer = document.createElement("div");
					timer.className = list.getAttribute("timerStyle");
					timer.innerHTML = root.childNodes.item(i).getAttribute("time");

					object.appendChild(keyword);
					object.appendChild(timer);

					list.appendChild(object);
				}
			}

			var object = document.createElement("div");
			object.className = list.getAttribute("timeStyle");
			object.innerHTML = root.childNodes.item(0).getAttribute("time");

			list.appendChild(object);
		}
	}

	setTimeout("GetLiveSearchKeyword('"+id+"','"+type+"',"+nums+","+limit+","+time+",'"+clicker+"')",time);
}

var gFormSubmitWaitingTimeout = null;
function FormSubmitWaiting(mode,msg) {
	var object = document.getElementById("TipLayer");
	
	if (gFormSubmitWaitingTimeout != null) {
		clearTimeout(gFormSubmitWaitingTimeout);
		gFormSubmitWaitingTimeout = null;
	}
	
	if (mode == true) {
		WindowDisabled(true);
		var width = 300;
		var height = 50;
	
		var left = Math.floor((document.documentElement.clientWidth - width)/2);
		var top = Math.floor((document.documentElement.clientHeight - height)/2)+document.body.scrollTop;
		
		object.style.left = left+"px";
		object.style.top = top+"px";
		
		object.style.width = width+"px";
		object.style.height = height+"px";
		object.innerHTML = '<div style="background:url('+ENV.dir+'/images/common/icon_loading.gif) no-repeat 50% 8px; padding:30px; 0px 0px 0px; height:auto; text-align:center;">'+msg+'</div>';
		
		object.style.display = "";
		
		gFormSubmitWaitingTimeout = setTimeout(FormSubmitWaiting,60000);
	} else if (mode == false) {
		WindowDisabled(false);
		object.style.display = "none";
		clearTimeout(gFormSubmitWaitingTimeout);
		gFormSubmitWaitingTimeout = null;
	} else {
		if (object.style.display == "") {
			alert("Submit Error.\nPlease, Try again.");
			FormSubmitWaiting(false);
		}
	}
}

/*****************************************************************************************
 * Modules Commons
 *****************************************************************************************/
function OpenLinkToPopup(object) {
	var link = $(object);
	OpenPopup(link.attr("href"),link.attr("width"),link.attr("height"),true);
	return false;
}

/*****************************************************************************************
 * Designed Input
 *****************************************************************************************/
function InputSelectBox(id) {
	var object = document.getElementById(id);
	object.getElementsByTagName("ul")[0].style.display = "";
	object.getElementsByTagName("ul")[0].style.width = parseInt(document.getElementById(id).style.width.replace("px","")-2)+"px";
	GlobalToggleList[id] = object.getElementsByTagName("ul")[0];
}

function InputSelectBoxSelect(id,text,value,callback) {
	var object = document.getElementById(id);
	object.getElementsByTagName("div")[0].innerHTML = text;
	object.getElementsByTagName("ul")[0].style.display = "none";
	delete GlobalToggleList[id];
	if (typeof callback == "function") {
		callback(text,value);
	}
}

// Event Listener
function addEvent(object,type,fn) {
	if (object.addEventListener) {
		object.addEventListener(type,fn,false);
	} else if (object.attachEvent) {
		object.attachEvent("on"+type,fn);
	}
}

function fireEvent(target,eventName) {
	if(!eventName || !target)return; 

	if (target.fireEvent) taret.fireEvent("on"+eventName);
	else if (window.document.createEvent) {
		var evt = window.document.createEvent("HTMLEvents"); 
		evt.initEvent(eventName,true,true); 
		target.dispatchEvent(evt); 
	}
}

addEvent(document,"click",function(event) {
	var e = event ? event : window.event;
	var object = e.target ? e.target : e.srcElement;

	for (id in GlobalToggleList) {
		if (document.getElementById(id)) {
			if (object.getAttribute("id") != id && object.getAttribute("clicker") != id) {
				GlobalToggleList[id].style.display = "none";
				delete GlobalToggleList[id];
			}
		}
	}
});

addEvent(window,"resize",function(event) {
	var width = document.documentElement.clientWidth < document.documentElement.scrollWidth ? document.documentElement.scrollWidth : document.documentElement.clientWidth;
	var height = document.documentElement.clientHeight < document.documentElement.scrollHeight ? document.documentElement.scrollHeight : document.documentElement.clientHeight;

	if (document.getElementById("WindowDisabledLayer").style.display == "") {
		document.getElementById("WindowDisabledLayer").style.width = width+"px";
		document.getElementById("WindowDisabledLayer").style.height = height+"px";
	}
});