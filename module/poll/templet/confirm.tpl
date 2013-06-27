<!-- Input Password Start -->
<div class="errorArea">
{$formStart}
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="errorTitleArea">
	<td><div class="confirmTitle"></div></td>
</tr>
<tr>
	<td>
		<div class="errorBox">
			{$msg}

			<div class="errorButton">
				<input type="image" src="{$moduleDir}/images/common/btn_confirm.gif" alt="확인" /><a href="{$link.cancel}"><img src="{$moduleDir}/images/common/btn_back.gif" alt="뒤로가기" /></a>
			</div>
		</div>
	</td>
</tr>
</table>
{$formEnd}
</div>
<!-- Input Password End -->