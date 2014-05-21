<div id="sForm">
	<div class="innerimg"><img src="{$skinDir}/images/title.gif" alt="아이디/패스워드 찾기" /></div>

	<div class="stepbar">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
		<tr>
			<td class="center"><img src="{$skinDir}/images/step1.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step2.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step3.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step4.png" /></td>
		</tr>
		</table>
	</div>

	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="50%" /><col width="10" /><col width="50%" />
	<tr>
		<td class="vTop">
			<form name="idForm" onsubmit="return false;">
			<div class="steptitlebar"><img src="{$skinDir}/images/title_user_id.gif" /></div>
			<div class="innerimg"><img src="{$skinDir}/images/info_user_id.gif" /></div>

			<div class="height10"></div>

			<div class="box">
				<table cellpadding="0" cellspacing="10" class="layoutfixed">
				<col width="80" /><col width="73" /><col width="10" /><col width="100%" />
				<tr>
					<td><img src="{$skinDir}/images/text_realname.gif" /></td>
					<td colspan="3"><input type="text" name="name" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				{if $isJumin == true || false}
				<tr>
					<td><img src="{$skinDir}/images/text_jumin.gif" /></td>
					<td><input type="text" name="jumin1" class="inputbox" style="width:63px;" maxlength="6" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 6) document.forms['MemberSignIn'].jumin2.focus();" /></td>
					<td class="center">-</td>
					<td><input type="password" name="jumin2" class="inputbox" style="width:110px;" maxlength="7" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				{else}
				<tr>
					<td><img src="{$skinDir}/images/text_email.gif" /></td>
					<td colspan="3"><input type="text" name="email" class="inputbox" style="width:200px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				{/if}
				</table>

				<div class="height10"></div>

				<div id="FindUserIDInfo" class="infobox" style="height:110px;">
					회원님의 실명과 회원가입당시 입력했던 정보를 입력하신 뒤 아래의 확인 버튼을 누르시면, 회원님의 아이디를 확인하실 수 있습니다.
				</div>

				<div class="height10"></div>

				<div class="center innerimg">
					<img src="{$skinDir}/images/btn_confirm.gif" alt="확인" class="pointer" onclick="FindUserID()" />
					<img id="FindPasswordButton" src="{$skinDir}/images/btn_next.gif" alt="패스워드찾기" class="pointer" onclick="SetFindPassword()" style="display:none; margin-left:3px;" />
				</div>
			</div>
			</form>
		</td>
		<td></td>
		<td class="vTop">
			<form name="passwordForm" onsubmit="return false;">
			<div class="steptitlebar"><img src="{$skinDir}/images/title_password.gif" /></div>
			<div class="innerimg"><img src="{$skinDir}/images/info_password.gif" /></div>

			<div class="height10"></div>

			<div class="box">
				<table id="FindPasswordStep1" cellpadding="0" cellspacing="10" class="layoutfixed">
				<col width="80" /><col width="73" /><col width="10" /><col width="100%" />
				<tr>
					<td><img src="{$skinDir}/images/text_user_id.gif" /></td>
					<td colspan="3"><input type="text" name="user_id" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_realname.gif" /></td>
					<td colspan="3"><input type="text" name="name" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>

				{if $isJumin == true || false}
				<tr>
					<td><img src="{$skinDir}/images/text_jumin.gif" /></td>
					<td><input type="text" name="jumin1" class="inputbox" style="width:63px;" maxlength="6" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 6) document.forms['MemberSignIn'].jumin2.focus();" /></td>
					<td class="center">-</td>
					<td><input type="password" name="jumin2" class="inputbox" style="width:110px;" maxlength="7" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				{else}
				<tr>
					<td><img src="{$skinDir}/images/text_email.gif" /></td>
					<td colspan="3"><input type="text" name="email" class="inputbox" style="width:200px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				{/if}
				</table>

				<table id="FindPasswordStep2" cellpadding="0" cellspacing="10" class="layoutfixed" style="display:none;">
				<col width="40" /><col width="100%" />
				<tr>
					<td colspan="2">
						<div id="FindPasswordQuestion" class="questionbox" style="height:40px;"></div>
					</td>
				</tr>
				<tr>
					<td><img src="{$skinDir}/images/text_answer.gif" /></td>
					<td><input type="text" name="answer" class="inputbox" style="width:250px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
				</tr>
				</table>

				<div class="height10"></div>

				<div id="FindPasswordInfo" class="infobox" style="height:76px;">
					회원님의 아이디와 실명, 그리고 회원가입당시 입력했던 정보를 입력하신 뒤 아래의 확인 버튼을 누르시면, 회원님의 패스워드를 변경하실 수 있습니다.
				</div>

				<div class="height10"></div>

				<div class="center innerimg">
					<img id="FindPasswordNextButton" src="{$skinDir}/images/btn_confirm.gif" alt="확인" class="pointer" onclick="FindPassword()" />
					<img id="SendPasswordButton" src="{$skinDir}/images/btn_sendpassword.gif" alt="패스워드발급" class="pointer" onclick="SendFindPassword()" style="display:none; margin-left:3px;" />
				</div>
			</div>
		</td>
	</tr>
	</table>
</div>