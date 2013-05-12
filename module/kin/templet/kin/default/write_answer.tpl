{$formStart}
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
		<div class="height10"></div>
		<ul class="filelist">
			{foreach name=file from=$file item=file}
				<li><a href="{$file.link}" target="downloadFrame">{$file.filename}</a> ({$file.filesize}, {$file.hit|number_format} Hit{if $file.hit > 1}s{/if})</li>
			{/foreach}
		</ul>
		{/if}
	</div>
</div>

<div class="writeBoxTitle" style="border-top:0px;">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="80" /><col width="100%" />
	<tr>
		<td class="right"><img src="{$skinDir}/images/text_answer.gif" /></td>
		<td class="right"><input type="text" name="title" class="inputTitle" blank="제목을 입력하여 주십시오." value="{$post.title}" autosave="true" /></td>
	</tr>
	</table>
</div>

<div>
	<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
	{mKin->PrintWysiwyg id="content"}
</div>

<div class="writeBox">
	{mKin->PrintUploader type='answer' form=$formName id="uploader" skin="kin" wysiwyg="content"}
</div>

<div class="writeBox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="120" /><col width="100%" />
	<tr>
		<td><div class="formHeader">자료출처</div></td>
		<td><input type="text" name="resource" value="{$post.resource}" class="inputLong" /></td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">익명답변</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input id="is_hidename" type="checkbox" name="is_hidename" value="1"{if $post.is_hidename == true} checked="checked"{/if} /></td>
				<td class="f12 gray"><label for="is_hidename">답변을 작성한 작성자 정보를 공개하지 않습니다. (단 질문채택율은 공개됩니다.)</label></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>

<div class="writeBox">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="120" /><col width="100%" />
	{if $member.idx == 0}
	<tr>
		<td><div class="formHeader">작성자이름</div></td>
		<td><input type="text" name="name" value="{$post.name}" class="inputShort" /></td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">이메일주소</div></td>
		<td><input type="text" name="email" value="{$post.email}" class="inputLong" /></td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td><div class="formHeader">패스워드</div></td>
		<td><input type="password" name="password" value="" class="inputShort" /> <span class="f11 gray">{if $post.password}패스워드를 수정하려면 입력하세요.{else}답변을 수정하거나, 답변을 삭제할 경우 필요합니다.{/if}</td>
	</tr>
	<tr class="writeLine">
		<td colspan="2"></td>
	</tr>
	{/if}
	<tr>
		<td><div class="formHeader">이용동의</div></td>
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="20" /><col width="100%" />
			<tr>
				<td><input id="is_agree" type="checkbox" name="is_agree"{if $post.is_agree == true} checked="checked"{/if} /></td>
				<td class="f12 gray"><label for="is_agree">답변채택시나, 질문마감시 답변내용을 수정하거나 삭제할 수 없다는 것에 동의합니다.</label></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>

{if $antispam}
<div class="writeBox padding10">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="170" /><col width="100%" />
	<tr>
		<td>{$antispam}</td>
		<td class="antispam">
			스팸게시물 방지를 위하여, 왼쪽의 수식의 답에 해당하는 값을 입력하여 주십시오.<br />
			회원로그인을 하시면, 좀 더 편리하게 게시물을 작성할 수 있습니다.<br />
			<input type="text" name="antispam" style="width:100px;" />
		</td>
	</tr>
	</table>
</div>
{/if}

<div class="height10"></div>

<div class="center">
	<input type="image" src="{$skinDir}/images/btn_write_question.gif" />
	<a href="{$link.list}"><img src="{$skinDir}/images/btn_cancel.gif" /></a>
</div>

{$formEnd}