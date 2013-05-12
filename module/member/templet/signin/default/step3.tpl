{$formStart}
<div id="sForm">
	<div class="innerimg"><img src="{$skinDir}/images/title.gif" alt="회원가입" /></div>
	<div class="stepbar">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		{if $is_realname == true}
		<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
		{else}
		<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
		{/if}
		<tr>
			<td class="center"><img src="{$skinDir}/images/step1_off.png" /></td>
			{if $is_realname == true}<td class="center"><img src="{$skinDir}/images/step2_off.png" /></td>{/if}
			<td class="center"><img src="{$skinDir}/images/step3_on.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step4_off.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step5_off.png" /></td>
		</tr>
		</table>
	</div>

	<div class="steptitlebar"><img src="{$skinDir}/images/title_step3.gif" /></div>
	<div class="innerimg"><img src="{$skinDir}/images/info_step3.gif" /></div>

	<div class="height10"></div>

	<div class="checkbox">
		{if $isForm == false}
			{if $isFind == true}
				회원님께서는 <span class="pointText bold">{$reg_date|date_format:"%Y년 %m월 %d일 %H시 %M분"}</span>에 <span class="pointText bold">{$user_id}</span> 아이디로 가입하신 이력이 있습니다.<br />
				해당 아이디로 로그인하시거나, 아이디 또는 비밀번호가 기억나지 않으신다면, 아래의 아이디/비밀번호 찾기 버튼을 클릭하여 주십시오.
			{else}
				회원님께서는 가입하신 이력이 없는 것으로 조회되셨습니다.<br />
				아래의 계속가입 버튼을 클릭하여 가입절차를 계속 진행하여 주십시오.
			{/if}
		{else}
			{if $isJumin == true}
				<table cellpadding="0" cellspacing="10" class="layoutfixed">
				<col width="50%" /><col width="23" /><col width="100" /><col width="20" /><col width="71" /><col width="73" /><col width="10" /><col width="120" /><col width="50%" />
				<tr>
					<td></td>
					<td><img src="{$skinDir}/images/text_realname.gif" /></td>
					<td><input type="text" name="checkname" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
					<td></td>
					<td><img src="{$skinDir}/images/text_jumin.gif" /></td>
					<td><input type="text" name="jumin1" class="inputbox" style="width:63px;" maxlength="6" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 6) document.forms['MemberSignIn'].jumin2.focus();" /></td>
					<td class="center">-</td>
					<td><input type="password" name="jumin2" class="inputbox" style="width:110px;" maxlength="7" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
					<td></td>
				</tr>
				</table>

				<div class="realnameinfo">
				회원님의 실명과 주민등록번호로 가입여부를 확인합니다.<br />
				가입이력이 존재하지 않으면, 다음단계로 진행됩니다. 가입이력이 있을 경우, 가입절차가 중단됩니다.
				</div>
			{else}
				<table cellpadding="0" cellspacing="10" class="layoutfixed">
				<col width="50%" /><col width="23" /><col width="100" /><col width="20" /><col width="35" /><col width="200" /><col width="50%" />
				<tr>
					<td></td>
					<td><img src="{$skinDir}/images/text_realname.gif" /></td>
					<td><input type="text" name="checkname" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
					<td></td>
					<td><img src="{$skinDir}/images/text_email.gif" /></td>
					<td><input type="text" name="email" class="inputbox" style="width:190px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
					<td></td>
				</tr>
				</table>

				<div class="realnameinfo">
				회원님의 실명과 이메일주소로 가입여부를 확인합니다.<br />
				가입이력이 존재하지 않으면, 다음단계로 진행됩니다. 가입이력이 있을 경우, 가입절차가 중단됩니다.
				</div>
			{/if}
		{/if}
	</div>

	<div class="buttonbox">
		{if $isForm == false && $isFind == true}
		<img src="{$skinDir}/images/btn_help.gif" onclick="MemberHelp()" class="pointer" />
		{elseif $isForm == false && $isFind == false}
		<input type="image" src="{$skinDir}/images/btn_continue.gif" />
		{else}
		<input type="image" src="{$skinDir}/images/btn_findid.gif" />
		{/if}
	</div>
</div>
{$formEnd}