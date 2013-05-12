<div id="content" class="whiteBg">
	<div class="viewTitle">
		<div class="viewTitleText">{$data.title}</div>
		<div class="viewTitleInfo">{$data.nickname} At {$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</div>
	</div>
	
	<div class="viewContent">
	{$data.content}
	</div>
</div>