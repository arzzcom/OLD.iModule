{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<col width="100%" /><col width="200" />
	<tr>
		<td class="sectionTitle">회원정보수정</td>
		<td class="right"><div class="essential">필수입력항목</div></td>
	</tr>
	<tr class="sectionBar">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td colspan="2" class="sectionInfo">
			입력하신 회원정보는 개인정보보호정책에 따라 철저하게 보호되며 회원동의 없이 공개되지 않습니다.<br />
			자세한 내용은 개인정보보호정책을 참고하시기 바랍니다.
		</td>
	</tr>
	</table>
	
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<col width="120" /><col width="1" /><col width="100%" />
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{foreach name=form from=$form item=data}
	{if $data.type == 'user_id'}
	<tr>
		<td class="headerCell"><div class="essential">{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="text" name="{$data.type}" class="input" style="width:200px;" value="{$member.user_id}" disabled="disabled" />
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'name'}
	<tr>
		<td class="headerCell"><div class="essential">{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="text" name="{$data.type}" class="input" value="{$member.name}" style="width:200px;" value="{$member.name}"{if $member.jumin} disabled="disabled"{/if} />
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'nickname'}
	<tr>
		<td class="headerCell"><div class="essential">{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="text" name="{$data.type}" class="input" onblur="MemberSignInFormCheck('nickname');" style="width:200px;" value="{$member.nickname}" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'password'}
	<tr>
		<td class="headerCell">{$data.title}</td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr class="height25">
				<td><input type="checkbox" id="MemberPasswordModifyCheck" name="password_modify" value="TRUE" onclick="MemberPasswordModify();" /></td>
				<td><label for="MemberPasswordModifyCheck" onclick="MemberPasswordModify();">{$data.title}를 변경하시려면 체크하세요.</label></td>
			</tr>
			</table>

			<div id="MemberPasswordInsert" style="display:none;">
				<table cellpadding="0" cellspacing="0" class="memberTable">
				<col width="120" /><col width="1" /><col width="100%" />
				<tr class="sectionBar">
					<td colspan="3"></td>
				</tr>
				<tr>
					<td class="headerCell">기존{$data.title}</td>
					<td class="splitBar"></td>
					<td class="bodyCell"><input type="password" name="password" class="input" style="width:150px;" /></td>
				</tr>
				<tr class="splitBar">
					<td colspan="3"></td>
				</tr>
				<tr>
					<td class="headerCell">변경할{$data.title}</td>
					<td class="splitBar"></td>
					<td class="bodyCell">
						<input type="password" name="password1" class="input" onblur="MemberSignInFormCheck('password');" style="width:150px;" />
					</td>
				</tr>
				<tr class="splitBar">
					<td colspan="3"></td>
				</tr>
				<tr>
					<td class="headerCell">{$data.title}확인</td>
					<td class="splitBar"></td>
					<td class="bodyCell">
						<input type="password" name="password2" class="input" onblur="MemberSignInFormCheck('password');" style="width:150px;" />
					</td>
				</tr>
				<tr class="sectionLineEnd">
					<td colspan="3"><div></div></td>
				</tr>
				</table>
				<div class="help-block">기존 패스워드를 입력하신 뒤 변경할 패스워드를 입력하여 주십시오.</div>
			</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	<tr>
		<td class="headerCell"><div class="essential">{$data.title}재발급</div></td>
		<td class="splitBar"></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="memberTable">
			<col width="160" /><col width="1" /><col width="100%" />
			<tr>
				<td class="headerCell">패스워드 재발급 질문</td>
				<td class="splitBar"></td>
				<td class="bodyCell">
					<input type="hidden" name="password_question" value="{$password.idx}" />
					<div class="drop" style="width:100%;" form="MemberSignIn" field="password_question">
						<button>{$password.question} <span class="arrow"></span></button>
						<ul>
							{foreach name=passwords from=$passwords item=question}
							<li value="{$question.idx}">{$question.question}</li>
							{/foreach}
						</ul>
					</div>
				</td>
			</tr>
			<tr class="splitBar">
				<td colspan="3"></td>
			</tr>
			<tr>
				<td class="headerCell">패스워드 재발급 답변</td>
				<td class="splitBar"></td>
				<td class="bodyCell">
					<input type="text" name="password_answer" class="input" style="width:100%;" value="{$member.password_answer}" />
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'email'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="email" name="{$data.type}" class="input" onblur="MemberSignInFormCheck('email');" style="width:100%;" value="{$member.email}" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'homepage'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="url" name="{$data.type}" class="input" style="width:100%;" value="{$member.homepage}" />
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'telephone'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="telephone1" value="{$member.telephone.telephone1}" />
			<div class="drop" style="width:100px;" form="MemberSignIn" field="telephone1">
				<button>{$member.telephone.telephone1} <span class="arrow"></span></button>
				<ul>
					<li value="02">02(서울)</li>
					<li value="031">031(경기)</li>
					<li value="032">032(인천)</li>
					<li value="033">033(강원)</li>
					<li value="041">041(충남)</li>
					<li value="042">042(대전)</li>
					<li value="043">043(충북)</li>
					<li value="044">044(세종)</li>
					<li value="051">051(부산)</li>
					<li value="052">052(울산)</li>
					<li value="053">053(대구)</li>
					<li value="054">054(경북)</li>
					<li value="055">055(경남)</li>
					<li value="061">061(전남)</li>
					<li value="062">062(광주)</li>
					<li value="063">063(전북)</li>
					<li value="064">064(제주)</li>
					<li value="070">070</li>
					<li value="0505">0505</li>
					<li value="010">010</li>
					<li value="011">011</li>
					<li value="016">016</li>
					<li value="017">017</li>
					<li value="018">018</li>
					<li value="019">019</li>
				</ul>
			</div>
			
			<span class="inputTag">-</span>
			
			<input type="number" name="telephone2" class="input" maxlength="4" style="width:45px;" value="{$member.telephone.telephone2}" pattern="\d*" />
			
			<span class="inputTag">-</span>
			
			<input type="number" name="telephone3" class="input" maxlength="4" style="width:45px;" value="{$member.telephone.telephone3}" pattern="\d*" />

			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'cellphone'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			{if $data.value.provider == 'on'}
			<input type="hidden" name="provider" value="{$member.cellphone.provider}" />
			<div class="drop" style="width:80px;" form="MemberSignIn" field="provider">
				<button>{$member.cellphone.provider} <span class="arrow"></span></button>
				<ul>
					<li value="SKT">SKT</li>
					<li value="SKT">KT</li>
					<li value="SKT">LGT</li>
				</ul>
			</div>
			
			<span class="inputTag"></span>
			{/if}
			
			<input type="hidden" name="cellphone1" value="{$member.cellphone.cellphone1}" />
			<div class="drop" style="width:70px;">
				<button>{$member.cellphone.cellphone1} <span class="arrow"></span></button>
				<ul>
					<li value="010">010</li>
					<li value="011">011</li>
					<li value="016">016</li>
					<li value="017">017</li>
					<li value="018">018</li>
					<li value="019">019</li>
				</ul>
			</div>
			
			<span class="inputTag">-</span>
			
			<input type="number" name="cellphone2" class="input" style="width:45px;" value="{$member.cellphone.cellphone2}" pattern="\d*" />
			
			<span class="inputTag">-</span>
			
			<input type="number" name="cellphone3" class="input" style="width:45px;" value="{$member.cellphone.cellphone3}" pattern="\d*" />
			
			{if $data.value.realphone == 'on'}<div class="btn btn-sm btn-default" onclick="MemberCellPhoneCheck();">인증번호받기</div>{/if}
			
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
			
			<div id="MemberCellPhoneCheckInsert" class="height25" style="margin-top:10px; border-top:1px solid #cccccc; padding-top:15px; display:none;">
				<input type="number" name="pcode" class="input" maxlength="5" style="width:60px;" pattern="\d*" />
				<span class="inputTag">전송된 인증번호를 입력하여 주십시오. 재전송받으실려면 인증번호받기 버튼을 클릭하여 주세요.</span>
			</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'birthday'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="birthday1" value="{$member.birthday.year}" />
			<input type="hidden" name="birthday2" value="{$member.birthday.month}" />
			<input type="hidden" name="birthday3" value="{$member.birthday.day}" />
			
			<div class="drop" style="width:80px;" form="MemberSignIn" field="birthday1">
				<button>{$member.birthday.year}년 <span class="arrow"></span></button>
				<ul>
					{section name=year start=1950 loop=$smarty.now+31536000|date_format:"%Y" step=1}
					<li value="{$smarty.section.year.index}">{$smarty.section.year.index}년</li>
					{/section}
				</ul>
			</div>
			
			<div class="drop" style="width:70px;" form="MemberSignIn" field="birthday2">
				<button>{$member.birthday.month}월 <span class="arrow"></span></button>
				<ul>
					{section name=month start=1 loop=13 step=1}
					<li value="{$smarty.section.month.index}">{$smarty.section.month.index}월</li>
					{/section}
				</ul>
			</div>
			
			<div class="drop" style="width:70px;" form="MemberSignIn" field="birthday3">
				<button>{$member.birthday.day}일 <span class="arrow"></span></button>
				<ul>
					{section name=day start=1 loop=32 step=1}
					<li value="{$smarty.section.day.index}">{$smarty.section.day.index}일</li>
					{/section}
				</ul>
			</div>

			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == "address"}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="{$data.name}_juso_depth1" />
			<div class="drop" style="width:100px;" form="MemberSignIn" field="{$data.name}_juso_depth1" callback="MemberSearchAddressDepth1('{$data.name}','?');">
				<button>시도 <span class="arrow"></span></button>
				<ul>
					<li value="서울">서울특별시</li>
					<li value="부산">부산광역시</li>
					<li value="대구">대구광역시</li>
					<li value="인천">인천광역시</li>
					<li value="광주">광주광역시</li>
					<li value="대전">대전광역시</li>
					<li value="울산">울산광역시</li>
					<li value="세종">세종시</li>
					<li value="경기">경기도</li>
					<li value="강원">강원도</li>
					<li value="충북">충청북도</li>
					<li value="충남">충청남도</li>
					<li value="전북">전라북도</li>
					<li value="전남">전라남도</li>
					<li value="경북">경상북도</li>
					<li value="경남">경상남도</li>
					<li value="제주">제주도</li>
				</ul>
			</div>
			
			<input type="hidden" name="{$data.name}_juso_depth2" />
			<div class="drop" style="width:120px;" form="MemberSignIn" field="{$data.name}_juso_depth2" callback="MemberSearchAddressDepth2('{$data.name}','?');">
				<button disabled="disabled">시군구 <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<input type="hidden" name="{$data.name}_juso_depth3" />
			<div class="drop" style="width:120px;" form="MemberSignIn" field="{$data.name}_juso_depth3" callback="MemberSearchAddressDepth3('{$data.name}','?');">
				<button disabled="disabled">읍면동 <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<input type="hidden" name="{$data.name}_juso_depth4" />
			<div class="drop" style="width:150px;" form="MemberSignIn" field="{$data.name}_juso_depth4" callback="MemberSearchAddressDepth4('{$data.name}','?');">
				<button disabled="disabled">도로명 <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<div class="height10"></div>
			
			<input type="text" name="{$data.name}_juso_keyword" class="input" style="width:429px;" placeholder="번지 또는 건물번호(건물이름) / 도로명 + 건물번호" disabled="disabled" callback="MemberSearchAddressSearch('{$data.name}');" />
			
			<button class="btn btn-sm btn-default" onclick="MemberSearchAddressSearch('{$data.name}');">주소검색</button>
			
			<div class="help-block">검색결과를 줄이기 위해 가급적 도로명까지 선택 후 검색하여 주시기 바랍니다.</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="hidden" name="{$data.name}_zipcode" value="{$member.zipcode}" />
			
			<div class="drop" style="width:100%;" field="{$data.name}_address1" callback="MemberSearchAddressSelect('{$data.name}','?');">
				<button disabled="disabled">주소를 선택하여 주세요. <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address1" class="input" style="width:100%;" placeholder="상단 선택박스에서 주소를 선택하여 주세요." readonly="readonly" value="{$member.address.address1}" />
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address2" class="input" style="width:100%;" placeholder="나머지주소(동, 호실 등)가 필요하다면 입력하여 주십시오." value="{$member.address.address2}" />
			
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'gender'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="gender" value="{$member.gender}" />
			<div class="drop" style="width:80px;" form="MemberSignIn" field="gender">
				<button>{if $member.gender == 'MALE'}남자{else}여자{/if} <span class="arrow"></span></button>
				<ul>
					<li value="MALE">남자</li>
					<li value="FEMALE">여자</li>
				</ul>
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'nickcon'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="file" name="{$data.type}" class="input" style="width:100%;" />
			<div class="height5"></div>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr class="height25">
				<td><input type="checkbox" id="{$data.type}_delete" name="{$data.type}_delete" value="TRUE" /></td>
				<td><label for="{$data.type}_delete">등록되어 있는 이미지파일을 삭제합니다.</label></td>
			</tr>
			</table>
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'photo'}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="file" name="{$data.type}" class="input" style="width:100%;" />
			<div class="height5"></div>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr class="height25">
				<td><input type="checkbox" id="{$data.type}_delete" name="{$data.type}_delete" value="TRUE" /></td>
				<td><label for="{$data.type}_delete">등록되어 있는 이미지파일을 삭제합니다.</label></td>
			</tr>
			</table>
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'input'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="text" name="{$data.name}" class="input" style="width:100%;" value="{$member.extra.$field}" />
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}

	{if $data.type == 'textarea'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<textarea name="{$data.name}" class="textarea" style="width:100%; height:{$data.value}px;">{$member.extra.$field}</textarea>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'select'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="{$data.name}" value="{$member.extra.$field}" />
			<div class="drop" style="width:200px;" form="MemberSignIn" field="{$data.name}">
				<button>{$member.extra.$field} <span class="arrow"></span></button>
				<ul>
					{foreach from=$data.value item=list}
					<li value="{$list}">{$list}</li>
					{/foreach}
				</ul>
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'checkbox'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<div>
				{foreach from=$data.value item=list}
				<input type="checkbox" name="{$data.name}[]" value="{$list}"{if in_array($list,$member.extra.$field) == true} checked="checked"{/if} />&nbsp;&nbsp;{$list}&nbsp;&nbsp;&nbsp;&nbsp;
				{/foreach}
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'radio'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<div>
				{foreach from=$data.value item=list}
				<input type="radio" name="{$data.name}" value="{$list}"{if $list == $member.extra.$field} checked="checked"{/if} />&nbsp;&nbsp;{$list}&nbsp;&nbsp;&nbsp;&nbsp;
				{/foreach}
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'search_address'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="{$data.name}_juso_depth1" />
			<div class="drop" style="width:100px;" form="MemberSignIn" field="{$data.name}_juso_depth1" callback="MemberSearchAddressDepth1('{$data.name}','?');">
				<button>시도 <span class="arrow"></span></button>
				<ul>
					<li value="서울">서울특별시</li>
					<li value="부산">부산광역시</li>
					<li value="대구">대구광역시</li>
					<li value="인천">인천광역시</li>
					<li value="광주">광주광역시</li>
					<li value="대전">대전광역시</li>
					<li value="울산">울산광역시</li>
					<li value="세종">세종시</li>
					<li value="경기">경기도</li>
					<li value="강원">강원도</li>
					<li value="충북">충청북도</li>
					<li value="충남">충청남도</li>
					<li value="전북">전라북도</li>
					<li value="전남">전라남도</li>
					<li value="경북">경상북도</li>
					<li value="경남">경상남도</li>
					<li value="제주">제주도</li>
				</ul>
			</div>
			
			<input type="hidden" name="{$data.name}_juso_depth2" />
			<div class="drop" style="width:120px;" form="MemberSignIn" field="{$data.name}_juso_depth2" callback="MemberSearchAddressDepth2('{$data.name}','?');">
				<button disabled="disabled">시군구 <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<input type="hidden" name="{$data.name}_juso_depth3" />
			<div class="drop" style="width:120px;" form="MemberSignIn" field="{$data.name}_juso_depth3" callback="MemberSearchAddressDepth3('{$data.name}','?');">
				<button disabled="disabled">읍면동 <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<input type="hidden" name="{$data.name}_juso_depth4" />
			<div class="drop" style="width:150px;" form="MemberSignIn" field="{$data.name}_juso_depth4" callback="MemberSearchAddressDepth4('{$data.name}','?');">
				<button disabled="disabled">도로명 <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<div class="height10"></div>
			
			<input type="text" name="{$data.name}_juso_keyword" class="input" style="width:429px;" placeholder="번지 또는 건물번호(건물이름) / 도로명 + 건물번호" disabled="disabled" callback="MemberSearchAddressSearch('{$data.name}');" />
			
			<button class="btn btn-sm btn-default" onclick="MemberSearchAddressSearch('{$data.name}');">주소검색</button>
			
			<div class="help-block">검색결과를 줄이기 위해 가급적 도로명까지 선택 후 검색하여 주시기 바랍니다.</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="hidden" name="{$data.name}_zipcode" value="{$member.extra.$field.zipcode}" />
			
			<div class="drop" style="width:100%;" field="{$data.name}_address1" callback="MemberSearchAddressSelect('{$data.name}','?');">
				<button disabled="disabled">주소를 선택하여 주세요. <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address1" class="input" style="width:100%;" placeholder="상단 선택박스에서 주소를 선택하여 주세요." readonly="readonly" value="{$member.extra.$field.address1}" />
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address2" class="input" style="width:100%;" placeholder="나머지주소(동, 호실 등)가 필요하다면 입력하여 주십시오." value="{$member.extra.$field.address2}" />
			
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'phone'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}{assign var=temp value="-"|explode:$member.extra.$field}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="{$data.name}-1" value="{$temp[0]}" />
			<div class="drop" style="width:100px;" form="MemberSignIn" field="{$data.name}-1">
				<button>{$member.telephone.telephone1} <span class="arrow"></span></button>
				<ul>
					<li value="02">02(서울)</li>
					<li value="031">031(경기)</li>
					<li value="032">032(인천)</li>
					<li value="033">033(강원)</li>
					<li value="041">041(충남)</li>
					<li value="042">042(대전)</li>
					<li value="043">043(충북)</li>
					<li value="044">044(세종)</li>
					<li value="051">051(부산)</li>
					<li value="052">052(울산)</li>
					<li value="053">053(대구)</li>
					<li value="054">054(경북)</li>
					<li value="055">055(경남)</li>
					<li value="061">061(전남)</li>
					<li value="062">062(광주)</li>
					<li value="063">063(전북)</li>
					<li value="064">064(제주)</li>
					<li value="070">070</li>
					<li value="0505">0505</li>
					<li value="010">010</li>
					<li value="011">011</li>
					<li value="016">016</li>
					<li value="017">017</li>
					<li value="018">018</li>
					<li value="019">019</li>
				</ul>
			</div>
			
			<span class="inputTag">-</span>
			
			<input type="number" name="{$data.name}-2" class="input" maxlength="4" style="width:45px;" value="{$temp[1]}" pattern="\d*" />
			
			<span class="inputTag">-</span>
			
			<input type="number" name="{$data.name}-3" class="input" maxlength="4" style="width:45px;" value="{$temp[2]}" pattern="\d*" />

			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	
	{if $data.type == 'date'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}{assign var=temp value="-"|explode:$member.extra.$field}
	<tr>
		<td class="headerCell"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar"></td>
		<td class="bodyCell">
			<input type="hidden" name="{$data.name}-1" value="{$temp[0]}" />
			<input type="hidden" name="{$data.name}-2" value="{$temp[1]}" />
			<input type="hidden" name="{$data.name}-3" value="{$temp[2]}" />
			
			<div class="drop" style="width:80px;" form="MemberSignIn" field="{$data.name}-1">
				<button>{$temp[0]}년 <span class="arrow"></span></button>
				<ul>
					{section name=year start=1950 loop=$smarty.now+31536000|date_format:"%Y" step=1}
					<li value="{$smarty.section.year.index}">{$smarty.section.year.index}년</li>
					{/section}
				</ul>
			</div>
			
			<div class="drop" style="width:70px;" form="MemberSignIn" field="{$data.name}-2">
				<button>{$temp[1]}월 <span class="arrow"></span></button>
				<ul>
					{section name=month start=1 loop=13 step=1}
					<li value="{$smarty.section.month.index}">{$smarty.section.month.index}월</li>
					{/section}
				</ul>
			</div>
			
			<div class="drop" style="width:70px;" form="MemberSignIn" field="{$data.name}-3">
				<button>{$temp[2]}일 <span class="arrow"></span></button>
				<ul>
					{section name=day start=1 loop=32 step=1}
					<li value="{$smarty.section.day.index}">{$smarty.section.day.index}일</li>
					{/section}
				</ul>
			</div>

			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="3"></td>
	</tr>
	{/if}
	{/foreach}
	<tr class="sectionEnd">
		<td colspan="3"><div></div></td>
	</tr>
	</table>
</div>

<div class="height10"></div>

<div class="center">
	<input type="submit" class="btn btn-sm btn-success" value="회원정보 수정하기" />
</div>
{$formEnd}