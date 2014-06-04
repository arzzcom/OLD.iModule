{$formStart}
<table cellpadding="0" cellspacing="0" class="boardTable">
<col width="100" /><col width="1" /><col width="100%" /><col width="300" />
<tr>
	<td colspan="4" class="sectionTitle">{if $mode == 'modify'}이미지 수정하기{else}이미지 등록하기{/if}</td>
</tr>
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
{if $member.idx == 0}
<tr>
	<td class="headerCell">작성자</td>
	<td class="splitBar"></td>
	<td colspan="2" class="bodyCell">
		<input type="text" name="name" value="{$post.name}" style="width:120px;" blank="이름을 입력하여 주십시오." autosave="true" class="input" />
	</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">패스워드</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="password" name="password" value="" style="width:120px;" {if !$post.password}blank="패스워드를 입력하여 주십시오."{/if} class="input" />
	</td>
	<td class="gray right">{if $post.password}패스워드를 변경하시려면, 입력하세요.{else}글을 수정/삭제하거나, 비밀글 열람시 사용됩니다.{/if}</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">이메일</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="text" name="email" value="{$post.email}" autosave="true" class="input" />
	</td>
	<td class="gray right">(댓글을 해당메일로 받아 볼 수 있습니다.)</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">홈페이지</td>
	<td class="splitBar"></td>
	<td colspan="2" class="bodyCell"><input type="text" name="homepage" value="{$post.homepage}" autosave="true" class="input" /></td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
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
	<td colspan="4"></td>
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
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4">
		<div class="height5"></div>
		<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
		<div class="height5"></div>
		{mBoard->PrintWysiwyg id="content"}
		{mBoard->PrintUploader type="post" form=$formName id="uploader" skin="default" wysiwyg="content"}
		
		<div class="height5"></div>
		<div class="boxAlert">
			여러장의 이미지를 한번에 업로드할 수 있으며, 이미지파일의 경우 본문에 삽입하지 않더라도 갤러리형식으로 자동으로 보이게 됩니다.<br />
			가급적 이미지 첨부 후 해당 이미지를 본문에 삽입하지 않는 것을 권장합니다.
		</div>
	</td>
</tr>
<tr>
	<td colspan="4">
		<div class="optionarea">
			<div class="checkbox"><input type="checkbox" id="is_notice" name="is_notice" value="1"{if $post.is_notice == 'TRUE'} checked="checked"{/if} /><label for="is_notice">해당글을 <span class="pointText">공지사항으로 설정</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_html_title" name="is_html_title" value="1"{if $post.is_html_title == 'TRUE'} checked="checked"{/if} /><label for="is_html_title">해당글의 제목에 <span class="pointText">HTML태그를 사용</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_secret" name="is_secret" value="1"{if $post.is_secret == 'TRUE'} checked="checked"{/if} /><label for="is_secret">작성자와 관리권한을 가진 회원만 확인할 수 있는 <span class="pointText">"비밀글" 상태로 등록</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_ment" name="is_ment" value="1"{if $post.is_ment == 'TRUE'} checked="checked"{/if} /><label for="is_ment">이 글에, 댓글을 입력할 수 있는 권한을 가진 다른유저가 <span class="pointText">댓글을 입력할 수 있도록 설정</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_trackback" name="is_trackback" value="1"{if $post.is_trackback == 'TRUE'} checked="checked"{/if} /><label for="is_trackback">이 글에, 트랙백을 보낼 수 있는 다른 곳에서의 <span class="pointText">트랙백을 받을 수 있도록 설정</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $post.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">이 글에, 댓글이 입력될 경우 <span class="pointText">쪽지</span>로 해당 내용을 받아봅니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $post.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">이 글에, 댓글이 입력될 경우 <span class="pointText">이메일</span>로 해당 내용을 받아봅니다.</label></div>
		</div>
	</td>
</tr>
{if $antispam}
<tr>
	<td colspan="4">
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
	</td>
</tr>
{/if}
<tr class="height5">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4">
		<input type="submit" class="btn btn-sm btn-primary btn-block" value="{if $mode == 'modify'}이미지 수정하기{else}이미지 등록하기{/if}" />
	</td>
</tr>
<tr class="height5">
	<td colspan="4"></td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr class="sectionEnd">
	<td colspan="4"><div></div></td>
</tr>
</table>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="50%" /><col width="50%" />
<tr>
	<td>
		<a href="{$link.list}" class="btn btn-sm btn-default">목록보기</a>
	</td>
	<td></td>
</tr>
</table>
{$formEnd}