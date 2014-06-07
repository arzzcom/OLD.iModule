function MemberSignInCheck(step) {
	var object = $("form[name=MemberSignIn]");

	if (step == 1) {
		if ($(object.find("input[name=agreement]")).length != 0 && $(object.find("input[name=agreement]")).is(":checked") == false) {
			alert("이용약관에 동의하여 주십시오.");
			return false;
		}

		if ($(object.find("input[name=privacy]")).length != 0 && $(object.find("input[name=privacy]")).is(":checked") == false) {
			alert("개인정보 보호정책에 동의하여 주십시오.");
			return false;
		}

		if ($(object.find("input[name=youngpolicy]")).length != 0 && $(object.find("input[name=youngpolicy]")).is(":checked") == false) {
			alert("청소년 보호정책에 동의하여 주십시오.");
			return false;
		}
	}
	/*
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
	*/
	if (step == 3) {
		if (object.data("isNext") == true) return true;
		
		if ($(object.find("input[name=checkname]")).length != 0) {
			if (!$(object.find("input[name=checkname]")).val()) {
				alert("실명을 입력하여 주십시오.");
				$(object.find("input[name=checkname]")).focus();
				return false;
			}

			if ($(object.find("input[name=companyno1]")).length != 0) {
				if ($(object.find("input[name=companyno1]")).val().length != 3 || $(object.find("input[name=companyno2]")).val().length != 2 || $(object.find("input[name=companyno3]")).val().length != 5) {
					alert("사업자등록번호 정확하게 입력하여 주십시오.");
					$(object.find("input[name=companyno1]")).focus();
					return false;
				} else {
					var QueryString = "companyno="+$(object.find("input[name=companyno1]")).val()+"-"+$(object.find("input[name=companyno2]")).val()+"-"+$(object.find("input[name=companyno3]")).val();
				}
			} else {
				if (!$(object.find("input[name=email]")).val()) {
					alert("이메일 주소를 정확하게 입력하여 주십시오.");
					$(object.find("input[name=email]")).focus();
					return false;
				} else {
					var QueryString = "email="+$(object.find("input[name=email]")).val();
				}
			}
			
			$.ajax({
				type:"POST",
				url:ENV.dir+"/exec/Ajax.get.php?action=membercheck",
				data:QueryString,
				dataType:"json",
				success:function(result) {
					if (result.success == true) {
						alert("회원님께서는 가입하신 이력이 없습니다.\n가입절차를 계속 진행합니다.");
						object.data("isNext",true);
						object.submit();
					} else if (result.find == true) {
						alert("회원님께서는 "+result.reg_date+"에 "+result.user_id+" 아이디로 가입하신 이력이 있습니다.\n해당 아이디로 로그인하시거나, 아이디 또는 비밀번호가 기억나지 않으신다면, 아래의 아이디/비밀번호 찾기를 이용하여 주십시오.");
					} else {
						alert("입력된 정보가 잘못되었습니다.");
					}
				},
				error:function() {
					alert("서버와 통신중에 에러가 발생하였습니다.\\n잠시후에 다시 시도하여 주십시오.");
				}
			});

			return false;
		}
	}

	return true;
}

function MemberSignInFormCheck(field) {
	var object = $("form[name=MemberSignIn]");

	if (field != "password") {
		if (field == "user_id") {
			if ($(object.find("input[name=user_id]")).val().length == 0) {
				var helpBlock = FindHelpBlock($(object.find("input[name=user_id]")));
				$(helpBlock.find(".help-block")).html("아이디를 입력하세요.");
				helpBlock.addClass("has-error");
				return false;
			}
			var QueryString = "check=user_id&value="+$(object.find("input[name=user_id]")).val();
		}

		if (field == "email") {
			if ($(object.find("input[name=email]")).val().length == 0) {
				var helpBlock = FindHelpBlock($(object.find("input[name=email]")));
				$(helpBlock.find(".help-block")).html("이메일주소를 입력하세요.");
				helpBlock.addClass("has-error");
				return false;
			}
			var QueryString = "check=email&value="+$(object.find("input[name=email]")).val();
		}

		if (field == "nickname") {
			if ($(object.find("input[name=nickname]")).val().length == 0) {
				var helpBlock = FindHelpBlock($(object.find("input[name=nickname]")));
				$(helpBlock.find(".help-block")).html("닉네임을 입력하세요.");
				helpBlock.addClass("has-error");
				return false;
			}
			var QueryString = "check=nickname&value="+$(object.find("input[name=nickname]")).val();
		}

		if (field == "voter") {
			if ($(object.find("input[name=voter]")).val().length) {
				var QueryString = "check=voter&value="+$(object.find("input[name=voter]")).val();
			} else {
				return false;
			}
		}
		
		$.ajax({
			type:"POST",
			url:ENV.dir+"/exec/Ajax.get.php?action=duplication",
			data:QueryString,
			dataType:"json",
			success:function(result) {
				var object = $("form[name=MemberSignIn]");
				var helpBlock = FindHelpBlock($(object.find("input[name="+result.check+"]")));
				if (result.success == true) {
					helpBlock.removeClass("has-error").addClass("has-success");
					$(helpBlock.find(".help-block")).html(result.message);
				} else {
					helpBlock.removeClass("has-success").addClass("has-error");
					$(helpBlock.find(".help-block")).html(result.message);
				}
			},
			error:function() {
				alert("서버와 통신중에 에러가 발생하였습니다.\\n잠시후에 다시 시도하여 주십시오.");
			}
		});
	} else {
		if ($(object.find("input[name=password1]")).val().length > 0 && $(object.find("input[name=password2]")).val().length > 0) {
			if ($(object.find("input[name=password1]")).val() != $(object.find("input[name=password2]")).val()) {
				var helpBlock = FindHelpBlock($(object.find("input[name=password2]")));
				$(helpBlock.find(".help-block")).html("패스워드가 서로 일치하지 않습니다.");
				helpBlock.addClass("has-error");
				return false;
			} else {
				var helpBlock = FindHelpBlock($(object.find("input[name=password2]")));
				$(helpBlock.find(".help-block")).html("패스워드가 확인되었습니다.");
				helpBlock.removeClass("has-error").addClass("has-success");
			}
		}
	}
}

function MemberPasswordModify() {
	if ($("#MemberPasswordModifyCheck").is(":checked") == true) {
		$("#MemberPasswordInsert").show();
		$($("#MemberPasswordInsert").find("input[name=password]")).focus();
	} else {
		$("#MemberPasswordInsert").hide();
	}
}

function MemberCellPhoneCheck() {
	var object = $("form[name=MemberSignIn]");

	if ($(object.find("input[name=cellphone1]")).length != 0) {
		if ($(object.find("input[name=provider]")).length != 0 && $(object.find("input[name=provider]")).val().length == 0) {
			alert("통신사를 선택하여 주십시오.");
			return false;
		}
		if ($(object.find("input[name=cellphone1]")).val().length == 0 || $(object.find("input[name=cellphone2]")).val().length == 0 || $(object.find("input[name=cellphone3]")).val().length == 0) {
			alert("휴대전화번호를 정확하게 입력하여 주십시오.");
		} else {
			var phone = $(object.find("input[name=cellphone1]")).val()+"-"+$(object.find("input[name=cellphone2]")).val()+"-"+$(object.find("input[name=cellphone3]")).val();
			
			$.ajax({
				type:"POST",
				url:ENV.dir+"/exec/Ajax.get.php?action=phonecheck",
				data:"phone="+phone,
				dataType:"json",
				success:function(result) {
					var object = $("form[name=MemberSignIn]");
					var helpBlock = FindHelpBlock($(object.find("input[name=cellphone1]")));
					$(helpBlock.find(".help-block")).html(result.message);
					if (result.success == true) {
						helpBlock.removeClass("has-error");
						$("#MemberCellPhoneCheckInsert").show();
					} else {
						helpBlock.addClass("has-error");
					}
				},
				error:function() {
					alert("서버와 통신중에 에러가 발생하였습니다.\\n잠시후에 다시 시도하여 주십시오.");
				}
			});
		}
	}
}

function MemberSearchAddressDepth1(field,code) {
	$("div.drop[field="+field+"_juso_depth2] > button").html("로딩중...");
	$("div.drop[field="+field+"_juso_depth2] > button").attr("disabled",true);
	$("div.drop[field="+field+"_juso_depth3] > button").html('읍면동 <span class="arrow"></span>');
	$("div.drop[field="+field+"_juso_depth3] > button").attr("disabled",true);
	$("div.drop[field="+field+"_juso_depth4] > button").html('도로명 <span class="arrow"></span>');
	$("div.drop[field="+field+"_juso_depth4] > button").attr("disabled",true);
	
	$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth2]")).val("");
	$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth3]")).val("");
	$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth4]")).val("");
	
	$.getJSON("http://api.imodule.kr/juso.php?callback=?",{
		action:"depth1",
		depth1:code
	}).done(function(data) {
		try {
			var object = $("div.drop[field="+field+"_juso_depth2] > ul");
			object.html("");
			for (var i=0, loop=data.length;i<loop;i++) {
				object.append($("<li>").attr("value",data[i]).html(data[i]).on("click",function() {
					if ($(this).parent().parent().attr("form")) {
						$($("form[name="+$(this).parent().parent().attr("form")+"]").find("input[name="+$(this).parent().parent().attr("field")+"]")).val($(this).attr("value"));
					}
					
					if ($(this).parent().parent().attr("callback")) {
						eval($(this).parent().parent().attr("callback").replace('?',$(this).attr("value")));
					}
					
					$($(this).parent().parent().find("button")).html($(this).html()+' <div class="arrow"></div>');
				}));
			}
			
			$("div.drop[field="+field+"_juso_depth2] > button").html('시군구 <span class="arrow"></span>');
			if (loop > 0) {
				$("div.drop[field="+field+"_juso_depth2] > button").attr("disabled",false);
				$("input[name="+field+"_juso_keyword]").attr("disabled",false);
			}
		} catch (e) {}
	});
}

function MemberSearchAddressDepth2(field,code) {
	$("div.drop[field="+field+"_juso_depth3] > button").html("로딩중...");
	$("div.drop[field="+field+"_juso_depth3] > button").attr("disabled",true);
	$("div.drop[field="+field+"_juso_depth4] > button").html('도로명 <span class="arrow"></span>');
	$("div.drop[field="+field+"_juso_depth4] > button").attr("disabled",true);
	
	$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth3]")).val("");
	$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth4]")).val("");

	$.getJSON("http://api.imodule.kr/juso.php?callback=?",{
		action:"depth2",
		depth1:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth1]")).val(),
		depth2:code
	}).done(function(data) {
		try {
			var object = $("div.drop[field="+field+"_juso_depth3] > ul");
			object.html("");
			for (var i=0, loop=data.length;i<loop;i++) {
				object.append($("<li>").attr("value",data[i]).html(data[i]).on("click",function() {
					if ($(this).parent().parent().attr("form")) {
						$($("form[name="+$(this).parent().parent().attr("form")+"]").find("input[name="+$(this).parent().parent().attr("field")+"]")).val($(this).attr("value"));
					}
					
					if ($(this).parent().parent().attr("callback")) {
						eval($(this).parent().parent().attr("callback").replace('?',$(this).attr("value")));
					}
					
					$($(this).parent().parent().find("button")).html($(this).html()+' <div class="arrow"></div>');
				}));
			}
			
			$("div.drop[field="+field+"_juso_depth3] > button").html('읍면동 <span class="arrow"></span>');
			if (loop > 0) $("div.drop[field="+field+"_juso_depth3] > button").attr("disabled",false);
		} catch (e) {}
	});
}

function MemberSearchAddressDepth3(field,code) {
	$("div.drop[field="+field+"_juso_depth4] > button").html("로딩중...");
	$("div.drop[field="+field+"_juso_depth4] > button").attr("disabled",true);
	
	$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth4]")).val("");

	$.getJSON("http://api.imodule.kr/juso.php?callback=?",{
		action:"depth3",
		depth1:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth1]")).val(),
		depth2:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth2]")).val(),
		depth3:code
	}).done(function(data) {
		try {
			var object = $("div.drop[field="+field+"_juso_depth4] > ul");
			object.html("");
			for (var i=0, loop=data.length;i<loop;i++) {
				object.append($("<li>").attr("value",data[i]).html(data[i]).on("click",function() {
					if ($(this).parent().parent().attr("form")) {
						$($("form[name="+$(this).parent().parent().attr("form")+"]").find("input[name="+$(this).parent().parent().attr("field")+"]")).val($(this).attr("value"));
					}
					
					if ($(this).parent().parent().attr("callback")) {
						eval($(this).parent().parent().attr("callback").replace('?',$(this).attr("value")));
					}
					
					$($(this).parent().parent().find("button")).html($(this).html()+' <div class="arrow"></div>');
				}));
			}
			
			$("div.drop[field="+field+"_juso_depth4] > button").html('도로명 <span class="arrow"></span>');
			if (loop > 0) $("div.drop[field="+field+"_juso_depth4] > button").attr("disabled",false);
		} catch (e) {}
	});
}

function MemberSearchAddressDepth4(field,code) {
	$("input[name="+field+"_juso_keyword]").focus();
}

function MemberSearchAddressSearch(field) {
	if ($($("form[name=MemberSignIn]").find("input[name="+field+"_juso_keyword]")).val().length == 0) {
		alert("번지나 건물번호 또는 건물명을 입력하신 후 검색하여 주시기 바랍니다.");
		return false;
	}
	$("div.drop[field="+field+"_address1] > button").html("검색중...");
	$("div.drop[field="+field+"_address1] > button").attr("disabled",true);
	
	$.getJSON("http://api.imodule.kr/juso.php?callback=?",{
		action:"search",
		depth1:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth1]")).val(),
		depth2:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth2]")).val(),
		depth3:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth3]")).val(),
		depth4:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_depth4]")).val(),
		keyword:$($("form[name=MemberSignIn]").find("input[name="+field+"_juso_keyword]")).val()
	}).done(function(data) {
		try {
			var object = $("div.drop[field="+field+"_address1] > ul");
			object.html("");
			for (var i=0, loop=data.length;i<loop;i++) {
				var viewAddress = data[i].address+' <span class="gray">('+data[i].old+')</span>';
				object.append($("<li>").data("address",data[i]).html(viewAddress).on("click",function() {
					if ($(this).parent().parent().attr("form")) {
						$($("form[name="+$(this).parent().parent().attr("form")+"]").find("input[name="+$(this).parent().parent().attr("field")+"]")).val($(this).data("address").address);
						$($("form[name="+$(this).parent().parent().attr("form")+"]").find("input[name="+$(this).parent().parent().attr("field").replace("_address1","_zipcode")+"]")).val($(this).data("address").zipcode);
					}
					
					$($(this).parent().parent().find("button")).html($(this).html()+' <div class="arrow"></div>');
					$("input[name="+field+"_address2]").val("");
					$("input[name="+field+"_address2]").focus();
				}));
			}
			
			if (loop > 0) {
				$("div.drop[field="+field+"_address1] > button").html('검색이 완료되었습니다. 주소를 선택하여 주십시오. <span class="arrow"></span>');
				$("div.drop[field="+field+"_address1] > button").attr("disabled",false);
			} else {
				$("div.drop[field="+field+"_address1] > button").html('해당 검색어로 주소를 찾을 수 없습니다. 다른 검색어를 이용해보시기 바랍니다. <span class="arrow"></span>');
			}
		} catch (e) {}
	});
}