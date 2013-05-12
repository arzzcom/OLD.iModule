<script type="text/javascript" src="{$skinDir}/script.js"></script>
{$formStart}
<div id="sForm">
	<div class="steptitlebar"><img src="{$skinDir}/images/title_leave.gif" /></div>
	<div class="innerimg"><img src="{$skinDir}/images/info_leave.gif" /></div>

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
		<td class="inputtext">회원아이디</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="user_id" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:100px;" value="{$member.user_id}" disabled="disabled" />
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	<tr>
		<td class="inputicon"><img src="{$skinDir}/images/icon_essential.gif" /></td>
		<td class="inputtext">패스워드</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="password" name="password" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:100px;" />
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{if $member.jumin}
	<tr>
		<td class="inputicon"><img src="{$skinDir}/images/icon_essential.gif" /></td>
		<td class="inputtext">주민등록번호</td>
		<td class="inputline"></td>
		<td class="inputform">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="60" /><col width="10" /><col width="100%" />
			<tr>
				<td><input type="text" name="jumin1" class="inputbox" onfocus="this.className='inputboxon';" maxlength="6" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 6) document.forms['MemberSignIn'].jumin2.focus();" style="width:50px;" /></td>
				<td class="dotum f11 center">-</td>
				<td><input type="password" name="jumin2" class="inputbox" onfocus="this.className='inputboxon';" maxlength="7" onblur="this.className='inputbox';" style="width:85px;" /></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	<tr>
		<td class="inputicon"></td>
		<td class="inputtext">탈퇴사유</td>
		<td class="inputline"></td>
		<td class="inputform">
			<textarea name="msg" class="textbox" onfocus="this.className='textboxon';" onblur="this.className='textbox';"></textarea>
			<div class="msg">사이트의 발전을 위해 탈퇴사유를 간단하게나마 입력하여 주십시오.</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	</table>

	<div class="buttonbox">
		<input type="image" src="{$skinDir}/images/btn_confirm.gif" />
	</div>
</div>
{$formEnd}