{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td>
			<div class="stepbar">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				{if $is_realname == true}
				<col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" /><col width="20%" />
				{else}
				<col width="25%" /><col width="25%" /><col width="25%" /><col width="25%" />
				{/if}
				<tr>
					<td><div>약관동의</div></td>
					{if $is_realname == true}<td><div>실명인증</div></td>{/if}
					<td><div>이력조회</div></td>
					<td><div class="select">정보입력</div></td>
					<td><div style="border-right:1px solid #cccccc;">가입완료</div></td>
				</tr>
				</table>
			</div>
		</td>
	</tr>
	</table>
	
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<tr class="splitBar">
		<td style="width:120px;" class="hidden-xs"></td>
		<td style="width:1px;" class="hidden-xs"></td>
		<td style="width:100%;"></td>
	</tr>
	{foreach name=form from=$form item=data}
	{if $data.type == 'user_id'}
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">{$data.title}</div></div>
			<input type="text" name="{$data.type}" class="input" style="width:100%;" onblur="MemberSignInFormCheck('user_id');" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'name'}
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">{$data.title}</div></div>
			<input type="text" name="{$data.type}" class="input" value="{$name}" style="width:100%;" readonly="readonly" />
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'nickname'}
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">{$data.title}</div></div>
			<input type="text" name="{$data.type}" class="input" onblur="MemberSignInFormCheck('nickname');" style="width:100%;" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'password'}
	<tr>
		<td class="headerCell hidden-xs">{$data.title}</td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">{$data.title}</div></div>
			<input type="password" name="password1" class="input" onblur="MemberSignInFormCheck('password');" style="width:100%;" />
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	<tr>
		<td class="headerCell hidden-xs">{$data.title}확인</td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">{$data.title}확인</div></div>
			<input type="password" name="password2" class="input" onblur="MemberSignInFormCheck('password');" style="width:100%;" />
			<div class="help-block">{$data.title}를 한번더 입력하여 주십시오.</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	<tr>
		<td class="headerCell hidden-xs"><div class="essential">{$data.title}재발급</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div class="essential">{$data.title}재발급 질문</div></div>
			<table cellpadding="0" cellspacing="0" class="memberTable">
			<tr class="sectionBar">
				<td style="width:60px;" class="hidden-xs"></td>
				<td style="width:1px;" class="hidden-xs"></td>
				<td style="width:100%;"></td>
			</tr>
			<tr>
				<td class="headerCell hidden-xs">질문</td>
				<td class="splitBar hidden-xs"></td>
				<td class="bodyCell">
					<input type="hidden" name="password_question" value="{$password.idx}" />
					<div class="drop" style="width:100%;" form="MemberSignIn" field="password_question">
						<button>질문을 선택하세요. <span class="arrow"></span></button>
						<ul>
							{foreach name=passwords from=$passwords item=question}
							<li value="{$question.idx}">{$question.question}</li>
							{/foreach}
						</ul>
					</div>
				</td>
			</tr>
			<tr class="splitBar">
				<td class="hidden-xs"></td>
				<td class="hidden-xs"></td>
				<td></td>
			</tr>
			<tr>
				<td class="headerCell hidden-xs">답변</td>
				<td class="splitBar hidden-xs"></td>
				<td class="bodyCell">
					<input type="text" name="password_answer" class="input" style="width:100%;" placeholder="답변입력" />
				</td>
			</tr>
			<tr class="sectionEnd">
				<td class="hidden-xs"><div></div></td>
				<td class="hidden-xs"><div></div></td>
				<td><div></div></td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'email'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="email" name="{$data.type}" class="input" value="{$email}" onblur="MemberSignInFormCheck('email');" style="width:100%;" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'homepage'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="url" name="{$data.type}" class="input" style="width:100%;" />
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'telephone'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="hidden" name="telephone1" />
			<div class="drop" style="width:100px;" form="MemberSignIn" field="telephone1">
				<button>국번 <span class="arrow"></span></button>
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
			
			<input type="number" name="telephone2" class="input" maxlength="4" style="width:45px;" pattern="\d*" />
			
			<span class="inputTag">-</span>
			
			<input type="number" name="telephone3" class="input" maxlength="4" style="width:45px;" pattern="\d*" />

			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'cellphone'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			{if $data.value.provider == 'on'}
			<input type="hidden" name="provider" />
			<div class="drop" style="width:80px;" form="MemberSignIn" field="provider">
				<button>통신사 <span class="arrow"></span></button>
				<ul>
					<li value="SKT">SKT</li>
					<li value="SKT">KT</li>
					<li value="SKT">LGT</li>
				</ul>
			</div>
			{/if}
			
			<input type="hidden" name="cellphone1" />
			<div class="drop" style="width:70px;">
				<button>국번 <span class="arrow"></button></button>
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
			
			<input type="number" name="cellphone2" class="input" style="width:45px;" pattern="\d*" />
			
			<span class="inputTag">-</span>
			
			<input type="number" name="cellphone3" class="input" style="width:45px;" pattern="\d*" />
			
			{if $data.value.realphone == 'on'}<div class="btn btn-sm btn-default" onclick="MemberCellPhoneCheck();">인증번호받기</div>{/if}
			
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
			
			<div id="MemberCellPhoneCheckInsert" class="height25" style="margin-top:10px; border-top:1px solid #cccccc; padding-top:15px; display:none;">
				<input type="number" name="pcode" class="input" maxlength="5" style="width:60px;" pattern="\d*" />
				<span class="inputTag">전송된 인증번호를 입력하여 주십시오. 재전송받으실려면 인증번호받기 버튼을 클릭하여 주세요.</span>
			</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'birthday'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="hidden" name="birthday1" />
			<input type="hidden" name="birthday2" />
			<input type="hidden" name="birthday3" />
			
			<div class="drop" style="width:80px;" form="MemberSignIn" field="birthday1">
				<button>년 <span class="arrow"></span></button>
				<ul>
					{section name=year start=1950 loop=$smarty.now+31536000|date_format:"%Y" step=1}
					<li value="{$smarty.section.year.index}">{$smarty.section.year.index}년</li>
					{/section}
				</ul>
			</div>
			
			<div class="drop" style="width:70px;" form="MemberSignIn" field="birthday2">
				<button>월 <span class="arrow"></span></button>
				<ul>
					{section name=month start=1 loop=13 step=1}
					<li value="{$smarty.section.month.index}">{$smarty.section.month.index}월</li>
					{/section}
				</ul>
			</div>
			
			<div class="drop" style="width:70px;" form="MemberSignIn" field="birthday3">
				<button>일 <span class="arrow"></span></button>
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
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == "address"}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="hidden" name="{$data.name}_juso_depth1" />
			<div class="drop" style="width:120px;" form="MemberSignIn" field="{$data.name}_juso_depth1" callback="MemberSearchAddressDepth1('{$data.name}','?');">
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
			
			<div class="height5 visible-xs"></div>
			<div class="height10 visible-xs"></div>
			
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
			
			<div class="height5"></div>
			<div class="height10"></div>
			
			<input type="text" name="{$data.name}_juso_keyword" class="input" style="width:100%;" placeholder="번지/건물번호(건물이름)/도로명+건물번호" disabled="disabled" />
			
			<div class="height10"></div>
			
			<div class="btn btn-sm btn-default btn-block" onclick="MemberSearchAddressSearch('{$data.name}');">주소검색</div>
			
			<div class="help-block">검색결과를 줄이기 위해 가급적 도로명까지 선택 후 검색하여 주시기 바랍니다.</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="hidden" name="{$data.name}_zipcode" />
			
			<div class="drop" style="width:100%;" form="MemberSignIn" field="{$data.name}_address1" callback="MemberSearchAddressSelect('{$data.name}','?');">
				<button disabled="disabled">주소를 선택하여 주세요. <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address1" class="input" style="width:100%;" placeholder="상단 선택박스에서 주소를 선택하여 주세요." readonly="readonly" />
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address2" class="input" style="width:100%;" placeholder="나머지주소(동, 호실 등)가 필요하다면 입력" />
			
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'gender'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="hidden" name="gender" />
			<div class="drop" style="width:80px;" form="MemberSignIn" field="gender">
				<button>선택 <span class="arrow"></span></button>
				<ul>
					<li value="MALE">남자</li>
					<li value="FEMALE">여자</li>
				</ul>
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'nickcon'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="file" name="{$data.type}" class="input" style="width:100%;" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'photo'}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="file" name="{$data.type}" class="input" style="width:100%;" />
			<div class="help-block">{$data.msg}</div>
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'input'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="text" name="{$data.name}" class="input" style="width:100%;" />
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}

	{if $data.type == 'textarea'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<textarea name="{$data.name}" class="textarea" style="width:100%; height:{$data.value}px;"></textarea>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'select'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="hidden" name="{$data.name}" />
			<div class="drop" style="width:100%;" form="MemberSignIn" field="{$data.name}">
				<button>선택 <span class="arrow"></span></button>
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
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'checkbox'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<div>
				{foreach from=$data.value item=list}
				<input type="checkbox" name="{$data.name}[]" value="{$list}"{if in_array($list,$member.extra.$field) == true} checked="checked"{/if} />&nbsp;&nbsp;{$list}&nbsp;&nbsp;&nbsp;&nbsp;
				{/foreach}
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'radio'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<div>
				{foreach from=$data.value item=list}
				<input type="radio" name="{$data.name}" value="{$list}"{if $list == $member.extra.$field} checked="checked"{/if} />&nbsp;&nbsp;{$list}&nbsp;&nbsp;&nbsp;&nbsp;
				{/foreach}
			</div>
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'search_address'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
			<input type="hidden" name="{$data.name}_juso_depth1" />
			<div class="drop" style="width:120px;" form="MemberSignIn" field="{$data.name}_juso_depth1" callback="MemberSearchAddressDepth1('{$data.name}','?');">
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
			
			<div class="height5 visible-xs"></div>
			<div class="height10 visible-xs"></div>
			
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
			
			<div class="height5 visible-xs"></div>
			<div class="height10"></div>
			
			<input type="text" name="{$data.name}_juso_keyword" class="input" style="width:100%;" placeholder="번지/건물번호(건물이름)/도로명+건물번호" disabled="disabled" />
			
			<div class="height10"></div>
			
			<div class="btn btn-sm btn-default btn-block" onclick="MemberSearchAddressSearch('{$data.name}');">주소검색</div>
			
			<div class="help-block">검색결과를 줄이기 위해 가급적 도로명까지 선택 후 검색하여 주시기 바랍니다.</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="hidden" name="{$data.name}_zipcode" />
			
			<div class="drop" style="width:100%;" form="MemberSignIn" field="{$data.name}_address1" callback="MemberSearchAddressSelect('{$data.name}','?');">
				<button disabled="disabled">주소를 선택하여 주세요. <span class="arrow"></span></button>
				<ul></ul>
			</div>
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address1" class="input" style="width:100%;" placeholder="상단 선택박스에서 주소를 선택하여 주세요." readonly="readonly" />
			
			<div class="height10"></div>
			<div class="height5"></div>
			
			<input type="text" name="{$data.name}_address2" class="input" style="width:100%;" placeholder="나머지주소(동, 호실 등)가 필요하다면 입력" />
			
			{if $data.msg}<div class="help-block">{$data.msg}</div>{/if}
		</td>
	</tr>
	<tr class="splitBar">
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'phone'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}{assign var=temp value="-"|explode:$member.extra.$field}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<input type="hidden" name="{$data.name}-1" value="{$temp[0]}" />
			<div class="drop" style="width:100px;" form="MemberSignIn" field="{$data.name}-1">
				<button> <span class="arrow"></span></button>
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
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	
	{if $data.type == 'date'}{assign var='field' value=$data.name|regex_replace:"/^extra_/":""}{assign var=temp value="-"|explode:$member.extra.$field}
	<tr>
		<td class="headerCell hidden-xs"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></td>
		<td class="splitBar hidden-xs"></td>
		<td class="bodyCell">
			<div class="visible-xs headerLayer"><div{if $data.allowblank == 'FALSE'} class="essential"{/if}>{$data.title}</div></div>
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
		<td class="hidden-xs"></td>
		<td class="hidden-xs"></td>
		<td></td>
	</tr>
	{/if}
	{/foreach}
	<tr class="sectionEnd">
		<td class="hidden-xs"><div></div></td>
		<td class="hidden-xs"><div></div></td>
		<td><div></div></td>
	</tr>
	</table>
</div>

<div class="height10"></div>

<div class="center">
	<input type="submit" class="btn btn-success" value="회원가입하기" />
</div>

<div class="height10"></div>
{$formEnd}