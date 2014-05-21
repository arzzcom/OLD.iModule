{$mentStart}
{foreach name=data from=$data item=data}
	{$data.replyStart}
	<div class="height5"></div>
	{if $data.is_delete == true}
	<div class="mentdelete">아래의 답변댓글에 대한 원래의 댓글이 작성자 또는 관리자에 의해 삭제되었습니다.</div>
	{else}
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="7" /><col width="35" /><col width="100%" /><col width="200" /><col width="10" />
	<tr class="mentbar">
		<td class="left"></td>
		<td class="photo"><img src="{$data.photo}" /></td>
		<td class="text">
			<div class="name">{$data.nickname}</div>
			<div class="subname">Email : {if $data.email}<a href="mailto:{$data.email}">{$data.email}</a>{else}<span class="disable">none</span>{/if}, Homepage : {if $data.homepage}<a href="{$data.homepage}" target="_blank">{$data.homepage}</a>{else}<span class="disable">none</span>{/if}</div>
		</td>
		<td class="infor">
			<div class="date">{$data.reg_date|date_format:"%Y-%m-%d, %I:%M %p"}</div>
			<div><span class="button" onclick="{$data.action.reply}">REPLY</span> <span class="bar">|</span> <span class="button"><a href="{$data.link.modify}" alt="수정">MODIFY</a></span> <span class="bar">|</span> <span class="button"><a href="{$data.link.delete}" alt="삭제" style="color:#960000;">DELETE</a></span></div>
		</td>
		<td class="right"></td>
	</tr>
	</table>

	<div class="mentbody">
		{$data.content}
		{if $data.last_modify.hit > 0}<div class="lastModify">Last Edited by <span class="bold">{$data.last_modify.editor}</span> At {$data.last_modify.date|date_format:"%Y/%m/%d %H:%M:%S"} <span class="bold">({$data.last_modify.hit|number_format})</span></div>{/if}
		{if $data.file}
		<ul class="filelist">
			{foreach name=file from=$data.file item=file}
				<li><a href="{$file.link}" target="downloadFrame">{$file.filename}</a> ({$file.filesize}, {$file.hit|number_format} Hit{if $file.hit > 1}s{/if})</li>
			{/foreach}
		</ul>
		{/if}
	</div>
	{/if}
	{$data.reply}

	{$data.replyEnd}
{/foreach}


<!-- MentWrite Start -->
{if $permission.ment == true}
	{$ment_write}
{/if}
<!-- MentWrite End -->
{$mentEnd}