<div class="tabBarArea">
	<div class="on">전체</div>
	<div class="off">채택을 기다리는 Q&amp;A</div>
	<div class="off">답변을 기다리는 질문</div>
	<div class="off">채택된 Q&amp;A</div>
</div>

<div class="listArea">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="100%" /><col width="100" /><col width="100" /><col width="50" /><col width="60" />
	<tr>
		<td class="listHeader center">제목</td>
		<td class="listHeader">카테고리</td>
		<td class="listHeader">작성자</td>
		<td class="listHeader">답변</td>
		<td class="listHeader">등록일</td>
	</tr>
	<tr class="listBar">
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	{foreach name=list from=$data item=data}
	<tr class="listBar">
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
	<tr class="listBody">
		<td>
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="40" />{if $data.is_secret == 'TRUE'}<col width="20" />{/if}<col width="100%" />
			<tr>
				<td class="center">{if $data.is_select == 'TRUE'}<img src="{$skinDir}/images/icon_select.gif" />{elseif $data.is_complete == 'TRUE'}<img src="{$skinDir}/images/icon_complete.gif" />{elseif $data.point != '0'}<img src="{$skinDir}/point/icon_point_{$data.point}.gif" />{/if}</td>
				{if $data.is_secret == 'TRUE'}<td><img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" style="vertical-align:middle" /></td>{/if}
				<td>{if $data.is_rewrite == 'TRUE'}<span class="isRewrite">재등록</span>{/if} <a href="{$data.postlink}">{$data.title|cutstring:28}</a></td>
			</tr>
			</table>
		</td>
		<td>{$data.category}</td>
		<td class="listName">{$data.nickname}</td>
		<td class="listAnswer{if $data.is_newanswer == true} newAnswer{/if}">
			{$data.answer|number_format}
		</td>
		<td class="listDate">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}</td>
	</tr>
	{/foreach}
	</table>
</div>

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