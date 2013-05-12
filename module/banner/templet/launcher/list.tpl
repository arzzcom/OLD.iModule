{foreach name=list from=$data item=data}
	<div style="width:{$width}px; height:{$height}px; position:relative;">
		<div style="width:{$width}px; height:{$height}px; position:absolute; top:0px; left:0px; z-index:0;">{$data.bannerfile}</div>
		<div style="width:{$width}px; height:{$height}px; position:absolute; top:0px; left:0px; z-index:0; cursor:pointer;" onclick="parent.Launcher.OpenURL('http://launcher.ndoor.kr{$data.url}');"></div>
	</div>
{/foreach}