<div id="toolbar">
	<h1>{$setup.title}</h1>
	<a id="backButton" class="button" href="{$link.back}">이전</a>
	<a class="button" href="{$link.list}">목록</a>
</div>

<div id="content" class="line">
{$formStart}
	<div class="height5"></div>
	<div class="titlebox">패스워드를 입력하여 주십시오.</div>
	<div class="errorbox">
		{$msg}
	</div>
	
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="80" /><col width="100%" />
		<tr>
			<td class="header">패스워드</td>
			<td class="input"><input type="password" name="password" msg="패스워드를 입력하여 주십시오." /></td>
		</tr>
		</table>
	</div>
	
	<div class="submitbox">
		<input type="submit" value="확인" />
	</div>
{$formEnd}
</div>