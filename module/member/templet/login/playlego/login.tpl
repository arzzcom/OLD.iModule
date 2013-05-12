{$formStart}
<div class="LoginBox">
	<div class="LoginFormArea">
		<input type="text" name="user_id" class="LoginForm" style="background-image:url({$skinDir}/images/loginid.gif);" onfocus="this.style.backgroundImage='';" msg="아이디를 입력하세요." />
		<div style="height:4px; overflow:hidden;"></div>
		<input type="password" name="password" class="LoginForm" style="background-image:url({$skinDir}/images/loginpw.gif);" onfocus="this.style.backgroundImage='';" msg="아이디를 입력하세요." />
	</div>

	<input type="image" src="{$skinDir}/images/loginbtn.gif" class="LoginButton" />

	<div style="clear:both;"></div>
	<div class="LoginBar"></div>

	<div class="dotum f11">
		<input type="checkbox" id="autologin" name="autologin" value="1" onclick="MemberLoginAutoLogin(this.id);" class="LoginAuto" msg="공용컴퓨터에서 자동로그인을 설정할 경우, 개인정보가 유출될 수 있습니다.<br />자동로그인을 하도록 설정하시겠습니까?" /><label for="autologin" class="LoginAutoText">자동로그인 <span style="color:#E65101;">(PC 보안주의!)</span></label>
	</div>
	<div style="clear:both;"></div>
	<div class="LoginBottom">
		<a href="{$link.signin}"><span class="selectedtext">회원가입</span></a> | <span class="pointedtext" style="cursor:pointer;" onclick="OpenPopup('/find.php',400,154);">아이디/비밀번호 찾기</span>
	</div>
</div>
{$formEnd}