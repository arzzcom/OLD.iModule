<!-- Input Password Start -->
<div class="errorArea">
{$formStart}
<table cellspacing="0" cellpadding="0" class="layoutfixed">
<tr class="errorTitleArea">
	<td><div class="passwordTitle"></div></td>
</tr>
<tr>
	<td>
		<div class="errorBox">
			{$msg}
			<div class="height5"></div>
			<table cellspacing="0" cellpadding="0" class="layoutfixed">
			<col width="105" /><col width="100%" />
			<tr>
				<td><img src="{$moduleDir}/images/common/text_password.gif" alt="패스워드" /></td>
				<td><input type="password" name="password" class="inputbox" msg="패스워드를 입력하여 주십시오." /></td>
			</tr>
			</table>

			<div class="errorButton">
				<input type="image" src="{$moduleDir}/images/common/btn_confirm.gif" alt="확인" /><a href="{$link.back}"><img src="{$moduleDir}/images/common/btn_back.gif" alt="뒤로가기" /></a>
			</div>
		</div>
	</td>
</tr>
</table>
{$formEnd}
</div>
<!-- Input Password End -->