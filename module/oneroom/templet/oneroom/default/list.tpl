<div class="titleBar">
	<table cellpadding="0" cellspacing="0" class="layoutfixed">
	<col width="200" /><col width="100%" />
	<tr>
		<td><img src="{$skinDir}/images/list_title.gif" /></td>
		<td class="right">최근등록순 | 낮은전세순 | 낮은월세순 | 낮은보증금순 | 큰면적순</td>
	</tr>
	</table>
</div>
<table cellpadding="0" cellspacing="0" class="layoutfixed">
<col width="120" /><col width="100%" />
{foreach name=list from=$data item=data}
	{if $smarty.foreach.list.index % 2 == 0}<tr class="listGray">{else}<tr>{/if}
		<td class="vTop"><div class="listImage"><a href="{$data.itemlink}"><img src="{$data.image}" /></a></div></td>
		<td class="vTop">
			<div class="listTitle"><a href="{$data.itemlink}">{$data.title}</a></div>
			<div class="listInfo">
				<div class="listCategory">{$data.category}</div>
				{if $data.is_rent_all == 'TRUE'}<div class="priceRentAll">{$data.price_rent_all|number_format}만</div>{/if}
				{if $data.is_rent_month == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_deposit|number_format}만/{$data.price_rent_month|number_format}만</div>{/if}
				{if $data.is_rent_short == 'TRUE'}<div class="priceRentMonth">{$data.price_rent_month|number_format}만(단기)</div>{/if}
				{if $data.is_buy == 'TRUE'}<div class="priceBuy">{$data.price_buy|number_format}만</div>{/if}
			</div>
			<div class="listInfo">
				<span class="listRegion">{$data.region}</span>
				{if $data.subway}/ <span class="pointText3 bold">{$data.subway} {$data.subway_distance}분 거리</span>{/if}
				{if $data.university}/ <span class="pointText2 bold">{$data.university}</span>{/if}
			</div>
			<div class="listInfo">
				{$data.areasize} / 
				{$data.floor}{if $data.is_double == 'TRUE'}<span class="pointText1">(복층)</span>{/if}
				/ 방 {$data.rooms}개 / {if $data.parkings > 0}주차공간 {$data.parkings}대{else}주차공간없음{/if}
				/ 관리비 {if $data.price_maintenance > 0}{$data.price_maintenance}만/월{else}없음{/if}
			</div>
		</td>
	</tr>
	<tr class="listRow"><td colspan="2"></td></tr>
{/foreach}

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
	<td class="pageinfo"><span class="bold">{$totalitem}</span> items / <span style="color:#EF5900;">{$p}</span> of {$totalpage} page{if $totalpage > 1}s{/if}</td>
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