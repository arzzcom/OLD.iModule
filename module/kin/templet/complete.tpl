{$formStart}
<div class="completeArea">
	<div class="completeTitle">질문 마감하기</div>
</div>

<div class="completeForm">
	<div>채택할 만한 만족스러운 답벼을 받지 못하셨다면, 채택없이 질문을 마감하거나, 새 질문 목록에 다시 노출하여 새로운 답변을 추가로 받으실 수 있습니다.<br />단, <span class="pointText">정말 채택할 만한 답변이 없는지 다시 한번 확인해주세요!</span></div>
	
	<div class="height5"></div>
	
	<div class="innerLayer">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="50%" /><col width="10" /><col width="1" /><col width="10" /><col width="50%" />
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="20" /><col width="100%" />
				<tr>
					<td><input id="complete" type="radio" name="mode" value="complete" checked="checked" onclick="document.getElementById('BtnComplete').style.display=''; document.getElementById('BtnAnswer').style.display='none';" /></td>
					<td><label for="complete"><span class="bold">질문 마감하기</span></label></td>
				</tr>
				</table>
				
				<div class="height20"></div>
				
				<div class="bold">답변을 채택하지 않고,<br /><span class="pointText">질문을 종료하기</span> 원하시나요?</div>
				
				<div class="height10"></div>
				
				<div class="gray">
					질문 마감률 인정<br />
					추가 포인트가 있을 경우, 이중 10% 환급<br />
					(답변 채택시 50% 환급)<br />
					마감된 질문에는 답변등록불가<br />
					의견은 등록가능
				</div>
			</td>
			<td></td>
			<td style="background:#E5E5E5;"></td>
			<td></td>
			<td>
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="20" /><col width="100%" />
				<tr>
					<td><input id="rewrite" type="radio" name="mode" value="rewrite" onclick="document.getElementById('BtnComplete').style.display='none'; document.getElementById('BtnAnswer').style.display='';" /></td>
					<td><label for="rewrite"><span class="bold">새로운 답변받기</span></label></td>
				</tr>
				</table>
				
				<div class="height20"></div>
				
				<div class="bold">기존 답변과 함께 <span class="pointText">새로운 답변</span>을<br />더 받기를 원하시나요?</div>
				
				<div class="height10"></div>
				
				<div class="gray">
					질문목록 상단에 재노출<br />
					(질문을 등록한지 15일 경과되었을 경우)
					기존답변이 있다면 함께 노출<br />
					질문 마감률 인정되지 않음<br />
					답변등록 가능
				</div>
			</td>
		</tr>
		</table>
		
		<div class="height5"></div>
	</div>

	<div class="height10"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="110" /><col width="100%" /><col width="160" />
	<tr>
		<td>{if $is_password == true}<input type="password" name="password" class="passwordbox" />{/if}</td>
		<td class="f11 gray">{if $is_password == true}질문등록시 입력한 패스워드를 입력하세요.{/if}</td>
		<td>
			<div class="completeButton">
				<input id="BtnComplete" type="image" src="{$moduleDir}/images/common/btn_question_close.gif" />
				<input id="BtnAnswer" type="image" src="{$moduleDir}/images/common/btn_new_answer.gif" style="display:none;" />
				<img src="{$moduleDir}/images/common/btn_close.gif" onclick="self.close();" />
			</div>
		</td>
	</tr>
	</table>

</div>
{$formEnd}