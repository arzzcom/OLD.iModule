<table cellpadding="0" cellspacing="0" class="layoutfixed">
{if $categoryList}<col width="152" />{/if}{if $setup.use_select == 'TRUE'}<col width="102" />{/if}<col width="100%" />
<tr>
	{if $categoryList}
	<td>
		<div id="iReleaseCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iReleaseCategory')" clicker="iReleaseCategory">{if $categoryName}{$categoryName}{else}전체보기{/if}</div>

			<ul style="display:none;" clicker="iReleaseCategory">
				<li onclick="InputSelectBoxSelect('iReleaseCategory','전체보기','',ListSelectCategory)">전체보기</li>
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iReleaseCategory','{$categoryList.category|replace:"'":"\'"}','{$categoryList.idx}',ListSelectCategory)">{$categoryList.category}</li>
				{/foreach}
			</ul>
		</div>
	</td>
	{/if}
	<td class="right sortList">
		<a href="{mRelease->GetSortLink sort="idx"}"{if $sort == "idx"} class="on"{/if}>최신업데이트순</a> | <a href="{mRelease->GetSortLink sort="loop"}"{if $sort == "download"} class="on"{/if}>다운로드순</a> | <a href="{mRelease->GetSortLink sort="vote"}"{if $sort == "vote"} class="on"{/if}>추천순</a> | <a href="{mRelease->GetSortLink sort="price"}"{if $sort == "price"} class="on"{/if}>높은가격순</a>
	</td>
</tr>
</table>
<div class="height5"></div>
<div id="tList">
	{foreach name=list from=$data item=data}
	<div class="listbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="130" /><col width="100%" />
		<tr>
			<td colspan="2" class="title"><a href="{$data.postlink}"{if $setup.use_mode == 'TRUE'} onclick="return OpenBoard(this);"{/if}>{$data.title|cutstring:32:true:$data.category}</a>{if $data.ment != 0} <span class="ment">[{$data.ment}{if $data.is_newment == true}+{/if}]</span>{/if}{if $data.is_new == true} <img src="{$skinDir}/images/icon_new.gif" style="vertical-align:middle;" />{/if}</td>
		</tr>
		<tr>
			<td>
				{if $data.logo}<img src="{$data.logo}" class="logo" />{else}<img src="{$skinDir}/images/nologo.gif" class="logo" />{/if}
			</td>
			<td class="vTop">
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="40%" /><col width="60%" />
				<tr>
					<td class="detail">
						<div><span class="bold">분류</span> : {if $data.category}{$data.category}{else}분류없음{/if}</div>
					</td>
					<td class="detail">
						<div><span class="bold">마지막 업데이트</span> : {if $data.last_date}{$data.last_date|date_format:"%Y년 %m월 %d일"} (v.{$data.last_version}){else}버전이 등록되지 않았습니다.{/if}</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="detail"><div class="dotted"></div></td>
				</tr>
				<tr>
					<td class="detail">
						<div><span class="bold">제작자</span> : {$data.nickname}</div>
					</td>
					<td class="detail">
						<div><span class="bold">다운로드(구매) / 추천</span> : <span class="bold tahoma blue">{$data.download|number_format}</span> / <span class="bold tahoma red">{$data.vote|number_format}</span></div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="detail"><div class="dotted"></div></td>
				</tr>
				<tr>
					<td class="detail">
						<div><span class="bold">라이센스</span> : {$data.license}</div>
					</td>
					<td class="detail">
						<div><span class="bold">프로그램가격</span> : {if $data.price == 0}<span class="blue bold">무료</span>{else}<span class="red bold">{$data.price|number_format}P</span>{/if}</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="detail"><div class="dotted"></div></td>
				</tr>
				<tr>
					<td colspan="2" class="intro"><div>{$data.search|GetCutString:120}</div></td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
			<!--
	<tr class="listrow">
		<td></td>
		{if $is_view_loopnum == true}<td class="number center">{if $data.is_read == true}<img src="{$skinDir}/images/icon_read.gif" />{else}{$data.loopnum|number_format}{/if}</td>{/if}
		<td>
			{if $data.is_mobile == true}<img src="{$skinDir}/images/icon_mobile.gif" alt="모바일" style="vertical-align:middle" />{/if}
			{if $data.is_secret == true}<img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" style="vertical-align:middle" />{/if}
			{if $data.is_select == true}<span class="select">답변채택</span>{/if}
			{if $data.category}<span class="category">[{$data.category}]</span> {/if}{if $data.is_file == true} <img src="{$skinDir}/images/icon_file.gif" style="vertical-align:middle;" />{/if}{if $data.is_image == true} <img src="{$skinDir}/images/icon_image.gif" style="vertical-align:middle;" />{/if}
		</td>
		{if $is_view_name == true}<td class="center">{$data.nickname}</td>{/if}
		{if $is_view_reg_date == true}<td class="number center">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}</td>{/if}
		{if $is_view_hit == true}<td class="number center">{$data.hit}</td>{/if}
		{if $is_view_vote == true}<td class="number center">{$data.vote}</td>{/if}
		{if $is_view_avgvote == true}<td class="number center">{$data.avgvote}</td>{/if}
		<td></td>
	</tr>
	<tr class="listrowbar">
		<td></td>
		{if $is_view_loopnum == true}<td></td>{/if}
		<td></td>
		{if $is_view_name == true}<td></td>{/if}
		{if $is_view_reg_date == true}<td></td>{/if}
		{if $is_view_hit == true}<td></td>{/if}
		{if $is_view_vote == true}<td></td>{/if}
		{if $is_view_avgvote == true}<td></td>{/if}
		<td></td>
	</tr>
	-->
	</div>
	{/foreach}

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
		<td class="pageinfo"><span class="bold">{$totalpost}</span> topics / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
		<td class="innerimg right">
			{if $setup.use_mode == 'FALSE'}<a href="{$link.post}"><img src="{$skinDir}/images/btn_newpost.gif" style="margin-right:3px;" /></a>{/if}
			{if $nextlist != false}
				<a href="{$link.page}{$nextlist}"><img src="{$skinDir}/images/btn_next.gif" /></a>
			{else}
				<img src="{$skinDir}/images/btn_next_off.gif" />
			{/if}
		</td>
	</tr>
	</table>

	<div class="height10"></div>

	<div class="pagenav">
	{if $prevpage != false}
		<a href="{$link.page}{$prevpage}">이전{$pagenum}페이지</a>
	{else}
		<span>이전{$pagenum}페이지</span>
	{/if}
	{foreach name=page from=$page item=page}
		{if $page == $p}
		<strong>{$page}</strong>
		{else}
		<a href="{$link.page}{$page}">{$page}</a>
		{/if}
	{/foreach}
	{if $nextpage != false}
		<a href="{$link.page}{$nextpage}">다음{$pagenum}페이지</a>
	{else}
		<span>다음{$pagenum}페이지</span>
	{/if}
	</div>

	<div class="height10"></div>

	{$searchFormStart}
	<input type="hidden" name="key" value="{$key}" />
	<div class="searchbox">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="100%" /><col width="204" />
		<tr>
			<td class="innerimg">
				<span id="checkbox_tc" class="checkbox{if $key == "tc"}on{else}off{/if}" onclick="SearchOptionCheck(this.id);"></span>
				<span class="text">컨텐츠</span>
				<span id="checkbox_name" class="checkbox{if $key == "name"}on{else}off{/if}" onclick="SearchOptionCheck(this.id);"></span>
				<span class="text">작성자</span>
				<span id="checkbox_ment" class="checkbox{if $key == "ment"}on{else}off{/if}" onclick="SearchOptionCheck(this.id);"></span>
				<span class="text">댓글</span>
			</td>
			<td>
				<div class="searchinput">
					<input id="ModuleBoardkeyword_{$bid}" type="text" name="keyword" class="inputbox" autocomplete="off" onfocus="LiveSearchStart(this.id)" onblur="LiveSearchStop(this.id)" onkeydown="LiveSearchListMove(event,this.id)" value="{$keyword}" />
					<input type="submit" class="buttonbox" value="" />
					<div id="ModuleBoardkeyword_{$bid}-live-arrow" class="searchlivebutton" show="background-position:0px -11px;" hide="background-position:0px 0px;"></div>
					<div id="ModuleBoardkeyword_{$bid}-live-list" class="livelist" style="display:none;" select="background:#E5E5E5;" unselect="background:#FFFFFF;"></div>
				</div>
			</td>
			<td>

			</td>
		</tr>
		</table>
	</div>
	{$searchFormEnd}
</div>