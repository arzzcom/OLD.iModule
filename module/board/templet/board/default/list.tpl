<table cellpadding="0" cellspacing="0" class="boardTable">
<col width="100%" />{if $categoryList}<col width="152" />{/if}{if $setup.use_select == 'TRUE'}<col width="102" />{/if}{if $setup.use_rss == 'TRUE'}<col width="22" />{/if}
<tr>
	<td class="sectionTitle">목록보기</td>
	{if $categoryList}
	<td>
		<div id="iBoardCategory" class="selectbox" style="width:150px;">
			<div onclick="InputSelectBox('iBoardCategory')" clicker="iBoardCategory">{if $categoryName}{$categoryName}{else}전체보기{/if}</div>

			<ul style="display:none;" clicker="iBoardCategory">
				<li onclick="InputSelectBoxSelect('iBoardCategory','전체보기','',ListSelectCategory)">전체보기</li>
				{foreach from=$categoryList item=categoryList}
				<li onclick="InputSelectBoxSelect('iBoardCategory','{$categoryList.category}','{$categoryList.idx}',ListSelectCategory)">{$categoryList.category}</li>
				{/foreach}
			</ul>
		</div>
	</td>
	{/if}
	{if $setup.use_select == 'TRUE'}
	<td>
		<div id="iBoardSelectAnswer" class="selectbox" style="width:100px;">
			<div onclick="InputSelectBox('iBoardSelectAnswer')" clicker="iBoardSelectAnswer">{if $select == 'true'}해결된질문{elseif $select == 'false'}미해결질문{elseif $select == 'my'}나의질문{else}전체질문{/if}</div>

			<ul style="display:none;" clicker="iBoardSelectAnswer">
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','전체보기','',ListSelectSelectAnswer)">전체질문</li>
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','해결된질문','true',ListSelectSelectAnswer)">해결된질문</li>
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','미해결질문','false',ListSelectSelectAnswer)">미해결질문</li>
				<li onclick="InputSelectBoxSelect('iBoardSelectAnswer','나의질문','my',ListSelectSelectAnswer)">나의질문</li>
			</ul>
		</div>
	</td>
	{/if}
	{if $setup.use_rss == 'TRUE'}<td class="right"><a href="{$link.rss}" target="_blank"><img src="{$skinDir}/images/icon_rss.png" alt="RSS" style="width:20px; vertical-align:middle;" /></a></td>{/if}
</tr>
</table>

<table cellpadding="0" cellspacing="0" class="boardTable">
<tr class="sectionBar">
	{if $is_view_loopnum == true}<td style="width:50px;"></td><td style="width:1px;"></td>{/if}
	<td style="width:100%;"></td>
	{if $is_view_name == true}<td style="width:1px;"></td><td style="width:100px;"></td>{/if}
	{if $is_view_reg_date == true}<td style="width:1px;"></td><td style="width:80px;"></td>{/if}
	{if $is_view_hit == true}<td style="width:1px;"></td><td style="width:50px;"></td>{/if}
	{if $is_view_vote == true}<td style="width:1px;"></td><td style="width:50px;"></td>{/if}
	{if $is_view_avgvote == true}<td style="width:1px;"></td><td style="width:50px;"></td>{/if}
</tr>
<tr>
	{if $is_view_loopnum == true}<td class="headerCell"><a href="{mBoard->GetSortLink sort="idx"}">번호</a>{if $sort == 'idx'}<span class="{$dir}"></span>{/if}</td><td class="splitBar"></td>{/if}
	<td class="headerCell"><a href="{mBoard->GetSortLink sort="title"}">제목</a>{if $sort == 'title'}<span class="{$dir}"></span>{/if}</td>
	{if $is_view_name == true}<td class="splitBar"></td><td class="headerCell">작성자</td>{/if}
	{if $is_view_reg_date == true}<td class="splitBar"></td><td class="headerCell"><a href="{mBoard->GetSortLink sort="reg_date"}">등록일</a>{if $sort == 'reg_date'}<span class="{$dir}"></span>{/if}</td>{/if}
	{if $is_view_hit == true}<td class="splitBar"></td><td class="headerCell"><a href="{mBoard->GetSortLink sort="hit"}">조회</a>{if $sort == 'hit'}<span class="{$dir}"></span>{/if}</td>{/if}
	{if $is_view_vote == true}<td class="splitBar"></td><td class="headerCell"><a href="{mBoard->GetSortLink sort="vote"}">추천</a>{if $sort == 'vote'}<span class="{$dir}"></span>{/if}</td>{/if}
	{if $is_view_avgvote == true}<td class="splitBar"></td><td class="headerCell"><a href="{mBoard->GetSortLink sort="avgvote"}">평점</a>{if $sort == 'avgvote'}<span class="{$dir}"></span>{/if}</td>{/if}
</tr>
<tr class="splitBar">
	{if $is_view_loopnum == true}<td></td><td></td>{/if}
	<td></td>
	{if $is_view_name == true}<td></td><td></td>{/if}
	{if $is_view_reg_date == true}<td></td><td></td>{/if}
	{if $is_view_hit == true}<td></td><td></td>{/if}
	{if $is_view_vote == true}<td></td><td></td>{/if}
	{if $is_view_avgvote == true}<td></td><td></td>{/if}
</tr>
{foreach name=list from=$notice item=notice}
<tr class="noticeCell">
	{if $is_view_loopnum == true}<td class="numberCell">notice</td><td class="splitBar"></td>{/if}
	<td class="titleCell">
		<div>
			<a href="{$notice.postlink}">{$notice.title|cutstring:32:true}</a>
			{if $notice.ment != 0}<span class="ment">[{$notice.ment}{if $notice.is_newment == true}+{/if}]</span>{/if}
			{if $notice.is_file == true}<img src="{$skinDir}/images/icon_file.gif" />{/if}
			{if $notice.is_image == true}<img src="{$skinDir}/images/icon_image.gif" />{/if}
		</div>
	</td>
	{if $is_view_name == true}<td class="splitBar"></td><td class="nicknameCell">{$notice.author.nickname}</td>{/if}
	{if $is_view_reg_date == true}<td class="splitBar"></td><td class="numberCell">{if $time.server|date_format:"%Y%m%d" == $notice.reg_date|date_format:"%Y%m%d"}{$notice.reg_date|date_format:"%H:%M:%S"}{else}{$notice.reg_date|date_format:"%Y.%m.%d"}{/if}{if $notice.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</td>{/if}
	{if $is_view_hit == true}<td class="splitBar"></td><td class="countCell">{$notice.hit}</td>{/if}
	{if $is_view_vote == true}<td class="splitBar"></td><td class="countCell">{$notice.vote}</td>{/if}
	{if $is_view_avgvote == true}<td class="splitBar"></td><td class="countCell">{$notice.avgvote}</td>{/if}
</tr>
<tr class="splitBar">
	{if $is_view_loopnum == true}<td></td><td></td>{/if}
	<td></td>
	{if $is_view_name == true}<td></td><td></td>{/if}
	{if $is_view_reg_date == true}<td></td><td></td>{/if}
	{if $is_view_hit == true}<td></td><td></td>{/if}
	{if $is_view_vote == true}<td></td><td></td>{/if}
	{if $is_view_avgvote == true}<td></td><td></td>{/if}
</tr>
{/foreach}
{foreach name=list from=$data item=data}
<tr>
	{if $is_view_loopnum == true}<td class="numberCell">{if $data.is_read == true}<img src="{$skinDir}/images/icon_read.gif" />{else}{$data.loopnum|number_format}{/if}</td><td class="splitBar"></td>{/if}
	<td class="titleCell">
		<div>
			{if $data.is_mobile == true}<img src="{$skinDir}/images/icon_mobile.gif" alt="모바일" />{/if}
			{if $data.is_secret == true}<img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" />{/if}
			{if $data.is_select == 'TRUE'}<span class="label label-danger tahoma" style="margin:1px;">SOLVED</span>{elseif $data.is_select == 'COMPLETE'}<span class="label label-primary tahoma" style="margin:1px;">COMPLETE</span>{/if}
			{if $data.category}<span class="category">[{$data.category}]</span> {/if}<a href="{$data.postlink}">{$data.title|cutstring:32:true:$data.category}</a>
			{if $data.ment != 0}<span class="ment">[{$data.ment}{if $data.is_newment == true}+{/if}]</span>{/if}
			{if $data.is_file == true}<img src="{$skinDir}/images/icon_file.gif" />{/if}
			{if $data.is_image == true}<img src="{$skinDir}/images/icon_image.gif" />{/if}
		</div>
	</td>
	{if $is_view_name == true}<td class="splitBar"></td><td class="nicknameCell">{$data.author.nickname}</td>{/if}
	{if $is_view_reg_date == true}<td class="splitBar"></td><td class="numberCell">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}{if $data.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</td>{/if}
	{if $is_view_hit == true}<td class="splitBar"></td><td class="countCell">{$data.hit}</td>{/if}
	{if $is_view_vote == true}<td class="splitBar"></td><td class="countCell">{$data.vote}</td>{/if}
	{if $is_view_avgvote == true}<td class="splitBar"></td><td class="countCell">{$data.avgvote}</td>{/if}
</tr>
<tr class="splitBar">
	{if $is_view_loopnum == true}<td></td><td></td>{/if}
	<td></td>
	{if $is_view_name == true}<td></td><td></td>{/if}
	{if $is_view_reg_date == true}<td></td><td></td>{/if}
	{if $is_view_hit == true}<td></td><td></td>{/if}
	{if $is_view_vote == true}<td></td><td></td>{/if}
	{if $is_view_avgvote == true}<td></td><td></td>{/if}
</tr>
{/foreach}
<tr class="sectionEnd">
	{if $is_view_loopnum == true}<td><div></div></td><td><div></div></td>{/if}
	<td><div></div></td>
	{if $is_view_name == true}<td><div></div></td><td><div></div></td>{/if}
	{if $is_view_reg_date == true}<td><div></div></td><td><div></div></td>{/if}
	{if $is_view_hit == true}<td><div></div></td><td><div></div></td>{/if}
	{if $is_view_vote == true}<td><div></div></td><td><div></div></td>{/if}
	{if $is_view_avgvote == true}<td><div></div></td><td><div></div></td>{/if}
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
	<td class="pageinfo"><span class="bold">{$totalpost}</span> topics / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
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
		<a class="btn btn-success btn-sm" href="{$link.post}">{if $setup.use_select == 'TRUE'}새 질문작성하기{else}새글 작성하기{/if}</a>
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