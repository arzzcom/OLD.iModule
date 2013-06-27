{$formStart}
<div class="mentbox">
	<div class="writebox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100%" /><col width="68" />
		{if $member.idx == 0}
		<tr>
			<td colspan="2" class="myinfo"><input type="text" name="name" class="inputbox" /> | 작성자 <input type="password" name="password" class="inputbox" /> | 패스워드</td>
		</tr>
		<tr class="height5">
			<td colspan="2"></td>
		</tr>
		{/if}
		<tr>
			<td>
				<div class="inputarea"><textarea name="content" class="textbox"></textarea></div>
			</td>
			<td><input type="image" src="{$skinDir}/images/btn_ment_write.gif" /></td>
		</tr>
		</table>
	</div>
</div>
{$formEnd}