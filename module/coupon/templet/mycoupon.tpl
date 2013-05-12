<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="250" /><col width="100%" /><col width="5" />
<tr class="listbar">
	<td class="left"></td>
	<td class="center">쿠폰이미지</td>
	<td class="center">쿠폰설명</td>
	<td class="right"></td>
</tr>
{foreach name=list from=$data item=data}
<tr class="height5">
	<td colspan="4"></td>
</tr>
<tr class="listrow">
	<td colspan="2"><img src="{$data.image}" alt="{$data.title}"{if $data.use_date} class="usedcoupon"{/if} /></td>
	<td colspan="2">
		<div class="title">{$data.title}</div>
		<div class="infor">{$data.infor}</div>
		<div class="subinfor">쿠폰유효기간 : {if $data.expire_date == 0}무제한{else}{$data.expire_date|date_format:"%Y.%m.%d %H:%M:%S"}까지{/if} / 구매일 : {$data.buy_date|date_format:"%Y.%m.%d"}{if $data.use_date} / 사용일 : {$data.buy_date|date_format:"%Y.%m.%d"}{/if}</div>
	</td>
</tr>
<tr class="listsplit">
	<td colspan="4"><div></div></td>
</tr>
{/foreach}
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
	<tr>
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