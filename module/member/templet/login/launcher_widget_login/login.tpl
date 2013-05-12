{$formStart}

<table id="Table_01" width="717" height="408" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="7">
			<img src="images/login_01.jpg" width="717" height="47" alt=""></td>
	</tr>
	<tr>
		<td colspan="5" style="vertical-align:top; padding:27px 0px 0px 20px;">
			<div style="padding:0px 0px 0px 227px; position:absolute;"><input  TYPE="IMAGE" src="images/login_button.jpg" name="Submit" value="Submit"  align="absmiddle" style="position:absolute;"></div>
			<div style="padding:0px 0px 5px 0px;"><input name="user_id" class="id" style="padding:8px 0px 0px 15px; width:200px; height:27px; vertical-align:middle; border:1px solid #c6c9cc; font-size:13pt;" onfocus="this.className=''" onblur="this.className=(this.value.length==0)?'id':''"></div>
			<div style="padding:0px 0px 0px 0px" float:left;><input type="password" class="pw" name="password" style="padding:6px 0px 0px 15px; width:200px; height:25px; border:1px solid #c6c9cc; font-size:13pt;" onfocus="this.className=''" onblur="this.className=(this.value.length==0)?'pw':''"></div>
			<div style="padding:5px 0px 0px 0px; height:15px; font-size:9pt; font-family:dotum; vertical-align:middle;"><input type="checkbox" class="checkbox" id="autologin" name="autologin" value="1" onclick="MemberLoginAutoLogin(this.id);" msg="공용컴퓨터에서 자동로그인을 설정할 경우, 개인정보가 유출될 수 있습니다.<br />자동로그인을 하도록 설정하시겠습니까?" style="vertical-align:middle;" /> <label for="autologin" onclick="MemberLoginAutoLogin('autologin');">로그인 상태유지</label>&nbsp;&nbsp;&nbsp;IP보안 <img src="images/login_ipsecurity.jpg"></div>
		</td>
		<td>
			<img src="images/login_03.jpg" width="9" height="290" alt=""></td>
		<td rowspan="2">
			<img src="images/login_04.jpg" width="369" height="321" alt=""></td>
	</tr>
	<tr>
		<td>
			<img src="images/login_05.jpg" width="1" height="31" alt=""></td>
		<td><a href="http://www.ndoor.co.kr{$link.help}"><img src="images/login_06.jpg" width="153" height="31" alt=""></a></td>
		<td>
			<img src="images/login_07.jpg" width="60" height="31" alt=""></td>
		<td width="59px" height="31px"></td>
		<td><a href="http://www.ndoor.co.kr{$link.signin}"><img src="images/login_08.jpg" alt="" style="padding:0px 20px 0px 0px;"></a></td>
		<td>
			<img src="images/login_10.jpg" width="9" height="31" alt=""></td>
	</tr>
	<tr>
		<td colspan="7">
			<img src="images/login_11.jpg" width="717" height="40" alt=""></td>
	</tr>
</table>

{$formEnd}


