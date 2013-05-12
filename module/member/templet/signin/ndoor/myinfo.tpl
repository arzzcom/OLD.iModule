<script type="text/javascript" src="{$skinDir}/script.js"></script>
{$formStart}
<div id="sForm">
	<div class="steptitlebar"><img src="{$skinDir}/images/title_modify.gif" /></div>
	<div class="innerimg"><img src="{$skinDir}/images/info_step4.gif" /></div>

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
	{foreach name=form from=$form item=data}
	{if $data.type == 'user_id'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:100px;" value="{$member.user_id}" disabled="disabled" />
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'name'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" value="{$member.name}" style="width:100px;" value="{$member.name}"{if $member.jumin} disabled="disabled"{/if} />
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'nickname'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; MemberDuplicationCheck('nickname');" style="width:100px;" value="{$member.nickname}" />
			<div id="DuplicationCheck_nickname" class="msg">{$data.msg}</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'password'}
	<tr>
		<td class="inputicon"></td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr class="height25">
				<td><input type="checkbox" id="MemberPasswordModifyCheck" name="password_modify" value="TRUE" onclick="MemberPasswordModify()" /></td>
				<td><label for="MemberPasswordModifyCheck" onclick="MemberPasswordModify()">{$data.title}를 변경하시려면 체크하세요.</label></td>
			</tr>
			</table>

			<div class="innerbox" id="MemberPasswordInsert" style="display:none;">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100" /><col width="300" />
			<tr>
				<td class="title">기존{$data.title}</td>
				<td><input type="password" name="password" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:150px;" /></td>
			</tr>
			<tr>
				<td class="title">변경할{$data.title}</td>
				<td>
					<div class="height5"></div>
					<input type="password" name="password1" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; MemberDuplicationCheck('password');" style="width:150px;" />
				</td>
			</tr>
			<tr>
				<td class="title">{$data.title}확인</td>
				<td>
					<div class="height5"></div>
					<input type="password" name="password2" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; MemberDuplicationCheck('password');" style="width:150px;" />
				</td>
			</tr>
			</table>
			<div id="DuplicationCheck_password" class="msg">기존 패스워드를 입력하신 뒤 변경할 패스워드를 입력하여 주십시오.</div>
			</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}재발급</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="password_question" value="{$password.idx}" />
			<div class="innerbox">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="30" /><col width="370" />
			<tr>
				<td class="text center">Q.</td>
				<td>
					<div id="SelectPasswordQuestion" class="selectbox" style="width:370px;">
						<div onclick="InputSelectBox('SelectPasswordQuestion')" clicker="SelectPasswordQuestion">{$password.question}</div>

						<ul style="display:none;" clicker="SelectPasswordQuestion">
							{foreach name=passwords from=$passwords item=question}
							<li onclick="InputSelectBoxSelect('SelectPasswordQuestion','{$question.question}','{$question.idx}',SelectPasswordQuestionBySkin)">{$question.question}</li>
							{/foreach}
						</ul>
					</div>
				</td>
			</tr>
			<tr>
				<td class="text center">A.</td>
				<td>
					<div class="height5"></div>
					<input type="text" name="password_answer" class="inputboxnum" onfocus="this.className='inputboxnumon';" onblur="this.className='inputboxnum';" style="width:364px;" value="{$member.password_answer}" />
				</td>
			</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'email'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox'; MemberDuplicationCheck('email');" style="width:400px;" value="{$member.email}" />
			<div id="DuplicationCheck_email" class="msg">{$data.msg}</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'homepage'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:400px;" value="{$member.homepage}" />
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'jumin'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="60" /><col width="10" /><col width="100%" />
			<tr>
				<td><input type="text" name="jumin1" class="inputbox" onfocus="this.className='inputboxon';" maxlength="6" onblur="this.className='inputbox';" onkeyup="if (this.value.length == 6) document.forms['MemberSignIn'].jumin2.focus();" style="width:50px;" value="{$member.jumin1}"{if $member.jumin1} disabled="disabled"{/if} /></td>
				<td class="dotum f11 center">-</td>
				<td><input type="password" name="jumin2" class="inputbox" onfocus="this.className='inputboxon';" maxlength="7" onblur="this.className='inputbox';" style="width:85px;" value="{$member.jumin2}"{if $member.jumin2} disabled="disabled"{/if} /></td>
			</tr>
			</table>
			<div id="DuplicationCheck_jumin" class="msg">{$data.msg}</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'cellphone'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			{if $data.value.provider == 'on'}<input type="hidden" name="provider" value="{$member.cellphone.provider}" />{/if}
			<input type="hidden" name="cellphone1" value="{$member.cellphone.cellphone1}" />
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			{if $data.value.provider == 'on'}<col width="70" />{/if}<col width="52" /><col width="10" /><col width="41" /><col width="10" /><col width="46" /><col width="100%" />
			<tr>
				<td>
					<div id="SelectCellPhoneProvider" class="selectbox" style="width:65px;">
						<div onclick="InputSelectBox('SelectCellPhoneProvider')" clicker="SelectCellPhoneProvider">{$member.cellphone.provider}</div>

						<ul style="display:none;" clicker="SelectCellPhoneProvider">
							<li onclick="InputSelectBoxSelect('SelectCellPhoneProvider','SKT','SKT',SelectCellPhoneProviderBySkin)">SKT</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhoneProvider','KT','KT',SelectCellPhoneProviderBySkin)">KT</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhoneProvider','LGT','LGT',SelectCellPhoneProviderBySkin)">LGT</li>
						</ul>
					</div>
				</td>
				<td>
					<div id="SelectCellPhonePno" class="selectbox" style="width:50px;">
						<div onclick="InputSelectBox('SelectCellPhonePno')" clicker="SelectCellPhonePno">{$member.cellphone.cellphone1}</div>

						<ul style="display:none;" clicker="SelectCellPhonePno">
							<li onclick="InputSelectBoxSelect('SelectCellPhonePno','010','010',SelectCellPhonePnoBySkin)">010</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhonePno','011','011',SelectCellPhonePnoBySkin)">011</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhonePno','016','016',SelectCellPhonePnoBySkin)">016</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhonePno','017','017',SelectCellPhonePnoBySkin)">017</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhonePno','018','018',SelectCellPhonePnoBySkin)">018</li>
							<li onclick="InputSelectBoxSelect('SelectCellPhonePno','019','019',SelectCellPhonePnoBySkin)">019</li>
						</ul>
					</div>
				</td>
				<td class="center dotum f11">-</td>
				<td><input type="text" name="cellphone2" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="4" onblur="this.className='inputboxnum';" onkeyup="if (this.value.length == 4) document.forms['MemberSignIn'].cellphone3.focus();" style="width:35px;" value="{$member.cellphone.cellphone2}" /></td>
				<td class="center dotum f11">-</td>
				<td><input type="text" name="cellphone3" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="4" onblur="this.className='inputboxnum';"  style="width:35px;" value="{$member.cellphone.cellphone3}" /></td>
				<td><img src="{$skinDir}/images/btn_cellphone.gif" class="pointer" onclick="MemberCellPhoneCheck()" /></td>
			</tr>
			<tr id="MemberPhoneInsert" class="height25" style="display:none;">
				<td><input type="text" name="pcode" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="5" onblur="this.className='inputboxnum';"  style="width:45px;" /></td>
				<td colspan="{if $data.value.provider == 'on'}6{else}5{/if}" class="dotum f11" />전송된 인증번호를 입력하여 주십시오. 다시 받으실려면 인증번호받기 버튼을 클릭하여 주세요.</td>
			</tr>
			</table>

			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	
	{if $data.type == 'birthday'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="birthday1" value="{$member.birthday.year}" />
			<input type="hidden" name="birthday2" value="{$member.birthday.month}" />
			<input type="hidden" name="birthday3" value="{$member.birthday.day}" />
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="80" /><col width="70" /><col width="70" /><col width="100%" />
			<tr>
				<td>
					<div id="SelectBirthdayYear" class="selectbox" style="width:75px;">
						<div onclick="InputSelectBox('SelectBirthdayYear')" clicker="SelectBirthdayYear">{$member.birthday.year}년</div>
					
						<ul style="display:none; height:200px; overflow:scroll;" clicker="SelectBirthdayYear">
							{section name=year start=1950 loop=$smarty.now+31536000|date_format:"%Y" step=1}
							<li onclick="InputSelectBoxSelect('SelectBirthdayYear','{$smarty.section.year.index}년','{$smarty.section.year.index}',SelectBirthdayYearBySkin)">{$smarty.section.year.index}년</li>
							{/section}
						</ul>
					</div>
				</td>
				<td>
					<div id="SelectBirthdayMonth" class="selectbox" style="width:65px;">
						<div onclick="InputSelectBox('SelectBirthdayMonth')" clicker="SelectBirthdayMonth">{$member.birthday.month}월</div>
					
						<ul style="display:none;" clicker="SelectBirthdayMonth">
							{section name=month start=1 loop=13 step=1}
							<li onclick="InputSelectBoxSelect('SelectBirthdayMonth','{$smarty.section.month.index}월','{$smarty.section.month.index}',SelectBirthdayMonthBySkin)">{$smarty.section.month.index}월</li>
							{/section}
						</ul>
					</div>
				</td>
				<td>
					<div id="SelectBirthdayDay" class="selectbox" style="width:65px;">
						<div onclick="InputSelectBox('SelectBirthdayDay')" clicker="SelectBirthdayDay">{$member.birthday.day}일</div>
					
						<ul style="display:none; height:200px; overflow:scroll;" clicker="SelectBirthdayDay">
							{section name=day start=1 loop=32 step=1}
							<li onclick="InputSelectBoxSelect('SelectBirthdayDay','{$smarty.section.day.index}일','{$smarty.section.day.index}',SelectBirthdayDayBySkin)">{$smarty.section.day.index}일</li>
							{/section}
						</ul>
					</div>
				</td>
			</tr>
			</table>

			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == "address"}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="zipcode" value="{$member.zipcode}" />
			<div class="innerbox">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="120" /><col width="280" />
			<tr>
				<td><input type="text" id="SearchZipcode" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';"  onkeydown="return SearchAddressBySkin(event);" style="width:110px;" /></td>
				<td style="padding-left:4px;"><img src="{$skinDir}/images/btn_zipcode.gif" class="pointer" onclick="SearchAddressBySkin(false)" /></td>
			</tr>
			<tr class="height5">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="SelectAddress" class="selectbox" style="width:400px;">
						<div onclick="InputSelectBox('SelectAddress')" clicker="SelectAddress">읍.면.동을 입력 후 우편번호검색버튼을 클릭하여 주십시오.</div>

						<ul style="display:none;" clicker="SelectAddress">
						</ul>
					</div>
				</td>
			</tr>
			<tr class="height5">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>
					<div><input type="text" name="address1" class="inputboxnum disable" style="width:394px;" readonly="readonly" value="{$member.address.address1}" /></div>
					<div style="margin-top:3px;"><input type="text" name="address2" class="inputboxnum" onfocus="this.className='inputboxnumon';" onblur="this.className='inputboxnum';" style="width:394px;" value="{$member.address.address2}" /></div>
				</td>
			</tr>
			</table>
			</div>
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'gender'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="gender" value="{$member.gender}" />
			<div id="SelectGender" class="selectbox" style="width:80px;">
				<div onclick="InputSelectBox('SelectGender')" clicker="SelectGender">{if $member.gender == 'MALE'}남자{else}여자{/if}</div>

				<ul style="display:none;" clicker="SelectGender">
					<li onclick="InputSelectBoxSelect('SelectGender','남자','MALE',SelectGenderBySkin)">남자</li>
					<li onclick="InputSelectBoxSelect('SelectGender','여자','FEMALE',SelectGenderBySkin)">여자</li>
				</ul>
			</div>
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'nickcon'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="file" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:400px;" />
			<div class="height5"></div>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr class="height25">
				<td><input type="checkbox" id="{$data.type}_delete" name="{$data.type}_delete" value="TRUE" /></td>
				<td><label for="{$data.type}_delete">등록되어 있는 이미지파일을 삭제합니다.</label></td>
			</tr>
			</table>
			<div id="DuplicationCheck_email" class="msg">{$data.msg}</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'photo'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="file" name="{$data.type}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:400px;" />
			<div class="height5"></div>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr class="height25">
				<td><input type="checkbox" id="{$data.type}_delete" name="{$data.type}_delete" value="TRUE" /></td>
				<td><label for="{$data.type}_delete">등록되어 있는 이미지파일을 삭제합니다.</label></td>
			</tr>
			</table>
			<div id="DuplicationCheck_email" class="msg">{$data.msg}</div>
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{/foreach}
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	</table>

	<div class="buttonbox">
		<input type="image" src="{$skinDir}/images/btn_confirm.gif" />
	</div>
</div>
{$formEnd}