<div id="toolbar">
	<h1>{$setup.title}</h1>
	<a id="backButton" class="button" href="{$link.list}">이전</a>
</div>

<div id="content" class="line">
{$formStart}
	<div class="height5"></div>
	<div class="titlebox">게시물 기본정보</div>
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="60" /><col width="100%" />
		<tr>
			<td class="header">제목</td>
			<td class="input"><input type="text" name="title" vlaue="{$post.title}" /></td>
		</tr>
		{if $categoryList}
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="header">분류</td>
			<td class="input">
				<select>
					<option value="">분류없음</option>
					{foreach from=$categoryList item=categoryList}
					<option value="{$categoryList.idx}"{if $post.category == $categoryList.idx} selected="selected"{/if}>{$categoryList.category}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		{/if}
		</table>
	</div>
	
	{if $member.idx == 0}
	<div class="titlebox">작성자 기본정보</div>
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="80" /><col width="100%" />
		<tr>
			<td class="header">이름</td>
			<td class="input"><input type="text" name="name" vlaue="{$post.name}" /></td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="header">패스워드</td>
			<td class="input"><input type="password" name="password" /></td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="header">이메일</td>
			<td class="input"><input type="email" name="email" value="{$post.email}" /></td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="header">홈페이지</td>
			<td class="input"><input type="text" name="homepage" value="{$post.homepage}" /></td>
		</tr>
		</table>
	</div>
	{/if}
	
	<div class="titlebox">내용</div>
	<div class="inputbox">
		<div class="textarea">
			<textarea name="content" class="TEXTAREA">{$post.content}</textarea>
		</div>
	</div>
	
	<div class="titlebox">게시물 옵션</div>
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="30" /><col width="100%" />
		<tr>
			<td class="check">
				<input type="checkbox" id="is_notice" name="is_notice" value="1"{if $post.is_notice == 'TRUE'} checked="checked"{/if} />
			</td>
			<td class="checkText">공지사항으로 설정</td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="check">
				<input type="checkbox" id="is_secret" name="is_secret" value="1"{if $post.is_secret == 'TRUE'} checked="checked"{/if} />
			</td>
			<td class="checkText">비밀글로 설정</td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="check">
				<input type="checkbox" id="is_ment" name="is_ment" value="1"{if $post.is_ment == 'TRUE'} checked="checked"{/if} />
			</td>
			<td class="checkText">댓글을 등록할 수 있도록 설정</td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="check">
				<input type="checkbox" id="is_trackback" name="is_trackback" value="1"{if $post.is_trackback == 'TRUE'} checked="checked"{/if} />
			</td>
			<td class="checkText">트랙백을 받을 수 있도록 설정</td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="check">
				<input type="checkbox" id="is_msg" name="is_msg" value="1"{if $post.is_msg == 'TRUE'} checked="checked"{/if} />
			</td>
			<td class="checkText">댓글등록시, 쪽지로 알림</td>
		</tr>
		<tr class="line">
			<td colspan="2"><div></div></td>
		</tr>
		<tr>
			<td class="check">
				<input type="checkbox" id="is_email" name="is_email" value="1"{if $post.is_email == 'TRUE'} checked="checked"{/if} />
			</td>
			<td class="checkText">댓글등록시, 이메일로 알림</td>
		</tr>
		</table>
	</div>

	{if $antispam}
	<div class="titlebox">스팸방지코드</div>
	<div class="inputbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100" /><col width="100%" />
		<tr>
			<td class="header">{$antispam}</td>
			<td class="input"><input type="text" name="antispam" /></td>
		</tr>
		</table>
		<script type="text/javascript">$(".AntiSpamImage").css("border",0).css("height",40).css("verticalAlign","middle");</script>
	</div>
	{/if}

	<div class="submitbox"><input type="submit" value="확인" /></div>
{$formEnd}
</div>

