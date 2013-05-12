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

	{if $data.type == 'telephone'}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="telephone1" value="{$member.telephone.telephone1}" />
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="82" /><col width="10" /><col width="41" /><col width="10" /><col width="46" /><col width="100%" />
			<tr>
				<td>
					<div id="SelectTelePhonePno" class="selectbox" style="width:80px;">
						<div onclick="InputSelectBox('SelectTelePhonePno')" clicker="SelectTelePhonePno">{$member.telephone.telephone1}</div>
					
						<ul style="display:none; height:200px; overflow-y:scroll;" clicker="SelectTelePhonePno">
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','02(서울)','02',SelectTelePhonePnoBySkin)">02(서울)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','031(경기)','031',SelectTelePhonePnoBySkin)">031(경기)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','032(인천)','032',SelectTelePhonePnoBySkin)">032(인천)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','033(강원)','033',SelectTelePhonePnoBySkin)">033(강원)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','041(충남)','041',SelectTelePhonePnoBySkin)">041(충남)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','042(대전)','042',SelectTelePhonePnoBySkin)">042(대전)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','043(충북)','043',SelectTelePhonePnoBySkin)">043(충북)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','044(세종)','044',SelectTelePhonePnoBySkin)">044(세종)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','051(부산)','051',SelectTelePhonePnoBySkin)">051(부산)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','052(울산)','052',SelectTelePhonePnoBySkin)">052(울산)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','053(대구)','053',SelectTelePhonePnoBySkin)">053(대구)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','054(경북)','054',SelectTelePhonePnoBySkin)">054(경북)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','055(경남)','055',SelectTelePhonePnoBySkin)">055(경남)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','061(전남)','061',SelectTelePhonePnoBySkin)">061(전남)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','062(광주)','062',SelectTelePhonePnoBySkin)">062(광주)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','063(전북)','063',SelectTelePhonePnoBySkin)">063(전북)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','064(제주)','064',SelectTelePhonePnoBySkin)">064(제주)</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','070','070',SelectTelePhonePnoBySkin)">070</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','0505','0505',SelectTelePhonePnoBySkin)">0505</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','010','010',SelectTelePhonePnoBySkin)">010</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','011','011',SelectTelePhonePnoBySkin)">011</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','016','016',SelectTelePhonePnoBySkin)">016</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','017','017',SelectTelePhonePnoBySkin)">017</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','018','018',SelectTelePhonePnoBySkin)">018</li>
							<li onclick="InputSelectBoxSelect('SelectTelePhonePno','019','019',SelectTelePhonePnoBySkin)">019</li>
						</ul>
					</div>
				</td>
				<td class="center dotum f11">-</td>
				<td><input type="text" name="telephone2" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="4" onblur="this.className='inputboxnum';" onkeyup="if (this.value.length == 4) document.forms['MemberSignIn'].telephone3.focus();" style="width:35px;" value="{$member.telephone.telephone2}" /></td>
				<td class="center dotum f11">-</td>
				<td><input type="text" name="telephone3" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="4" onblur="this.className='inputboxnum';"  style="width:35px;" value="{$member.telephone.telephone3}" /></td>
				<td></td>
			</tr>
			</table>

			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
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
				{if $data.value.provider == 'on'}
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
				{/if}
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
				<td>{if $data.value.realphone == 'on'}<img src="{$skinDir}/images/btn_cellphone.gif" class="pointer" onclick="MemberCellPhoneCheck()" />{/if}</td>
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
				<td><input type="text" id="SearchZipcode" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';"  onkeydown="return SearchAddressBySkin(event,'SearchZipcode');" address="SelectAddress" zipcode="zipcode" address1="address1" address2="address2" style="width:110px;" /></td>
				<td style="padding-left:4px;"><img src="{$skinDir}/images/btn_zipcode.gif" class="pointer" onclick="SearchAddressBySkin(false,'SearchZipcode')" /></td>
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
	
	{if $data.type == 'input'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="text" name="{$data.name}" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';" style="width:400px;" value="{$member.extra.$field}" />
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}

	{if $data.type == 'textarea'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<textarea name="{$data.name}" class="textbox" style="width:400px; height:{$data.value}px;" onfocus="this.className='textboxon';" onblur="this.className='textbox';">{$member.extra.$field}</textarea>
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	
	{if $data.type == 'select'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="{$data.name}" value="{$member.extra.$field}" />
			<div id="{$data.name}-Select" class="selectbox" style="width:200px;">
				<div onclick="InputSelectBox('{$data.name}-Select')" clicker="{$data.name}-Select">{$member.extra.$field}</div>
			
				<ul style="display:none;" clicker="{$data.name}-Select">
					{foreach from=$data.value item=list}
					<li onclick="InputSelectBoxSelect('{$data.name}-Select','{$list}','{$list}',function(text,value) {ldelim} document.forms['MemberSignIn']['{$data.name}'].value = value; {rdelim})">{$list}</li>
					{/foreach}
				</ul>
			</div>
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	
	{if $data.type == 'checkbox'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<div class="list">
				{foreach from=$data.value item=list}
				<input type="checkbox" name="{$data.name}[]" value="{$list}"{if in_array($list,$member.extra.$field) == true} checked="checked"{/if} />&nbsp;&nbsp;{$list}&nbsp;&nbsp;&nbsp;&nbsp;
				{/foreach}
			</div>
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	
	{if $data.type == 'radio'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<div class="list">
				{foreach from=$data.value item=list}
				<input type="radio" name="{$data.name}" value="{$list}"{if $list == $member.extra.$field} checked="checked"{/if} />&nbsp;&nbsp;{$list}&nbsp;&nbsp;&nbsp;&nbsp;
				{/foreach}
			</div>
			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	
	{if $data.type == 'search_address'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="{$data.name}-zipcode" value="{$member.extra.$field.zipcode}" />
			<div class="innerbox">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="120" /><col width="280" />
			<tr>
				<td><input type="text" id="{$data.name}-SearchZipcode" class="inputbox" onfocus="this.className='inputboxon';" onblur="this.className='inputbox';"  onkeydown="return SearchAddressBySkin(event,'{$data.name}-SearchZipcode');" address="{$data.name}-SelectAddress" zipcode="{$data.name}-zipcode" address1="{$data.name}-address1" address2="{$data.name}-address2" style="width:110px;" /></td>
				<td style="padding-left:4px;"><img src="{$skinDir}/images/btn_zipcode.gif" class="pointer" onclick="SearchAddressBySkin(false,'{$data.name}-SearchZipcode')" /></td>
			</tr>
			<tr class="height5">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="{$data.name}-SelectAddress" class="selectbox" style="width:400px;">
						<div onclick="InputSelectBox('{$data.name}-SelectAddress')" clicker="{$data.name}-SelectAddress">읍.면.동을 입력 후 우편번호검색버튼을 클릭하여 주십시오.</div>
					
						<ul style="display:none;" clicker="{$data.name}-SelectAddress">
						</ul>
					</div>
				</td>
			</tr>
			<tr class="height5">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>
					<div><input type="text" name="{$data.name}-address1" class="inputboxnum disable" style="width:394px;" readonly="readonly" value="{$member.extra.$field.address1}" /></div>
					<div style="margin-top:3px;"><input type="text" name="{$data.name}-address2" class="inputboxnum" onfocus="this.className='inputboxnumon';" onblur="this.className='inputboxnum';" style="width:394px;" value="{$member.extra.$field.address2}" /></div>
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
	
	{if $data.type == 'phone'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}{assign var=temp value="-"|explode:$member.extra.$field}
	<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="{$data.name}-1" value="{$temp[0]}" />
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="82" /><col width="10" /><col width="41" /><col width="10" /><col width="46" /><col width="100%" />
			<tr>
				<td>
					<div id="{$data.name}-Select" class="selectbox" style="width:80px;">
						<div onclick="InputSelectBox('{$data.name}-Select')" clicker="{$data.name}-Select">{$temp[0]}</div>
					
						<ul style="display:none; height:200px; overflow-y:scroll;" clicker="{$data.name}-Select">
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','02(서울)','02',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">02(서울)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','031(경기)','031',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">031(경기)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','032(인천)','032',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">032(인천)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','033(강원)','033',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">033(강원)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','041(충남)','041',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">041(충남)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','042(대전)','042',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">042(대전)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','043(충북)','043',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">043(충북)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','044(세종)','044',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">044(세종)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','051(부산)','051',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">051(부산)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','052(울산)','052',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">052(울산)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','053(대구)','053',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">053(대구)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','054(경북)','054',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">054(경북)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','055(경남)','055',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">055(경남)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','061(전남)','061',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">061(전남)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','062(광주)','062',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">062(광주)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','063(전북)','063',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">063(전북)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','064(제주)','064',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">064(제주)</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','070','070',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">070</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','0505','0505',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">0505</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','010','010',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">010</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','011','011',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">011</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','016','016',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">016</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','017','017',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">017</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','018','018',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">018</li>
							<li onclick="InputSelectBoxSelect('{$data.name}-Select','019','019',function(text,value) {ldelim}document.forms['MemberSignIn'].value = value;{rdelim})">019</li>
						</ul>
					</div>
				</td>
				<td class="center dotum f11">-</td>
				<td><input type="text" name="{$data.name}-2" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="4" onblur="this.className='inputboxnum';" onkeyup="if (this.value.length == 4) document.forms['MemberSignIn']['{$data.name}-3'].focus();" style="width:35px;" value="{$temp[1]}" /></td>
				<td class="center dotum f11">-</td>
				<td><input type="text" name="{$data.name}-3" class="inputboxnum" onfocus="this.className='inputboxnumon';" maxlength="4" onblur="this.className='inputboxnum';"  style="width:35px;" value="{$temp[2]}" /></td>
				<td></td>
			</tr>
			</table>

			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="inputrow">
		<td colspan="4"></td>
	</tr>
	{/if}
	
	{if $data.type == 'date'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}{assign var=temp value="-"|explode:$member.extra.$field}
		<tr>
		<td class="inputicon">{if $data.allowblank == 'FALSE'}<img src="{$skinDir}/images/icon_essential.gif" />{/if}</td>
		<td class="inputtext">{$data.title}</td>
		<td class="inputline"></td>
		<td class="inputform">
			<input type="hidden" name="{$data.name}-1" value="{$temp[0]}" />
			<input type="hidden" name="{$data.name}-2" value="{$temp[1]}" />
			<input type="hidden" name="{$data.name}-3" value="{$temp[2]}" />
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="80" /><col width="70" /><col width="70" /><col width="100%" />
			<tr>
				<td>
					<div id="{$data.name}-1-Select" class="selectbox" style="width:75px;">
						<div onclick="InputSelectBox('{$data.name}-1-Select')" clicker="{$data.name}-1-Select">{$temp[0]}년</div>
					
						<ul style="display:none; height:200px; overflow:scroll;" clicker="{$data.name}-1-Select">
							{section name=year start=1950 loop=$smarty.now+31536000|date_format:"%Y" step=1}
							<li onclick="InputSelectBoxSelect('{$data.name}-1-Select','{$smarty.section.year.index}년','{$smarty.section.year.index}',function(text,value) {ldelim}document.forms['MemberSignIn']['{$data.name}-1'].value = value;{rdelim})">{$smarty.section.year.index}년</li>
							{/section}
						</ul>
					</div>
				</td>
				<td>
					<div id="{$data.name}-2-Select" class="selectbox" style="width:65px;">
						<div onclick="InputSelectBox('{$data.name}-2-Select')" clicker="{$data.name}-2-Select">{$temp[1]}월</div>
					
						<ul style="display:none; height:200px; overflow-y:scroll;" clicker="{$data.name}-2-Select">
							{section name=month start=1 loop=13 step=1}
							<li onclick="InputSelectBoxSelect('{$data.name}-2-Select','{$smarty.section.month.index}월','{$smarty.section.month.index}',function(text,value) {ldelim}document.forms['MemberSignIn']['{$data.name}-2'].value = value;{rdelim})">{$smarty.section.month.index}월</li>
							{/section}
						</ul>
					</div>
				</td>
				<td>
					<div id="{$data.name}-3-Select" class="selectbox" style="width:65px;">
						<div onclick="InputSelectBox('{$data.name}-3-Select')" clicker="{$data.name}-3-Select">{$temp[2]}일</div>
					
						<ul style="display:none; height:200px; overflow:scroll;" clicker="{$data.name}-3-Select">
							{section name=day start=1 loop=32 step=1}
							<li onclick="InputSelectBoxSelect('{$data.name}-3-Select','{$smarty.section.day.index}일','{$smarty.section.day.index}',function(text,value) {ldelim}document.forms['MemberSignIn']['{$data.name}-3'].value = value;{rdelim})">{$smarty.section.day.index}일</li>
							{/section}
						</ul>
					</div>
				</td>
				<td></td>
			</tr>
			</table>

			{if $data.msg}<div class="msg">{$data.msg}</div>{/if}
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