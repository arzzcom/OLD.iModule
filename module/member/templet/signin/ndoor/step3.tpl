{$formStart}
<div id="SigninNdoor">
	<div style="padding:25px 0px 50px 0px;"><img src="{$skinDir}/images/title_step3.gif" /></div>

	<div style="padding:0px 120px 0px 120px;">
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
					<col width="50%" /><col width="30" /><col width="100" /><col width="20" /><col width="71" /><col width="73" /><col width="10" /><col width="120" /><col width="50%" />
					<tr>
						<td></td>
						<td class="bold">실명</td>
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
				{elseif $isCompanyNo == true}
					<table cellpadding="0" cellspacing="10" class="layoutfixed">
					<col width="50%" /><col width="45" /><col width="100" /><col width="20" /><col width="100" /><col width="45" /><col width="10" /><col width="40" /><col width="10" /><col width="70" /><col width="50%" />
					<tr>
						<td></td>
						<td class="bold">회사명</td>
						<td><input type="text" name="checkname" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
						<td></td>
						<td class="bold">사업자등록번호</td>
						<td><input type="text" name="companyno1" class="inputbox" style="width:35px;" maxlength="3" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 3) document.forms['MemberSignIn'].companyno2.focus();" /></td>
						<td class="center">-</td>
						<td><input type="text" name="companyno2" class="inputbox" style="width:30px;" maxlength="2" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 2) document.forms['MemberSignIn'].companyno3.focus();" /></td>
						<td class="center">-</td>
						<td><input type="text" name="companyno3" class="inputbox" style="width:60px;" maxlength="5" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
						<td></td>
					</tr>
					</table>
	
					<div class="realnameinfo">
					회사명과 사업자등록번호로 가입여부를 확인합니다.<br />
					가입이력이 존재하지 않으면, 다음단계로 진행됩니다. 가입이력이 있을 경우, 가입절차가 중단됩니다.
					</div>
				{else}
					<table cellpadding="0" cellspacing="10" class="layoutfixed">
					<col width="50%" /><col width="30" /><col width="100" /><col width="20" /><col width="85" /><col width="200" /><col width="50%" />
					<tr>
						<td></td>
						<td class="bold">실명</td>
						<td><input type="text" name="checkname" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
						<td></td>
						<td class="bold">이메일주소</td>
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
	</div>
	
	<div class="height20"></div>
	
	<div class="innerimg center">
		{if $isForm == false && $isFind == true}
		<img src="{$skinDir}/images/btn_submit.gif" onclick="MemberHelp()" class="pointer" />
		{elseif $isForm == false && $isFind == false}
		<input type="image" src="{$skinDir}/images/btn_submit.gif" />
		{else}
		<input type="image" src="{$skinDir}/images/btn_submit.gif" />
		{/if}
	</div>
</div>
{$formEnd}