{$formStart}
<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100" /><col width="1" /><col width="100%" /><col width="250" />
{if $mode == 'modify'}
<tr>
	<td colspan="4" class="sectionTitle">댓글 수정하기</td>
</tr>
{/if}
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
{if $member.idx == 0}
<tr>
	<td class="headerCell">작성자</td>
	<td class="splitBar"></td>
	<td class="bodyCell" colspan="2"><input type="text" name="name" style="width:120px;" value="{$data.name}" blank="이름을 입력하여 주십시오." /></td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">패스워드</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="password" name="password" style="width:120px;" value=""{if !$data.password} blank="패스워드를 입력하여 주십시오."{/if} /></td>
	<td class="right gray">{if $mode == 'post'}댓글을 수정/삭제할 때 사용됩니다.{else}패스워드를 변경하려면, 입력하세요.{/if}</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">이메일</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="text" name="email" style="width:95%;" value="{$data.email}" /></td>
	<td class="right gray">댓글을 해당메일로 받아 볼 수 있습니다.</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">홈페이지</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="text" name="homepage" style="width:95%;" value="{$data.homepage}" /></td>
	<td class="right gray">http:// 를 포함하여 입력하여 주세요.</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
{/if}
<tr>
	<td colspan="4">
		<div id="Wrap{$wysiwygName}">
			<textarea name="content" id="{$wysiwygName}" style="width:100%; height:150px;" blank="내용을 입력하여 주십시오.">{$data.content}</textarea>
		</div>
		<div class="height5"></div>
		{mRelease->PrintUploader type="ment" form=$formName id=$uploaderName skin="default" wysiwyg=$wysiwygName}
	</td>
</tr>
<tr>
	<td colspan="4">
		<div class="optionarea">
			<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $data.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">이 댓글에, 댓글이 입력될 경우 <span class="pointText">쪽지</span>로 해당 내용을 받아봅니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $data.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">이 댓글에, 댓글이 입력될 경우 <span class="pointText">이메일</span>로 해당 내용을 받아봅니다.</label></div>
		</div>
	</td>
</tr>
</table>

{if $antispam}
<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="180" /><col width="100%" />
<tr>
	<td class="sectionInfo">{$antispam}</td>
	<td class="antispam">
		스팸게시물 방지를 위하여, 왼쪽의 수식의 답에 해당하는 값을 입력하여 주십시오.<br />
		회원로그인을 하시면, 좀 더 편리하게 게시물을 작성할 수 있습니다.<br />
		<input type="text" name="antispam" style="width:100px;" />
	</td>
</tr>
</table>
{/if}

<div class="height5"></div>

<input type="submit" class="btn btn-primary btn-block btn-sm" value="{if $mode == 'modify'}댓글 수정하기{else}댓글 작성하기{/if}" />

<div class="height5"></div>

{$formEnd}