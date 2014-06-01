{$formStart}

{if $member.idx == 0}
<input type="text" name="name" class="form-control input-sm" placeholder="작성자">
<div class="height5"></div>
<input type="password" name="password" class="form-control input-sm" placeholder="패스워드">
<div class="height5"></div>
<input type="text" name="email" class="form-control input-sm" placeholder="이메일">
<div class="height5"></div>
<input type="text" name="homepage" class="form-control input-sm" placeholder="홈페이지">
<div class="height5"></div>
{/if}

<div id="Wrap{$wysiwygName}">
	<textarea name="content" id="{$wysiwygName}" style="width:100%; height:150px;" blank="내용을 입력하여 주십시오.">{$data.content}</textarea>
</div>
<div class="height5"></div>
{mBoard->PrintUploader type="ment" form=$formName id=$uploaderName skin="default" wysiwyg=$wysiwygName}
<div class="height5"></div>

{if $antispam}
<div class="alert alert-warning">
	스팸게시물 방지를 위하여, 왼쪽의 수식의 답에 해당하는 값을 입력하여 주십시오.
	<div class="height5"></div>
	{$antispam}
	<div class="height5"></div>
	<input type="text" name="antispam" class="form-control input-sm" />
</div>
{/if}

<input type="submit" class="btn btn-primary btn-block btn-sm" value="{if $mode == 'modify'}댓글 수정하기{else}댓글 작성하기{/if}" />
{$formEnd}