{$formStart}
<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="10" /><col width="100%" /><col width="10" />
<tr class="mentthin">
	<td class="left"></td>
	<td></td>
	<td class="right"></td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100" /><col width="100%" />
{if $member.idx == 0}
<tr>
	<td><img src="{$skinDir}/images/text_write_name.gif" /></td>
	<td>
		<input type="text" name="name" value="{$data.name}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:100px;" class="inputbox" blank="이름을 입력하여 주십시오." />
		<span class="infor">*</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_password.gif" /></td>
	<td>
		<input type="password" name="password" value="" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:100px;" class="inputbox"{if !$data.password} blank="패스워드를 입력하여 주십시오."{/if} />
		<span class="infor">* ({if $mode == 'post'}댓글을 수정/삭제할 때 사용됩니다.{else}패스워드를 변경하려면, 입력하세요.{/if})</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_email.gif" /></td>
	<td>
		<input type="text" name="email" value="{$data.email}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:200px;" class="inputbox" />
		<span class="infor"> (댓글을 해당메일로 받아 볼 수 있습니다.)</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_homepage.gif" /></td>
	<td><input type="text" name="homepage" value="{$data.homepage}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:300px;" class="inputbox" /></td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
{/if}
<tr>
	<td colspan="2">
		<div class="height5"></div>
		<div id="Wrap{$wysiwygName}">
		<textarea name="content" id="{$wysiwygName}" style="width:100%; height:150px;" blank="내용을 입력하여 주십시오.">{$data.content}</textarea>
		</div>
		<div class="height5"></div>
		{mBoard->PrintUploader type="ment" form=$formName id=$uploaderName skin="default" wysiwyg=$wysiwygName}
	</td>
</tr>
</table>

<div class="optionarea">
	<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $data.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">이 댓글에, 댓글이 입력될 경우 <span class="pointText">쪽지</span>로 해당 내용을 받아봅니다.</label></div>
	<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $data.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">이 댓글에, 댓글이 입력될 경우 <span class="pointText">이메일</span>로 해당 내용을 받아봅니다.</label></div>
</div>

{if $antispam}
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="180" /><col width="100%" />
<tr>
	<td>{$antispam}</td>
	<td class="antispam">
		스팸게시물 방지를 위하여, 왼쪽의 수식의 답에 해당하는 값을 입력하여 주십시오.<br />
		회원로그인을 하시면, 좀 더 편리하게 게시물을 작성할 수 있습니다.<br />
		<input type="text" name="antispam" style="width:100px;" />
	</td>
</tr>
</table>
{/if}

<div class="innerimg right"><input type="image" src="{$skinDir}/images/btn_{$mode}.gif" /></div>
{$formEnd}