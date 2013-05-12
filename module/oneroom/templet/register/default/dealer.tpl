<script type="text/javascript" src="{$skinDir}/script.js"></script>
<div id="sForm">
{$formStart}

<div class="innerimg"><img src="{$skinDir}/images/title.gif" alt="회원가입" /></div>

간단하게 검은색 배경으로 중개담당자에 대한 설명을 적어주자.

<div class="titlebar"><img src="{$skinDir}/images/title_agreement.gif" /></div>

<div class="height5"></div>

<div class="box">
{$agreement}
</div>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="20" /><col width="100%" />
<tr>
	<td><input id="agree" type="checkbox" name="agree" value="true" /></td>
	<td><label for="agree">위의 약관에 동의합니다.</label></td>
</tr>
</table>

<div class="height10"></div>

<div class="titlebar"><img src="{$skinDir}/images/title_dealer.gif" /></div>
<div class="innerimg"><img src="{$skinDir}/images/text_dealer.gif" /></div>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100%" /><col width="145" />
<tr>
	<td class="right"><img src="{$skinDir}/images/icon_essential.gif" /></td>
	<td class="right dotum f11">항목은 필수입력항목입니다.</td>
</tr>
</table>
<div class="height5"></div>
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="20" /><col width="120" /><col width="1" /><col width="100%" />
<tr class="boldline">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="inputicon"><img src="{$skinDir}/images/icon_essential.gif" /></td>
	<td class="inputtext">사업자등록번호</td>
	<td class="inputline"></td>
	<td class="inputform">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="35" /><col width="15" /><col width="30" /><col width="15" /><col width="100%" />
		<tr>
			<td><input type="text" name="register_number1" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; CheckRegisterNumber('dealer');" style="width:25px; text-align:center;" maxlength="3" onkeyup="if (this.value.length == 3) document.forms['RegisterForm'].register_number2.focus();" /></td>
			<td class="center">-</td>
			<td><input type="text" name="register_number2" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; CheckRegisterNumber('dealer');" style="width:20px; text-align:center;" maxlength="2" onkeyup="if (this.value.length == 2) document.forms['RegisterForm'].register_number3.focus();" /></td>
			<td class="center">-</td>
			<td><input type="text" name="register_number3" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; CheckRegisterNumber('dealer');" style="width:45px; text-align:center;" maxlength="6" /></td>
		</tr>
		</table>
		<div id="DuplicationCheck" class="msg">중개담당자로 속하게 될 중개업소의 사업자등록번호를 입력하여 주십시오.</div>
	</td>
</tr>
<tr class="boldline">
	<td colspan="4"></td>
</tr>
</table>

<div class="height10"></div>

<div class="titlebar"><img src="{$skinDir}/images/title_dealer_info.gif" /></div>
<div class="innerimg"><img src="{$skinDir}/images/text_dealer_info.gif" /></div>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="20" /><col width="120" /><col width="1" /><col width="100%" />
<tr class="boldline">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="inputicon"><img src="{$skinDir}/images/icon_essential.gif" /></td>
	<td class="inputtext">실명</td>
	<td class="inputline"></td>
	<td class="inputform">
		<input type="text" name="name" value="{$member.name}" class="inputbox" style="width:100px;" readonly="readonly" />
	</td>
</tr>
<tr class="inputrow">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="inputicon"><img src="{$skinDir}/images/icon_essential.gif" /></td>
	<td class="inputtext">주민등록번호</td>
	<td class="inputline"></td>
	<td class="inputform">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="60" /><col width="10" /><col width="100%" />
		<tr>
			<td><input type="text" name="jumin1" class="inputbox" style="width:50px;" value="{$member.jumin|substr:0:6}" readonly="readonly" /></td>
			<td class="dotum f11 center">-</td>
			<td><input type="password" name="jumin2" class="inputbox" style="width:85px;" value="{$member.jumin|substr:7:7}" readonly="readonly" /></td>
		</tr>
		</table>
		<div id="DuplicationCheck_jumin" class="msg">주민등록번호를 입력하여 주십시오.</div>
	</td>
</tr>
<tr class="boldline">
	<td colspan="4"></td>
</tr>
</table>

<div class="height10"></div>

<div class="buttonbox">
	<input type="image" src="{$skinDir}/images/btn_confirm.gif" />
</div>
{$formEnd}
</div>