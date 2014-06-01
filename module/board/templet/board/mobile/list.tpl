{if $smarty.env.isMobile == false}
	이 스킨은 모바일버전에서만 동작하도록 설계되었습니다.
{else}
	{if $categoryList}
	<div class="btn-group">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			{if $categoryName}{$categoryName}{else}카테고리{/if} <span class="caret"></span>
		</button>
		<ul class="dropdown-menu" role="menu">
			<li><a href="javascript:ListSelectCategory('','');">전체보기</a></li>
			<li class="divider"></li>
			{foreach from=$categoryList item=categoryList}
			<li><a href="javascript:ListSelectCategory('',{$categoryList.idx});">{$categoryList.category}</a></li>
			{/foreach}
		</ul>
	</div>
	{/if}
	
	{if $setup.use_select == 'TRUE'}
	<div class="btn-group">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			{if $select == 'true'}해결된질문{elseif $select == 'false'}미해결질문{elseif $select == 'my'}나의질문{else}전체질문{/if}  <span class="caret"></span>
		</button>
		<ul class="dropdown-menu" role="menu">
			<li><a href="javascript:ListSelectSelectAnswer('','');">전체질문</a></li>
			<li class="divider"></li>
			<li><a href="javascript:ListSelectSelectAnswer('','true');">해결된질문</a></li>
			<li><a href="javascript:ListSelectSelectAnswer('','false');">미해결질문</a></li>
			<li class="divider"></li>
			<li><a href="javascript:ListSelectSelectAnswer('','my');">나의질문</a></li>
		</ul>
	</div>
	{/if}
	
	{if $categoryList || $setup.use_select == 'TRUE'}<div class="height10"></div>{/if}
	
	<table class="table table-striped layoutfixed">
	<thead>
		<tr>
			{if $is_view_loopnum == true}<th style="width:80px;" class="hidden-xs">#</th>{/if}
			<th style="width:100%;">제목</th>
			{if $is_view_name == true}<th style="width:120px;" class="hidden-xs">작성자</th>{/if}
			{if $is_view_reg_date == true}<th style="width:100px;" class="hidden-xs">작성일</th>{/if}
		</tr>
	</thead>
	<tbody>
		{foreach name=list from=$notice item=notice}
		<tr class="warning">
			{if $is_view_loopnum == true}<td class="hidden-xs center">공지</td>{/if}
			<td>
				<a href="{$notice.postlink}">{$notice.title|cutstring:32:true}</a>
				{if $notice.ment != 0}<span class="label label-sm label-danger">{$notice.ment}{if $notice.is_newment == true}+{/if}</span>{/if}
				{if $notice.is_file == true}<img src="{$skinDir}/images/icon_file.gif" />{/if}
				{if $notice.is_image == true}<img src="{$skinDir}/images/icon_image.gif" />{/if}
			</td>
			{if $is_view_name == true}<td class="hidden-xs">{$notice.author.nickname}</td>{/if}
			{if $is_view_reg_date == true}<td class="hidden-xs">{if $time.server|date_format:"%Y%m%d" == $notice.reg_date|date_format:"%Y%m%d"}{$notice.reg_date|date_format:"%H:%M:%S"}{else}{$notice.reg_date|date_format:"%Y.%m.%d"}{/if}{if $notice.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</td>{/if}
		</tr>
		{/foreach}
		
		{foreach name=list from=$data item=data}
		<tr>
			{if $is_view_loopnum == true}<td class="hidden-xs center">{$data.loopnum|number_format}</td>{/if}
			<td>
				{if $data.is_mobile == true}<img src="{$skinDir}/images/icon_mobile.gif" alt="모바일" />{/if}
				{if $data.is_secret == true}<img src="{$skinDir}/images/icon_locked.gif" alt="비밀글" />{/if}
				<a href="{$data.postlink}">{$data.title|cutstring:32:true}</a>
				{if $data.ment != 0}<span class="label label-sm label-danger">{$data.ment}{if $data.is_newment == true}+{/if}</span>{/if}
				<span class="label label-warning label-sm hidden-xs pull-right" style="margin-right:2px;">{$data.hit|number_format}Hits</span>
				{if $data.is_file == true}<img src="{$skinDir}/images/icon_file.gif" />{/if}
				{if $data.is_image == true}<img src="{$skinDir}/images/icon_image.gif" />{/if}
				
				<div style="margin-top:5px; vertical-align:middle;" class="visible-xs">
					<span class="visible-xs pull-left">{$data.author.nickname}</span>
					<span class="label label-default label-sm visible-xs pull-right">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}{if $data.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</span>
					<span class="label label-warning label-sm pull-right" style="margin-right:2px;">{$data.hit|number_format}Hits</span>
				</div>
			</td>
			{if $is_view_name == true}<td class="hidden-xs textwrap">{$data.author.nickname}</td>{/if}
			{if $is_view_reg_date == true}<td class="hidden-xs">{if $time.server|date_format:"%Y%m%d" == $data.reg_date|date_format:"%Y%m%d"}{$data.reg_date|date_format:"%H:%M:%S"}{else}{$data.reg_date|date_format:"%Y.%m.%d"}{/if}{if $data.is_new == true}<img src="{$skinDir}/images/icon_new.png" />{/if}</td>{/if}
		</tr>
		{/foreach}
	</tbody>
	<tfoot>
		<tr>
			{if $is_view_loopnum == true}<td class="hidden-xs" style="height:1px !important; overflow:hidden; padding:0px;"></td>{/if}
			<td style="height:1px !important; overflow:hidden; padding:0px;"></td>
			{if $is_view_name == true}<td class="hidden-xs" style="height:1px !important; overflow:hidden; padding:0px;"></td>{/if}
			{if $is_view_reg_date == true}<td class="hidden-xs" style="height:1px !important; overflow:hidden; padding:0px;"></td>{/if}
		</tr>
	</tfoot>
	</table>

	{$searchFormStart}
	<div class="row">
		<div class="col-xs-7">
			<table cellpadding="0" cellspacing="0" class="layoutfixed">
			<col width="100%" /><col width="60" />
			<tr>
				<td><input type="text" name="keyword" class="form-control input-sm" placeholder="" value="{$keyword}"></td>
				<td style="padding-left:5px;">
					<input type="submit" value="검색" class="btn btn-primary btn-sm">
				</td>
			</tr>
			</table>
		</div>
		<div class="col-xs-5 right">
			<a class="btn btn-success btn-sm" href="{$link.post}">{if $setup.use_select == 'TRUE'}새 질문작성{else}새글 작성{/if}</a>
		</div>
	</div>
	{$searchFormEnd}
	
	<div class="center">
		<ul class="pagination pagination-sm">
			<li{if $prevpage == false} class="disabled"{/if}><a href="{if $prevpage != false}{$link.page}{$prevpage}{else}javascript:void(0);{/if}">«</a></li>
			{foreach name=page from=$page item=page}
			<li{if $page == $p} class="active"{/if}><a href="{$link.page}{$page}">{$page}</a></li>
			{/foreach}
			<li{if $nextpage == false} class="disabled"{/if}><a href="{if $nextpage != false}{$link.page}{$nextpage}{else}javascript:void(0);{/if}">»</a></li>
		</ul>
	</div>
{/if}