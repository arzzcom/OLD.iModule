<div class="questionbox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="80" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/icon_question.gif" class="boxIcon" /></td>
		<td class="vTop">
			<div class="viewTitle">{$data.title}</div>
			<div class="viewInfo">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="25" /><col width="5" /><col width="100%" /><col width="250" />
				<tr>
					<td class="center"><img src="{$data.photo}" class="viewPhotoIcon" /></td>
					<td></td>
					<td class="viewInfoUser">
						<div class="innerLayer">{$data.nickname}</div>
						<div class="innerLayer gray">
							({if $data.mno == 0}비회원님이 질문한 글입니다.{else}질문 <span class="pointText">{$user.question|number_format}</span>건&nbsp;|&nbsp;질문마감률 <span class="pointText">{$user.complete}</span>{/if})
						</div>
					</td>
					<td class="viewInfoText">
						조회 <span class="pointText">{$data.hit|number_format}</span>
						&nbsp;|&nbsp;
						답변 <span class="pointText">{$data.answer|number_format}</span>
						&nbsp;|&nbsp;
						{$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}
					</td>
				</tr>
				</table>
			</div>
		</td>
	</tr>
	</table>
	
	<div class="viewContent">
		{$data.content}
		{if $file}
		<div class="height20"></div>
		<ul class="filelist">
			{foreach name=file from=$file item=file}
				<li><a href="{$file.link}" target="downloadFrame">{$file.filename}</a> ({$file.filesize}, {$file.hit|number_format} Hit{if $file.hit > 1}s{/if})</li>
			{/foreach}
		</ul>
		{/if}
	</div>
	
	<div class="mentAreaBox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="200" /><col width="100%" />
		<tr class="subButton">
			<td><div><span class="pointer" onclick="{$action.ment}">의견쓰기<span class="pointText">({$data.ment})</span></span></div></td>
			<td class="right">
				{if $permission.modify == true}
					{if $data.mno != '0'}아이디비공개{/if}{if $data.answer == 0}{if $data.mno != '0'}&nbsp;&nbsp;|&nbsp;&nbsp;{/if}<a href="{$link.modify_question}">수정</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="{$link.delete_question}">삭제</a>{/if}
				{/if}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				{$mentlist}
			</td>
		</tr>
		</table>
	</div>
	
	{if $permission.answer}
	<div class="writeAnswerbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100%" /><col width="180" />
		<tr>
			<td class="text">
				{if $data.is_complete == false}
				이 질문에 답변을 하시면 <span class="pointText">{$point.answer}포인트</span>를 받을 수 있습니다.<br />
				답변이 채택될 경우 추가로 <span class="pointText">{$point.select}포인트</span>와 질문자로부터 추가로 <span class="pointText">{$data.point}포인트</span>를 받을 수 있습니다.
				{else}
				이미 질문이 마감된 경우에는 <span class="pointText">추가로 답변을 등록할 수 없습니다</span>.<br />
				의견은 질문이 마감된 경우에도 등록하실 수 있습니다.
				{/if}
			</td>
			<td class="right">{if $data.is_complete == false}<a href="{$link.answer}"><img src="{$skinDir}/images/btn_my_answer.gif" class="pointer" /></a>{/if}</td>
		</tr>
		</table>
	</div>
	{/if}
	<div class="height20"></div>
</div>
{foreach name=list from=$answer item=answer}
<div class="{if $answer.is_select == true}selectedbox{else}answerbox{/if}">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="80" /><col width="100%" /><col width="140" />
	<tr>
		<td><img src="{$skinDir}/images/{if $answer.is_select == true}icon_myselect{else}icon_answer{/if}.gif" class="boxIcon" /></td>
		<td class="vTop">
			<div class="viewTitle">{$answer.title}</div>
			<div class="viewInfo">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="25" /><col width="5" /><col width="100%" />
				<tr>
					<td class="center"><img src="{$answer.photo}" class="viewPhotoIcon" /></td>
					<td></td>
					<td class="viewInfoUser">
						<div class="innerLayer">{$answer.nickname}</div>
						<div class="innerLayer gray">
							({if $answer.mno == 0}비회원님이 답변한 글입니다.{else}답변 <span class="pointText">{$answer.user.answer|number_format}</span>건&nbsp;|&nbsp;답변채택률 <span class="pointText">{$answer.user.selected}</span>{/if})
							&nbsp;|&nbsp;의견 <span class="pointText">{$data.ment|number_format}</span>
							&nbsp;|&nbsp;{$data.reg_date|date_format:"%Y.%m.%d %H:%M:%S"}
						</div>
					</td>
				</tr>
				</table>
			</div>
		</td>
		<td class="viewInfoText">
			<div class="button">
				{if $permission.select == true}
				<img src="{$skinDir}/images/btn_select_answer.gif" class="pointer" onclick="{$answer.action.select}" />
				{else}
				<div class="btnVote" onmouseover="this.className='btnVote over';" onmouseout="this.className='btnVote';">
					<div>{$answer.vote|number_format}</div>
				</div>
				{/if}
			</div>
		</td>
	</tr>
	</table>
	
	<div class="viewContent">
		{if $answer.is_select == true}
		<div class="thanksMessage">
			<span>질문자 감사인사</span><br />
			{$answer.thanks}
		</div>
		{/if}
	
		{$answer.content}
	</div>
	
	<div class="mentAreaBox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="200" /><col width="100%" />
		<tr class="subButton">
			<td><div><span class="pointer" onclick="{$answer.action.ment}">의견쓰기<span class="pointText">({$answer.ment})</span></span></div></td>
			<td class="right">
				{if $answer.permission.modify == true}
					{if $answer.mno != '0'}아이디비공개{/if}{if $answer.is_select == false}{if $answer.mno != '0'}&nbsp;&nbsp;|&nbsp;&nbsp;{/if}<a href="{$answer.link.modify}">수정</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="{$answer.link.delete}">삭제</a>{/if}
				{/if}
			</td>
		</tr>
		<tr>
			<td colspan="2">
				{$answer.mentlist}
			</td>
		</tr>
		</table>
	</div>
	<div class="height10"></div>
</div>
{/foreach}

{if $permission.select == true}
<div class="completebox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="100%" /><col width="200" />
	<tr>
		<td class="text">
			만족스러운 답변을 받지 못하셨다면, 채택 없이 질문을 마감하실 수 있습니다.<br />
			단, 정말 <span class="pointText">채택할 만한 답변이 없는지 다시 한 번 확인</span>해 주세요.
		</td>
		<td class="right"><img src="{$skinDir}/images/btn_complete_answer.gif" class="pointer" onclick="{$action.complete}" /></td>
	</tr>
	</table>
</div>
{/if}

<div class="height10"></div>