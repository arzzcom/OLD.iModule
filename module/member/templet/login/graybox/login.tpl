{$formStart}
<div class="LoginBox">
	<div class="top"></div>
	<div class="bg">
		<div class="LoginTitle"></div>
		<div class="LoginFormArea">
			<input type="text" name="user_id" class="LoginForm" style="background-image:url({$skinDir}/images/login_id.gif);" onfocus="this.style.backgroundImage='';" msg="아이디를 입력하세요." />
			<div style="height:4px; overflow:hidden;"></div>
			<input type="password" name="password" class="LoginForm" style="background-image:url({$skinDir}/images/login_pw.gif);" onfocus="this.style.backgroundImage='';" msg="아이디를 입력하세요." />
		</div>
		<input type="image" src="{$skinDir}/images/login_button.gif" class="LoginButton" />

		<div style="clear:both;"></div>

		<div class="LoginBar"></div>

		<div class="LoginAutoLogin">
			<input type="checkbox" id="autologin" name="autologin" value="1" onclick="MemberLoginAutoLogin(this.id);" msg="공용컴퓨터에서 자동로그인을 설정할 경우, 개인정보가 유출될 수 있습니다.<br />자동로그인을 하도록 설정하시겠습니까?" /><label for="autologin">자동로그인 <span>(PC 보안주의!)</span></label>
		</div>
		<div style="clear:both;"></div>
		<div class="LoginBottom">
			<a href="{$link.signin}">회원가입</a> | <a href="{$link.help}">아이디/패스워드 찾기</a>
		</div>

		<div class="height5"></div>
	</div>
	<div class="bottom"></div>
</div>
{$formEnd}