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
			{if $is_realname == true}<td class="center"><img src="{$skinDir}/images/step2_on.png" /></td>{/if}
			<td class="center"><img src="{$skinDir}/images/step3_off.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step4_off.png" /></td>
			<td class="center"><img src="{$skinDir}/images/step5_off.png" /></td>
		</tr>
		</table>
	</div>

	<div class="steptitlebar"><img src="{$skinDir}/images/title_step2.gif" /></div>
	<div class="innerimg"><img src="{$skinDir}/images/info_step2.gif" /></div>

	<div class="height10"></div>

	<div class="realnamebox">
		<table cellpadding="0" cellspacing="10" class="layoutfixed">
		<col width="50%" /><col width="23" /><col width="100" /><col width="20" /><col width="71" /><col width="73" /><col width="10" /><col width="120" /><col width="50%" />
		<tr>
			<td></td>
			<td><img src="{$skinDir}/images/text_realname.gif" /></td>
			<td><input type="text" name="realname" class="inputbox" style="width:90px;" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
			<td></td>
			<td><img src="{$skinDir}/images/text_jumin.gif" /></td>
			<td><input type="text" name="jumin1" class="inputbox" style="width:63px;" maxlength="6" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 6) document.forms['MemberSignIn'].jumin2.focus();" /></td>
			<td class="center">-</td>
			<td><input type="password" name="jumin2" class="inputbox" style="width:110px;" maxlength="7" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" /></td>
			<td></td>
		</tr>
		</table>

		<div class="realnameinfo">
		개정 "주민등록법"에 의해 타인의 주민등록번호를 부정사용하는 자는 3년 이하의 징역 또는 1천만원 이하의 벌금이 부과될 수 있습니다. <span class="pointText">관련법률: 주민등록법 제37조(벌칙) 제10호(시행일 : 2009.04.01)</span><br />
		만약, 타인의 주민번호를 도용하여 온라인 회원 가입을 하신 이용자분들은 지금 즉시 명의 도용을 중단하시길 바랍니다.
		</div>
	</div>

	<div class="buttonbox">
		<input type="image" src="{$skinDir}/images/btn_realname.gif" />
	</div>
</div>
{$formEnd}