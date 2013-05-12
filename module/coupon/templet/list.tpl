<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="5" /><col width="250" /><col width="100%" /><col width="80" /><col width="5" />
<tr class="listbar">
	<td class="left"></td>
	<td class="center">쿠폰이미지</td>
	<td class="center">쿠폰설명</td>
	<td class="center">포인트</td>
	<td class="right"></td>
</tr>
{foreach name=list from=$data item=data}
<tr class="height5">
	<td colspan="5"></td>
</tr>
<tr class="listrow">
	<td colspan="2"><img src="{$data.image}" alt="{$data.title}" /></td>
	<td>
		<div class="title">{if $data.is_new == 'TRUE'}<img src="{$skinDir}/images/icon_new.gif" alt="신규" />{/if}{if $data.is_vote == 'TRUE'}<img src="{$skinDir}/images/icon_vote.gif" alt="추천" />{/if} {$data.title}</div>
		<div class="infor">{$data.infor}</div>
		<div class="subinfor">쿠폰유효기간 : {if $data.expire == 0}무제한{else}구매후 {$data.expire}일 후 까지{/if} / 남은수량 : {if $data.ea == 0}매진{else}{$data.ea|number_format}개{/if}{if $data.is_buy == 'TRUE'} / 쿠폰보유중{/if}</div>
	</td>
	<td colspan="2">
		<div class="point"><span>{$data.point|number_format}</span> Point</div>
		<div class="button"><img src="{$skinDir}/images/btn_buy.gif" alt="구매하기" onclick="{$data.action.buy}" /></div>
	</td>
</tr>
<tr class="listsplit">
	<td colspan="5"><div></div></td>
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