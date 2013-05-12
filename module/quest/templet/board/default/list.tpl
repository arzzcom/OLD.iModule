<table cellpadding="0" cellspacing="0" class="layoutfixed">
{if $categoryList}<col width="152" />{/if}{if $setup.use_select == 'TRUE'}<col width="102" />{/if}<col width="100%" />
<tr>
	{if $categoryList}
	<td>
		<div id="iBoardCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iBoardCategory')" clicker="iBoardCategory">{if $categoryName}{$categoryName}{else}전체보기{/if}</div>

			<ul style="display:none;" clicker="iBoardCategory">
				<li onclick="InputSelectBoxSelect('iBoardCategory','전체보기','',ListSelectCategory)">전체보기</li>
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iBoardCategory','{$categoryList.category|replace:"'":"\'"}','{$categoryList.idx}',ListSelectCategory)">{$categoryList.category}</li>
				{/foreach}
			</ul>
		</div>
	</td>
	{/if}
	{if $setup.use_select == 'TRUE'}
	<td>
		<div id="iBoardSelectAnswer" class="selectbox" style="width:100px;">
			<div onclick="InputSelectBox('iBoardSelectAnswer')" clicker="iBoardSelectAnswer">{if $select == 'true'}해결된질문{elseif $select == 'false'}미해결질문{else}전체질문{/if}</div>

			<ul style="display:none;" clicker="iBoardSelectAnswer">
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','전체보기','',ListSelectSelectAnswer)">전체질문</li>
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','해결된질문','true',ListSelectSelectAnswer)">해결된질문</li>
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','미해결질문','false',ListSelectSelectAnswer)">미해결질문</li>
			</ul>
		</div>
	</td>
	{/if}
	<td class="right">{if $setup.use_rss == 'TRUE'}<a href="{$link.rss}" target="_blank"><img src="{$skinDir}/images/icon_rss.gif" alt="RSS" /></a>{/if}</td>
</tr>
</table>
<div class="height5"></div>
<div id="tList">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="5" />{if $is_view_loopnum == true}<col width="50" />{/if}<col width="100%" />{if $is_view_name == true}<col width="100" />{/if}{if $is_view_reg_date == true}<col width="80" />{/if}
	{if $is_view_hit == true}<col width="40" />{/if}{if $is_view_vote == true}<col width="40" />{/if}{if $is_view_avgvote == true}<col width="40" />{/if}<col width="5" />
	<thead>
	<tr class="listbar">
		<td class="listbar left"></td>
		{if $is_view_loopnum == true}<td class="text pointer" onclick="location.href='{mBoard->GetSortLink sort="idx"}';">#</td>{/if}
		<td class="text pointer" onclick="location.href='{mBoard->GetSortLink sort="title"}';">제목</td>
		{if $is_view_name == true}<td class="text">작성자</td>{/if}
		{if $is_view_reg_date == true}<td class="text pointer" onclick="location.href='{mBoard->GetSortLink sort="reg_date"}';">작성일</td>{/if}
		{if $is_view_hit == true}<td class="text pointer" onclick="location.href='{mBoard->GetSortLink sort="hit"}';">조회</td>{/if}
		{if $is_view_vote == true}<td class="text pointer" onclick="location.href='{mBoard->GetSortLink sort="vote"}';">추천</td>{/if}
		{if $is_view_avgvote == true}<td class="text pointer" onclick="location.href='{mBoard->GetSortLink sort="avgvote"}';">평점</td>{/if}
		<td class="right"></td>
	</tr>
	</thead>
	<tbody>
	{foreach name=notice from=$notice item=notice}
	<tr class="listrow noticerow">
		<td></td>
		{if $is_view_loopnum == true}<td class="number center">{if $notice.is_read == true}<img src="{$skinDir}/images/icon_read.gif" />{else}notice{/if}</td>{/if}
		<td>
			{if $notice.is_mobile == true}<img src="{$skinDir}/images/icon_mobile.gif" alt="모바일" style="vertical-align:middle" />{/if}
			{if $notice.is_secret == true}<img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" style="vertical-align:middle" />{/if}
			<a href="{$notice.postlink}"{if $setup.use_mode == 'TRUE'} onclick="return OpenBoard(this);"{/if}>{$notice.title|cutstring:32:true}</a>{if $notice.ment != 0} <span class="ment">[{$notice.ment}{if $notice.is_newment == true}+{/if}]</span>{/if}{if $notice.is_new == true} <img src="{$skinDir}/images/icon_new.gif" style="vertical-align:middle;" />{/if}{if $notice.is_file == true} <img src="{$skinDir}/images/icon_file.gif" style="vertical-align:middle;" />{/if}{if $notice.is_image == true} <img src="{$skinDir}/images/icon_image.gif" style="vertical-align:middle;" />{/if}
		</td>
		{if $is_view_name == true}<td class="center">{$notice.nickname}</td>{/if}
		{if $is_view_reg_date == true}<td class="number center">{if $time.server|date_format:"%Y%m%d" == $notice.reg_date|date_format:"%Y%m%d"}{$notice.reg_date|date_format:"%H:%M:%S"}{else}{$notice.reg_date|date_format:"%Y.%m.%d"}{/if}</td>{/if}
		{if $is_view_hit == true}<td class="number center">{$notice.hit}</td>{/if}
		{if $is_view_vote == true}<td class="number center">{$notice.vote}</td>{/if}
		{if $is_view_avgvote == true}<td class="number center">{$notice.avgvote}</td>{/if}
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
	{/foreach}
	{foreach name=list from=$data item=data}
	<tr class="listrow">
		<td></td>
		{if $is_view_loopnum == true}<td class="number center">{if $data.is_read == true}<img src="{$skinDir}/images/icon_read.gif" />{else}{$data.loopnum|number_format}{/if}</td>{/if}
		<td>
			{if $data.is_mobile == true}<img src="{$skinDir}/images/icon_mobile.gif" alt="모바일" style="vertical-align:middle" />{/if}
			{if $data.is_secret == true}<img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" style="vertical-align:middle" />{/if}
			{if $data.is_select == true}<span class="select">답변채택</span>{/if}
			{if $data.category}<span class="category">[{$data.category}]</span> {/if}<a href="{$data.postlink}"{if $setup.use_mode == 'TRUE'} onclick="return OpenBoard(this);"{/if}>{$data.title|cutstring:32:true:$data.category}</a>{if $data.ment != 0} <span class="ment">[{$data.ment}{if $data.is_newment == true}+{/if}]</span>{/if}{if $data.is_new == true} <img src="{$skinDir}/images/icon_new.gif" style="vertical-align:middle;" />{/if}{if $data.is_file == true} <img src="{$skinDir}/images/icon_file.gif" style="vertical-align:middle;" />{/if}{if $data.is_image == true} <img src="{$skinDir}/images/icon_image.gif" style="vertical-align:middle;" />{/if}
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
	{/foreach}
	</tbody>
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