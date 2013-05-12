<ul>
	<li class="group">댓글보기</li>
</ul>
{foreach name=data from=$data item=data}
	{$data.replyStart}
	<div class="viewMentBody">
	{if $data.parent != '0'}<div class="reply"></div>{/if}
	{if $data.is_delete == true}
		<div class="mentdelete">아래의 답변댓글에 대한 원래의 댓글이 작성자 또는 관리자에 의해 삭제되었습니다.</div>
	{else}
	<div class="mentbody">
		<div class="viewMent">{$data.content}</div>
		<div class="viewMentInfo">{$data.nickname} At {$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}</div>
		<div class="viewMentLink"><a href="{$data.link.reply}">답글</a> | <a href="{$data.link.modify}">수정</a> | <a href="{$data.link.delete}" style="color:#960000;">삭제</a></div>
	</div>
	{/if}
	</div>
	{$data.reply}

	{$data.replyEnd}
{foreachelse}
<ul>
	<li>등록된 댓글이 없습니다.</li>
</ul>
{/foreach}