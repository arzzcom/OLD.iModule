<div id="toolbar">
	<h1>{$setup.title}</h1>
	<a id="backButton" class="button" href="{$link.cancel}">이전</a>
	<a class="button" href="{$link.list}">목록</a>
</div>

<div id="content" class="line">
{$formStart}
	<div class="height5"></div>
	<div class="titlebox">확인</div>
	<div class="errorbox">
		{$msg}
	</div>
	
	<div class="submitbox">
		<input type="submit" value="확인" />
	</div>
{$formEnd}
</div>