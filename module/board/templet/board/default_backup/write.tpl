{$formStart}
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="100%" /><col width="5" />
<tr class="viewbar">
	<td class="left"></td>
	<td class="title">글 작성하기</td>
	<td class="right"></td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100" /><col width="100%" />
{if $member.idx == 0}
<tr>
	<td><img src="{$skinDir}/images/text_write_name.gif" /></td>
	<td>
		<input type="text" name="name" value="{$post.name}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:100px;" class="inputbox" blank="이름을 입력하여 주십시오." autosave="true" />
		<span class="infor">*</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_password.gif" /></td>
	<td>
		<input type="password" name="password" value="" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:100px;" class="inputbox" {if !$post.password}blank="패스워드를 입력하여 주십시오."{/if} />
		<span class="infor">* ({if $post.password}패스워드를 변경하시려면, 입력하세요.{else}글을 수정/삭제하거나, 비밀글 열람시 사용됩니다.{/if})</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_email.gif" /></td>
	<td>
		<input type="text" name="email" value="{$post.email}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:200px;" class="inputbox" autosave="true" />
		<span class="infor"> (댓글을 해당메일로 받아 볼 수 있습니다.)</span>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_homepage.gif" /></td>
	<td><input type="text" name="homepage" value="{$post.homepage}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:300px;" class="inputbox" autosave="true" /></td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
{/if}
<tr>
	<td><img src="{$skinDir}/images/text_write_title.gif" /></td>
	<td>
		<input type="text" name="title" value="{$post.title}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:90%;" class="inputbox" blank="제목을 입력하여 주십시오." autosave="true" />
		<span class="infor">*</span>
	</td>
</tr>
{if $categoryList}
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_category.gif" /></td>
	<td>
		<input type="hidden" name="category" value="{$post.category}" />
		<div style="margin:5px 0px 5px 10px;">
		<div id="iBoardCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iBoardCategory')" clicker="iBoardCategory">{if $categoryName}{$categoryName}{else}카테고리{/if}</div>

			<ul style="display:none;" clicker="iBoardCategory">
				<li onclick="InputSelectBoxSelect('iBoardCategory','분류없음','',WriteSelectCategory)">분류없음</li>
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iBoardCategory','{$categoryList.category|replace:"'":"\'"}','{$categoryList.idx}',WriteSelectCategory)">{$categoryList.category}</li>
				{/foreach}
			</ul>
		</div>
		</div>
	</td>
</tr>
{/if}
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td colspan="2">
		<div class="height5"></div>
		<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
		<div class="height5"></div>
		{mBoard->PrintWysiwyg id="content"}
		{mBoard->PrintUploader type="post" form=$formName id="uploader" skin="default" wysiwyg="content"}
	</td>
</tr>
<tr>
	<td colspan="2">
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
	<td colspan="2">
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
</table>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="10" /><col width="100%" /><col width="10" />
<tr class="mentthin">
	<td class="left"></td>
	<td></td>
	<td class="right"></td>
</tr>
</table>

<div class="height5"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="50%" /><col width="50%" />
<tr>
	<td class="innerimg">
		<a href="{$link.list}"><img src="{$skinDir}/images/btn_list.gif" style="margin-right:3px;" /></a>
	</td>
	<td class="innerimg right">
		<input type="image" src="{$skinDir}/images/btn_{$mode}.gif" />
	</td>
</tr>
</table>{$formEnd}