<div class="boxBlue">
	<span class="pointText">*</span>표시가 있는 항목은 필수항목으로, 모두 입력하여 주셔야 합니다.<br />
	카테고리를 선택하여야만, 등록폼이 로딩되므로, 먼저 카테고리를 선택하여 주시기 바랍니다.<br />
	업로드를 하기전에 업로드 규칙을 먼저 읽어보신 후 업로드를 하여 주십시오.<br /><br />
	Items marked with <span class="pointText">*</span>, enter all the required items, must<br />
	Must select a category, the registration form is loaded first, so select the category that please.<br />
	Please read the upload rules first before upload upload.
</div>

<div class="height10"></div>

{$formStart}
<input type="hidden" name="category1" value="{$category1}" />
<input type="hidden" name="category2" value="{$category2}" />
<input type="hidden" name="category3" value="{$category3}" />
{if $mode == 'write'}
<table cellspacing="1" cellpadding="0" class="trackerFormTable">
<col width="110" /><col width="100%" />
<tr>
	<td class="header">어나운스 주소<br />Announce URL</td>
	<td class="content">
		<input type="text" class="inputbox" style="width:592px;" value="{$announce}" onclick="this.select();" />
		
		<div class="formInfo">
			회원님 개인에게만  고유의 어나운스 주소입니다.<br />
			해당주소가 노출되지 않도록 주의하여 주시기 바라며, 토렌트파일을 생성할 때 트래커주소에 위의 주소를 입력하신 후 토렌트파일을 생성하시면 됩니다.<br />
			Address is only for your personal, unique've announced.<br />
			Careful not to expose the address, please Tracker Address enter the address above when you create a torrent file The torrent file can be created.
		</div>
	</td>
</tr>
{if $addmode == false}
<tr>
	<td class="header">카테고리<span class="pointText">*</span><br />Category<span class="pointText">*</span></td>
	<td class="content">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="155" /><col width="155" /><col width="100%" />
		<tr>
			<td>
				<div id="TrackerCategory1" class="selectbox" style="width:150px;">
					<div onclick="InputSelectBox('TrackerCategory1')" clicker="TrackerCategory1">{if $categoryName1}{$categoryName1}{else}전체보기{/if}</div>
					<ul style="display:none;" clicker="TrackerCategory1">
						<li onclick="InputSelectBoxSelect('TrackerCategory1','전체보기','',TrackerListSelectCategory1)">전체보기</li>
						{foreach from=$categoryList1 item=categoryList1}
						<li onclick="InputSelectBoxSelect('TrackerCategory1','{$categoryList1.title}','{$categoryList1.idx}',TrackerListSelectCategory1)">{$categoryList1.title}</li>
						{/foreach}
					</ul>
				</div>
			</td>
			<td>
				<div id="TrackerCategory2" class="selectbox" style="width:150px; display:{if !$categoryList2}none{/if};">
					<div onclick="InputSelectBox('TrackerCategory2')" clicker="TrackerCategory2">{if $categoryName2}{$categoryName2}{else}전체보기{/if}</div>
					<ul style="display:none;" clicker="TrackerCategory2">
						<li onclick="InputSelectBoxSelect('TrackerCategory2','전체보기','',TrackerListSelectCategory2)">전체보기</li>
						{foreach from=$categoryList2 item=categoryList2}
						<li onclick="InputSelectBoxSelect('TrackerCategory2','{$categoryList2.title}','{$categoryList2.idx}',TrackerListSelectCategory2)">{$categoryList2.title}</li>
						{/foreach}
					</ul>
				</div>
			</td>
			<td>
				<div id="TrackerCategory3" class="selectbox" style="width:150px; display:{if !$categoryList3}none{/if};">
					<div onclick="InputSelectBox('TrackerCategory3')" clicker="TrackerCategory3">{if $categoryName3}{$categoryName3}{else}전체보기{/if}</div>
					<ul style="display:none;" clicker="TrackerCategory3">
						<li onclick="InputSelectBoxSelect('TrackerCategory3','전체보기','',TrackerListSelectCategory3)">전체보기</li>
						{foreach from=$categoryList2 item=categoryList2}
						<li onclick="InputSelectBoxSelect('TrackerCategory3','{$categoryList3.title}','{$categoryList3.idx}',TrackerListSelectCategory3)">{$categoryList3.title}</li>
						{/foreach}
					</ul>
				</div>
			</td>
		</tr>
		</table>
		
		<div class="formInfo">
			카테고리를 선택하면, 해당 카테고리에 해당하는 등록폼이 로딩됩니다.<br />
			카테고리는 가급적 최하위 단계까지 선택하여 주십시오.<br />
			Select the category that corresponds to the appropriate category, if the registration form is loaded.<br />
			Categories by selecting from the lowest level possible, please.
		</div>
	</td>
</tr>
{/if}
</table>
<div class="height5"></div>
{/if}
{$formEnd}

{$innerForm}

