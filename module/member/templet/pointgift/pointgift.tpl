<div class="frame">
	<div class="inframe">
		<div class="title">포인트 선물하기</div>
		{$formStart}
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="20" /><col width="80" /><col width="100%" />
		<tr>
			<td class="inputHeader" colspan="2">받으실분 아이디</td>
			<td class="inputBox"><input type="text" name="user_id" value="{$user_id}" class="input" onfocus="this.className='input focus';" onblur="this.className='input';" /></td>
		</tr>

		<tr>
			<td class="inputHeader" colspan="2">선물가능 포인트</td>
			<td class="inputBox"><div><span class="bold">{$mypoint|number_format}</span> Point</div></td>
		</tr>
		<tr>
			<td class="inputHeader" colspan="2">선물할 포인트</td>
			<td class="inputBox"><input type="text" name="point" class="input" onfocus="this.className='input focus';" onblur="this.className='input'; CheckPoint(this);" /></td>
		</tr>
		<tr>
			<td class="inputHeader" colspan="2">실제 선물포인트</td>
			<td class="inputBox"><input type="text" name="realPoint" value="0" class="input" onfocus="this.className='input focus';" onblur="this.className='input';" disabled="disabled" /> (세금<span class="red">10%</span>제외)</td>
		</tr>
		<tr>
			<td class="inputHeader" colspan="2">전달할 메세지</td>
			<td class="inputBox"><div>선물받는분께 아래의 메세지가 전송됩니다.</div></td>
		</tr>
		<tr>
			<td colspan="3">
				<textarea name="message" class="textarea" onfocus="this.className='textarea focus';" onblur="this.className='textarea';"></textarea>
			</td>
		</tr>
		<tr>
			<td class="right"><input type="checkbox" name="is_secret" value="true" /></td>
			<td class="inputBox" colspan="2"><div>비공개로 포인트를 선물합니다.</div></td>
		</tr>
		</table>

		<div class="button"><input type="image" src="{$skinDir}/images/btn_confirm.gif" /></div>
		{$formEnd}
	</div>
</div>