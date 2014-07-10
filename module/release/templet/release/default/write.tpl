{$formStart}
<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100" /><col width="1" /><col width="100%" /><col width="300" />
<tr>
	<td colspan="4" class="sectionTitle">{if $mode == 'modify'}프로그램 수정하기{else}프로그램 등록하기{/if}</td>
</tr>
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4" class="sectionInfo">
		이 페이지는 기존 프로그램의 버전업데이트 등록이 아닌 새로운 프로그램을 등록페이지입니다.<br />
		만약 새로운 프로그램이 아니라 기존 프로그램의 새로운 버전을 등록하길 원하는 경우, 나의 프로그램 목록에서 새로운 버전을 등록하고자 하는 프로그램를 찾아 프로그램 상세보기 화면에서 새 버전 등록하기 페이지를 이용하시기 바랍니다.
	</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">프로그램명</td>
	<td class="splitBar"></td>
	<td colspan="2" class="bodyCell">
		<input type="text" name="title" value="{$post.title}" blank="제목을 입력하여 주십시오." class="input" />
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
		<div id="iReleaseCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iReleaseCategory')" clicker="iReleaseCategory">{if $categoryName}{$categoryName}{else}카테고리{/if}</div>

			<ul style="display:none;" clicker="iReleaseCategory">
				{if $setup.use_category == 'OPTION'}<li onclick="InputSelectBoxSelect('iReleaseCategory','분류없음','',WriteSelectCategory)">분류없음</li>{/if}
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iReleaseCategory','{$categoryList.category}','{$categoryList.idx}',WriteSelectCategory)">{$categoryList.category}</li>
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
	<td class="headerCell">홈페이지</td>
	<td class="splitBar"></td>
	<td colspan="2" class="bodyCell">
		<input type="text" name="homepage" value="{$post.homepage}" class="input" />
	</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">라이센스</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="hidden" name="license" value="{$post.license}" />
		<div class="drop" style="width:150px;" form="ModuleReleasePost" field="license">
			<button>{if $post.license}{$post.license}{else}라이센스{/if} <span class="arrow"></span></button>
			<ul>
				<li value="GPL v2">GPL v2</li>
				<li value="LGPL v2">LGPL v2</li>
				<li value="GPL v3">GPL v3</li>
				<li value="LGPL v3">LGPL v3</li>
				<li value="MIT License">MIT License</li>
				<li value="Apache License 2.0">Apache License 2.0</li>
				<li value="기타 라이센스">기타 라이센스</li>
			</ul>
		</div>
	</td>
	<td class="bodyCell right gray">기타 라이센스일 경우 프로그램소개에 라이센스를 명시</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">프로그램가격</td>
	<td class="splitBar"></td>
	<td class="bodyCell">
		<input type="text" name="price" value="{$post.price}" style="width:100px;" class="input" /> P(포인트)
	</td>
	<td class="bodyCell gray right">유료판매를 원할경우 가격을 입력 (판매수수료 : {$setup.tax_point}%)</td>
</tr>
<tr class="splitBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td class="headerCell">프로그램로고</td>
	<td class="splitBar"></td>
	<td colspan="2" class="bodyCell">
		<input type="file" name="logo" class="input" />
	</td>
</tr>
<tr class="sectionEnd">
	<td colspan="4"><div></div></td>
</tr>
<tr class="height10">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4" class="sectionTitle">프로그램 소개</td>
</tr>
<tr class="sectionBar">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4">
		<div class="height5"></div>
		<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오.">{$post.content}</textarea>
		<div class="height5"></div>
		{mRelease->PrintWysiwyg id="content"}
		{mRelease->PrintUploader type="post" form=$formName id="uploader" skin="default" wysiwyg="content"}
	</td>
</tr>
<tr>
	<td colspan="4">
		<div class="optionarea">
			<div class="checkbox"><input type="checkbox" id="is_ment" name="is_ment" value="1"{if $post.is_ment == 'TRUE'} checked="checked"{/if} /><label for="is_ment">이 글에, 댓글을 입력할 수 있는 권한을 가진 다른유저가 <span class="pointText">댓글을 입력할 수 있도록 설정</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $post.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">이 글에, 댓글이 입력될 경우 <span class="pointText">쪽지</span>로 해당 내용을 받아봅니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $post.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">이 글에, 댓글이 입력될 경우 <span class="pointText">이메일</span>로 해당 내용을 받아봅니다.</label></div>
		</div>
	</td>
</tr>
<tr class="height5">
	<td colspan="4"></td>
</tr>
<tr>
	<td colspan="4">
		<input type="submit" class="btn btn-sm btn-primary btn-block" value="{if $mode == 'modify'}프로그램 수정하기{else}프로그램 등록하기{/if}" />
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