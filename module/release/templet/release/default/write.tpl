{$formStart}
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="100%" /><col width="5" />
<tr class="viewbar">
	<td class="left"></td>
	<td class="title">프로그램 등록하기</td>
	<td class="right"></td>
</tr>
</table>

<div class="height5"></div>

<div class="boxDefault">
	이 페이지는 기존 프로그램의 버전업데이트 등록이 아닌 새로운 프로그램을 등록페이지입니다.<br />
	만약 새로운 프로그램이 아니라 기존 프로그램의 새로운 버전을 등록하길 원하는 경우, 나의 프로그램 목록에서 새로운 버전을 등록하고자 하는 프로그램를 찾아 프로그램 상세보기 화면에서 새 버전 등록하기 페이지를 이용하시기 바랍니다.
</div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100" /><col width="100%" />
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
		<div id="iReleaseCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iReleaseCategory')" clicker="iReleaseCategory">{if $categoryName}{$categoryName}{else}카테고리{/if}</div>

			<ul style="display:none;" clicker="iReleaseCategory">
				<li onclick="InputSelectBoxSelect('iReleaseCategory','분류없음','',WriteSelectCategory)">분류없음</li>
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iReleaseCategory','{$categoryList.category|replace:"'":"\'"}','{$categoryList.idx}',WriteSelectCategory)">{$categoryList.category}</li>
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
	<td colspan="2"><img src="{$skinDir}/images/text_write_intro.gif" style="margin:5px 0px;" /></td>
</tr>
<tr>
	<td colspan="2">
		<textarea name="content" id="content" style="width:100%; height:400px;" blank="내용을 입력하여 주십시오." autosave="true" opserve="true">{$post.content}</textarea>
		<div class="height5"></div>
		{mRelease->PrintWysiwyg id="content"}
		{mRelease->PrintUploader type="post" form=$formName id="uploader" skin="default" wysiwyg="content"}
		<div class="height5"></div>
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
<tr>
	<td><img src="{$skinDir}/images/text_write_license.gif" /></td>
	<td>
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="170" /><col width="100%" />
		<tr>
			<td>
				<input type="hidden" name="license" value="{$post.license}" />
				<div style="margin:5px 0px 5px 10px;">
					<div id="iReleaseLicense" class="selectbox" style="width:150px;">
						<div onclick="InputSelectBox('iReleaseLicense')" clicker="iReleaseLicense">{if $post.license}{$post.license}{else}라이센스{/if}</div>
			
						<ul style="display:none;" clicker="iReleaseLicense">
							<li onclick="InputSelectBoxSelect('iReleaseLicense','GPL v2','GPL v2',WriteSelectLicense)">GPL v2</li>
							<li onclick="InputSelectBoxSelect('iReleaseLicense','LGPL v2','LGPL v2',WriteSelectLicense)">LGPL v2</li>
							<li onclick="InputSelectBoxSelect('iReleaseLicense','GPL v3','GPL v3',WriteSelectLicense)">GPL v3</li>
							<li onclick="InputSelectBoxSelect('iReleaseLicense','LGPL v3','LGPL v3',WriteSelectLicense)">LGPL v3</li>
							<li onclick="InputSelectBoxSelect('iReleaseLicense','MIT License','MIT License',WriteSelectLicense)">MIT License</li>
							<li onclick="InputSelectBoxSelect('iReleaseLicense','Apache License 2.0','Apache License 2.0',WriteSelectLicense)">Apache License 2.0</li>
							<li onclick="InputSelectBoxSelect('iReleaseLicense','기타 라이센스','기타 라이센스',WriteSelectLicense)">기타 라이센스</li>
						</ul>
					</div>
				</div>
			</td>
			<td>
				<span class="infor">* (기타 라이센스일 경우 프로그램소개에 라이센스를 명시하여 주십시오.)</span>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_logo.gif" /></td>
	<td>
		<input type="file" name="logo" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:90%;" class="inputbox" autosave="true" />
	</td>
</tr>
<tr class="dashed">
	<td colspan="2"></td>
</tr>
<tr>
	<td><img src="{$skinDir}/images/text_write_price.gif" /></td>
	<td>
		<input type="text" name="price" value="{$post.price}" onfocus="this.className='inputbox-on';" onblur="this.className='inputbox';" style="width:100px;" class="inputbox" autosave="true" />
		<span class="infor">* (유료로 판매하길 원하는 경우 판매포인트를 입력하여 주세요. 수수료 : {$setup.tax_point}%)</span>
	</td>
</tr>
<tr>
	<td colspan="2">
		<div class="optionarea">
			<div class="checkbox"><input type="checkbox" id="is_html_title" name="is_html_title" value="1"{if $post.is_html_title == 'TRUE'} checked="checked"{/if} /><label for="is_html_title">해당글의 제목에 <span class="pointText">HTML태그를 사용</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_ment" name="is_ment" value="1"{if $post.is_ment == 'TRUE'} checked="checked"{/if} /><label for="is_ment">이 글에, 댓글을 입력할 수 있는 권한을 가진 다른유저가 <span class="pointText">댓글을 입력할 수 있도록 설정</span>합니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_msg" name="is_msg" value="1"{if $post.is_msg == 'TRUE'} checked="checked"{/if} /><label for="is_msg">이 글에, 댓글이 입력될 경우 <span class="pointText">쪽지</span>로 해당 내용을 받아봅니다.</label></div>
			<div class="checkbox"><input type="checkbox" id="is_email" name="is_email" value="1"{if $post.is_email == 'TRUE'} checked="checked"{/if} /><label for="is_email">이 글에, 댓글이 입력될 경우 <span class="pointText">이메일</span>로 해당 내용을 받아봅니다.</label></div>
		</div>
	</td>
</tr>
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