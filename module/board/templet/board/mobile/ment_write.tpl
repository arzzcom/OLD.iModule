{$formStart}
<table cellpadding="0" cellspacing="0" class="boardTable">
<col width="100" /><col width="1" /><col width="100%" />
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
{if $member.idx == 0}
<tr>
	<td class="headerCell">작성자</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="text" name="name" style="width:120px;" value="{$data.name}" blank="이름을 입력하여 주십시오." class="input" /></td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">패스워드</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="password" name="password" style="width:120px;" value=""{if !$data.password} blank="패스워드를 입력하여 주십시오."{/if} class="input" /></td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">이메일</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="text" name="email" value="{$data.email}" class="input" /></td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">홈페이지</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="text" name="homepage" value="{$data.homepage}" class="input" /></td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
{/if}
<tr>
	<td colspan="3" style="padding:5px 5px 0px 5px;">
		<div id="Wrap{$wysiwygName}">
			<textarea name="content" id="{$wysiwygName}" style="width:100%; height:150px;" blank="내용을 입력하여 주십시오.">{$data.content}</textarea>
		</div>
		<div class="height5"></div>
		{mBoard->PrintUploader type="ment" form=$formName id=$uploaderName skin="mobile" wysiwyg=$wysiwygName}
	</td>
</tr>
<tr>
	<td colspan="3" style="padding:0px 5px;">
		<div class="optionarea">
			<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $data.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">댓글알림 쪽지로 받음</label></div>
			<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $data.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">댓글알림 이메일로 받음</label></div>
		</div>
	</td>
</tr>
</table>

{if $antispam}
<div style="padding:0px 5px;">
	<table cellpadding="0" cellspacing="0" class="boardTable">
	<col width="165" /><col width="100%" />
	<tr>
		<td class="sectionInfo">{$antispam}</td>
		<td class="antispam">
			<div style="height:35px; line-height:1.6;">스팸방지를 위해, 왼쪽의 수식의 답을 입력하여 주십시오.</div>
			<input type="text" name="antispam" class="input" />
		</td>
	</tr>
	</table>
</div>
{/if}

<div class="padding5">
	<input type="submit" class="btn btn-primary btn-block btn-sm" value="{if $mode == 'modify'}댓글 수정하기{else}댓글 작성하기{/if}" />
</div>

{$formEnd}