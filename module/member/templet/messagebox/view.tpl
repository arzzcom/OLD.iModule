<div class="viewer">
	<div class="viewerInner">
		<div class="viewerTitle">HISTORY{$checker}</div>
		
		<div class="viewerContent">
			<div id="MessageMore" class="more" onclick="GetMessage('prev');">이전 메세지 더보기</div>
			<div id="MessageList"></div>
			<div class="height5"></div>
		</div>
		
		<div class="wywiwyg">
			{$formStart}
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100%" /><col width="75" />
			<tr>
				<td class="vTop">{$wysiwyg}</td>
				<td class="viewerButton">
					<input type="image" src="{$skinDir}/images/btn_send.gif" accesskey="s" />
				</td>
			</tr>
			</table>
			{$formEnd}
		</div>
	</div>
</div>