{$formStart}
<div class="selectArea">
	<div class="selectTitle">질문자 답변 채택하기</div>
</div>

<div class="selectForm">
	<div><span class="bold">답변주신 분께 감사의 인사를 남겨주세요.</span> <span class="gray">(최대 250자)</span></div>
	
	<div class="height5"></div>
	
	<div>답변을 채택하시면, <span class="pointText">답변채택 감사 포인트 {$point.select}포인트</span>와 질문등록시 <span class="pointText">추가한 포인트의 50%({$point.gift}포인트)</span>를 돌려드립니다.</div>
	
	<div class="innerLayer">
		<textarea name="message" class="inputbox" onfocus="if (this.value == '친절하고 정확한 답변 감사드립니다.') this.value = '';" onblur="if (!this.value) this.value = '친절하고 정확한 답변 감사드립니다.';">친절하고 정확한 답변 감사드립니다.</textarea>
		
		<div class="height5"></div>
		
		<div>
			질문자가 답변을 채택한 경우에는 추가로 답변을 받을 수 없으므로, <span class="pointText">추가질문은 감사의 인사에 남기지 마시고, 새로 질문을 등록해주시기 바랍니다.</span><br />
			답변을 채택하면, <span class="pointText">질문글을 삭제할 수 없으며, 추가답변 등록이 불가능</span>합니다.(의견은 등록가능합니다.)
		</div>
	</div>

	<div class="height10"></div>
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="110" /><col width="100%" /><col width="128" />
	<tr>
		<td>{if $is_password == true}<input type="password" name="password" class="passwordbox" />{/if}</td>
		<td class="f11 gray">{if $is_password == true}질문등록시 입력한 패스워드를 입력하세요.{/if}</td>
		<td>
			<div class="selectButton">
				<input type="image" src="{$moduleDir}/images/common/btn_select.gif" />
				<img src="{$moduleDir}/images/common/btn_close.gif" onclick="self.close();" />
			</div>
		</td>
	</tr>
	</table>

</div>
{$formEnd}