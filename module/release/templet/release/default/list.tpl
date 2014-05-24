<table cellpadding="0" cellspacing="0" class="releaseTable">
<col width="100%" />{if $categoryList}<col width="152" />{/if}<col width="120" />
<tr>
	<td class="sectionTitle">목록보기</td>
	{if $categoryList}
	<td>
		<div id="iReleaseCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iReleaseCategory')" clicker="iReleaseCategory">{if $categoryName}{$categoryName}{else}전체보기{/if}</div>

			<ul style="display:none;" clicker="iReleaseCategory">
				<li onclick="InputSelectBoxSelect('iReleaseCategory','전체보기','',ListSelectCategory)">전체보기</li>
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iReleaseCategory','{$categoryList.category}','{$categoryList.idx}',ListSelectCategory)">{$categoryList.category}</li>
				{/foreach}
			</ul>
		</div>
	</td>
	{/if}
	<td>
		<div id="iReleaseSelectSort" class="selectbox" style="width:120px;">
			<div onclick="InputSelectBox('iReleaseSelectSort')" clicker="iReleaseSelectSort">{if $sort == 'idx'}최신업데이트순{elseif $sort == 'download'}다운로드순{elseif $sort == 'vote'}추천순{else}높은가격순{/if}</div>

			<ul style="display:none;" clicker="iReleaseSelectSort">
				<li onclick="InputSelectBoxSelect('iReleaseSelectSort','최신업데이트순','{mRelease->GetSortLink sort="idx"}',ListSelectSelectSort)">최신업데이트순</li>
				<li onclick="InputSelectBoxSelect('iReleaseSelectSort','다운로드순','{mRelease->GetSortLink sort="download"}',ListSelectSelectSort)">다운로드순</li>
				<li onclick="InputSelectBoxSelect('iReleaseSelectSort','추천순','{mRelease->GetSortLink sort="vote"}',ListSelectSelectSort)">추천순</li>
				<li onclick="InputSelectBoxSelect('iReleaseSelectSort','높은가격순','{mRelease->GetSortLink sort="price"}',ListSelectSelectSort)">높은가격순</li>
			</ul>
		</div>
	</td>
</tr>
</table>

<table cellpadding="0" cellspacing="0" class="releaseTable">
<tr>
	<td class="sectionBar"></td>
</tr>
{foreach name=list from=$data item=data}
<tr>
	<td class="splitBar"></td>
</tr>
<tr>
	<td>
		<table cellpadding="0" cellspacing="0" class="releaseTable">
		<col width="154" /><col width="1" /><col width="100" /><col width="1" /><col width="150" /><col width="1" /><col width="120" /><col width="1" /><col width="100%" />
		<tr>
			<td colspan="9" class="headerCell titleCell bold" style="text-align:left;">
				<div><a href="{$data.postlink}"{if $setup.use_mode == 'TRUE'} onclick="return OpenBoard(this);"{/if}>{$data.title|cutstring:32:true:$data.category}</a>{if $data.ment != 0} <span class="ment">[{$data.ment}{if $data.is_newment == true}+{/if}]</span>{/if}</div>
			</td>
		</tr>
		<tr class="splitBar">
			<td colspan="9"></td>
		</tr>
		<tr>
			<td class="logoCell" rowspan="7">{if $data.logo}<img src="{$data.logo}" />{else}<img src="{$skinDir}/images/nologo.gif" />{/if}</td>
			<td class="splitBar" rowspan="7"></td>
			<td class="headerCell">분류</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.category}{$data.category}{else}분류없음{/if}</td>
			<td class="splitBar"></td>
			<td class="headerCell">최신업데이트</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.last_date}{$data.last_date|date_format:"%Y년 %m월 %d일"} <span class="bold tahoma f10 red">(v.{$data.last_version}){if $data.is_new == true} <img src="{$skinDir}/images/icon_new.png" style="vertical-align:middle;" />{/if}</span>{else}버전이 등록되지 않았습니다.{/if}</td>
		</tr>
		<tr class="splitBar">
			<td colspan="7"></td>
		</tr>
		<tr>
			<td class="headerCell">제작자</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{$data.nickname}</td>
			<td class="splitBar"></td>
			<td class="headerCell">다운로드/추천</td>
			<td class="splitBar"></td>
			<td class="bodyCell"><span class="bold tahoma blue">{$data.download|number_format}</span> / <span class="bold tahoma red">{$data.vote|number_format}</span></td>
		</tr>
		<tr class="splitBar">
			<td colspan="7"></td>
		</tr>
		<tr>
			<td class="headerCell">라이센스</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{$data.license}</td>
			<td class="splitBar"></td>
			<td class="headerCell">프로그램가격</td>
			<td class="splitBar"></td>
			<td class="bodyCell">{if $data.price == 0}<span class="blue bold">무료</span>{else}<span class="red bold">{$data.price|number_format}P</span>{/if}</td>
		</tr>
		<tr class="splitBar">
			<td colspan="7"></td>
		</tr>
		<tr class="introCell">
			<td colspan="7">
				<div>{$data.search|GetCutString:120}</div>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="splitBar"></td>
</tr>
{/foreach}
<tr class="sectionEnd">
	<td><div></div></td>
</tr>
</table>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="150" /><col width="100%" /><col width="150" />
<tr>
	<td class="innerimg">
		{if $prevlist != false}
			<a href="{$link.page}{$prevlist}"><img src="{$skinDir}/images/btn_prev.gif" /></a>
		{else}
			<img src="{$skinDir}/images/btn_prev_off.gif" />
		{/if}
	</td>
	<td class="pageinfo"><span class="bold">{$totalpost}</span> programs / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
	<td class="innerimg right">
		{if $nextlist != false}
			<a href="{$link.page}{$nextlist}"><img src="{$skinDir}/images/btn_next.gif" /></a>
		{else}
			<img src="{$skinDir}/images/btn_next_off.gif" />
		{/if}
	</td>
</tr>
</table>

<div class="height10"></div>

<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="100%" /><col width="200" />
<tr>
	<td>
		{$searchFormStart}
		<input type="hidden" name="key" value="{$key}" />
		<div class="searchbox">
			<div class="key" onclick="$('.searchbox > .keylist').toggle();">
				{if $key == 'tc'}컨텐츠{elseif $key == 'name'}작성자{else}댓글내용{/if}
			</div>
			<div>
				<input id="ModuleBoardkeyword_{$bid}" type="text" name="keyword" class="inputbox" autocomplete="off" onfocus="LiveSearchStart(this.id)" onblur="LiveSearchStop(this.id)" onkeydown="LiveSearchListMove(event,this.id)" value="{$keyword}" />
				<input type="submit" class="buttonbox" value="" />
				<div id="ModuleBoardkeyword_{$bid}-live-arrow" class="searchlivebutton" show="background-position:0px -11px;" hide="background-position:0px 0px;"></div>
				<div id="ModuleBoardkeyword_{$bid}-live-list" class="livelist" style="display:none;" select="background:#E5E5E5;" unselect="background:#FFFFFF;"></div>
			</div>
			
			<div class="keylist" style="display:none;">
				<div key="tc" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="SearchFormKeySelect(this);">컨텐츠</div>
				<div key="name" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="SearchFormKeySelect(this);">작성자</div>
				<div key="ment" onmouseover="$(this).addClass('hover');" onmouseout="$(this).removeClass('hover');" onclick="SearchFormKeySelect(this);">댓글내용</div>
			</div>
		</div>
		{$searchFormEnd}
	</td>
	<td class="right">
		<a class="btn btn-success btn-sm" href="{$link.post}">신규 프로그램 등록하기</a>
	</td>
</tr>
</table>

<div class="center">
	<ul class="pagination pagination-sm">
		<li{if $prevpage == false} class="disabled"{/if}><a href="{if $prevpage != false}{$link.page}{$prevpage}{else}javascript:void(0);{/if}">이전{$pagenum}페이지</a></li>
		{foreach name=page from=$page item=page}
		<li{if $page == $p} class="active"{/if}><a href="{$link.page}{$page}">{$page}</a></li>
		{/foreach}
		<li{if $nextpage == false} class="disabled"{/if}><a href="{if $nextpage != false}{$link.page}{$nextpage}{else}javascript:void(0);{/if}">다음{$pagenum}페이지</a></li>
	</ul>
</div>