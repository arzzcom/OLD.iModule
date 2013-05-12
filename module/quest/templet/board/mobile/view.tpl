<div id="content" class="whiteBg">
	<div class="viewTitle">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="55" /><col width="100%" />
		<tr>
			<td><img src="{$data.photo}" class="photo" /></td>
			<td>
				<div class="viewTitleText">{$data.title}</div>
				<div class="viewTitleInfo">{$data.nickname} At {$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</div>
			</td>
		</tr>
		</table>
	</div>
	
	<div class="viewContent">
	{$data.content}
	</div>
</div>

{literal}
<script type="text/javascript">
var images = document.getElementsByClassName("smartOutput")[0].getElementsByTagName("img");
for (var i=0, loop=images.length;i<loop;i++) images.onclick("alert('test');");
</script>
{/literal}