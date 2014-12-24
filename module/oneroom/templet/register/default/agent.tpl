<script type="text/javascript" src="{$skinDir}/script.js"></script>
<div id="sForm">
{$formStart}

<div class="innerimg"><img src="{$skinDir}/images/title.gif" alt="중개업소/중개담당자등록" /></div>



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

<div class="titlebar"><img src="{$skinDir}/images/title_agent.gif" /></div>
<div class="innerimg"><img src="{$skinDir}/images/text_agent.gif" /></div>

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
	<td class="inputtext">중개업소이름</td>
	<td class="inputline"></td>
	<td class="inputform">
		<input type="text" name="title" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:200px;" />
	</td>
</tr>
<tr class="inputrow">
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
			<td><input type="text" name="register_number1" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; CheckRegisterNumber('agent');" style="width:25px; text-align:center;" maxlength="3" onkeyup="if (this.value.length == 3) document.forms['RegisterForm'].register_number2.focus();" /></td>
			<td class="center">-</td>
			<td><input type="text" name="register_number2" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; CheckRegisterNumber('agent');" style="width:20px; text-align:center;" maxlength="2" onkeyup="if (this.value.length == 2) document.forms['RegisterForm'].register_number3.focus();" /></td>
			<td class="center">-</td>
			<td><input type="text" name="register_number3" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; CheckRegisterNumber('agent');" style="width:45px; text-align:center;" maxlength="6" /></td>
		</tr>
		</table>
		<div id="DuplicationCheck" class="msg">중개업소를 등록하려면 사업자등록번호가 필요합니다.</div>
	</td>
</tr>
<tr class="inputrow">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="inputicon"></td>
	<td class="inputtext">홈페이지주소</td>
	<td class="inputline"></td>
	<td class="inputform">
		<input type="text" name="homepage" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:300px;" />
		<div class="msg">별도의 홈페이지가 있다면, http://를 포함하여 입력하여 주십시오.</div>
	</td>
</tr>
<tr class="inputrow">
	<td colspan="4"></td>
</tr>
<tr class="inputrow">
	<td colspan="4"></td>
</tr>
</table>

<div class="height10"></div>

<div class="buttonbox">
	<input type="image" src="{$skinDir}/images/btn_confirm.gif" />
</div>
{$formEnd}
</div>