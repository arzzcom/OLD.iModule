{$formStart}
<div class="ModuleMemberSignInDefault">
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<tr>
		<td class="sectionTitle">회원가입</td>
	</tr>
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td class="stepbar">
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
		</td>
	</tr>
	<tr class="sectionBar">
		<td></td>
	</tr>
	<tr>
		<td class="sectionInfo">
			회원가입에 앞서 회원님의 이전가입내역을 조회합니다.<br />
			아래의 입력란에 정보를 입력 후 회원가입이력조회 버튼을 클릭하여 주십시오.
		</td>
	</tr>
	<tr class="splitBar">
		<td></td>
	</tr>
	<tr class="sectionEnd">
		<td><div></div></td>
	</tr>
	</table>
	
	<div class="height10"></div>
	
	<table cellpadding="0" cellspacing="0" class="memberTable">
	<col width="60" /><col width="1" /><col width="200" /><col width="1" /><col width="100" /><col width="1" /><col width="100%" />
	<tr>
		<td colspan="7" class="sectionTitle">회원가입여부 조회하기</td>
	</tr>
	<tr class="sectionBar">
		<td colspan="7"></td>
	</tr>
	<tr>
		<td colspan="7" class="sectionInfo">
			회원님의 실명과 이메일주소로 가입여부를 확인합니다.<br />
			가입이력이 존재하지 않으면, 다음단계로 진행됩니다. 가입이력이 있을 경우, 가입절차가 중단됩니다.
		</td>
	</tr>
	<tr class="splitBar">
		<td colspan="7"></td>
	</tr>
	<tr>
		<td class="headerCell">실명</td>
		<td class="splitBar"></td>
		<td class="bodyCell"><input type="text" name="checkname" class="input" style="width:100%;" /></td>
		<td class="splitBar"></td>
		<td class="headerCell">이메일주소</td>
		<td class="splitBar"></td>
		<td class="bodyCell"><input type="text" name="email" class="input" style="width:100%;" /></td>
	</tr>
	<tr class="splitBar">
		<td colspan="7"></td>
	</tr>
	<tr class="sectionEnd">
		<td colspan="7"><div></div></td>
	</tr>
	</table>
	
	<div class="height10"></div>

	<div class="center">
		<input type="submit" class="btn btn-sm btn-primary" value="회원가입여부 조회하기" />
	</div>
</div>
{$formEnd}