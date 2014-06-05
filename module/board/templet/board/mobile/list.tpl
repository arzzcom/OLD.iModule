{if $categoryList}
<div style="background:#e5e5e5; padding:5px 0px 5px 0px; border-top:1px solid #cccccc;">
	<div class="categoryBox">
	<div class="categoryItem{if $category == ''} select{/if}" onclick="ListSelectCategory('','');">전체보기</div>
	{foreach from=$categoryList item=printCategory}
	<div class="categoryItem{if $category == $printCategory.idx} select{/if}" onclick="ListSelectCategory('',{$printCategory.idx});">{$printCategory.category}</div>
	{/foreach}
	
	{if $categoryList|@sizeof < 3}
	<div class="categoryItem"></div>
	{/if}
	
	{if $categoryList|@sizeof < 2}
	<div class="categoryItem"></div>
	{/if}
	
	{if $categoryList|@sizeof < 1}
	<div class="categoryItem"></div>
	{/if}
	</div>
</div>
{/if}

{if $setup.use_select == 'TRUE'}
<div style="background:#e5e5e5; padding:5px 0px 5px 0px; border-top:1px solid #cccccc;">
	<div class="categoryBox">
	<div class="categoryItem{if $select == ''} select{/if}" onclick="ListSelectSelectAnswer('','');">전체질문</div>
	<div class="categoryItem{if $select == 'true'} select{/if}" onclick="ListSelectSelectAnswer('','true');">해결된질문</div>
	<div class="categoryItem{if $select == 'false'} select{/if}" onclick="ListSelectSelectAnswer('','false');">미해결질문</div>
	<div class="categoryItem{if $select == 'my'} select{/if}" onclick="ListSelectSelectAnswer('','my');">나의질문</div>
	</div>
</div>
{/if}

<table cellpadding="0" cellspacing="0" class="boardTable">
<tr class="sectionBar">
	<td style="width:100%;"></td>
	{if $is_view_name == true}<td style="width:1px;" class="hidden-xs"></td><td style="width:120px;" class="hidden-xs"></td>{/if}
	{if $is_view_reg_date == true}<td style="width:1px;" class="hidden-xs"></td><td style="width:100px;" class="hidden-xs"></td>{/if}
</tr>
<tr>
	<td class="headerCell"><a href="{mBoard->GetSortLink sort="title"}">제목</a>{if $sort == 'title'}<span class="{$dir}"></span>{/if}</td>
	{if $is_view_name == true}<td class="splitBar hidden-xs"></td><td class="headerCell hidden-xs">작성자</td>{/if}
	{if $is_view_reg_date == true}<td class="splitBar hidden-xs"></td><td class="headerCell hidden-xs"><a href="{mBoard->GetSortLink sort="reg_date"}">등록일</a>{if $sort == 'reg_date'}<span class="{$dir}"></span>{/if}</td>{/if}
</tr>
<tr class="splitBar">
	<td></td>
	{if $is_view_name == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
	{if $is_view_reg_date == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
</tr>
{foreach name=list from=$notice item=notice}
<tr class="noticeCell">
	<td class="titleCell">
		<div>
			<a href="{$notice.postlink}">{$notice.title|cutstring:32:true}</a>
			{if $notice.ment != 0}<span class="ment">[{$notice.ment}{if $notice.is_newment == true}+{/if}]</span>{/if}
			{if $notice.is_file == true}<img src="{$skinDir}/images/icon_file.gif" />{/if}
			{if $notice.is_image == true}<img src="{$skinDir}/images/icon_image.gif" />{/if}
		</div>
	</td>
	{if $is_view_name == true}<td class="splitBar hidden-xs"></td><td class="nicknameCell hidden-xs">{$notice.author.nickname}</td>{/if}
	{if $is_view_reg_date == true}<td class="splitBar hidden-xs"></td><td class="numberCell hidden-xs">{if $time.server|date_format:"%Y%m%d" == $notice.reg_date|date_format:"%Y%m%d"}{$notice.reg_date|date_format:"%H:%M:%S"}{else}{$notice.reg_date|date_format:"%Y.%m.%d"}{/if}{if $notice.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</td>{/if}
</tr>
<tr class="splitBar">
	{if $is_view_loopnum == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
	<td></td>
	{if $is_view_name == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
	{if $is_view_reg_date == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
</tr>
{/foreach}
{foreach name=list from=$data item=data}
<tr onclick="location.href='{$data.postlink}';">
	<td class="titleCell">
		<div>
			{if $data.is_mobile == true}<img src="{$skinDir}/images/icon_mobile.gif" alt="모바일" />{/if}
			{if $data.is_secret == true}<img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" />{/if}
			{if $data.is_select == 'TRUE'}<span class="label label-danger tahoma" style="margin:1px;">SOLVED</span>{elseif $data.is_select == 'COMPLETE'}<span class="label label-primary tahoma" style="margin:1px;">COMPLETE</span>{/if}
			{if $data.category}<span class="category">[{$data.category}]</span> {/if}<a href="{$data.postlink}">{$data.title|cutstring:32:true:$data.category}</a>
			{if $data.ment != 0}<span class="ment">[{$data.ment}{if $data.is_newment == true}+{/if}]</span>{/if}
			{if $data.is_file == true}<img src="{$skinDir}/images/icon_file.gif" />{/if}
			{if $data.is_image == true}<img src="{$skinDir}/images/icon_image.gif" />{/if}
			
			{if $is_view_vote == true}<span class="label label-danger normal hidden-xs pull-right">{$data.vote|number_format}Vote</span>{/if}
			{if $is_view_hit == true}<span class="label label-warning normal hidden-xs pull-right" style="margin-right:5px;">{$data.hit|number_format}Hit</span>{/if}
		</div>
		<div class="right visible-xs">
			{if $is_view_name == true}<span>{$data.author.nickname}</span>{/if}
			{if $is_view_hit == true}<span class="label label-warning normal">{$data.hit|number_format}Hit</span>{/if}
			{if $is_view_vote == true}<span class="label label-danger normal">{$data.vote|number_format}Vote</span>{/if}
			{if $is_view_reg_date == true}<span class="label label-default normal">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}{if $data.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</span>{/if}
		</div>
	</td>
	{if $is_view_name == true}<td class="splitBar hidden-xs"></td><td class="nicknameCell hidden-xs">{$data.author.nickname}</td>{/if}
	{if $is_view_reg_date == true}<td class="splitBar hidden-xs"></td><td class="numberCell hidden-xs">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}{if $data.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</td>{/if}
</tr>
<tr class="splitBar">
	<td></td>
	{if $is_view_name == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
	{if $is_view_reg_date == true}<td class="hidden-xs"></td><td class="hidden-xs"></td>{/if}
</tr>
{/foreach}
<tr class="sectionEnd">
	{if $is_view_loopnum == true}<td class="hidden-xs"><div></div></td><td class="hidden-xs"><div></div></td>{/if}
	<td><div></div></td>
	{if $is_view_name == true}<td class="hidden-xs"><div></div></td><td class="hidden-xs"><div></div></td>{/if}
	{if $is_view_reg_date == true}<td class="hidden-xs"><div></div></td><td class="hidden-xs"><div></div></td>{/if}
</tr>
</table>

<div class="height10"></div>

<div style="margin-bottom:10px; padding:0px 5px;" class="visible-xs">
	<a class="btn btn-success btn-sm btn-block" href="{$link.post}">{if $setup.use_select == 'TRUE'}새 질문작성하기{else}새글 작성하기{/if}</a>
</div>

<div class="padding5">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<tr>
		<td style="width:50%;" class="visible-xs"></td>
		<td style="width:294px;">
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
		<td style="width:50%;" class="visible-xs"></td>
		<td style="width:100%;" class="hidden-xs right">
			<a class="btn btn-success btn-sm" href="{$link.post}">{if $setup.use_select == 'TRUE'}새 질문작성하기{else}새글 작성하기{/if}</a>
		</td>
	</tr>
	</table>
</div>

<div class="center">
	<ul class="pagination pagination-sm">
		<li{if $prevpage == false} class="disabled"{/if}><a href="{if $prevpage != false}{$link.page}{$prevpage}{else}javascript:void(0);{/if}">«</a></li>
		{foreach name=page from=$page item=page}
		<li{if $page == $p} class="active"{/if}><a href="{$link.page}{$page}">{$page}</a></li>
		{/foreach}
		<li{if $nextpage == false} class="disabled"{/if}><a href="{if $nextpage != false}{$link.page}{$nextpage}{else}javascript:void(0);{/if}">»</a></li>
	</ul>
</div>