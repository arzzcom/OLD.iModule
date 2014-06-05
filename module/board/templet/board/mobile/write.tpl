{$formStart}
<table cellpadding="0" cellspacing="0" class="boardTable">
<col width="80" /><col width="1" /><col width="100%" />
<tr class="sectionBar">
	<td colspan="3"></td>
</tr>
{if $member.idx == 0}
<tr>
	<td class="headerCell">작성자</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="text" name="name" value="{$post.name}" style="width:120px;" blank="이름을 입력하여 주십시오." autosave="true" class="input" />
	</td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">패스워드</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="password" name="password" value="" style="width:120px;" {if !$post.password}blank="패스워드를 입력하여 주십시오."{/if} class="input" />
	</td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">이메일</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="text" name="email" value="{$post.email}" autosave="true" class="input" />
	</td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">홈페이지</td>
	<td class="splitBar"></td>
	<td class="bodyCell"><input type="text" name="homepage" value="{$post.homepage}" autosave="true" class="input" /></td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
{/if}
<tr>
	<td class="headerCell">제목</td>
	<td class="splitBar"></td>
	<td colspan="2" class="bodyCell">
		<input type="text" name="title" value="{$post.title}" blank="제목을 입력하여 주십시오." autosave="true" class="input" />
	</td>
</tr>
{if $setup.use_category != 'FALSE'}
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td class="headerCell">분류</td>
	<td class="splitBar"></td>
	<td>
		<input type="hidden" name="category" value="{$post.category}" />
		<div style="margin:5px 0px 5px 10px;">
		<div id="iBoardCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iBoardCategory')" clicker="iBoardCategory">{if $categoryName}{$categoryName}{else}카테고리{/if}</div>

			<ul style="display:none;" clicker="iBoardCategory">
				{if $setup.use_category == 'OPTION'}<li onclick="InputSelectBoxSelect('iBoardCategory','분류없음','',WriteSelectCategory)">분류없음</li>{/if}
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iBoardCategory','{$categoryList.category}','{$categoryList.idx}',WriteSelectCategory)">{$categoryList.category}</li>
				{/foreach}
			</ul>
		</div>
		</div>
	</td>
</tr>
{/if}
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr>
	<td colspan="3" style="padding:5px 5px 0px 5px;">
		<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
		<div class="height5"></div>
		{mBoard->PrintWysiwyg id="content"}
		{mBoard->PrintUploader type="post" form=$formName id="uploader" skin="mobile" wysiwyg="content"}
	</td>
</tr>
<tr>
	<td colspan="3" style="padding:0 5px;">
		<div class="optionarea">
			<div class="checkbox"><input type="checkbox" id="is_notice" name="is_notice" value="1"{if $post.is_notice == 'TRUE'} checked="checked"{/if} /><label for="is_notice">공지사항 설정</label></div>
			<div class="checkbox"><input type="checkbox" id="is_html_title" name="is_html_title" value="1"{if $post.is_html_title == 'TRUE'} checked="checked"{/if} /><label for="is_html_title">HTML 제목 사용</label></div>
			<div class="checkbox"><input type="checkbox" id="is_secret" name="is_secret" value="1"{if $post.is_secret == 'TRUE'} checked="checked"{/if} /><label for="is_secret">비밀글 설정</label></div>
			<div class="checkbox"><input type="checkbox" id="is_ment" name="is_ment" value="1"{if $post.is_ment == 'TRUE'} checked="checked"{/if} /><label for="is_ment">댓글작성허용</label></div>
			<div class="checkbox"><input type="checkbox" id="is_trackback" name="is_trackback" value="1"{if $post.is_trackback == 'TRUE'} checked="checked"{/if} /><label for="is_trackback">트랙백 허용</label></div>
			<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $post.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">댓글알림 쪽지 받음</label></div>
			<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $post.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">댓글알림 이메일 받음</label></div>
		</div>
	</td>
</tr>
{if $antispam}
<tr>
	<td colspan="3" style="padding:0 5px;">
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
	</td>
</tr>
{/if}
<tr class="height5">
	<td colspan="3"></td>
</tr>
<tr>
	<td colspan="3" style="padding:0 5px;">
		<input type="submit" class="btn btn-sm btn-primary btn-block" value="{if $mode == 'modify'}게시물 수정하기{else}게시물 등록하기{/if}" />
	</td>
</tr>
<tr class="height5">
	<td colspan="3"></td>
</tr>
<tr class="splitBar">
	<td colspan="3"></td>
</tr>
<tr class="sectionEnd">
	<td colspan="3"><div></div></td>
</tr>
</table>

<div class="padding5">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="50%" /><col width="50%" />
	<tr>
		<td>
			<a href="{$link.list}" class="btn btn-sm btn-default">목록</a>
		</td>
		<td></td>
	</tr>
	</table>
</div>
{$formEnd}