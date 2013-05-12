<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100%" /><col width="120" />
<tr>
	<td class="gray f12 padding10">첨부파일을 등록하시려면 우측의 파일첨부 버튼을 클릭하세요.</td>
	<td class="right padding10"><div id="Wrap{$id}">{$button}</div></td>
</tr>
<tr>
	<td colspan="2" style="padding:0px 10px 10px 10px;">
		<div id="UploaderPreviewImage-{$id}"></div>
		<div id="UploaderPreviewFile-{$id}"></div>
	</td>
</tr>
</table>

<div id="UPBox-{$id}" class="progressbox" style="display:none;">
	파일을 업로드중입니다... (<span id="FileNum-{$id}" class="bold">0</span>/<span id="TotalNum-{$id}">0</span>)
	<div class="outer"><div id="UPBoxFile-{$id}" class="inner"></div><div id="UPBoxFileText-{$id}" class="text"></div></div>
	<div class="outer"><div id="UPBoxTotal-{$id}" class="inner"></div><div id="UPBoxTotalText-{$id}" class="text"></div></div>
	<div class="time">
		경과시간 <span id="UPBoxTime-{$id}" class="bold">0:00</span> / 예상남은시간 <span id="UPBoxRemain-{$id}" class="bold">0:00</span> / 전송속도 <span id="UPBoxSpeed-{$id}" class="bold">0.00KiB</span>/s
	</div>
</div>